<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/AirtelMoneyAPI.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit();
}

$client_id = $_SESSION['user_id'];
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch payment details
$sql = "SELECT * FROM payments WHERE id = $payment_id AND client_id = $client_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header("Location: client_dashboard.php");
    exit();
}

$payment = $result->fetch_assoc();

// Check payment status with Airtel
$airtel = new AirtelMoneyAPI(true);
$status_response = $airtel->checkPaymentStatus($payment['transaction_id']);

$payment_status = $payment['status'];
$payment_message = '';

// Update payment status if we got a response from Airtel
if (isset($status_response['data']['status'])) {
    $airtel_status = strtolower($status_response['data']['status']);
    
    if ($airtel_status === 'success' || $airtel_status === 'completed') {
        $payment_status = 'completed';
        $payment_message = 'Payment completed successfully!';
        $conn->query("UPDATE payments SET status = 'completed', completed_at = NOW() WHERE id = $payment_id");
    } elseif ($airtel_status === 'failed') {
        $payment_status = 'failed';
        $payment_message = 'Payment failed. Please try again.';
        $conn->query("UPDATE payments SET status = 'failed' WHERE id = $payment_id");
    } elseif ($airtel_status === 'pending') {
        $payment_status = 'processing';
        $payment_message = 'Payment is being processed. Please wait...';
    }
}

$status_class = 'status-pill processing';
if ($payment_status === 'completed') {
    $status_class = 'status-pill completed';
} elseif ($payment_status === 'failed') {
    $status_class = 'status-pill failed';
} elseif ($payment_status === 'pending') {
    $status_class = 'status-pill pending';
} elseif ($payment_status === 'cancelled') {
    $status_class = 'status-pill cancelled';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Status | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-card status-container">
        
        <?php if ($payment_status === 'completed'): ?>
            <div class="status-icon">✅</div>
            <h1 style="color: #166534; margin: 0 0 10px 0;">Payment Successful</h1>
            <span class="<?php echo $status_class; ?>"><?php echo ucfirst($payment_status); ?></span>
            <p class="text-muted" style="font-size: 1.05rem;">Your payment has been processed successfully.</p>
            
        <?php elseif ($payment_status === 'failed'): ?>
            <div class="status-icon">❌</div>
            <h1 style="color: #b91c1c; margin: 0 0 10px 0;">Payment Failed</h1>
            <span class="<?php echo $status_class; ?>"><?php echo ucfirst($payment_status); ?></span>
            <p class="text-muted" style="font-size: 1.05rem;">Your payment could not be processed. Please try again.</p>
            
        <?php else: ?>
            <div class="status-icon"><div class="processing-animation"></div></div>
            <h1 style="color: #1e40af; margin: 0 0 10px 0;">Processing Payment</h1>
            <span class="<?php echo $status_class; ?>"><?php echo ucfirst($payment_status); ?></span>
            <p class="text-muted" style="font-size: 1.05rem;">Please check your Airtel Money phone for a prompt.</p>
            <p class="text-muted" style="font-size: 0.9rem;">You will receive an SMS confirmation once the payment is complete.</p>
        <?php endif; ?>

        <div class="details-section">
            <div class="detail-row">
                <span class="detail-label">Amount</span>
                <span class="detail-value">ZMW <?php echo number_format($payment['amount'], 2); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone Number</span>
                <span class="detail-value">**** <?php echo substr($payment['phone_number'], -4); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Transaction ID</span>
                <span class="detail-value" style="font-family: monospace; font-size: 0.85rem;">
                    <?php echo htmlspecialchars($payment['transaction_id']); ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date</span>
                <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></span>
            </div>
            <?php if (!empty($payment['description'])): ?>
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['description']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="button-group">
            <?php if ($payment_status !== 'completed'): ?>
                <button class="btn btn-primary" onclick="location.reload();">🔄 Refresh Status</button>
            <?php endif; ?>
            <a href="client_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if ($payment_status === 'processing'): ?>
            <script>
                // Auto-refresh every 5 seconds for 2 minutes
                let refresh_count = 0;
                const refresh_interval = setInterval(() => {
                    refresh_count++;
                    if (refresh_count > 24) { // 24 * 5 seconds = 2 minutes
                        clearInterval(refresh_interval);
                    } else {
                        location.reload();
                    }
                }, 5000);
            </script>
        <?php endif; ?>
    </div>

</body>
</html>
