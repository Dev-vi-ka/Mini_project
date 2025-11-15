<?php
session_start();
include 'includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: #fef6f9; /* soft pink background */
            font-size: 14px;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        @media (min-width: 768px) {
            .container {
                margin: 40px auto;
                padding: 25px;
            }
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #d63384; /* deep pink */
            font-size: 1.5rem;
        }
        @media (min-width: 768px) {
            h1 {
                font-size: 2rem;
                margin-bottom: 25px;
            }
        }
        .product {
            margin-bottom: 12px;
            padding: 12px;
            border-radius: 8px;
            background: #fff0f6; /* pale pink box */
            border: 1px solid #f8d7e9;
        }
        @media (min-width: 768px) {
            .product {
                margin-bottom: 15px;
                padding: 15px;
            }
        }
        .product strong {
            font-size: 16px;
            color: #c2185b; /* darker pink for product names */
        }
        @media (min-width: 768px) {
            .product strong {
                font-size: 18px;
            }
        }
        hr {
            border: none;
            border-top: 1px solid #f3c6db;
            margin: 15px 0;
        }
        h3 {
            text-align: center;
            color: #880e4f;
            font-size: 1.2rem;
        }
        @media (min-width: 768px) {
            h3 {
                text-align: right;
            }
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
            min-height: 44px;
            width: 100%;
        }
        @media (min-width: 768px) {
            input[type="submit"] {
                width: auto;
            }
        }
        input[type="submit"]:hover {
            background: #c2185b;
        }
        .empty {
            text-align: center;
            padding: 15px;
            font-size: 16px;
            color: #666;
        }
        .empty a {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 15px;
            text-decoration: none;
            background: #d63384;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            transition: 0.3s;
            min-height: 44px;
            width: 100%;
        }
        @media (min-width: 768px) {
            .empty a {
                width: auto;
            }
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
