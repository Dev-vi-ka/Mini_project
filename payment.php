<?php
session_start();
include 'includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
</head>
<body>

<h1>Order Summary</h1>

<?php
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $total = 0;

    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $sql = "SELECT * FROM products WHERE id = $product_id";
        $result = mysqli_query($conn, $sql);
        $product = mysqli_fetch_assoc($result);

        $subtotal = $product['price'] * $qty;
        $total += $subtotal;

        echo "<div style='margin-bottom: 10px;'>";
        echo "<strong>" . $product['name'] . "</strong><br>";
        echo "Price: ₹" . $product['price'] . "<br>";
        echo "Quantity: " . $qty . "<br>";
        echo "Subtotal: ₹" . $subtotal . "<br>";
        echo "</div><hr>";
    }

    echo "<h3>Total: ₹" . $total . "</h3>";

    // Payment form
    echo "<form method='post' action='pay.php'>";
    echo "<input type='submit' name='pay' value='Pay Now'>";
    echo "</form>";

} else {
    echo "<p>Your cart is empty.</p>";
    echo "<a href='index.php'>Back to Products</a>";
}
?>

</body>
</html>
