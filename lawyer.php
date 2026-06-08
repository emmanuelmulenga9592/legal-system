<?php 
include 'db.php'; 
if(isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $conn->query("UPDATE cases SET case_status = $new_status WHERE id = 1");
}
$res = $conn->query("SELECT * FROM cases WHERE id = 1");
$case = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <h1>Lawyer Control Panel</h1>
        <p>Managing Case for: <strong><?php echo $case['client_name']; ?></strong></p>
        <form method="POST">
            <select name="status" style="padding: 10px; margin-right: 10px;">
                <option value="1" <?php if($case['case_status']==1) echo 'selected'; ?>>Step 1: Intake</option>
                <option value="2" <?php if($case['case_status']==2) echo 'selected'; ?>>Step 2: Review</option>
                <option value="3" <?php if($case['case_status']==3) echo 'selected'; ?>>Step 3: Drafting</option>
                <option value="4" <?php if($case['case_status']==4) echo 'selected'; ?>>Step 4: Finalized</option>
            </select>
            <button type="submit" name="update_status">Update Client Progress</button>
        </form>
        <br><a href="client.php" target="_blank">View Client Side →</a>
    </div>
</body>
</html>