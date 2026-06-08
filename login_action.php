<?php
session_start();
include 'includes/db.php';

// Simple mock login logic (In real life, use password_verify)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $role = $_POST['role']; // For this demo, we'll let users choose their role

    // Store user info in the Session
    $_SESSION['user_role'] = $role;
    
    if ($role == 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($role == 'lawyer') {
        header("Location: lawyer_dashboard.php");
    } else {
        header("Location: client_dashboard.php");
    }
}
?>