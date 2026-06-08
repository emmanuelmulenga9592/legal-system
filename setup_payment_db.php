<?php
include 'includes/db.php';

// Create payments table
$payments_sql = "CREATE TABLE IF NOT EXISTS payments (
    id int(11) NOT NULL AUTO_INCREMENT,
    client_id int(11) NOT NULL,
    case_id int(11) DEFAULT NULL,
    amount decimal(10, 2) NOT NULL,
    currency varchar(3) DEFAULT 'ZMW',
    payment_method enum('airtel_money','credit_card','debit_card','bank_account') DEFAULT 'airtel_money',
    phone_number varchar(20) DEFAULT NULL,
    transaction_id varchar(100) DEFAULT NULL,
    airtel_reference varchar(100) DEFAULT NULL,
    status enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
    description text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    completed_at datetime DEFAULT NULL,
    PRIMARY KEY (id),
    KEY client_id (client_id),
    KEY case_id (case_id),
    UNIQUE KEY transaction_id (transaction_id),
    UNIQUE KEY airtel_reference (airtel_reference),
    CONSTRAINT payments_ibfk_1 FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT payments_ibfk_2 FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($payments_sql)) {
    echo "✅ Payments table created successfully.\n";
} else {
    echo "⚠️ Error creating payments table: " . $conn->error . "\n";
}

// Check if payment_methods table exists and has the correct structure
$check_payment_methods = $conn->query("SHOW TABLES LIKE 'payment_methods'");
if ($check_payment_methods && $check_payment_methods->num_rows > 0) {
    // Check if columns exist
    $columns = $conn->query("SHOW COLUMNS FROM payment_methods");
    $column_names = [];
    while ($col = $columns->fetch_assoc()) {
        $column_names[] = $col['Field'];
    }
    
    if (!in_array('is_default', $column_names)) {
        $conn->query("ALTER TABLE payment_methods ADD COLUMN is_default tinyint(1) DEFAULT 0");
        echo "✅ Added is_default column to payment_methods table.\n";
    } else {
        echo "✅ Payment methods table already has is_default column.\n";
    }
    
    if (!in_array('created_at', $column_names)) {
        $conn->query("ALTER TABLE payment_methods ADD COLUMN created_at timestamp NOT NULL DEFAULT current_timestamp()");
        echo "✅ Added created_at column to payment_methods table.\n";
    }
    
    if (!in_array('updated_at', $column_names)) {
        $conn->query("ALTER TABLE payment_methods ADD COLUMN updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()");
        echo "✅ Added updated_at column to payment_methods table.\n";
    }
} else {
    echo "⚠️ Payment methods table not found. Creating it now.\n";
    $payment_methods_sql = "CREATE TABLE IF NOT EXISTS payment_methods (
        id int(11) NOT NULL AUTO_INCREMENT,
        client_id int(11) NOT NULL,
        method_type enum('credit_card','debit_card','bank_account') DEFAULT 'credit_card',
        card_holder_name varchar(100) DEFAULT NULL,
        card_last_four varchar(4) DEFAULT NULL,
        card_expiry varchar(5) DEFAULT NULL,
        bank_account_number varchar(50) DEFAULT NULL,
        bank_name varchar(100) DEFAULT NULL,
        is_default tinyint(1) DEFAULT 0,
        created_at timestamp NOT NULL DEFAULT current_timestamp(),
        updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (id),
        KEY client_id (client_id),
        CONSTRAINT payment_methods_ibfk_1 FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($payment_methods_sql)) {
        echo "✅ Payment methods table created successfully.\n";
    } else {
        echo "⚠️ Error creating payment methods table: " . $conn->error . "\n";
    }
}

echo "\n✅ Database update complete!\n";
echo "\nDatabase Summary:\n";
echo "- payments table: Ready for payment transactions\n";
echo "- payment_methods table: Ready for saved payment methods\n";
echo "\nYou can now access:\n";
echo "- Make Payment: http://localhost/legal_system/make_payment.php\n";
echo "- Payment History: http://localhost/legal_system/payment_history.php\n";
echo "- Payment Methods: http://localhost/legal_system/client_payment_methods.php\n";

$conn->close();
?>
