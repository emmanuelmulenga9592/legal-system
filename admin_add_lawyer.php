<?php
session_start();
include 'includes/db.php';

// Security: ONLY Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_POST['add_lawyer'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'lawyer';

    $sql = "INSERT INTO users (full_name, email, password, role) VALUES ('$full_name', '$email', '$password', '$role')";
    
    if ($conn->query($sql)) {
        $msg = "Lawyer added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Lawyer | Admin</title>
</head>
<body style="background: #f1f5f9; font-family: sans-serif; margin:0;">
    <?php include 'includes/navbar.php'; ?>
    
    <div style="max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #1e3a8a;">Add New Lawyer to Staff</h2>
        
        <?php if(isset($msg)) echo "<p style='color: green;'>$msg</p>"; ?>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #cbd5e1;">
            <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #cbd5e1;">
            <input type="password" name="password" placeholder="Temporary Password" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #cbd5e1;">
            <button type="submit" name="add_lawyer" style="width: 100%; background: #1e3a8a; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer;">Create Staff Account</button>
        </form>
        <br>
        <a href="admin_dashboard.php" style="color: #64748b; text-decoration: none;">← Back to Dashboard</a>
    </div>
</body>
</html>