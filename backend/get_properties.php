<?php
include("connect.php");

// Join with property_images to get the main image URL
$stmt = $conn->prepare("SELECT p.*, pi.image_url AS main_image_url 
                        FROM properties p
                        LEFT JOIN property_images pi ON p.property_id = pi.property_id AND pi.is_main = TRUE
                        ORDER BY p.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

echo json_encode($properties);
?>

