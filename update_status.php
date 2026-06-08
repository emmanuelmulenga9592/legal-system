<?php
include 'includes/db.php';
session_start();

// Security: Only lawyers can update status
if ($_SESSION['user_role'] !== 'lawyer') {
    die("Unauthorized access");
}

if (isset($_POST['update_status'])) {
    $case_id = $_POST['case_id'];
    $new_status = $_POST['status'];

    $sql = "UPDATE cases SET status = '$new_status' WHERE id = '$case_id'";

    if ($conn->query($sql)) {
        header("Location: lawyer_dashboard.php?msg=Status Updated");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>