<?php
header('Content-Type: application/json');
include 'connect.php'; 

$response = array('success' => false, 'message' => '');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $landlord_id = filter_input(INPUT_POST, 'landlord_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $state_province = filter_input(INPUT_POST, 'state_province', FILTER_SANITIZE_STRING);
    $zip_code = filter_input(INPUT_POST, 'zip_code', FILTER_SANITIZE_STRING);
    $num_bedrooms = filter_input(INPUT_POST, 'num_bedrooms', FILTER_SANITIZE_NUMBER_INT);
    $num_bathrooms = filter_input(INPUT_POST, 'num_bathrooms', FILTER_VALIDATE_FLOAT);
    $square_footage = filter_input(INPUT_POST, 'square_footage', FILTER_SANITIZE_NUMBER_INT);
    $rent_price = filter_input(INPUT_POST, 'rent_price', FILTER_VALIDATE_FLOAT);
    $currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_STRING);
    $property_type = filter_input(INPUT_POST, 'property_type', FILTER_SANITIZE_STRING);
    $image_descriptions = $_POST['image_descriptions'] ?? []; 

   
    if (empty($landlord_id) || empty($title) || empty($description) || empty($address) || empty($city) || empty($rent_price) || empty($property_type)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

   
    $uploaded_image_paths = [];
    $upload_dir = '../assets/property_images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_FILES['property_images']['name'][0])) {
        foreach ($_FILES['property_images']['name'] as $key => $name) {
            $file_tmp = $_FILES['property_images']['tmp_name'][$key];
            $file_error = $_FILES['property_images']['error'][$key];
            $file_size = $_FILES['property_images']['size'][$key];
            $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

            if ($file_error !== UPLOAD_ERR_OK) {
                $response['message'] = 'File upload error: ' . $name . ' (Error Code: ' . $file_error . ')';
                echo json_encode($response);
                exit();
            }

            if (!in_array($file_ext, $allowed_ext)) {
                $response['message'] = 'Invalid file type: ' . $name . '. Only JPG, JPEG, PNG, GIF are allowed.';
                echo json_encode($response);
                exit();
            }

            if ($file_size > 5 * 1024 * 1024) { 
                $response['message'] = 'File too large: ' . $name . '. Max 5MB allowed.';
                echo json_encode($response);
                exit();
            }

            $new_file_name = uniqid('img_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $uploaded_image_paths[] = [
                    'url' => 'assets/property_images/' . $new_file_name,
                    'description' => $image_descriptions[$key] ?? ''
                ];
            } else {
                $response['message'] = 'Failed to move uploaded file: ' . $name;
                echo json_encode($response);
                exit();
            }
        }
    } else {
        $response['message'] = 'No property images uploaded.';
        echo json_encode($response);
        exit();
    }

    $main_image_url_for_property_table = $uploaded_image_paths[0]['url'] ?? null;

    
    $landlord_check = $conn->prepare("SELECT approval_status FROM users WHERE user_id = ? AND user_type = 'landlord'");
    $landlord_check->bind_param('i', $landlord_id);
    $landlord_check->execute();
    $landlord_result = $landlord_check->get_result();
    $landlord = $landlord_result->fetch_assoc();
    
    if (!$landlord || $landlord['approval_status'] !== 'approved') {
        foreach ($uploaded_image_paths as $image_data) {
            $file_path_to_delete = '../' . $image_data['url'];
            if (file_exists($file_path_to_delete)) {
                unlink($file_path_to_delete);
            }
        }
        $response['message'] = 'You must be approved by an admin before listing properties. Please complete your profile and wait for approval.';
        echo json_encode($response);
        exit();
    }

    
    $status = 'available';
    $stmt_property = $conn->prepare("INSERT INTO properties (landlord_id, title, description, address, city, state_province, zip_code, num_bedrooms, num_bathrooms, square_footage, rent_price, currency, property_type, main_image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt_property === false) {
        $response['message'] = 'Failed to prepare property statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt_property->bind_param(
        "issssssiiidssss",
        $landlord_id, $title, $description, $address, $city, $state_province, $zip_code,
        $num_bedrooms, $num_bathrooms, $square_footage, $rent_price, $currency, $property_type, $main_image_url_for_property_table, $status
    );

    if ($stmt_property->execute()) {
        $property_id = $conn->insert_id; 

        
        foreach ($uploaded_image_paths as $key => $image_data) {
            $is_main = ($key === 0) ? 1 : 0; 
            $stmt_image = $conn->prepare("INSERT INTO property_images (property_id, image_url, description, is_main) VALUES (?, ?, ?, ?)");
            if ($stmt_image === false) {
                error_log('Failed to prepare image statement for image ' . $image_data['url'] . ': ' . $conn->error);
                continue;
            }
            $stmt_image->bind_param("issi", $property_id, $image_data['url'], $image_data['description'], $is_main);
            if (!$stmt_image->execute()) {
                error_log('Failed to insert image ' . $image_data['url'] . ': ' . $stmt_image->error);
            }
            $stmt_image->close();
        }

        $response['success'] = true;
        $response['message'] = 'Property added successfully!';
    } else {
    
        foreach ($uploaded_image_paths as $image_data) {
            $file_path_to_delete = '../' . $image_data['url'];
            if (file_exists($file_path_to_delete)) {
                unlink($file_path_to_delete);
            }
        }
        $response['message'] = 'Failed to add property: ' . $stmt_property->error;
    }

    $stmt_property->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>
