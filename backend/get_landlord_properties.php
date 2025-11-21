<?php
header('Content-Type: application/json');
include 'connect.php'; // Include your database connection

$response = array('success' => false, 'message' => '', 'properties' => []);

// Check if landlord_id is provided in the query string
if (isset($_GET['userId'])) {
    $landlord_id = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);

    if (empty($landlord_id)) {
        $response['message'] = 'Landlord ID is missing or invalid.';
        echo json_encode($response);
        exit();
    }

    // Prepare a statement to fetch properties for the given landlord_id
    $stmt = $conn->prepare("SELECT p.*, pi.image_url AS main_image_url 
                            FROM properties p
                            LEFT JOIN property_images pi ON p.property_id = pi.property_id AND pi.is_main = TRUE
                            WHERE p.landlord_id = ?");
    
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("i", $landlord_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
        $response['success'] = true;
        $response['properties'] = $properties;
    } else {
        $response['message'] = 'No properties found for this landlord.';
        // Success is still true if no properties are found, it's just an empty list
        $response['success'] = true; 
    }

    $stmt->close();
} else {
    $response['message'] = 'No landlord ID provided.';
}

$conn->close();
echo json_encode($response);
?>
