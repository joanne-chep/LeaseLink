<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'users' => []);

if (isset($_GET['userId'])) {
    $admin_id = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);

    if (empty($admin_id)) {
        $response['message'] = 'Admin ID is missing or invalid.';
        echo json_encode($response);
        exit();
    }

    // Validate user is an admin
    $user_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $user_check->bind_param('i', $admin_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user || $user['user_type'] !== 'admin') {
        $response['message'] = 'Invalid user. Only admins can access user list.';
        echo json_encode($response);
        exit();
    }

    // Fetch all users
    $stmt = $conn->prepare("SELECT user_id, username, email, user_type, first_name, last_name, created_at FROM users ORDER BY created_at DESC");
    
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $response['success'] = true;
        $response['users'] = $users;
    } else {
        $response['success'] = true;
        $response['message'] = 'No users found.';
    }

    $stmt->close();
} else {
    $response['message'] = 'No admin ID provided.';
}

$conn->close();
echo json_encode($response);
?>

