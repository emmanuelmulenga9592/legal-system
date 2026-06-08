<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$case_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Handle New Comment Submission
if (isset($_POST['post_comment'])) {
    $text = $conn->real_escape_string($_POST['comment_text']);
    $conn->query("INSERT INTO comments (case_id, user_id, comment_text) VALUES ('$case_id', '$user_id', '$text')");
    header("Location: case_details.php?id=$case_id");
    exit();
}

// 2. Fetch Case Info
$case_res = $conn->query("SELECT * FROM cases WHERE id = '$case_id'");
$case = $case_res->fetch_assoc();

// 3. Fetch Comments with User Names
$comments_res = $conn->query("SELECT comments.*, users.full_name, users.role 
                             FROM comments 
                             JOIN users ON comments.user_id = users.id 
                             WHERE case_id = '$case_id' 
                             ORDER BY created_at ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Case Details #<?php echo $case_id; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background:#f1f5f9; padding:40px; font-family:sans-serif;">
    <div style="max-width:700px; margin:auto; background:white; padding:30px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <a href="<?php echo $_SESSION['user_role']; ?>_dashboard.php" style="text-decoration:none; color:#1e3a8a;">← Back to Dashboard</a>
        
        <h2 style="margin-top:20px;"><?php echo $case['title']; ?></h2>
        <p><strong>Status:</strong> <?php echo $case['status']; ?></p>
        <p style="color:#64748b;"><?php echo $case['description']; ?></p>

        <hr style="margin:30px 0; border:0; border-top:1px solid #e2e8f0;">

        <h3>Case History & Notes</h3>
        <div style="background:#f8fafc; padding:15px; border-radius:8px; max-height:400px; overflow-y:auto; margin-bottom:20px;">
            <?php if($comments_res->num_rows > 0): ?>
                <?php while($c = $comments_res->fetch_assoc()): ?>
                    <div style="margin-bottom:15px; padding:10px; border-radius:5px; background: <?php echo ($c['user_id'] == $user_id) ? '#dbeafe' : 'white'; ?>; border: 1px solid #e2e8f0;">
                        <small><strong><?php echo $c['full_name']; ?></strong> (<?php echo ucfirst($c['role']); ?>) • <?php echo date('M d, H:i', strtotime($c['created_at'])); ?></small>
                        <p style="margin:5px 0;"><?php echo htmlspecialchars($c['comment_text']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#94a3b8; text-align:center;">No notes yet.</p>
            <?php endif; ?>
        </div>

        <form method="POST">
            <textarea name="comment_text" required placeholder="Type a note or update..." style="width:100%; height:80px; padding:10px; border-radius:5px; border:1px solid #cbd5e1; box-sizing:border-box;"></textarea>
            <button type="submit" name="post_comment" style="margin-top:10px; background:#1e3a8a; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; width:100%;">Post Note</button>
        </form>
    </div>
</body>
</html>