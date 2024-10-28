<?php
session_start();
include '../src/helpers/db_connect.php'; // Adjust the path as necessary

// Initialize error messages
$errors = [];
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $cardType = $_POST['card_type'] ?? '';
    $cardNumber = $_POST['card_number'] ?? '';
    $expirationDate = $_POST['expiration_date'] ?? '';
    $cardholderName = $_POST['cardholder_name'] ?? '';

    // Manual validation
    if (empty($cardType)) {
        $errors['card_type'] = "Card type is required.";
    }

    if (empty($cardNumber)) {
        $errors['card_number'] = "Card number is required.";
    } elseif (!preg_match('/^\d{16}$/', $cardNumber)) {
        $errors['card_number'] = "Card number must be 16 digits.";
    }

    if (empty($expirationDate)) {
        $errors['expiration_date'] = "Expiration date is required.";
    } elseif (!DateTime::createFromFormat('Y-m', $expirationDate)) {
        $errors['expiration_date'] = "Invalid expiration date format. Use YYYY-MM.";
    }

    if (empty($cardholderName)) {
        $errors['cardholder_name'] = "Cardholder name is required.";
    }

    // If no errors, proceed to insert the payment method into the database
    if (empty($errors)) {
        $userId = $_SESSION['user_id']; // Assuming user ID is stored in session
        $stmt = $conn->prepare("INSERT INTO payment_methods (user_id, card_type, card_number, expiration_date, cardholder_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $cardType, $cardNumber, $expirationDate, $cardholderName);

        if ($stmt->execute()) {
            $successMessage = "Payment method added successfully!";
        } else {
            $errors['database'] = "Error adding payment method: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add Payment Method</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Add Payment Method</h2>

        <!-- Display success or error messages -->
        <?php if (!empty($successMessage)) : ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)) : ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="add_payment.php" method="POST">
            <div class="mb-3">
                <label for="card_type" class="form-label">Card Type</label>
                <select name="card_type" id="card_type" class="form-select <?php echo isset($errors['card_type']) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Card Type</option>
                    <option value="Debit" <?php echo (isset($_POST['card_type']) && $_POST['card_type'] === 'Debit') ? 'selected' : ''; ?>>Debit</option>
                    <option value="Credit" <?php echo (isset($_POST['card_type']) && $_POST['card_type'] === 'Credit') ? 'selected' : ''; ?>>Credit</option>
                </select>
                <?php if (isset($errors['card_type'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['card_type']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="card_number" class="form-label">Card Number</label>
                <input type="text" name="card_number" id="card_number" class="form-control <?php echo isset($errors['card_number']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['card_number'] ?? ''); ?>">
                <?php if (isset($errors['card_number'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['card_number']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="expiration_date" class="form-label">Expiration Date (YYYY-MM)</label>
                <input type="text" name="expiration_date" id="expiration_date" class="form-control <?php echo isset($errors['expiration_date']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['expiration_date'] ?? ''); ?>">
                <?php if (isset($errors['expiration_date'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['expiration_date']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="cardholder_name" class="form-label">Cardholder Name</label>
                <input type="text" name="cardholder_name" id="cardholder_name" class="form-control <?php echo isset($errors['cardholder_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['cardholder_name'] ?? ''); ?>">
                <?php if (isset($errors['cardholder_name'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['cardholder_name']; ?></div>
                <?php endif; ?>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Add Payment Method</button>
                <a href="index.php" class="btn btn-secondary">Back to Home</a> <!-- Back to Home Button -->
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
