<?php include 'db.php'; 
$res = $conn->query("SELECT * FROM cases WHERE id = 1");
$case = $res->fetch_assoc();
$s = $case['case_status'];
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <h1>Hello, <?php echo $case['client_name']; ?></h1>
        <p>Your case type: <?php echo $case['case_type']; ?></p>
        <h3>Current Progress:</h3>
        <div class="status-bar">
            <div class="step <?php if($s >= 1) echo 'active'; ?>">Intake</div>
            <div class="step <?php if($s >= 2) echo 'active'; ?>">Review</div>
            <div class="step <?php if($s >= 3) echo 'active'; ?>">Drafting</div>
            <div class="step <?php if($s >= 4) echo 'active'; ?>">Finalized</div>
        </div>
        <p style="margin-top: 30px; color: #666;">Last updated: <?php echo $case['last_update']; ?></p>
    </div>
</body>
</html>