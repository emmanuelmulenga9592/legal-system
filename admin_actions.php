<?php
include 'includes/db.php';
session_start();

// Security: Only Admin can access this file
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized access. Admin privileges required.");
}

// --- HANDLE REASSIGNMENT ---
if (isset($_POST['reassign_case'])) {
    $case_id = $conn->real_escape_string($_POST['case_id']);
    $lawyer_id = $conn->real_escape_string($_POST['lawyer_id']);

    // If lawyer_id is empty, it means "Unassigned"
    $val = ($lawyer_id == "") ? "NULL" : "'$lawyer_id'";
    
    $sql = "UPDATE cases SET lawyer_id = $val WHERE id = '$case_id'";
    
    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?msg=Case updated successfully");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// --- HANDLE DELETION ---
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    
    $sql = "DELETE FROM cases WHERE id = '$delete_id'";
    
    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?msg=Case deleted successfully");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>