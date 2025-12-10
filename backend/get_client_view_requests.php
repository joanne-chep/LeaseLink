<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'requests' => []);

if (isset($_GET['userId'])) {
    $client_id = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);

    if (empty($client_id)) {
        $response['message'] = 'Client ID is missing or invalid.';
        echo json_encode($response);
        exit();
    }

    
    $stmt = $conn->prepare("SELECT vr.request_id, vr.property_id, vr.requested_date_time, vr.status, vr.landlord_notes, vr.client_notes, vr.created_at,
                            p.title, p.address, p.city, p.rent_price, p.currency
                            FROM view_requests vr
                            JOIN properties p ON vr.property_id = p.property_id
                            WHERE vr.client_id = ?
                            ORDER BY vr.created_at DESC");
    
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $response['success'] = true;
        $response['requests'] = $requests;
    } else {
        $response['success'] = true;
        $response['message'] = 'No viewing requests found.';
    }

    $stmt->close();
} else {
    $response['message'] = 'No client ID provided.';
}

$conn->close();
echo json_encode($response);
?>

