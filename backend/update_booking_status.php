<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $landlord_id = filter_input(INPUT_POST, 'landlord_id', FILTER_SANITIZE_NUMBER_INT);
    $lease_agreement_url = filter_input(INPUT_POST, 'lease_agreement_url', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($booking_id) || empty($status) || empty($landlord_id)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

    // Validate status
    if (!in_array($status, ['approved', 'rejected'])) {
        $response['message'] = 'Invalid status. Must be "approved" or "rejected".';
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
        $response['message'] = 'Invalid user. Only landlords can update booking status.';
        echo json_encode($response);
        exit();
    }

    // Verify the booking belongs to a property owned by this landlord
    $verify_stmt = $conn->prepare("SELECT b.booking_id, b.property_id FROM bookings b
                                    JOIN properties p ON b.property_id = p.property_id
                                    WHERE b.booking_id = ? AND p.landlord_id = ?");
    $verify_stmt->bind_param('ii', $booking_id, $landlord_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $booking = $verify_result->fetch_assoc();
    
    if (!$booking) {
        $response['message'] = 'Booking not found or you do not have permission to update it.';
        echo json_encode($response);
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update the booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, lease_agreement_url = ?, updated_at = CURRENT_TIMESTAMP WHERE booking_id = ?");
        if ($stmt === false) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }

        $stmt->bind_param('ssi', $status, $lease_agreement_url, $booking_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update booking status: ' . $stmt->error);
        }

        // If approved, update property status to 'rented'
        if ($status === 'approved') {
            $property_update = $conn->prepare("UPDATE properties SET status = 'rented', updated_at = CURRENT_TIMESTAMP WHERE property_id = ?");
            if ($property_update === false) {
                throw new Exception('Failed to prepare property update: ' . $conn->error);
            }
            $property_update->bind_param('i', $booking['property_id']);
            if (!$property_update->execute()) {
                throw new Exception('Failed to update property status: ' . $property_update->error);
            }
            $property_update->close();
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Booking status updated successfully.';
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }

    if (isset($stmt)) {
        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

