<?php
session_start();
include 'includes/db.php';
include 'includes/mailer.php'; // Include our mailer script

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (!isset($row['is_verified']) || !$row['is_verified']) {
            $error = "Your account is not verified yet. Please complete the registration verification step.";
        } elseif (password_verify($password, $row['password'])) {
            // Skip the extra verification flow for already verified users.
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['full_name'];
            $_SESSION['user_role'] = $row['role'];
            header("Location: " . $row['role'] . "_dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>M. CHUNGA & COMPANY | Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1>M. CHUNGA & COMPANY</h1>
        <p class="auth-description">Secure Legal Management Portal</p>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-info">ℹ️ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-cta">Login Securely</button>
        </form>

        <p class="auth-footer">New Client? <a href="register.php">Create Client Account</a>.</p>
    </div>
</body>
</html>