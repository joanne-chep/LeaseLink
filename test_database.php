<?php
// Database Test File - Run this to check if everything is working
echo "<h2>LeaseLink Database Test</h2>";
echo "<hr>";

// Test 1: Database Connection
echo "<h3>1. Testing Database Connection</h3>";
include("backend/connect.php");

if ($conn->connect_error) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
    echo "<strong>❌ Connection Failed:</strong> " . $conn->connect_error;
    echo "</div>";
    exit;
} else {
echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px;'>";
echo "<strong>Database Connection:</strong> Successfully connected to 'leaselink' database";
echo "</div>";
}

// Test 2: Check if tables exist
echo "<h3>2. Checking Database Tables</h3>";
$tables = ['landlords', 'clients', 'properties'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
echo "<div style='color: green; padding: 5px;'>Table '$table' exists</div>";
    } else {
        echo "<div style='color: red; padding: 5px;'>❌ Table '$table' does not exist</div>";
    }
}

// Test 3: Check sample data
echo "<h3>3. Checking Sample Data</h3>";

// Check landlords
$result = $conn->query("SELECT COUNT(*) as count FROM landlords");
$row = $result->fetch_assoc();
echo "<div style='padding: 5px;'>Landlords: " . $row['count'] . " records</div>";

// Check clients
$result = $conn->query("SELECT COUNT(*) as count FROM clients");
$row = $result->fetch_assoc();
echo "<div style='padding: 5px;'>Clients: " . $row['count'] . " records</div>";

// Check properties
$result = $conn->query("SELECT COUNT(*) as count FROM properties");
$row = $result->fetch_assoc();
echo "<div style='padding: 5px;'>Properties: " . $row['count'] . " records</div>";

// Test 4: Test sample login credentials
echo "<h3>4. Testing Sample Login Credentials</h3>";

// Test landlord login
$stmt = $conn->prepare("SELECT name, password FROM landlords WHERE email = ?");
$email = 'landlord@example.com';
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify('password123', $user['password'])) {
        echo "<div style='color: green; padding: 5px;'>Landlord login works: " . htmlspecialchars($user['name']) . "</div>";
    } else {
        echo "<div style='color: red; padding: 5px;'>Landlord password verification failed</div>";
    }
} else {
    echo "<div style='color: red; padding: 5px;'>❌ Landlord not found</div>";
}

// Test client login
$stmt = $conn->prepare("SELECT name, password FROM clients WHERE email = ?");
$email = 'client@example.com';
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify('password123', $user['password'])) {
        echo "<div style='color: green; padding: 5px;'>Client login works: " . htmlspecialchars($user['name']) . "</div>";
    } else {
        echo "<div style='color: red; padding: 5px;'>Client password verification failed</div>";
    }
} else {
    echo "<div style='color: red; padding: 5px;'>❌ Client not found</div>";
}

// Test 5: Test API endpoints
echo "<h3>5. Testing API Endpoints</h3>";

// Test get_properties.php
echo "<div style='padding: 5px;'>Testing get_properties.php...</div>";
$properties_url = "http://localhost:8081/LeaseLink/backend/get_properties.php";
$properties_data = @file_get_contents($properties_url);
if ($properties_data !== false) {
    $properties = json_decode($properties_data, true);
    if (is_array($properties)) {
        echo "<div style='color: green; padding: 5px;'>get_properties.php works (" . count($properties) . " properties)</div>";
    } else {
        echo "<div style='color: red; padding: 5px;'>get_properties.php returned invalid JSON</div>";
    }
} else {
    echo "<div style='color: red; padding: 5px;'>get_properties.php not accessible</div>";
}

// Test get_property.php
echo "<div style='padding: 5px;'>Testing get_property.php...</div>";
$property_url = "http://localhost:8081/LeaseLink/backend/get_property.php?id=1";
$property_data = @file_get_contents($property_url);
if ($property_data !== false) {
    $property = json_decode($property_data, true);
    if (is_array($property) && !isset($property['error'])) {
        echo "<div style='color: green; padding: 5px;'>get_property.php works (Property: " . htmlspecialchars($property['title']) . ")</div>";
    } else {
        echo "<div style='color: red; padding: 5px;'>get_property.php returned error: " . htmlspecialchars($property['error'] ?? 'Unknown error') . "</div>";
    }
} else {
    echo "<div style='color: red; padding: 5px;'>get_property.php not accessible</div>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p><strong>If all tests show green, your database is ready to use!</strong></p>";
echo "<p><strong>If any tests show red, please check the issues above.</strong></p>";
echo "<hr>";
echo "<p><a href='index.html'>← Back to LeaseLink Home</a></p>";
?>
