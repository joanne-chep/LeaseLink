<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($admin_id) || empty($property_id) || empty($status)) {
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
        $response['message'] = 'Invalid user. Only admins can update property status.';
        echo json_encode($response);
        exit();
    }

    // Validate status
    if (!in_array($status, ['available', 'inactive', 'pending', 'rented'])) {
        $response['message'] = 'Invalid status. Must be "available", "inactive", "pending", or "rented".';
        echo json_encode($response);
        exit();
    }

    // Check if property exists
    $property_check = $conn->prepare("SELECT property_id FROM properties WHERE property_id = ?");
    $property_check->bind_param('i', $property_id);
    $property_check->execute();
    $property_result = $property_check->get_result();
    
    if ($property_result->num_rows === 0) {
        $response['message'] = 'Property not found.';
        echo json_encode($response);
        exit();
    }

    // Update property status
    $stmt = $conn->prepare("UPDATE properties SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE property_id = ?");
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param('si', $status, $property_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Property status updated successfully.';
    } else {
        $response['message'] = 'Failed to update property status: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

