<?php
// Enable error reporting to see what's wrong
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied: You must be an admin to view this page.");
}

// Check if an ID was passed in the URL
if (!isset($_GET['id'])) {
    die("Error: No Case ID provided.");
}

$case_id = $_GET['id'];

// Handle the Form Submission
if (isset($_POST['assign'])) {
    $lawyer_id = $_POST['lawyer_id'];
    
    // Update the case with the lawyer and move status to 'In Progress'
    $sql = "UPDATE cases SET lawyer_id = '$lawyer_id', status = 'In Progress' WHERE id = $case_id";
    
    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?msg=Success");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }
}

// Fetch case details to show on screen
$case_result = $conn->query("SELECT * FROM cases WHERE id = $case_id");
$case = $case_result->fetch_assoc();

// Fetch all available lawyers
$lawyers = $conn->query("SELECT id, full_name FROM users WHERE role = 'lawyer'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Lawyer | LexFlow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: #f8fafc; padding: 50px;">
    <div class="admin-container" style="max-width: 500px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
        <h2 style="margin-top: 0; color: #1e3a8a;">Assign Lawyer</h2>
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        
        <p><strong>Case ID:</strong> #<?php echo $case['id']; ?></p>
        <p><strong>Subject:</strong> <?php echo $case['title']; ?></p>
        <p style="color: #64748b; font-size: 0.9rem;"><?php echo $case['description']; ?></p>

        <form method="POST" style="margin-top: 25px;">
            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Choose a Lawyer:</label>
            <select name="lawyer_id" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 20px;">
                <option value="">-- Select from Firm --</option>
                <?php while($l = $lawyers->fetch_assoc()): ?>
                    <option value="<?php echo $l['id']; ?>"><?php echo $l['full_name']; ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="assign" class="btn-primary" style="width: 100%; padding: 12px; cursor: pointer;">
                Confirm Assignment
            </button>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="admin_dashboard.php" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">Cancel and Go Back</a>
            </div>
        </form>
    </div>
</body>
</html>