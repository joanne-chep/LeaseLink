<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $user_type = filter_input(INPUT_POST, 'user_type', FILTER_SANITIZE_STRING);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($admin_id) || empty($user_id) || empty($username) || empty($email) || empty($user_type)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

    // Validate admin access
    $admin_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $admin_check->bind_param('i', $admin_id);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    $admin = $admin_result->fetch_assoc();
    
    if (!$admin || $admin['user_type'] !== 'admin') {
        $response['message'] = 'Invalid user. Only admins can update users.';
        echo json_encode($response);
        exit();
    }

    // Validate user_type
    if (!in_array($user_type, ['client', 'landlord', 'admin'])) {
        $response['message'] = 'Invalid user type.';
        echo json_encode($response);
        exit();
    }

    // Check if email is already taken by another user
    $email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $email_check->bind_param('si', $email, $user_id);
    $email_check->execute();
    $email_result = $email_check->get_result();
    
    if ($email_result->num_rows > 0) {
        $response['message'] = 'Email already taken by another user.';
        echo json_encode($response);
        exit();
    }

    // Update user
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, user_type = ?, first_name = ?, last_name = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param('sssssi', $username, $email, $user_type, $first_name, $last_name, $user_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User updated successfully.';
    } else {
        $response['message'] = 'Failed to update user: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

