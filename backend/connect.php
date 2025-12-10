<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$db_host = "localhost";
$db_username = "ajak.panchol";
$db_password = "sudo4541";
$db_name = "webtech_2025A_ajak_panchol";


$conn = @mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {

    $db_error = mysqli_connect_error();
}
?>
