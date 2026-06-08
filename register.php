<?php
session_start();
include 'includes/db.php';
include 'includes/mailer.php';

if (isset($_POST['register'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = 'client'; // Automatically client
    
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 1. CHECK IF THE EMAIL ALREADY EXISTS
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
    
    if ($check_email->num_rows > 0) {
        // Email found! Set a friendly error message
        $error = "This email address is already registered. Please try logging in.";
    } else {
        // 2. IF IT DOESN'T EXIST, INSERT THE NEW USER
        $sql = "INSERT INTO users (full_name, email, password, role, is_verified) 
                VALUES ('$full_name', '$email', '$hashed_password', '$role', 0)";

        if ($conn->query($sql)) {
            $user_id = $conn->insert_id;
            $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $conn->query("UPDATE users SET tfa_code = '$verification_code', tfa_expires_at = '$expiry' WHERE id = '$user_id'");

            if (sendVerificationEmail($email, $full_name, $verification_code)) {
                $_SESSION['temp_tfa_user_id'] = $user_id;
                $_SESSION['registration_verify'] = true;
                header("Location: verify_tfa.php?msg=Please check your email and enter the verification code before logging in.");
                exit();
            } else {
                // Email failed, but keep the user and show debug code for local testing
                $_SESSION['temp_tfa_user_id'] = $user_id;
                $_SESSION['registration_verify'] = true;
                $_SESSION['debug_tfa_code'] = $verification_code;
                header("Location: verify_tfa.php?msg=Verification email unavailable. Use the code shown below to complete registration.");
                exit();
            }
        } else {
            $error = "Something went wrong. Please try again.";
            file_put_contents(dirname(__FILE__) . '/db_errors.txt', '[' . date('Y-m-d H:i:s') . '] Registration SQL error: ' . $conn->error . "\n", FILE_APPEND);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>M. CHUNGA & COMPANY | Client Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h2>Client Sign Up</h2>
        <p class="auth-description">Create an account to track your legal cases.</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="register" class="btn btn-primary btn-cta">Create Account</button>
        </form>
        <p class="auth-footer"><a href="login.php">Already have an account? Login</a></p>
    </div>
</body>
</html>