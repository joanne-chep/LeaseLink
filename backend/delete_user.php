<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($admin_id) || empty($user_id)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

    $admin_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $admin_check->bind_param('i', $admin_id);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    $admin = $admin_result->fetch_assoc();
    
    if (!$admin || $admin['user_type'] !== 'admin') {
        $response['message'] = 'Invalid user. Only admins can delete users.';
        echo json_encode($response);
        exit();
    }

    if ($admin_id == $user_id) {
        $response['message'] = 'You cannot delete your own admin account.';
        echo json_encode($response);
        exit();
    }

    $user_check = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $user_check->bind_param('i', $user_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    
    if ($user_result->num_rows === 0) {
        $response['message'] = 'User not found.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User deleted successfully.';
    } else {
        $response['message'] = 'Failed to delete user: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

