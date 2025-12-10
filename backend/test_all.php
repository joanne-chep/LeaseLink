<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => [],
    'summary' => [
        'total' => 0,
        'passed' => 0,
        'failed' => 0
    ],
    'error' => null
];

function addTest($name, $passed, $message = '', $details = []) {
    global $results;
    $results['tests'][] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
        'details' => $details
    ];
    $results['summary']['total']++;
    if ($passed) {
        $results['summary']['passed']++;
    } else {
        $results['summary']['failed']++;
    }
}

// Test 1: PHP Version
$php_version = phpversion();
addTest('PHP Version', version_compare($php_version, '7.0.0', '>='), 
    "PHP $php_version", ['version' => $php_version]);

// Test 2: MySQLi Extension
addTest('MySQLi Extension', extension_loaded('mysqli'), 
    extension_loaded('mysqli') ? 'MySQLi is available' : 'MySQLi extension not loaded');

// Test 3: Database Connection
$db_connected = false;
$connection_error = null;

try {
    // Try to include connect.php
    @include('connect.php');
    
    // Check if connection exists and is valid
    if (isset($conn) && $conn) {
        // Try a simple query to verify connection works (better than ping)
        $test_query = @mysqli_query($conn, "SELECT 1");
        if ($test_query !== false) {
            mysqli_free_result($test_query);
            $db_connected = true;
            addTest('Database Connection', true, 'Successfully connected to MySQL');
        } else {
            // Connection object exists but query failed
            $connection_error = mysqli_error($conn) ?: 'Connection test query failed';
            addTest('Database Connection', false, 'MySQL connection test failed: ' . $connection_error, 
                ['suggestion' => 'MySQL may have disconnected. Check if MySQL is running in XAMPP']);
        }
    } else {
        // No connection object
        $error_msg = isset($db_error) ? $db_error : (mysqli_connect_error() ?: 'Connection failed');
        $connection_error = $error_msg;
        addTest('Database Connection', false, 'MySQL connection failed: ' . $error_msg, 
            ['suggestion' => 'Open XAMPP Control Panel and click "Start" next to MySQL']);
    }
} catch (mysqli_sql_exception $e) {
    $connection_error = $e->getMessage();
    addTest('Database Connection', false, 'MySQL connection refused: ' . $e->getMessage(), 
        ['suggestion' => 'Open XAMPP Control Panel and click "Start" next to MySQL']);
} catch (Throwable $e) {
    $connection_error = $e->getMessage();
    addTest('Database Connection', false, 'Error: ' . $e->getMessage(), 
        ['suggestion' => 'Open XAMPP Control Panel and click "Start" next to MySQL']);
}


if ($db_connected && isset($conn) && $conn) {
    // Test 4: Database Exists
    $db_name = "webtech_2025a_ajak_panchol";
    $db_check = mysqli_select_db($conn, $db_name);
    if ($db_check) {
        addTest('Database Exists', true, "Database '$db_name' exists");
        
        // Test 5: Required Tables
        $required_tables = ['users', 'properties', 'property_images', 'bookings', 'view_requests'];
        $missing_tables = [];
        foreach ($required_tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) == 0) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            addTest('Required Tables', true, 'All required tables exist', ['tables' => $required_tables]);
        } else {
            addTest('Required Tables', false, 'Missing tables: ' . implode(', ', $missing_tables), 
                ['missing' => $missing_tables, 'existing' => array_diff($required_tables, $missing_tables)]);
        }
        
        // Test 6: Sample Data
        $user_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
        $user_row = mysqli_fetch_assoc($user_count);
        $user_count = $user_row['count'];
        
        $property_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM properties");
        $property_row = mysqli_fetch_assoc($property_count);
        $property_count = $property_row['count'];
        
        if ($user_count > 0 && $property_count > 0) {
            addTest('Sample Data', true, "Found $user_count users and $property_count properties", 
                ['users' => $user_count, 'properties' => $property_count]);
        } else {
            addTest('Sample Data', false, "Limited data: $user_count users, $property_count properties", 
                ['users' => $user_count, 'properties' => $property_count, 
                 'suggestion' => 'Import sample_properties_with_images.sql']);
        }
        
        // Test 7: Approved Landlords
        $approved_landlords = mysqli_query($conn, 
            "SELECT COUNT(*) as count FROM users WHERE user_type = 'landlord' AND approval_status = 'approved'");
        $approved_row = mysqli_fetch_assoc($approved_landlords);
        $approved_count = $approved_row['count'];
        
        if ($approved_count > 0) {
            addTest('Approved Landlords', true, "Found $approved_count approved landlords");
        } else {
            addTest('Approved Landlords', false, "No approved landlords found", 
                ['suggestion' => 'Properties will not show until landlords are approved']);
        }
        
        // Test 8: Available Properties
        $available_props = mysqli_query($conn, 
            "SELECT COUNT(*) as count FROM properties p 
             INNER JOIN users u ON p.landlord_id = u.user_id 
             WHERE p.status = 'available' AND u.approval_status = 'approved'");
        $available_row = mysqli_fetch_assoc($available_props);
        $available_count = $available_row['count'];
        
        if ($available_count > 0) {
            addTest('Available Properties', true, "Found $available_count available properties from approved landlords");
        } else {
            addTest('Available Properties', false, "No available properties found", 
                ['suggestion' => 'Properties need to be from approved landlords with status=available']);
        }
        
        mysqli_close($conn);
    } else {
        addTest('Database Exists', false, "Database '$db_name' does not exist", 
            ['suggestion' => 'Create database and import migration files']);
    }
} else {
    $error = mysqli_connect_error() ?: 'Unknown error';
    addTest('Database Connection', false, "Connection failed: $error", 
        ['suggestion' => 'Check if MySQL is running in XAMPP']);
}

// Test 9: Session Handling
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
addTest('Session Support', session_status() === PHP_SESSION_ACTIVE, 
    session_status() === PHP_SESSION_ACTIVE ? 'Sessions working' : 'Session start failed');

// Test 10: File Permissions
$assets_exists = is_dir('../assets');
$assets_writable = $assets_exists && is_writable('../assets');
addTest('Assets Folder', $assets_exists, 
    $assets_exists ? ($assets_writable ? 'Exists and writable' : 'Exists but not writable') : 'Does not exist',
    ['exists' => $assets_exists, 'writable' => $assets_writable]);

// Test 11: Test get_session_info
if (isset($_SERVER['HTTP_HOST'])) {
    $session_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/get_session_info.php';
    $session_response = @file_get_contents($session_url);
    if ($session_response !== false) {
        $session_data = @json_decode($session_response, true);
        if ($session_data !== null && isset($session_data['isLoggedIn'])) {
            addTest('get_session_info.php Endpoint', true, 'Endpoint returns valid JSON', 
                ['response' => $session_data]);
        } else {
            addTest('get_session_info.php Endpoint', false, 'Endpoint returned invalid JSON', 
                ['response' => substr($session_response, 0, 200)]);
        }
    } else {
        addTest('get_session_info.php Endpoint', false, 'Could not reach endpoint', 
            ['url' => $session_url, 'error' => error_get_last()]);
    }
} else {
    addTest('get_session_info.php Endpoint', false, 'Cannot determine server URL', 
        ['suggestion' => 'Test manually by visiting the endpoint']);
}

// Test 12: Test get_properties.php endpoint
if (isset($_SERVER['HTTP_HOST'])) {
    $properties_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/get_properties.php';
    $properties_response = @file_get_contents($properties_url);
    if ($properties_response !== false) {
        $properties_data = @json_decode($properties_response, true);
        if ($properties_data !== null) {
            $prop_count = is_array($properties_data) ? count($properties_data) : 0;
            addTest('get_properties.php Endpoint', true, "Endpoint returns valid JSON with $prop_count properties", 
                ['property_count' => $prop_count]);
        } else {
            addTest('get_properties.php Endpoint', false, 'Endpoint returned invalid JSON', 
                ['response' => substr($properties_response, 0, 200)]);
        }
    } else {
        addTest('get_properties.php Endpoint', false, 'Could not reach endpoint', 
            ['url' => $properties_url, 'error' => error_get_last()]);
    }
} else {
    addTest('get_properties.php Endpoint', false, 'Cannot determine server URL', 
        ['suggestion' => 'Test manually by visiting the endpoint']);
}


$results['overall_status'] = $results['summary']['failed'] === 0 ? 'ALL TESTS PASSED' : 'SOME TESTS FAILED';


try {
    echo json_encode($results, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    
    echo json_encode([
        'error' => 'Failed to encode results: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>

