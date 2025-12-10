<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'properties' => []);

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
        $response['message'] = 'Invalid user. Only admins can access property list.';
        echo json_encode($response);
        exit();
    }

    
    $stmt = $conn->prepare("SELECT p.property_id, p.title, p.address, p.city, p.status, p.landlord_id, p.created_at,
                            u.username AS landlord_name, u.email AS landlord_email
                            FROM properties p
                            JOIN users u ON p.landlord_id = u.user_id
                            ORDER BY p.created_at DESC");
    
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

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
        $response['success'] = true;
        $response['message'] = 'No properties found.';
    }

    $stmt->close();
} else {
    $response['message'] = 'No admin ID provided.';
}

$conn->close();
echo json_encode($response);
?>

