
<?php

$db_host = "localhost:3307";
$db_username = "root";
$db_password = "";
$db_name = "leaselink_db";


$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
