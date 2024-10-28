<?php
session_start();
include '../src/helpers/db_connect.php'; // Database connection

// Optionally save cart to the database for this user
if (isset($_SESSION['user_id']) && !empty($_SESSION['cart'])) {
    $user_id = $_SESSION['user_id'];
    foreach ($_SESSION['cart'] as $product_id => $item) {
        // Update or insert the product in the 'cart' table
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $cart_result = $stmt->get_result();

        if ($cart_result->num_rows > 0) {
            // If the product is already in the cart, update the quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $quantity = $item['quantity'];
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt->execute();
        } else {
            // If the product is not in the cart, insert it
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $quantity = $item['quantity'];
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $stmt->execute();
        }
    }
}

// Clear session data
session_unset();
session_destroy();

// Redirect to the homepage
header("Location: index.php");
exit;
?>
