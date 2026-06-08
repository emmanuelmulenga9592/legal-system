<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit();
}

$client_id = $_SESSION['user_id'];

// Handle delete
if (isset($_POST['delete_method_id'])) {
    $delete_id = intval($_POST['delete_method_id']);
    $conn->query("DELETE FROM payment_methods WHERE id = $delete_id AND client_id = $client_id");
    header("Location: client_payment_methods.php?msg=Payment method removed successfully.");
    exit();
}

// Handle set default
if (isset($_POST['set_default_id'])) {
    $default_id = intval($_POST['set_default_id']);
    $conn->query("UPDATE payment_methods SET is_default = 0 WHERE client_id = $client_id");
    $conn->query("UPDATE payment_methods SET is_default = 1 WHERE id = $default_id AND client_id = $client_id");
    header("Location: client_payment_methods.php?msg=Default payment method updated.");
    exit();
}

// Fetch all payment methods
$sql = "SELECT * FROM payment_methods WHERE client_id = $client_id ORDER BY is_default DESC, created_at DESC";
$result = $conn->query($sql);
$paymentMethods = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paymentMethods[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Methods | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container" style="max-width: 700px;">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <header class="page-header">
            <div>
                <h1>Payment Methods</h1>
                <p>Manage your payment methods securely.</p>
            </div>
            <div class="page-actions">
                <a href="add_payment_method.php" class="btn btn-primary">+ Add Payment Method</a>
                <a href="client_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </header>

        <hr class="section-divider">

        <?php if (count($paymentMethods) > 0): ?>
            <?php foreach ($paymentMethods as $method): ?>
                <div class="payment-card <?php echo $method['is_default'] ? 'default' : ''; ?>">
                    <div class="payment-info">
                        <?php if ($method['method_type'] === 'credit_card' || $method['method_type'] === 'debit_card'): ?>
                            <h3>💳 <?php echo ucfirst(str_replace('_', ' ', $method['method_type'])); ?></h3>
                            <p><strong><?php echo htmlspecialchars($method['card_holder_name']); ?></strong></p>
                            <p>**** **** **** <?php echo htmlspecialchars($method['card_last_four']); ?></p>
                            <p class="text-muted">Expires: <?php echo htmlspecialchars($method['card_expiry']); ?></p>
                            <?php if ($method['is_default']): ?>
                                <span class="badge-default">DEFAULT</span>
                            <?php endif; ?>
                        <?php elseif ($method['method_type'] === 'bank_account'): ?>
                            <h3>🏦 Bank Account</h3>
                            <p><strong><?php echo htmlspecialchars($method['bank_name']); ?></strong></p>
                            <p>Account: **** <?php echo substr(htmlspecialchars($method['bank_account_number']), -4); ?></p>
                            <?php if ($method['is_default']): ?>
                                <span class="badge-default">DEFAULT</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="payment-actions">
                        <?php if (!$method['is_default']): ?>
                            <form method="POST">
                                <input type="hidden" name="set_default_id" value="<?php echo $method['id']; ?>">
                                <button type="submit" class="btn btn-primary btn-small">Set Default</button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" onsubmit="return confirm('Are you sure you want to remove this payment method?');">
                            <input type="hidden" name="delete_method_id" value="<?php echo $method['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-small">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="text-align: center;">
                <p class="text-muted" style="font-size: 1.1rem; margin-bottom: 20px;">You haven't added any payment methods yet.</p>
                <a href="add_payment_method.php" class="btn btn-primary">Add Payment Method</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
