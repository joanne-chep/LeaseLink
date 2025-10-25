<?php
include("connect.php");

$stmt = $conn->prepare("SELECT * FROM properties ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

echo json_encode($properties);
?>

