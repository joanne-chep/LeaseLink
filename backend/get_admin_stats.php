<?php
header('Content-Type: application/json');
include 'connect.php';

$response = array('success' => false, 'message' => '', 'stats' => []);

if (isset($_GET['userId'])) {
    $admin_id = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);

    if (empty($admin_id)) {
        $response['message'] = 'Admin ID is missing or invalid.';
        echo json_encode($response);
        exit();
    }

    // Validate user is an admin
    $user_check = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $user_check->bind_param('i', $admin_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user || $user['user_type'] !== 'admin') {
        $response['message'] = 'Invalid user. Only admins can access statistics.';
        echo json_encode($response);
        exit();
    }

    // Get total users count
    $users_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $users_stmt->execute();
    $users_result = $users_stmt->get_result();
    $users_row = $users_result->fetch_assoc();
    $total_users = $users_row['count'] ?? 0;

    // Get active properties count
    $properties_stmt = $conn->prepare("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
    $properties_stmt->execute();
    $properties_result = $properties_stmt->get_result();
    $properties_row = $properties_result->fetch_assoc();
    $active_properties = $properties_row['count'] ?? 0;

    // Get pending viewings count
    $viewings_stmt = $conn->prepare("SELECT COUNT(*) as count FROM view_requests WHERE status = 'pending'");
    $viewings_stmt->execute();
    $viewings_result = $viewings_stmt->get_result();
    $viewings_row = $viewings_result->fetch_assoc();
    $pending_viewings = $viewings_row['count'] ?? 0;

    // Get active bookings count 
    $bookings_stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = 'active'");
    $bookings_stmt->execute();
    $bookings_result = $bookings_stmt->get_result();
    $bookings_row = $bookings_result->fetch_assoc();
    $active_bookings = $bookings_row['count'] ?? 0;

    $response['success'] = true;
    $response['stats'] = [
        'total_users' => (int)$total_users,
        'active_properties' => (int)$active_properties,
        'pending_viewings' => (int)$pending_viewings,
        'active_bookings' => (int)$active_bookings
    ];

    $users_stmt->close();
    $properties_stmt->close();
    $viewings_stmt->close();
    $bookings_stmt->close();
} else {
    $response['message'] = 'No admin ID provided.';
}

$conn->close();
echo json_encode($response);
?>

