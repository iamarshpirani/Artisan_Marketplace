<?php
session_start();
require 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_id = $_GET['id'];

// Delete the payment method
$stmt = $conn->prepare("DELETE FROM payment_methods WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $payment_id, $user_id);

if ($stmt->execute()) {
    header("Location: profile.php#payment-methods?success=delete"); // Redirect back with success message
    exit();
} else {
    echo "Error deleting payment method: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
