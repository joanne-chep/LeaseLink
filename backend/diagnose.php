<?php
header('Content-Type: application/json');

$diagnostics = [
    'php_version' => phpversion(),
    'mysqli_available' => extension_loaded('mysqli'),
    'session_available' => function_exists('session_start'),
    'errors' => [],
    'warnings' => []
];

$db_host = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "webtech_2025a_ajak_panchol";

try {
    $conn = @mysqli_connect($db_host, $db_username, $db_password, $db_name);
} catch (mysqli_sql_exception $e) {
    $conn = false;
    $diagnostics['errors'][] = 'MySQL connection refused: ' . $e->getMessage();
    $diagnostics['errors'][] = 'Please start MySQL in XAMPP Control Panel';
}

if (!$conn) {
    $error_msg = mysqli_connect_error() ?: 'MySQL service is not running';
    $diagnostics['errors'][] = 'Database connection failed: ' . $error_msg;
    $diagnostics['errors'][] = 'SOLUTION: Open XAMPP Control Panel and click "Start" next to MySQL';
    $diagnostics['connection'] = false;
    echo json_encode($diagnostics, JSON_PRETTY_PRINT);
    exit;
} else {
    $diagnostics['connection'] = true;
    
   
    $db_check = mysqli_select_db($conn, $db_name);
    if (!$db_check) {
        $diagnostics['errors'][] = "Database '$db_name' does not exist";
        $diagnostics['database_exists'] = false;
    } else {
        $diagnostics['database_exists'] = true;
        
        $tables = ['users', 'properties', 'property_images'];
        $diagnostics['tables'] = [];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            $diagnostics['tables'][$table] = mysqli_num_rows($result) > 0;
            if (!$diagnostics['tables'][$table]) {
                $diagnostics['warnings'][] = "Table '$table' does not exist";
            }
        }
    }
    mysqli_close($conn);
}

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
$diagnostics['session_started'] = session_status() === PHP_SESSION_ACTIVE;

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>

