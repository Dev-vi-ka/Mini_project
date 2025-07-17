<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {

    // If it's a Buy Now order
    if (isset($_SESSION['buy_now'])) {
        $buy_now = $_SESSION['buy_now'];
        $product_id = $buy_now['product_id'];
        $quantity = $buy_now['quantity'];

        // Fetch current stock
        $stockResult = mysqli_query($conn, "SELECT stock FROM products WHERE id = $product_id");
        $stockRow = mysqli_fetch_assoc($stockResult);
        $currentStock = $stockRow['stock'];

        if ($currentStock >= $quantity) {
            // Deduct stock
            $updateStock = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
            mysqli_query($conn, $updateStock);

            // Clear Buy Now session
            unset($_SESSION['buy_now']);

        } else {
            echo "<h1>❌ Not enough stock available!</h1>";
            echo "<a href='index.php'>Back to Products</a>";
            exit();
        }
    }

    // If it's a Cart order
    if (isset($_SESSION['cart'])) {
        // Check stock for all products first
        $insufficientStock = false;

        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $stockResult = mysqli_query($conn, "SELECT stock FROM products WHERE id = $product_id");
            $stockRow = mysqli_fetch_assoc($stockResult);
            $currentStock = $stockRow['stock'];

            if ($currentStock < $quantity) {
                $insufficientStock = true;
                $productWithIssue = $product_id;
                break;
            }
        }

        if ($insufficientStock) {
            echo "<h1>❌ Not enough stock available for one of your cart items (Product ID: $productWithIssue)!</h1>";
            echo "<a href='cart.php'>Back to Cart</a>";
            exit();
        } else {
            // Deduct stock for all
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $updateStock = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
                mysqli_query($conn, $updateStock);
            }

            // Clear Cart session
            unset($_SESSION['cart']);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
</head>
<body>

<h1>✅ Thank you for your purchase!</h1>
<p>Your order is being prepared.</p>

<a href="index.php">Back to Products</a>

</body>
</html>
