<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "personal_tracker";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
