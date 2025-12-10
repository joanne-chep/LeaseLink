<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    $landlord_id = $_SESSION['user_id'] ?? filter_input(INPUT_POST, 'landlord_id', FILTER_SANITIZE_NUMBER_INT);
    $profile_picture_url = filter_input(INPUT_POST, 'profile_picture_url', FILTER_SANITIZE_STRING);
    $id_document_url = filter_input(INPUT_POST, 'id_document_url', FILTER_SANITIZE_STRING);
    $ownership_document_url = filter_input(INPUT_POST, 'ownership_document_url', FILTER_SANITIZE_STRING);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);

    // Validate landlord_id
    if (empty($landlord_id)) {
        $response['message'] = 'Landlord ID is required.';
        echo json_encode($response);
        exit();
    }

    // Verify user is a landlord
    $user_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $user_check->bind_param('i', $landlord_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user || $user['user_type'] !== 'landlord') {
        $response['message'] = 'Invalid user. Only landlords can update their profile.';
        echo json_encode($response);
        exit();
    }

    // Build update query dynamically based on provided fields
    $update_fields = [];
    $params = [];
    $types = '';

    if (!empty($profile_picture_url)) {
        $update_fields[] = "profile_picture_url = ?";
        $params[] = $profile_picture_url;
        $types .= 's';
    }

    if (!empty($id_document_url)) {
        $update_fields[] = "id_document_url = ?";
        $params[] = $id_document_url;
        $types .= 's';
    }

    if (!empty($ownership_document_url)) {
        $update_fields[] = "ownership_document_url = ?";
        $params[] = $ownership_document_url;
        $types .= 's';
    }

    if (!empty($phone_number)) {
        $update_fields[] = "phone_number = ?";
        $params[] = $phone_number;
        $types .= 's';
    }

    if (empty($update_fields)) {
        $response['message'] = 'No fields to update.';
        echo json_encode($response);
        exit();
    }

    // Add landlord_id to params
    $params[] = $landlord_id;
    $types .= 'i';

    // Build and execute update query
    $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
       
        if (!empty($profile_picture_url) && !empty($id_document_url) && !empty($ownership_document_url)) {
            $check_docs = $conn->prepare("SELECT approval_status FROM users WHERE user_id = ?");
            $check_docs->bind_param('i', $landlord_id);
            $check_docs->execute();
            $docs_result = $check_docs->get_result();
            $docs_user = $docs_result->fetch_assoc();
            
            
            if ($docs_user && $docs_user['approval_status'] === 'pending') {
               
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully.';
    } else {
        $response['message'] = 'Failed to update profile: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>

