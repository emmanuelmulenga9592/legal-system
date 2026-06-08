<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php'; 

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// 2. Fetch Stats for the Cards
$total_cases = $conn->query("SELECT count(*) as count FROM cases")->fetch_assoc()['count'];
$pending_cases = $conn->query("SELECT count(*) as count FROM cases WHERE status = 'Pending'")->fetch_assoc()['count'];
$total_lawyers = $conn->query("SELECT count(*) as count FROM users WHERE role = 'lawyer'")->fetch_assoc()['count'];

// 3. Fetch Lawyers for dropdowns
$lawyers_res = $conn->query("SELECT id, full_name FROM users WHERE role = 'lawyer'");
$lawyer_list = [];
while($l = $lawyers_res->fetch_assoc()) { $lawyer_list[] = $l; }

// 4. Handle Search
$search_query = "";
$search_term = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $search_query = " WHERE (cases.title LIKE '%$search_term%' OR c.full_name LIKE '%$search_term%') ";
}

$sql = "SELECT cases.*, c.full_name as client_name, l.full_name as lawyer_name 
        FROM cases 
        JOIN users c ON cases.client_id = c.id 
        LEFT JOIN users l ON cases.lawyer_id = l.id 
        $search_query ORDER BY cases.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
        
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Cases</span>
                <div class="stat-value"><?php echo $total_cases; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pending Action</span>
                <div class="stat-value"><?php echo $pending_cases; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Lawyers</span>
                <div class="stat-value"><?php echo $total_lawyers; ?></div>
            </div>
        </div>

        <div class="card">
            <div class="page-header">
                <div>
                    <h2>Case Management</h2>
                </div>
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Search cases..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Client & Title</th>
                            <th>Assigned Lawyer</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($row['client_name']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($row['title']); ?></div>
                            </td>
                            <td>
                                <form method="POST" action="admin_actions.php" class="page-actions">
                                    <input type="hidden" name="case_id" value="<?php echo $row['id']; ?>">
                                    <select name="lawyer_id" class="search-input">
                                        <option value="">-- Unassigned --</option>
                                        <?php foreach($lawyer_list as $l): ?>
                                            <option value="<?php echo $l['id']; ?>" <?php echo ($row['lawyer_id'] == $l['id']) ? 'selected' : ''; ?>><?php echo $l['full_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="reassign_case" class="btn btn-primary btn-small">Save</button>
                                </form>
                            </td>
                            <td>
                                <span class="status-pill <?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin_actions.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this case?')" class="btn btn-danger btn-small">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>