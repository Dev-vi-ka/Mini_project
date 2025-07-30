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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <style>
    body {
      background-color: #fff8f0;
      font-family: 'Segoe UI', sans-serif;
    }
    .buy-card {
      max-width: 500px;
      margin: 5vh auto;
      background: white;
      border-radius: 1rem;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      padding: 2rem;
    }
    .buy-title {
      font-weight: 700;
      font-size: 1.5rem;
      color: #d63384;
    }
    .price-label {
      font-weight: 500;
      color: #333;
    }
    .total {
      font-size: 1.3rem;
      font-weight: bold;
      color: #fd7e14;
    }
    .pay-btn {
      background-color: #d63384;
      color: white;
      font-weight: 600;
    }
    .back-link {
      color: #6c757d;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

  <section class="section">
    <div class="buy-card">
      <p class="has-text-right"><a href="index.php" class="back-link">← Back to Products</a></p>
      <h1 class="buy-title">Buy Now: <?php echo $product_name; ?></h1>
      <hr>

      <p class="price-label">Price per item: ₹<?php echo $price; ?></p>
      <p class="price-label">Quantity: <?php echo $quantity; ?></p>
      <p class="price-label total">Total: ₹<?php echo $subtotal; ?></p>

      <form method="post" action="pay.php">
        <button type="submit" name="pay" class="button is-fullwidth mt-4 pay-btn">Pay Now</button>
      </form>
    </div>
  </section>

</body>
</html>
