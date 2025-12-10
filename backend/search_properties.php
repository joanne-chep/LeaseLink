<?php
include("connect.php");

header('Content-Type: application/json');


$area = isset($_GET['area']) ? trim($_GET['area']) : '';
$city = isset($_GET['city']) ? trim($_GET['city']) : '';


$query = "SELECT p.*, 
          COALESCE(pi.image_url, p.main_image_url) AS main_image_url 
          FROM properties p
          LEFT JOIN property_images pi ON p.property_id = pi.property_id AND pi.is_main = TRUE
          INNER JOIN users u ON p.landlord_id = u.user_id
          WHERE p.status = 'available' AND u.approval_status = 'approved' AND u.user_type = 'landlord'";

$params = [];
$types = '';


if (!empty($city)) {
    $query .= " AND (p.city LIKE ? OR p.address LIKE ? OR p.state_province LIKE ?)";
    $searchTerm = "%{$city}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}


if (!empty($area) && empty($city)) {
    $query .= " AND (p.city LIKE ? OR p.address LIKE ? OR p.state_province LIKE ?)";
    $searchTerm = "%{$area}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Query preparation failed: ' . $conn->error, 'properties' => []]);
    exit();
}


if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

echo json_encode(['success' => true, 'properties' => $properties, 'count' => count($properties)]);

$stmt->close();
$conn->close();
?>

