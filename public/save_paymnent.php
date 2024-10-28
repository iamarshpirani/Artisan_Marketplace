<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id']; // Ensure user ID is available in the session

// Retrieve form data
$payment_type = $_POST['payment_type'];
$card_type = $_POST['card_type'];
$card_number = $_POST['card_number'];
$cvv = $_POST['cvv'];
$expiration = $_POST['expiration'];

// Mask card details and get last 4 digits for storage
$card_last4 = substr($card_number, -4);

// Insert into the payment_methods table
$sql = "INSERT INTO payment_methods (user_id, payment_type, card_type, card_last4, expiration, cvv, added_on) VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssss", $user_id, $payment_type, $card_type, $card_last4, $expiration, $cvv);

if ($stmt->execute()) {
    echo "Payment method saved successfully!";
    header("Location: profile.php#payment-methods");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
