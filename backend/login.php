<?php
header('Content-Type: application/json');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include("connect.php");


if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please check your database settings.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Please provide email and password.']);
        exit;
    }

   
    try {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_type FROM users WHERE email = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];

            $roleText = ucfirst($user['user_type']); 
        
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful! Welcome, ' . htmlspecialchars($user['username']) . ' (' . $roleText . ').',
                'userType' => $user['user_type']
            ]);
        } else if ($user) {
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
