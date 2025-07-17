<?php
session_start();

if (!isset($_SESSION['buy_now'])) {
    header("Location: index.php");
    exit();
}

$buy_now = $_SESSION['buy_now'];
$product_name = $buy_now['product_name'];
$price = $buy_now['price'];
$quantity = $buy_now['quantity'];
$subtotal = $price * $quantity;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buy Now - <?php echo $product_name; ?></title>
</head>
<body>

<h1>Buy Now: <?php echo $product_name; ?></h1>

<p>Price: ₹<?php echo $price; ?></p>
<p>Quantity: <?php echo $quantity; ?></p>
<p>Total: ₹<?php echo $subtotal; ?></p>

<form method="post" action="thankyou.php">
    <input type="submit" name="pay" value="Pay Now">
</form>

</body>
</html>
