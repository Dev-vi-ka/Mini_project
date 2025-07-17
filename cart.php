<?php
session_start();
include 'includes/db.php';

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // If cart not set, create it
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // If product already in cart, update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
</head>
<body>

<h1>Your Cart</h1>

<?php
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $total = 0;

    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $sql = "SELECT * FROM products WHERE id = $product_id";
        $result = mysqli_query($conn, $sql);
        $product = mysqli_fetch_assoc($result);

        $subtotal = $product['price'] * $qty;
        $total += $subtotal;

        echo "<div style='margin-bottom: 20px;'>";
        echo "<img src='images/" . $product['image'] . "' width='80'><br>";
        echo "<strong>" . $product['name'] . "</strong><br>";
        echo "Price: ₹" . $product['price'] . "<br>";
        echo "Quantity: " . $qty . "<br>";
        echo "Subtotal: ₹" . $subtotal . "<br>";

        // Remove item form
        echo "<form method='post' action='remove-item.php'>";
        echo "<input type='hidden' name='remove_id' value='" . $product_id . "'>";
        echo "<input type='submit' value='Remove'>";
        echo "</form>";

        echo "</div><hr>";
    }

    echo "<h3>Total: ₹" . $total . "</h3>";

    echo "<a href='payment.php'>Proceed to Payment</a>";

} else {
    echo "<p>Your cart is empty.</p>";
    echo "<a href='index.php'>Back to Products</a>";
}
?>

</body>
</html>
