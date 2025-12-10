<?php

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    @include 'connect.php';

    $response = [
        'isLoggedIn' => isset($_SESSION['user_id']),
        'userId' => $_SESSION['user_id'] ?? null,
        'userName' => $_SESSION['username'] ?? null,
        'userType' => $_SESSION['user_type'] ?? null
    ];

    echo json_encode($response);
} catch (Exception $e) {
    
    $response = [
        'isLoggedIn' => isset($_SESSION['user_id']),
        'userId' => $_SESSION['user_id'] ?? null,
        'userName' => $_SESSION['username'] ?? null,
        'userType' => $_SESSION['user_type'] ?? null,
        'error' => 'Session info retrieved but server error occurred'
    ];
    echo json_encode($response);
}
exit();
?>


