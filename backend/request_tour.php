<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_NUMBER_INT);
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_SANITIZE_NUMBER_INT);
    $requested_date = filter_input(INPUT_POST, 'requested_date', FILTER_SANITIZE_STRING);
    $requested_time = filter_input(INPUT_POST, 'requested_time', FILTER_SANITIZE_STRING);
    $client_notes = filter_input(INPUT_POST, 'client_notes', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($client_id) || empty($property_id) || empty($requested_date) || empty($requested_time)) {
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
        $response['message'] = 'Invalid user. Only clients can request tours.';
        echo json_encode($response);
        exit();
    }

    // Validate property exists
    $property_check = $conn->prepare("SELECT property_id, status FROM properties WHERE property_id = ?");
    $property_check->bind_param('i', $property_id);
    $property_check->execute();
    $property_result = $property_check->get_result();
    $property = $property_result->fetch_assoc();
    
    if (!$property) {
        $response['message'] = 'Property not found.';
        echo json_encode($response);
        exit();
    }

    // Combine date and time
    $requested_date_time = $requested_date . ' ' . $requested_time . ':00';
    
    
    $min_time = time() + 3600; 
    if (strtotime($requested_date_time) < $min_time) {
        $response['message'] = 'Requested date and time must be at least 1 hour from now.';
        echo json_encode($response);
        exit();
    }

    // Insert into view_requests table
    $stmt = $conn->prepare("INSERT INTO view_requests (client_id, property_id, requested_date_time, status, client_notes) VALUES (?, ?, ?, 'pending', ?)");
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param('iiss', $client_id, $property_id, $requested_date_time, $client_notes);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Tour request submitted successfully!';
    } else {
        $response['message'] = 'Failed to submit tour request: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

