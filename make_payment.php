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
$error = '';
$success = '';

// Handle payment initiation
if (isset($_POST['make_payment'])) {
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $amount = floatval($_POST['amount']);
    $case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : null;
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    
    // Validate inputs
    if (empty($phone_number) || $amount <= 0) {
        $error = "Please provide a valid phone number and amount.";
    } else {
        // Create transaction record
        $transaction_id = 'TXN-' . $client_id . '-' . time();
        
        $sql = "INSERT INTO payments (client_id, case_id, amount, currency, payment_method, phone_number, transaction_id, description, status) 
                VALUES ($client_id, $case_id, $amount, 'ZMW', 'airtel_money', '$phone_number', '$transaction_id', '$description', 'pending')";
        
        if ($conn->query($sql)) {
            $payment_id = $conn->insert_id;
            
            // Initialize Airtel Money API
            $airtel = new AirtelMoneyAPI(true); // true for sandbox, false for production
            
            // Initiate payment
            $payment_response = $airtel->initiatePayment($phone_number, $amount, $transaction_id, 'Legal Services - ' . $description);
            
            if (isset($payment_response['error'])) {
                $error = "Payment initiation failed: " . htmlspecialchars($payment_response['error']);
                $conn->query("UPDATE payments SET status = 'failed' WHERE id = $payment_id");
            } elseif (isset($payment_response['data']['transaction_id'])) {
                // Payment initiated successfully
                $airtel_ref = $payment_response['data']['transaction_id'];
                $conn->query("UPDATE payments SET airtel_reference = '$airtel_ref', status = 'processing' WHERE id = $payment_id");
                
                $_SESSION['payment_id'] = $payment_id;
                $_SESSION['transaction_id'] = $transaction_id;
                $_SESSION['airtel_reference'] = $airtel_ref;
                
                header("Location: payment_status.php?id=$payment_id&airtel_ref=$airtel_ref");
                exit();
            } else {
                $error = "Unexpected response from Airtel Money. Please try again.";
                $conn->query("UPDATE payments SET status = 'failed' WHERE id = $payment_id");
            }
        } else {
            $error = "Failed to create payment record. Please try again.";
        }
    }
}

// Fetch user's cases for payment
$cases_sql = "SELECT id, title FROM cases WHERE client_id = $client_id ORDER BY created_at DESC";
$cases_result = $conn->query($cases_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Make Payment | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


    <div class="container">
        <div class="card" style="max-width: 620px; margin: 40px auto;">
            <h1 style="margin-top: 0; text-align: center;">Make a Payment</h1>
            <p class="text-muted" style="text-align: center; margin-bottom: 30px;">Pay for your legal services using Airtel Money</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>

            <div class="info-box">
                💡 <strong>How it works:</strong> Enter your Airtel Money phone number and amount. You'll receive a prompt on your phone to confirm the payment.
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="case_id">Case (Optional)</label>
                    <select id="case_id" name="case_id">
                        <option value="">--- Select a case ---</option>
                        <?php if ($cases_result && $cases_result->num_rows > 0): ?>
                            <?php while ($case = $cases_result->fetch_assoc()): ?>
                                <option value="<?php echo $case['id']; ?>"><?php echo htmlspecialchars($case['title']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Amount (ZMW) *</label>
                    <input type="number" id="amount" name="amount" placeholder="250.00" step="0.01" min="1" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Airtel Money Phone Number *</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="0975123456 or 26597512345" required>
                    <small class="text-muted" style="display: block; margin-top: 5px;">Format: Zambian number (0975123456) or international (260975123456)</small>
                </div>

                <div class="form-group">
                    <label for="description">Description (What is this payment for?)</label>
                    <textarea id="description" name="description" placeholder="e.g., Consultation fee, court representation, etc."></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" name="make_payment" class="btn btn-primary">💳 Pay via Airtel Money</button>
                    <a href="client_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

            <hr class="section-divider">

            <div class="card" style="background: #f8fafc; border: 1px solid var(--border); text-align: center;">
                <p class="text-muted" style="margin: 0 0 10px 0;">Other Payment Methods Available</p>
                <a href="client_payment_methods.php" class="btn btn-info">Manage Credit Cards & Bank Accounts →</a>
            </div>
        </div>
    </div>

</body>
</html>
