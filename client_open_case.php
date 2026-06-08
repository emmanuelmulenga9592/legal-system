<?php 
session_start();
include 'includes/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit_case'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description']);
    $client_id = $_SESSION['user_id'];

    // We leave lawyer_id as NULL because the Admin will assign one later
    $sql = "INSERT INTO cases (title, description, client_id, status) 
            VALUES ('$title', '$desc', '$client_id', 'Pending')";
    
    if ($conn->query($sql)) {
        header("Location: client_dashboard.php?msg=CaseSubmitted");
    }
}
?>

<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="admin-container" style="max-width: 600px;">
        <h2>Open a New Case</h2>
        <p>Provide the details below and our team will assign a lawyer to you shortly.</p>
        
        <form method="POST">
            <label>What is this regarding? (Case Title)</label>
            <input type="text" name="title" required placeholder="e.g. Contract Review" style="width:100%; padding:10px; margin:10px 0;">

            <label>Detailed Description</label>
            <textarea name="description" required style="width:100%; height:150px; padding:10px; margin:10px 0;" placeholder="Describe your legal needs..."></textarea>

            <button type="submit" name="submit_case" class="btn-primary" style="width:100%;">Submit Case for Review</button>
        </form>
        <br><a href="client_dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>