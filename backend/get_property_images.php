<?php
include("connect.php");

header('Content-Type: application/json');

if (isset($_GET['property_id'])) {
    $property_id = intval($_GET['property_id']);
    
    $stmt = $conn->prepare("SELECT image_url, description, is_main FROM property_images WHERE property_id = ? ORDER BY is_main DESC, image_id ASC");
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $stmt->close();
    
 
    if (empty($images)) {
        $property_stmt = $conn->prepare("SELECT main_image_url FROM properties WHERE property_id = ?");
        $property_stmt->bind_param('i', $property_id);
        $property_stmt->execute();
        $property_result = $property_stmt->get_result();
        $property_row = $property_result->fetch_assoc();
        if ($property_row && !empty($property_row['main_image_url'])) {
            $images[] = [
                'image_url' => $property_row['main_image_url'],
                'description' => 'Main property image',
                'is_main' => true
            ];
        }
        $property_stmt->close();
    }
    
    echo json_encode(['success' => true, 'images' => $images]);
} else {
    echo json_encode(['success' => false, 'message' => 'Property ID required', 'images' => []]);
}

$conn->close();
?>

