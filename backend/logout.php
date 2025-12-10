<?php

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$_SESSION = array();


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear all session data
session_unset();
session_destroy();

// Start a new session to ensure clean state
session_start();
session_regenerate_id(true);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
exit();
?>

