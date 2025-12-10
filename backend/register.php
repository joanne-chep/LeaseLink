<?php
include("connect.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? ''; 
    $confirm_password = $_POST['confirm_password'] ?? '';

    
    $username = trim($firstName . ' ' . $lastName);
    if (empty($username)) {
        $username = $email;
    }

    
    if (empty($role) || empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Please fill all fields.</p>";
        echo "<p>Received data:</p>";
        echo "<ul>";
        echo "<li>Role: " . htmlspecialchars($role) . "</li>";
        echo "<li>First Name: " . htmlspecialchars($firstName) . "</li>";
        echo "<li>Last Name: " . htmlspecialchars($lastName) . "</li>";
        echo "<li>Email: " . htmlspecialchars($email) . "</li>";
        echo "<li>Password: " . (empty($password) ? 'Empty' : 'Provided') . "</li>";
        echo "<li>Confirm Password: " . (empty($confirm_password) ? 'Empty' : 'Provided') . "</li>";
        echo "</ul>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    
    if ($password !== $confirm_password) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Passwords do not match.</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    
    if (strlen($password) < 8) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Password must be at least 8 characters long.</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/', $password)) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param('s', $email);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows > 0) {
        echo "<div style='color: orange; padding: 20px; border: 1px solid orange; margin: 20px;'>";
        echo "<h3>Registration Failed</h3>";
        echo "<p>Email already registered.</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

  
    $approval_status = ($role === 'landlord') ? 'pending' : 'approved';
    
   
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, user_type, first_name, last_name, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssss', $username, $email, $hashed, $role, $firstName, $lastName, $approval_status);
    if ($stmt->execute()) {
        echo "<div style='color: green; padding: 20px; border: 1px solid green; margin: 20px;'>";
        echo "<h3>Registration Successful!</h3>";
        echo "<p>Welcome, " . htmlspecialchars($firstName) . "!</p>";
        echo "<p>You can now <a href='../login.html'>login here</a></p>";
        echo "</div>";
    } else {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Database Error</h3>";
        echo "<p>Error: " . htmlspecialchars($conn->error) . "</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
    }
} else {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
    echo "<h3>Invalid Request</h3>";
    echo "<p>This page should only be accessed via form submission.</p>";
    echo "<a href='../login.html'>Go back</a>";
    echo "</div>";
}
?>
