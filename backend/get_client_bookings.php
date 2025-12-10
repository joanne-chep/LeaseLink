<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'bookings' => []);

if (isset($_GET['userId'])) {
    $client_id = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);

    if (empty($client_id)) {
        $response['message'] = 'Client ID is missing or invalid.';
        echo json_encode($response);
        exit();
    }

   
    $stmt = $conn->prepare("SELECT b.booking_id, b.property_id, b.start_date, b.end_date, b.monthly_rent, b.security_deposit, b.status, b.created_at,
                            p.title, p.address, p.city, p.currency
                            FROM bookings b
                            JOIN properties p ON b.property_id = p.property_id
                            WHERE b.client_id = ?
                            ORDER BY b.created_at DESC");
    
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        $response['success'] = true;
        $response['bookings'] = $bookings;
    } else {
        $response['success'] = true;
        $response['message'] = 'No bookings found.';
    }

    $stmt->close();
} else {
    $response['message'] = 'No client ID provided.';
}

$conn->close();
echo json_encode($response);
?>

