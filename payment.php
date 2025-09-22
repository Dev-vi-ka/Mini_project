<?php
session_start();
include 'includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fef6f9; /* soft pink background */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #d63384; /* deep pink */
        }
        .product {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background: #fff0f6; /* pale pink box */
            border: 1px solid #f8d7e9;
        }
        .product strong {
            font-size: 18px;
            color: #c2185b; /* darker pink for product names */
        }
        hr {
            border: none;
            border-top: 1px solid #f3c6db;
            margin: 10px 0;
        }
        h3 {
            text-align: right;
            color: #880e4f;
        }
        form {
            text-align: center;
            margin-top: 20px;
        }
        input[type="submit"] {
            background: #e83e8c;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        input[type="submit"]:hover {
            background: #c2185b;
        }
        .empty {
            text-align: center;
            padding: 20px;
            font-size: 16px;
            color: #666;
        }
        .empty a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            background: #d63384;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            transition: 0.3s;
        }
        .empty a:hover {
            background: #ad1457;
        }
    </style>
</head>
<body>

<div class="container">
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

            echo "<div class='product'>";
            echo "<strong>" . $product['name'] . "</strong><br>";
            echo "Price: ₹" . $product['price'] . "<br>";
            echo "Quantity: " . $qty . "<br>";
            echo "Subtotal: ₹" . $subtotal . "<br>";
            echo "</div>";
        }

        echo "<hr><h3>Total: ₹" . $total . "</h3>";

        // Payment form
        echo "<form method='post' action='pay.php'>";
        echo "<input type='submit' name='pay' value='Pay Now'>";
        echo "</form>";

    } else {
        echo "<div class='empty'>";
        echo "<p>Your cart is empty.</p>";
        echo "<a href='index.php'>Back to Products</a>";
        echo "</div>";
    }
    ?>
</div>

</body>
</html>
