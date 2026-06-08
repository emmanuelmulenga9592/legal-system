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

// Fetch payment history
$sql = "SELECT p.*, c.title as case_title FROM payments p 
        LEFT JOIN cases c ON p.case_id = c.id 
        WHERE p.client_id = $client_id 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
$payments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}

// Calculate totals
$total_paid_sql = "SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id AND status = 'completed'";
$total_result = $conn->query($total_paid_sql)->fetch_assoc();
$total_paid = $total_result['total'] ?? 0;

$pending_sql = "SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id AND status IN ('pending', 'processing')";
$pending_result = $conn->query($pending_sql)->fetch_assoc();
$total_pending = $pending_result['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #1e3a8a;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .payment-table th {
            background: #f1f5f9;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
        }
        .payment-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .payment-table tbody tr:hover {
            background: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-pending {
            background: #fef9c3;
            color: #854d0e;
        }
        .status-failed {
            background: #fee2e2;
            color: #b91c1c;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        .empty-state h3 {
            color: #1e293b;
            margin-bottom: 20px;
        }
        .view-btn {
            background: #1e3a8a;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-block;
        }
        .view-btn:hover {
            background: #1e40af;
        }
    </style>
</head>
<body style="background: #f1f5f9; font-family: sans-serif; margin: 0; padding: 20px;">

    <div style="max-width: 1000px; margin: auto;">
        
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 style="color: #1e3a8a; margin: 0;">Payment History</h1>
                <p style="color: #64748b; margin: 5px 0 0 0;">Track all your payments and transactions</p>
            </div>
            <div>
                <a href="make_payment.php" class="btn-primary" style="text-decoration:none; background:#1e3a8a; color:white; padding:10px 15px; border-radius:6px; display:inline-block; margin-right:10px;">+ New Payment</a>
                <a href="client_dashboard.php" style="color: #64748b; text-decoration: none;">← Back to Dashboard</a>
            </div>
        </header>

        <?php if (count($payments) > 0): ?>
            <div class="header-stats">
                <div class="stat-card">
                    <div class="stat-label">Total Paid</div>
                    <div class="stat-value">ZMW <?php echo number_format($total_paid, 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending/Processing</div>
                    <div class="stat-value">ZMW <?php echo number_format($total_pending, 2); ?></div>
                </div>
            </div>

            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Case</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                        <td><strong>ZMW <?php echo number_format($payment['amount'], 2); ?></strong></td>
                        <td><?php echo $payment['case_title'] ? htmlspecialchars($payment['case_title']) : '<span style="color:#94a3b8;">N/A</span>'; ?></td>
                        <td>
                            <?php if ($payment['payment_method'] === 'airtel_money'): ?>
                                💱 Airtel Money
                            <?php elseif ($payment['payment_method'] === 'credit_card'): ?>
                                💳 Credit Card
                            <?php elseif ($payment['payment_method'] === 'debit_card'): ?>
                                💳 Debit Card
                            <?php else: ?>
                                🏦 Bank Account
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $payment['status']; ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="payment_status.php?id=<?php echo $payment['id']; ?>" class="view-btn">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Payments Yet</h3>
                <p>You haven't made any payments yet. When you're ready to pay for your legal services, click the button below.</p>
                <a href="make_payment.php" class="view-btn" style="padding: 10px 20px; font-size: 1rem;">Make a Payment</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
