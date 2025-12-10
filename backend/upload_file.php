<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'file_url' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'No file uploaded or upload error occurred.';
        echo json_encode($response);
        exit();
    }

    $file = $_FILES['file'];
    $file_type = $_POST['file_type'] ?? '';
    
    // Validate file type
    if (!in_array($file_type, ['profile_picture', 'id_document', 'ownership_document'])) {
        $response['message'] = 'Invalid file type specified.';
        echo json_encode($response);
        exit();
    }

    // Validate file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
    
    if (!in_array($file_ext, $allowed_ext)) {
        $response['message'] = 'Invalid file type. Only JPG, JPEG, PNG, GIF, and PDF are allowed.';
        echo json_encode($response);
        exit();
    }

    // Validate file size
    if ($file['size'] > 10 * 1024 * 1024) {
        $response['message'] = 'File too large. Maximum 10MB allowed.';
        echo json_encode($response);
        exit();
    }

    // Create upload directory based on file type
    $upload_dir = '../assets/';
    if ($file_type === 'profile_picture') {
        $upload_dir .= 'profile_pictures/';
    } else {
        $upload_dir .= 'documents/';
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $new_file_name = uniqid($file_type . '_', true) . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $file_url = str_replace('../', '', $destination); 
        $response['success'] = true;
        $response['message'] = 'File uploaded successfully.';
        $response['file_url'] = $file_url;
    } else {
        $response['message'] = 'Failed to move uploaded file.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>

