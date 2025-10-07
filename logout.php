<?php
// 1. Start the session to access it.
require 'config.php';

// 2. Unset all session variables.
$_SESSION = [];

// 3. (NEW) Delete the session cookie from the browser.
// This is an extra step to ensure a clean logout.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session on the server.
session_destroy();

// 5. Redirect to the login page.
header('Location: login.php');
exit;
