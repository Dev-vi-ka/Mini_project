<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $quantity = $_POST['quantity'];
    $action = $_POST['action'];

    if ($quantity > $stock) {
        die("Not enough stock available!");
    }

    if ($action == 'add_to_cart') {
        // Cart logic
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        header("Location: cart.php");
        exit();
    }

    if ($action == 'buy_now') {
        // Buy Now logic — pass data to buy-now page via session
        $_SESSION['buy_now'] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'price' => $price,
            'quantity' => $quantity
        ];

        header("Location: buy-now.php");
        exit();
    }
}
?>
