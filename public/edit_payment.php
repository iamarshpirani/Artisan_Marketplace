<?php
session_start();
include '../src/helpers/db_connect.php';

$user_id = $_SESSION['user_id'];
$error = '';
$method_id = $_GET['id'];

// Fetch the payment method details
$stmt = $conn->prepare("SELECT * FROM payment_methods WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $method_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Payment method not found.");
}

$payment_method = $result->fetch_assoc();
$stmt->close();

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_type = $_POST['card_type'];
    $card_number = $_POST['card_number'];
    $expiration_date = $_POST['expiration_date'];
    $cardholder_name = $_POST['cardholder_name'];

    if (empty($card_type) || empty($card_number) || empty($expiration_date) || empty($cardholder_name)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/^\d{16}$/', $card_number)) {
        $error = "Card number must be 16 digits.";
    } else {
        // Update the payment method
        $stmt = $conn->prepare("UPDATE payment_methods SET card_type = ?, card_number = ?, expiration_date = ?, cardholder_name = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $card_type, $card_number, $expiration_date, $cardholder_name, $method_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: payment_methods.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Payment Method</title>
</head>
<body>
<div class="container mt-5">
    <h2>Edit Payment Method</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form action="edit_payment.php?id=<?php echo $method_id; ?>" method="POST">
        <div class="mb-3">
            <label for="card_type" class="form-label">Card Type</label>
            <select class="form-select" id="card_type" name="card_type" required>
                <option value="Debit" <?php if ($payment_method['card_type'] == 'Debit') echo 'selected'; ?>>Debit</option>
                <option value="
