<?php
header('Content-Type: application/json');

include("connect.php");

if (!isset($conn) || !$conn) {
    echo json_encode(['error' => 'Database connection failed. Please check your database settings.']);
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $property = $result->fetch_assoc();

       
        $images_stmt = $conn->prepare("SELECT image_url, description, is_main FROM property_images WHERE property_id = ? ORDER BY is_main DESC, image_id ASC");
        $images_stmt->bind_param('i', $id);
        $images_stmt->execute();
        $images_result = $images_stmt->get_result();
        
        $images = [];
        while ($img_row = $images_result->fetch_assoc()) {
            $images[] = $img_row;
        }
        $images_stmt->close();

        $property['images'] = $images; 
        echo json_encode($property);
    } else {
        echo json_encode(['error' => 'Property not found']);
    }
} else {
    echo json_encode(['error' => 'No property ID provided']);
}
?>
