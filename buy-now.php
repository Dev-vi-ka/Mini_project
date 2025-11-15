<?php
session_start();

if (!isset($_SESSION['buy_now'])) {
    header("Location: index.php");
    exit();
}

$buy_now = $_SESSION['buy_now'];
$product_id = $buy_now['product_id'];
$product_name = $buy_now['product_name'];
$price = $buy_now['price'];
$quantity = $buy_now['quantity'];
$subtotal = $price * $quantity;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buy Now - <?php echo htmlspecialchars($product_name); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <style>
    * {
      box-sizing: border-box;
    }
    html, body {
      margin: 0;
      padding: 0;
    }
    body {
      background-color: #fff8f0;
      font-family: 'Segoe UI', sans-serif;
      font-size: 14px;
    }
    .buy-card {
      max-width: 500px;
      margin: 20px auto;
      background: white;
      border-radius: 1rem;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      padding: 1.5rem;
    }
    @media (min-width: 768px) {
      .buy-card {
        margin: 5vh auto;
        padding: 2rem;
      }
    }
    .buy-title {
      font-weight: 700;
      font-size: 1.3rem;
      color: #d63384; /* professional pink */
    }
    @media (min-width: 768px) {
      .buy-title {
        font-size: 1.5rem;
      }
    }
    .price-label {
      font-weight: 500;
      color: #333;
      margin: 0.3rem 0;
      font-size: 14px;
    }
    .total {
      font-size: 1.2rem;
      font-weight: bold;
      color: #c06c84; /* slightly muted, elegant pink */
      margin-top: 1rem;
    }
    @media (min-width: 768px) {
      .total {
        font-size: 1.3rem;
      }
    }
    .pay-btn {
      background-color: #d63384;
      color: white;
      font-weight: 600;
      border-radius: 8px;
      transition: background 0.2s ease-in-out;
      min-height: 44px;
    }
    .pay-btn:hover {
      background-color: #b52a6b;
    }
    .back-link {
      color: #6c757d;
      font-size: 0.85rem;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <section class="section">
    <div class="buy-card">
      <p class="has-text-right"><a href="index.php" class="back-link">← Back to Products</a></p>
      <h1 class="buy-title">Buy Now: <?php echo htmlspecialchars($product_name); ?></h1>
      <hr>

      <p class="price-label">Price per item: ₹<?php echo number_format($price, 2); ?></p>
      <p class="price-label">Quantity: <?php echo $quantity; ?></p>
      <p class="price-label total">Total: ₹<?php echo number_format($subtotal, 2); ?></p>

      <form method="post" action="pay.php">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
        <button type="submit" name="pay" class="button is-fullwidth mt-4 pay-btn">Pay Now</button>
      </form>
    </div>
  </section>

</body>
</html>
