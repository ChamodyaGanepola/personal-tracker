<?php
// Local development (XAMPP) MySQL credentials
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "personal_tracker";
// Note: When deploying to InfinityFree, used the credentials provided in the folder structure
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

