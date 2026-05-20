<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie cleanly if active
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Vaporize the session file from the server system memory
session_destroy();

// Redirect back to the central landing index page
header("Location: index.php");
exit();
?>