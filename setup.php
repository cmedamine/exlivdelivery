<?php
/**
 * One-time initial moderator setup.
 * This page is permanently disabled after a moderator account exists.
 */
session_start();
require_once __DIR__ . '/config.php';

try {
    $adminExists = (bool) $bdd->query("SELECT 1 FROM users WHERE type = 'moderator' LIMIT 1")->fetchColumn();
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database tables are unavailable. Import database_schema.sql before opening this page.');
}

if ($adminExists) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['setup_csrf'])) {
    $_SESSION['setup_csrf'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf_token'] ?? '');
    $fullname = trim((string) ($_POST['fullname'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!hash_equals($_SESSION['setup_csrf'], $token)) {
        $error = 'Session expired. Refresh the page and try again.';
    } elseif ($fullname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a name and a valid email address.';
    } elseif (strlen($password) < 12) {
        $error = 'Choose a password with at least 12 characters.';
    } elseif ($password !== $confirmation) {
        $error = 'The password confirmation does not match.';
    } else {
        try {
            $create = $bdd->prepare(
                "INSERT INTO users (fullname, picture, email, password, phone, type, roles, active, datesignup, trash)
                 VALUES (?, 'avatar.png', ?, ?, NULL, 'moderator', 'all', 'on', ?, '1')"
            );
            $create->execute([$fullname, $email, hash_password($password), time()]);

            $_SESSION['id'] = (int) $bdd->lastInsertId();
            $_SESSION['fullname'] = $fullname;
            $_SESSION['picture'] = 'avatar.png';
            $_SESSION['phone'] = null;
            $_SESSION['email'] = $email;
            $_SESSION['roles'] = 'all';
            $_SESSION['type'] = 'moderator';
            session_regenerate_id(true);

            unset($_SESSION['setup_csrf']);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Unable to create the administrator. The email address may already be in use.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Set up administrator</title>
    <style>
        body { align-items: center; background: #f4f6fb; color: #1f2937; display: flex; font-family: system-ui, sans-serif; justify-content: center; margin: 0; min-height: 100vh; }
        main { background: #fff; border-radius: 12px; box-shadow: 0 10px 35px #1118271a; max-width: 420px; padding: 32px; width: calc(100% - 32px); }
        h1 { margin-top: 0; } label { display: block; font-weight: 600; margin-top: 16px; }
        input { box-sizing: border-box; margin-top: 6px; padding: 10px; width: 100%; }
        button { background: #2563eb; border: 0; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 700; margin-top: 24px; padding: 12px; width: 100%; }
        .error { background: #fef2f2; color: #b91c1c; padding: 10px; }
    </style>
</head>
<body>
    <main>
        <h1>Create the administrator</h1>
        <p>This page works only until the first moderator account is created.</p>
        <?php if ($error !== ''): ?><p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['setup_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
            <label>Full name<input name="fullname" required maxlength="255"></label>
            <label>Email<input name="email" type="email" required maxlength="255"></label>
            <label>Password<input name="password" type="password" required minlength="12" autocomplete="new-password"></label>
            <label>Confirm password<input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password"></label>
            <button type="submit">Create administrator</button>
        </form>
    </main>
</body>
</html>
