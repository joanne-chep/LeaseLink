<?php
include("connect.php");

header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT DISTINCT p.city 
                        FROM properties p
                        INNER JOIN users u ON p.landlord_id = u.user_id
                        WHERE p.status = 'available' AND u.approval_status = 'approved' AND u.user_type = 'landlord' 
                        AND p.city IS NOT NULL AND p.city != ''
                        ORDER BY p.city ASC");
$stmt->execute();
$result = $stmt->get_result();

$cities = [];
while ($row = $result->fetch_assoc()) {
    $cities[] = $row['city'];
}

echo json_encode(['success' => true, 'cities' => $cities]);

$stmt->close();
$conn->close();
?>

