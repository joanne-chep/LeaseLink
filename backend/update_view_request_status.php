<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $landlord_id = filter_input(INPUT_POST, 'landlord_id', FILTER_SANITIZE_NUMBER_INT);
    $landlord_notes = filter_input(INPUT_POST, 'landlord_notes', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($request_id) || empty($status) || empty($landlord_id)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

    // Validate status
    if (!in_array($status, ['approved', 'denied'])) {
        $response['message'] = 'Invalid status. Must be "approved" or "denied".';
        echo json_encode($response);
        exit();
    }

    // Validate user is a landlord
    $user_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $user_check->bind_param('i', $landlord_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user || $user['user_type'] !== 'landlord') {
        $response['message'] = 'Invalid user. Only landlords can update request status.';
        echo json_encode($response);
        exit();
    }

    // Verify the request belongs to a property owned by this landlord
    $verify_stmt = $conn->prepare("SELECT vr.request_id FROM view_requests vr
                                    JOIN properties p ON vr.property_id = p.property_id
                                    WHERE vr.request_id = ? AND p.landlord_id = ?");
    $verify_stmt->bind_param('ii', $request_id, $landlord_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $response['message'] = 'Request not found or you do not have permission to update it.';
        echo json_encode($response);
        exit();
    }

    // Update the view request status
    $stmt = $conn->prepare("UPDATE view_requests SET status = ?, landlord_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE request_id = ?");
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param('ssi', $status, $landlord_notes, $request_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'View request status updated successfully.';
    } else {
        $response['message'] = 'Failed to update request status: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

