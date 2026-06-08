<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php'; 

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit();
}

$my_id = $_SESSION['user_id']; 

// 2. Fetch only THIS client's cases
$sql = "SELECT cases.*, l.full_name as lawyer_name 
        FROM cases 
        LEFT JOIN users l ON cases.lawyer_id = l.id 
        WHERE client_id = $my_id";
        
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Portal | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


    <div class="container">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <header class="page-header">
            <div>
                <h1>My Legal Cases</h1>
                <p>Welcome, <strong><?php echo $_SESSION['user_name']; ?></strong></p>
            </div>
            <div class="page-actions">
                <a href="client_open_case.php" class="btn btn-primary">+ Open New Case</a>
                <a href="make_payment.php" class="btn btn-accent">💳 Make Payment</a>
                <a href="payment_history.php" class="btn btn-info">📊 Payments</a>
                <a href="client_payment_methods.php" class="btn btn-tertiary">⚙️ Payment Methods</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </header>

        <hr class="section-divider">

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="case-card">
                    <div class="case-card-header">
                        <div>
                            <h2><?php echo $row['title']; ?></h2>
                            <p><strong>Lawyer:</strong> <?php echo $row['lawyer_name'] ?? 'Assignment Pending'; ?></p>
                        </div>
                        <div>
                            <span class="status-pill <?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                            <div style="margin-top: 10px;">
                                <a href="case_details.php?id=<?php echo $row['id']; ?>" class="doc-link">View Case Notes →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="case-card-body">
                        <p><?php echo $row['description']; ?></p>
                    </div>

                    <div class="case-docs">
                        <h4 style="margin-top: 0; color: var(--primary);">Case Documents</h4>
                        
                        <form action="upload.php" method="post" enctype="multipart/form-data" class="page-actions" style="margin-bottom: 15px;">
                            <input type="hidden" name="case_id" value="<?php echo $row['id']; ?>">
                            <input type="file" name="fileToUpload" required>
                            <button type="submit" name="upload" class="btn btn-primary btn-small">Upload</button>
                        </form>
                        
                        <ul class="file-list">
                            <?php
                            $case_id = $row['id'];
                            $docs = $conn->query("SELECT * FROM documents WHERE case_id = $case_id");
                            if($docs->num_rows > 0):
                                while($doc = $docs->fetch_assoc()): ?>
                                    <li>
                                        📄 <a href="<?php echo $doc['file_path']; ?>" target="_blank" class="doc-link"><?php echo $doc['file_name']; ?></a>
                                        <small class="text-muted">Uploaded by: <?php echo $doc['uploaded_by']; ?> • <?php echo date('M d', strtotime($doc['upload_date'])); ?></small>
                                    </li>
                                <?php endwhile; 
                            else: ?>
                                <li class="text-muted" style="font-style: italic;">No documents uploaded yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
                <p style="color: #64748b;">No active cases found. Need help? Contact our support team.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>