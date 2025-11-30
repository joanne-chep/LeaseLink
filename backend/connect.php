
<?php

$db_host = "localhost";
$db_username = "ajak.panchol";
$db_password = "sudo4541";
$db_name = "webtech_2025A_ajak_panchol";


$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
