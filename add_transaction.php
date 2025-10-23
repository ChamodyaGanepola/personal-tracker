<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id']; // link to logged-in user

    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, description, amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $user_id, $type, $desc, $amount);
    $stmt->execute();

    header("Location: index.php");
}
?>
