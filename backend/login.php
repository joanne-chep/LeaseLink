<?php
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        echo 'Please provide email and password.';
        exit;
    }

    // Try to find user in both landlords and clients tables
    $tables = ['landlords', 'clients'];
    $userFound = false;
    $user = null;
    $userType = '';

    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT id, name, password FROM $table WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $userType = $table;
            $userFound = true;
            break;
        }
    }

    if ($userFound && password_verify($password, $user['password'])) {
        $roleText = ($userType === 'landlords') ? 'Landlord' : 'Client';
        echo 'Login successful! Welcome, ' . htmlspecialchars($user['name']) . ' (' . $roleText . '). (<a href="../index.html">Go to Home</a>)';
    } else if ($userFound) {
        echo 'Invalid password. <a href="../login.html">Try again</a>';
    } else {
        echo 'User not found. <a href="../login.html">Register</a>';
    }
}
?>