<?php
include 'db.php';
session_start();

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($user = $result->fetch_assoc()) {
        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php"); // redirect after login
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}

// Handle signup success message
$signup_success = $_SESSION['signup_success'] ?? '';
unset($_SESSION['signup_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <h2>Login</h2>

    <?php if($signup_success): ?>
        <p id="signup-success" class="success"><?= $signup_success ?></p>
    <?php endif; ?>

    <?php if($error): ?>
        <p id="login-error" class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </form>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    // Hide signup success after 5 seconds
    const successMsg = document.getElementById('signup-success');
    if(successMsg) setTimeout(() => successMsg.style.display = 'none', 5000);

    // Hide login error when typing or deleting
    const loginError = document.getElementById('login-error');
    if(loginError) {
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => loginError.style.display = 'none');
        });
    }
});
</script>
</body>
</html>
