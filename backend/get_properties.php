<?php
header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    include("connect.php");

   
    if (!isset($conn) || !$conn) {
        $error_msg = mysqli_connect_error() ?: 'Unknown database connection error';
        echo json_encode(['error' => 'Database connection failed: ' . $error_msg]);
        exit;
    }

   
    $stmt = $conn->prepare("SELECT p.*, 
                            COALESCE(pi.image_url, p.main_image_url) AS main_image_url 
                            FROM properties p
                            LEFT JOIN property_images pi ON p.property_id = pi.property_id AND pi.is_main = TRUE
                            INNER JOIN users u ON p.landlord_id = u.user_id
                            WHERE p.status = 'available' AND u.approval_status = 'approved' AND u.user_type = 'landlord'
                            ORDER BY p.created_at DESC");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
        exit;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
    
    $stmt->close();

    echo json_encode($properties);
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

