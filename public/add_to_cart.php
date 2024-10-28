<?php
session_start();
include '../src/helpers/db_connect.php'; // Database connection

// Check if a product_id was submitted
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Check if cart exists in session, if not, create it
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1; // Increment quantity
    } else {
        // Fetch product details for the cart item
        $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ? AND approval_status = 'approved'");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // Add product to cart
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'image_url' => $product['image_url'],
            'quantity' => 1
        ];
    }

    // Update or insert the product in the 'cart' table
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();

    if ($cart_result->num_rows > 0) {
        // If the product is already in the cart, update the quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        // If the product is not in the cart, insert it
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $quantity = 1;
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt->execute();
    }

    // Set success message in session
    $_SESSION['message'] = "Product added to cart successfully!";

    // Redirect back to the shop page
    header("Location: shop.php?added_to_cart=true");
    exit;
}
?>
