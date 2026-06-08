<?php

/**
 * Airtel Money Webhook Handler
 * Handles payment confirmation callbacks from Airtel Money API
 */

include 'includes/db.php';

// Get webhook secret
$config = include 'config/airtel_config.php';
$webhook_secret = $config['webhook_secret'] ?? '';

// Get request body
$body = file_get_contents('php://input');
$data = json_decode($body, true);

// Log webhook request
$log_file = dirname(__FILE__) . '/logs/airtel_webhook.log';
if (!is_dir(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}
file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] Webhook received: ' . $body . "\n", FILE_APPEND);

// Verify webhook signature (implement based on Airtel's requirements)
// For now, we'll accept all webhooks with proper structure

if (!$data || !isset($data['transaction_id']) || !isset($data['status'])) {
    file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] Invalid webhook data: missing required fields' . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook data']);
    exit();
}

$transaction_id = $data['transaction_id'];
$status = strtolower($data['status']);
$reference = $data['reference'] ?? null;

// Find payment record
$sql = "SELECT id, client_id FROM payments WHERE transaction_id = '" . $conn->real_escape_string($transaction_id) . "'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $payment = $result->fetch_assoc();
    $payment_id = $payment['id'];
    
    // Determine new status
    if ($status === 'success' || $status === 'completed') {
        $new_status = 'completed';
        $completed_at = 'NOW()';
    } elseif ($status === 'failed') {
        $new_status = 'failed';
        $completed_at = 'NOW()';
    } elseif ($status === 'pending') {
        $new_status = 'processing';
        $completed_at = 'NULL';
    } else {
        $new_status = 'pending';
        $completed_at = 'NULL';
    }
    
    // Update payment record
    $update_sql = "UPDATE payments SET status = '$new_status'" . 
                  ($completed_at !== 'NULL' ? ", completed_at = $completed_at" : '') .
                  ($reference ? ", airtel_reference = '" . $conn->real_escape_string($reference) . "'" : '') .
                  " WHERE id = $payment_id";
    
    if ($conn->query($update_sql)) {
        file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] Payment ' . $payment_id . ' updated to ' . $new_status . "\n", FILE_APPEND);
        
        // Send confirmation email to client (optional)
        if ($new_status === 'completed') {
            // You can add email notification here using your mailer
            file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] Payment ' . $payment_id . ' completed - consider sending confirmation email' . "\n", FILE_APPEND);
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'payment_id' => $payment_id]);
    } else {
        file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] Database error: ' . $conn->error . "\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
} else {
    file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] Payment not found for transaction: ' . $transaction_id . "\n", FILE_APPEND);
    http_response_code(404);
    echo json_encode(['error' => 'Payment not found']);
}

?>
