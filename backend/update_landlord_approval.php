<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
    $landlord_id = filter_input(INPUT_POST, 'landlord_id', FILTER_SANITIZE_NUMBER_INT);
    $approval_status = filter_input(INPUT_POST, 'approval_status', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($admin_id) || empty($landlord_id) || empty($approval_status)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

    // Validate approval_status
    if (!in_array($approval_status, ['approved', 'rejected'])) {
        $response['message'] = 'Invalid approval status. Must be "approved" or "rejected".';
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
        $response['message'] = 'Invalid user. Only admins can update landlord approval status.';
        echo json_encode($response);
        exit();
    }

    // Verify landlord exists
    $landlord_check = $conn->prepare("SELECT user_type, approval_status FROM users WHERE user_id = ?");
    $landlord_check->bind_param('i', $landlord_id);
    $landlord_check->execute();
    $landlord_result = $landlord_check->get_result();
    $landlord = $landlord_result->fetch_assoc();
    
    if (!$landlord || $landlord['user_type'] !== 'landlord') {
        $response['message'] = 'Invalid landlord ID.';
        echo json_encode($response);
        exit();
    }

    // Update approval status
    $stmt = $conn->prepare("UPDATE users SET approval_status = ? WHERE user_id = ? AND user_type = 'landlord'");
    $stmt->bind_param('si', $approval_status, $landlord_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Landlord approval status updated successfully.';
    } else {
        $response['message'] = 'Failed to update approval status: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

