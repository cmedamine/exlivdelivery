<?php
/**
 * Security helpers for the server-rendered public authentication forms.
 * These intentionally do not depend on the retired React API endpoints.
 */

function public_auth_csrf_token()
{
    if (empty($_SESSION['public_auth_csrf'])) {
        $_SESSION['public_auth_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['public_auth_csrf'];
}

function public_auth_valid_csrf($token)
{
    $expected = $_SESSION['public_auth_csrf'] ?? '';
    return is_string($token) && $expected !== '' && hash_equals($expected, $token);
}

function public_auth_rate_limit_config()
{
    return [
        'maxAttempts' => max(1, (int) ($_ENV['AUTH_LOGIN_MAX_ATTEMPTS'] ?? 5)),
        'windowSeconds' => max(60, (int) ($_ENV['AUTH_LOGIN_WINDOW_SECONDS'] ?? 900)),
        'blockSeconds' => max(60, (int) ($_ENV['AUTH_LOGIN_BLOCK_SECONDS'] ?? 900)),
    ];
}

function public_auth_rate_limit_keys($email)
{
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return [
        'ip:' . hash('sha256', $ipAddress),
        'email:' . hash('sha256', strtolower(trim($email))),
    ];
}

function public_auth_has_rate_limit_table(PDO $database)
{
    static $available = null;
    if ($available !== null) {
        return $available;
    }

    try {
        $database->query('SELECT 1 FROM auth_rate_limits LIMIT 1');
        $available = true;
    } catch (PDOException $exception) {
        try {
            $database->exec('CREATE TABLE IF NOT EXISTS auth_rate_limits (rate_key VARCHAR(80) NOT NULL, window_started INT NOT NULL, attempts INT NOT NULL DEFAULT 0, blocked_until INT NOT NULL DEFAULT 0, updated_at INT NOT NULL, PRIMARY KEY (rate_key), KEY auth_rate_limits_updated_at (updated_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            $available = true;
        } catch (PDOException $creationException) {
            // A session-level limiter remains available if the database user
            // cannot apply the migration automatically.
            $available = false;
        }
    }

    return $available;
}

function public_auth_login_block_remaining(PDO $database, $email)
{
    $now = time();
    if (public_auth_has_rate_limit_table($database)) {
        $keys = public_auth_rate_limit_keys($email);
        $statement = $database->prepare('SELECT blocked_until FROM auth_rate_limits WHERE rate_key IN (?, ?)');
        $statement->execute($keys);
        $blockedUntil = 0;
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $blockedUntil = max($blockedUntil, (int) $row['blocked_until']);
        }
        return $blockedUntil > $now ? $blockedUntil - $now : 0;
    }

    $limiter = $_SESSION['public_auth_login_limiter'] ?? [];
    return (($limiter['blockedUntil'] ?? 0) > $now) ? (int) $limiter['blockedUntil'] - $now : 0;
}

function public_auth_record_login_failure(PDO $database, $email)
{
    $config = public_auth_rate_limit_config();
    $now = time();
    if (!public_auth_has_rate_limit_table($database)) {
        $limiter = $_SESSION['public_auth_login_limiter'] ?? ['startedAt' => $now, 'attempts' => 0, 'blockedUntil' => 0];
        if ($now - (int) $limiter['startedAt'] >= $config['windowSeconds']) {
            $limiter = ['startedAt' => $now, 'attempts' => 0, 'blockedUntil' => 0];
        }
        $limiter['attempts']++;
        if ($limiter['attempts'] >= $config['maxAttempts']) {
            $limiter['blockedUntil'] = $now + $config['blockSeconds'];
        }
        $_SESSION['public_auth_login_limiter'] = $limiter;
        return;
    }

    $database->beginTransaction();
    try {
        $select = $database->prepare('SELECT window_started, attempts FROM auth_rate_limits WHERE rate_key = ? FOR UPDATE');
        $insert = $database->prepare('INSERT INTO auth_rate_limits(rate_key, window_started, attempts, blocked_until, updated_at) VALUES (?, ?, ?, ?, ?)');
        $update = $database->prepare('UPDATE auth_rate_limits SET window_started = ?, attempts = ?, blocked_until = ?, updated_at = ? WHERE rate_key = ?');
        foreach (public_auth_rate_limit_keys($email) as $key) {
            $select->execute([$key]);
            $row = $select->fetch(PDO::FETCH_ASSOC);
            $windowStarted = $row ? (int) $row['window_started'] : $now;
            $attempts = $row ? (int) $row['attempts'] : 0;
            if ($now - $windowStarted >= $config['windowSeconds']) {
                $windowStarted = $now;
                $attempts = 0;
            }
            $attempts++;
            $blockedUntil = $attempts >= $config['maxAttempts'] ? $now + $config['blockSeconds'] : 0;
            if ($row) {
                $update->execute([$windowStarted, $attempts, $blockedUntil, $now, $key]);
            } else {
                $insert->execute([$key, $windowStarted, $attempts, $blockedUntil, $now]);
            }
        }
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function public_auth_clear_login_failures(PDO $database, $email)
{
    unset($_SESSION['public_auth_login_limiter']);
    if (!public_auth_has_rate_limit_table($database)) {
        return;
    }

    $statement = $database->prepare('DELETE FROM auth_rate_limits WHERE rate_key IN (?, ?)');
    $statement->execute(public_auth_rate_limit_keys($email));
}
