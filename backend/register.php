<?php
include("connect.php");

// Debug: Check if form is being submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Debug: Show received data
    if (empty($role) || empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Please fill all fields.</p>";
        echo "<p>Received data:</p>";
        echo "<ul>";
        echo "<li>Role: " . htmlspecialchars($role) . "</li>";
        echo "<li>Name: " . htmlspecialchars($name) . "</li>";
        echo "<li>Email: " . htmlspecialchars($email) . "</li>";
        echo "<li>Password: " . (empty($password) ? 'Empty' : 'Provided') . "</li>";
        echo "<li>Confirm Password: " . (empty($confirm_password) ? 'Empty' : 'Provided') . "</li>";
        echo "</ul>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    // Validate password confirmation
    if ($password !== $confirm_password) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Passwords do not match.</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    // Validate password strength
    if (strlen($password) < 8) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Password must be at least 8 characters long.</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).</p>";
        echo "<a href='../login.html'>Go back</a>";
        echo "</div>";
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'landlord') {
        $table = 'landlords';
    } else {
        $table = 'clients';
    }

    // Check existing email
    $check = $conn->prepare("SELECT id FROM $table WHERE email = ?");
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

    $stmt = $conn->prepare("INSERT INTO $table (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $name, $email, $hashed);
    if ($stmt->execute()) {
        echo "<div style='color: green; padding: 20px; border: 1px solid green; margin: 20px;'>";
        echo "<h3>Registration Successful!</h3>";
        echo "<p>Welcome, " . htmlspecialchars($name) . "!</p>";
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