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
$error = '';
$success = '';

if (isset($_POST['add_payment'])) {
    $method_type = $conn->real_escape_string($_POST['method_type']);
    
    if ($method_type === 'credit_card' || $method_type === 'debit_card') {
        $card_holder_name = $conn->real_escape_string($_POST['card_holder_name']);
        $card_number = preg_replace('/\D/', '', $_POST['card_number']); // Remove non-digits
        $card_expiry = $conn->real_escape_string($_POST['card_expiry']);
        
        // Validate card number (basic check)
        if (strlen($card_number) < 13 || strlen($card_number) > 19) {
            $error = "Invalid card number. Must be 13-19 digits.";
        } elseif (empty($card_holder_name) || empty($card_expiry)) {
            $error = "Please fill in all card details.";
        } else {
            $card_last_four = substr($card_number, -4);
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            
            // If no other payment methods exist, make this one default
            $check_count = $conn->query("SELECT COUNT(*) as count FROM payment_methods WHERE client_id = $client_id");
            $count_result = $check_count->fetch_assoc();
            if ($count_result['count'] === 0) {
                $is_default = 1;
            }
            
            if ($is_default) {
                $conn->query("UPDATE payment_methods SET is_default = 0 WHERE client_id = $client_id");
            }
            
            $sql = "INSERT INTO payment_methods (client_id, method_type, card_holder_name, card_last_four, card_expiry, is_default) 
                    VALUES ($client_id, '$method_type', '$card_holder_name', '$card_last_four', '$card_expiry', $is_default)";
            
            if ($conn->query($sql)) {
                header("Location: client_payment_methods.php?msg=Payment method added successfully.");
                exit();
            } else {
                $error = "Error saving payment method. Please try again.";
            }
        }
    } elseif ($method_type === 'bank_account') {
        $bank_name = $conn->real_escape_string($_POST['bank_name']);
        $bank_account_number = preg_replace('/\D/', '', $_POST['bank_account_number']);
        
        if (empty($bank_name) || empty($bank_account_number) || strlen($bank_account_number) < 8) {
            $error = "Please provide valid bank details.";
        } else {
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            
            $check_count = $conn->query("SELECT COUNT(*) as count FROM payment_methods WHERE client_id = $client_id");
            $count_result = $check_count->fetch_assoc();
            if ($count_result['count'] === 0) {
                $is_default = 1;
            }
            
            if ($is_default) {
                $conn->query("UPDATE payment_methods SET is_default = 0 WHERE client_id = $client_id");
            }
            
            $sql = "INSERT INTO payment_methods (client_id, method_type, bank_name, bank_account_number, is_default) 
                    VALUES ($client_id, '$method_type', '$bank_name', '$bank_account_number', $is_default)";
            
            if ($conn->query($sql)) {
                header("Location: client_payment_methods.php?msg=Bank account added successfully.");
                exit();
            } else {
                $error = "Error saving bank account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Payment Method | M. CHUNGA & COMPANY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #1e293b;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
            cursor: pointer;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
        }
        .btn-primary {
            background: #1e3a8a;
            color: white;
        }
        .btn-primary:hover {
            background: #1e40af;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #1e293b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body style="background: #f1f5f9; font-family: sans-serif; margin: 0; padding: 20px;">

    <div style="max-width: 600px; margin: auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        
        <h1 style="color: #1e3a8a; margin-top: 0;">Add Payment Method</h1>
        <p style="color: #64748b; margin-bottom: 30px;">Securely add a new payment method to your account.</p>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca;">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            
            <div class="form-group">
                <label for="method_type">Payment Method Type</label>
                <select id="method_type" name="method_type" required onchange="updateForm()">
                    <option value="">Select a method type</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="bank_account">Bank Account</option>
                </select>
            </div>

            <!-- Credit/Debit Card Form -->
            <div id="card_fields">
                <div class="form-group">
                    <label for="card_holder_name">Cardholder Name</label>
                    <input type="text" id="card_holder_name" name="card_holder_name" placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                </div>

                <div class="form-group">
                    <label for="card_expiry">Expiry Date (MM/YY)</label>
                    <input type="text" id="card_expiry" name="card_expiry" placeholder="12/25" maxlength="5">
                </div>
            </div>

            <!-- Bank Account Form -->
            <div id="bank_fields" class="hidden">
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="First National Bank">
                </div>

                <div class="form-group">
                    <label for="bank_account_number">Account Number</label>
                    <input type="text" id="bank_account_number" name="bank_account_number" placeholder="1234567890">
                </div>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_default" name="is_default" value="1">
                <label for="is_default" style="margin-bottom: 0;">Set as default payment method</label>
            </div>

            <div class="button-group">
                <button type="submit" name="add_payment" class="btn btn-primary">Add Payment Method</button>
                <a href="client_payment_methods.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

    </div>

    <script>
        function updateForm() {
            const methodType = document.getElementById('method_type').value;
            const cardFields = document.getElementById('card_fields');
            const bankFields = document.getElementById('bank_fields');
            
            if (methodType === 'credit_card' || methodType === 'debit_card') {
                cardFields.classList.remove('hidden');
                bankFields.classList.add('hidden');
                document.getElementById('card_holder_name').required = true;
                document.getElementById('card_number').required = true;
                document.getElementById('card_expiry').required = true;
                document.getElementById('bank_name').required = false;
                document.getElementById('bank_account_number').required = false;
            } else if (methodType === 'bank_account') {
                cardFields.classList.add('hidden');
                bankFields.classList.remove('hidden');
                document.getElementById('card_holder_name').required = false;
                document.getElementById('card_number').required = false;
                document.getElementById('card_expiry').required = false;
                document.getElementById('bank_name').required = true;
                document.getElementById('bank_account_number').required = true;
            }
        }

        // Format card number with spaces
        document.getElementById('card_number').addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '');
            let formattedValue = value.replace(/(\d{4})/g, '$1 ').trim();
            this.value = formattedValue;
        });

        // Format expiry date
        document.getElementById('card_expiry').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
        });
    </script>

</body>
</html>
