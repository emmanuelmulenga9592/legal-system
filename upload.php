<?php
session_start();
include 'includes/db.php';

if (isset($_POST['upload'])) {
    $case_id = $_POST['case_id'];
    $user_name = $_SESSION['user_name'];
    $target_dir = "uploads/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name; // Add timestamp to prevent overwriting

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // Save info to database
        $sql = "INSERT INTO documents (case_id, file_name, file_path, uploaded_by) 
                VALUES ('$case_id', '$file_name', '$target_file', '$user_name')";
        
        if ($conn->query($sql)) {
            header("Location: " . $_SESSION['user_role'] . "_dashboard.php?msg=File Uploaded Successfully");
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>