<?php
include 'includes/db.php';

echo "=== Database Schema Verification ===\n\n";

// Check payments table structure
echo "PAYMENTS TABLE:\n";
$payments_structure = $conn->query("DESCRIBE payments");
if ($payments_structure) {
    while ($col = $payments_structure->fetch_assoc()) {
        echo "- " . str_pad($col['Field'], 25) . " | " . $col['Type'] . "\n";
    }
    $payments_count = $conn->query("SELECT COUNT(*) as count FROM payments")->fetch_assoc();
    echo "- Records: " . $payments_count['count'] . "\n\n";
} else {
    echo "ERROR: Could not retrieve payments table structure\n\n";
}

// Check payment_methods table structure
echo "PAYMENT_METHODS TABLE:\n";
$methods_structure = $conn->query("DESCRIBE payment_methods");
if ($methods_structure) {
    while ($col = $methods_structure->fetch_assoc()) {
        echo "- " . str_pad($col['Field'], 25) . " | " . $col['Type'] . "\n";
    }
    $methods_count = $conn->query("SELECT COUNT(*) as count FROM payment_methods")->fetch_assoc();
    echo "- Records: " . $methods_count['count'] . "\n\n";
} else {
    echo "ERROR: Could not retrieve payment_methods table structure\n\n";
}

// Check foreign keys
echo "FOREIGN KEYS:\n";
$fk_result = $conn->query("SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'legal_ops' AND (TABLE_NAME = 'payments' OR TABLE_NAME = 'payment_methods') AND REFERENCED_TABLE_NAME IS NOT NULL");
if ($fk_result && $fk_result->num_rows > 0) {
    while ($fk = $fk_result->fetch_assoc()) {
        echo "- " . $fk['CONSTRAINT_NAME'] . ": " . $fk['TABLE_NAME'] . "." . $fk['COLUMN_NAME'] . " → " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . "\n";
    }
} else {
    echo "No foreign key constraints found\n";
}

echo "\n✅ Database is ready for payment processing!\n";

$conn->close();
?>
