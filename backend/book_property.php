<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_NUMBER_INT);
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_SANITIZE_NUMBER_INT);
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
    $security_deposit = filter_input(INPUT_POST, 'security_deposit', FILTER_VALIDATE_FLOAT);

    // Validate required fields
    if (empty($client_id) || empty($property_id) || empty($start_date) || empty($end_date)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

    // Validate user is a client
    $user_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $user_check->bind_param('i', $client_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user || $user['user_type'] !== 'client') {
        $response['message'] = 'Invalid user. Only clients can book properties.';
        echo json_encode($response);
        exit();
    }

    // Validate property exists and is available
    $property_check = $conn->prepare("SELECT property_id, rent_price, status FROM properties WHERE property_id = ?");
    $property_check->bind_param('i', $property_id);
    $property_check->execute();
    $property_result = $property_check->get_result();
    $property = $property_result->fetch_assoc();
    
    if (!$property) {
        $response['message'] = 'Property not found.';
        echo json_encode($response);
        exit();
    }

    if ($property['status'] !== 'available') {
        $response['message'] = 'Property is not available for booking.';
        echo json_encode($response);
        exit();
    }

    // Validate dates
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    
    if ($start_timestamp === false || $end_timestamp === false) {
        $response['message'] = 'Invalid date format.';
        echo json_encode($response);
        exit();
    }

    if ($start_timestamp >= $end_timestamp) {
        $response['message'] = 'End date must be after start date.';
        echo json_encode($response);
        exit();
    }

    if ($start_timestamp < time()) {
        $response['message'] = 'Start date must be in the future.';
        echo json_encode($response);
        exit();
    }

    $monthly_rent = $property['rent_price'];
    if (empty($security_deposit)) {
        $security_deposit = null;
    }

    // Insert into bookings table
    $stmt = $conn->prepare("INSERT INTO bookings (client_id, property_id, start_date, end_date, monthly_rent, security_deposit, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param('iissdd', $client_id, $property_id, $start_date, $end_date, $monthly_rent, $security_deposit);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Booking request submitted successfully!';
    } else {
        $response['message'] = 'Failed to submit booking request: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

