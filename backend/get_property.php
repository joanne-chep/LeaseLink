<?php
include("connect.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $property = $result->fetch_assoc();
        echo json_encode($property);
    } else {
        echo json_encode(['error' => 'Property not found']);
    }
} else {
    echo json_encode(['error' => 'No property ID provided']);
}
?>

