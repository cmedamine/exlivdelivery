<?php
/**
 * Unified browser logout route.
 *
 * PHP owns the authenticated session; after clearing it, return to the PHP
 * login page.
 */

session_start();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

foreach (['id', 'fullname', 'picture', 'phone', 'email', 'roles', 'type', 'upuser', 'rememberme'] as $cookieName) {
    setcookie($cookieName, '', time() - 3600, '/');
}

session_destroy();
header('Location: login.php', true, 302);
exit;
