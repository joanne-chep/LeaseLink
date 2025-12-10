<?php
header('Content-Type: application/json');

$db_host = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "webtech_2025a_ajak_panchol";

$response = ['success' => false, 'message' => '', 'details' => []];


try {
    $conn = @mysqli_connect($db_host, $db_username, $db_password, $db_name);
} catch (mysqli_sql_exception $e) {
    $response['message'] = 'MySQL connection refused';
    $response['details'] = [
        'error' => $e->getMessage(),
        'host' => $db_host,
        'username' => $db_username,
        'database' => $db_name,
        'suggestion' => 'MySQL is not running. Open XAMPP Control Panel and click "Start" next to MySQL'
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

if (!$conn) {
    $response['message'] = 'Database connection failed';
    $response['details'] = [
        'error' => mysqli_connect_error(),
        'host' => $db_host,
        'username' => $db_username,
        'database' => $db_name,
        'suggestion' => 'Check if MySQL is running and credentials are correct'
    ];
} else {
    
    $db_check = mysqli_select_db($conn, $db_name);
    if (!$db_check) {
        $response['message'] = 'Database does not exist';
        $response['details'] = [
            'database' => $db_name,
            'suggestion' => 'Create the database: ' . $db_name
        ];
        mysqli_close($conn);
    } else {
        
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
        if (mysqli_num_rows($table_check) == 0) {
            $response['message'] = 'Database exists but tables are missing';
            $response['details'] = [
                'database' => $db_name,
                'suggestion' => 'Import the migration SQL files'
            ];
        } else {
            $response['success'] = true;
            $response['message'] = 'Connection successful!';
            $response['details'] = [
                'database' => $db_name,
                'tables_exist' => true
            ];
        }
        mysqli_close($conn);
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>

