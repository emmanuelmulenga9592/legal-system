<?php
session_start();
include 'includes/db.php';

// Route back to login if they haven't passed step 1
if (!isset($_SESSION['temp_tfa_user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['verify'])) {
    // IMPORTANT: Trim whitespace from the code input
    $entered_code = trim($_POST['code']);
    $entered_code = $conn->real_escape_string($entered_code);
    $user_id = $_SESSION['temp_tfa_user_id'];
    $current_time = date('Y-m-d H:i:s');

    // Fetch the code credentials from DB
    $sql = "SELECT * FROM users WHERE id = '$user_id' AND tfa_code = '$entered_code' AND tfa_expires_at > '$current_time'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Clear 2FA data out of database so code can't be reused
        if (isset($_SESSION['registration_verify']) && $_SESSION['registration_verify']) {
            $conn->query("UPDATE users SET tfa_code = NULL, tfa_expires_at = NULL, is_verified = 1 WHERE id = '$user_id'");
            
            // Log user in securely for registration verification
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['full_name'];
            $_SESSION['user_role'] = $row['role'];
            
            unset($_SESSION['registration_verify']);
            unset($_SESSION['temp_tfa_user_id']);
            unset($_SESSION['debug_tfa_code']);
            
            // Take them directly to their dashboard
            header("Location: " . $row['role'] . "_dashboard.php");
            exit();
        }

        $conn->query("UPDATE users SET tfa_code = NULL, tfa_expires_at = NULL WHERE id = '$user_id'");
        
        // Log user in securely for login 2FA
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['full_name'];
        $_SESSION['user_role'] = $row['role'];
        
        unset($_SESSION['temp_tfa_user_id'], $_SESSION['debug_tfa_code']); // Remove temp session placeholders
        
        header("Location: " . $row['role'] . "_dashboard.php");
        exit();
    } else {
        $error = "Invalid or expired verification code.";
        // Log failed verification attempt
        file_put_contents(dirname(__FILE__) . '/verify_errors.txt', '[' . date('Y-m-d H:i:s') . '] Failed: user_id=' . $user_id . ', entered=' . $entered_code . ', time=' . $current_time . "\n", FILE_APPEND);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LexFlow | Secure Verification</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h2>Two-Factor Verification</h2>
        <?php if (isset($_SESSION['debug_tfa_code'])): ?>
            <p class="auth-description">Email delivery was unavailable, so the code is shown below for local testing. Enter it to continue.</p>
        <?php else: ?>
            <p class="auth-description">We have sent a secure 6-digit access code to your registered email address.</p>
        <?php endif; ?>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-info">ℹ️ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(isset($_SESSION['debug_tfa_code'])): ?>
            <div class="alert alert-info">
                <strong>Debug code:</strong> <?php echo htmlspecialchars($_SESSION['debug_tfa_code']); ?>
                <div style="margin-top: 8px; color: #475569; font-size: 0.85rem;">Use this code if email delivery is unavailable.</div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="code" required maxlength="6" placeholder="000000" style="letter-spacing: 8px; text-align: center; font-size: 1.8rem;">
            </div>
            <button type="submit" name="verify" class="btn btn-primary btn-cta">Verify Access</button>
        </form>
    </div>
</body>
</html>