<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id']; // sanitize
    $type = $_POST['type'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id'];

    // Update only if the transaction belongs to the logged-in user
    $stmt = $conn->prepare("UPDATE transactions SET type=?, description=?, amount=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssdii", $type, $description, $amount, $id, $user_id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>
