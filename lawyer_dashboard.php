<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php'; 

// Security Check: Only Lawyers
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lawyer') {
    header("Location: index.php");
    exit();
}

$lawyer_id = $_SESSION['user_id'];

// Fetch cases assigned to this lawyer + client names
$sql = "SELECT cases.*, c.full_name as client_name 
        FROM cases 
        JOIN users c ON cases.client_id = c.id 
        WHERE cases.lawyer_id = '$lawyer_id' 
        ORDER BY cases.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lawyer Portal | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        
        <header class="page-header">
            <div>
                <h1>Lawyer Case Manager</h1>
                <p>Advocate: <strong><?php echo $_SESSION['user_name']; ?></strong></p>
            </div>
        </header>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Case & Client</th>
                        <th>Documents</th>
                        <th>Status Update</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: bold; color: var(--text-main);"><?php echo $row['title']; ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">Client: <?php echo $row['client_name']; ?></div>
                            </td>

                            <td>
                                <?php
                                $case_id = $row['id'];
                                $docs = $conn->query("SELECT * FROM documents WHERE case_id = $case_id LIMIT 3");
                                if($docs->num_rows > 0) {
                                    while($doc = $docs->fetch_assoc()) {
                                        echo "<a href='{$doc['file_path']}' target='_blank' class='doc-link'>📄 " . htmlspecialchars($doc['file_name']) . "</a>";
                                    }
                                } else {
                                    echo "<span class='text-muted'>No files</span>";
                                }
                                ?>
                            </td>

                            <td>
                                <form method="POST" action="update_status.php" class="page-actions">
                                    <input type="hidden" name="case_id" value="<?php echo $row['id']; ?>">
                                    <select name="status" class="search-input">
                                        <option value="Pending" <?php if($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="In Progress" <?php if($row['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                        <option value="Closed" <?php if($row['status'] == 'Closed') echo 'selected'; ?>>Closed</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-accent btn-small">Save</button>
                                </form>
                            </td>

                            <td>
                                <a href="case_details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-small">View Notes</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 40px; text-align: center; color: var(--text-muted);">No cases assigned yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>