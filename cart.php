<?php
session_start();
include 'includes/db.php';

// Handle Add to Cart (from product page)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <style>
        body {
            background-color: #fde6e6;
            font-family: 'Segoe UI', sans-serif;
        }
        .cart-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-remove {
            background-color: #e56b6b;
            color: white;
        }
        .btn-yellow {
            background-color: #c6ad4c;
            color: white;
        }
        .btn-back {
            background-color: #dc8b8b;
            color: white;
        }
    </style>
</head>
<body>

<section class="section">
    <div class="container">
        <h1 class="title has-text-centered mb-5">🛒 Your Cart</h1>

        <?php
        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
            $total = 0;

            foreach ($_SESSION['cart'] as $product_id => $qty) {
                $sql = "SELECT * FROM products WHERE id = $product_id";
                $result = mysqli_query($conn, $sql);
                $product = mysqli_fetch_assoc($result);

                $subtotal = $product['price'] * $qty;
                $total += $subtotal;
        ?>
                <div class="cart-card">
                    <div class="columns is-vcentered">
                        <div class="column is-narrow">
                            <figure class="image is-96x96">
                                <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            </figure>
                        </div>
                        <div class="column">
                            <p class="title is-5 mb-1"><?php echo $product['name']; ?></p>
                            <p class="is-size-6">Price: ₹<?php echo $product['price']; ?> &nbsp;|&nbsp; Quantity: <?php echo $qty; ?> &nbsp;|&nbsp; Subtotal: ₹<?php echo $subtotal; ?></p>
                        </div>
                        <div class="column is-narrow">
                            <form method="post" action="remove-item.php">
                                <input type="hidden" name="remove_id" value="<?php echo $product_id; ?>">
                                <button class="button btn-remove is-small">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
        <?php
            }

            echo "<div class='box has-background-light has-text-centered'>";
            echo "<h3 class='title is-4'>Total: ₹" . $total . "</h3>";
            echo "<a href='payment.php' class='button btn-yellow is-medium mt-3'>Proceed to Payment</a><br><br>";
            echo "<a href='index.php' class='button btn-back is-light'>← Back to Products</a>";
            echo "</div>";
        } else {
            echo "<div class='notification is-warning has-text-centered'>Your cart is empty.</div>";
            echo "<div class='has-text-centered'><a href='index.php' class='button btn-back mt-3'>← Back to Products</a></div>";
        }
        ?>
    </div>
</section>

</body>
</html>
