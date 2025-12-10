<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'profile' => null);

session_start();

if (isset($_GET['userId'])) {
    $landlord_id = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);
    
    $session_user_id = $_SESSION['user_id'] ?? null;
    $session_user_type = $_SESSION['user_type'] ?? null;
    
    if ($session_user_id != $landlord_id && $session_user_type !== 'admin') {
        $response['message'] = 'Unauthorized access.';
        echo json_encode($response);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT user_id, username, email, user_type, first_name, last_name, phone_number, profile_picture_url, id_document_url, ownership_document_url, approval_status, created_at FROM users WHERE user_id = ? AND user_type = 'landlord'");
    $stmt->bind_param('i', $landlord_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
        $response['success'] = true;
        $response['profile'] = $profile;
    } else {
        $response['message'] = 'Landlord profile not found.';
    }
    
    $stmt->close();
} else {
    $response['message'] = 'No landlord ID provided.';
}

$conn->close();
echo json_encode($response);
?>

