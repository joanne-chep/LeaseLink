<?php
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        echo 'Please provide email and password.';
        exit;
    }

    // Find user in the users table
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_type FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $roleText = ucfirst($user['user_type']); // Capitalize first letter (e.g., 'landlord' -> 'Landlord')
        echo json_encode(['success' => true, 'message' => 'Login successful! Welcome, ' . htmlspecialchars($user['username']) . ' (' . $roleText . ').', 'userId' => $user['user_id'], 'userName' => htmlspecialchars($user['username']), 'userType' => $user['user_type']]);
    } else if ($user) {
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }
}
?>
