<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'landlords' => []);

if (isset($_GET['userId'])) {
    $admin_id = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);

    if (empty($admin_id)) {
        $response['message'] = 'Admin ID is missing or invalid.';
        echo json_encode($response);
        exit();
    }

    $user_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $user_check->bind_param('i', $admin_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user || $user['user_type'] !== 'admin') {
        $response['message'] = 'Invalid user. Only admins can access pending landlords.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name, phone_number, profile_picture_url, id_document_url, ownership_document_url, approval_status, created_at FROM users WHERE user_type = 'landlord' AND approval_status = 'pending' ORDER BY created_at DESC");
    
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $landlords = [];
        while ($row = $result->fetch_assoc()) {
            $landlords[] = $row;
        }
        $response['success'] = true;
        $response['landlords'] = $landlords;
    } else {
        $response['success'] = true;
        $response['message'] = 'No pending landlords found.';
    }

    $stmt->close();
} else {
    $response['message'] = 'No admin ID provided.';
}

$conn->close();
echo json_encode($response);
?>

