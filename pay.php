<?php
session_start();
include 'includes/db.php';
include 'includes/razorpay_config.php';

// Figure out mode + compute amount (in paise)
$mode = null;
$amountPaise = 0;  // Razorpay expects smallest unit

// --- BUY NOW mode ---
if (isset($_SESSION['buy_now'])) {
    $mode = 'buy_now';
    $p = $_SESSION['buy_now']; // product_id, product_name, price, quantity

    // Stock check (fresh)
    $pid = (int)$p['product_id'];
    $qty = (int)$p['quantity'];
    $rs = mysqli_query($conn, "SELECT stock, price FROM products WHERE id={$pid}");
    $row = mysqli_fetch_assoc($rs);
    if (!$row || $row['stock'] < $qty) {
        echo "<h2>❌ Not enough stock for this product.</h2><a href='index.php'>Back</a>";
        exit;
    }

    // Use DB price as source of truth
    $amountPaise = ((int)$row['price'] * $qty) * 100;

// --- CART mode ---
} elseif (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $mode = 'cart';

    // Check stock for all cart items and compute total from DB prices
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $pid = (int)$product_id;
        $qty = (int)$qty;

        $rs = mysqli_query($conn, "SELECT stock, price FROM products WHERE id={$pid}");
        $row = mysqli_fetch_assoc($rs);
        if (!$row || $row['stock'] < $qty) {
            echo "<h2>❌ Not enough stock for one of your items (Product ID: {$pid}).</h2><a href='cart.php'>Back to Cart</a>";
            exit;
        }
        $amountPaise += ((int)$row['price'] * $qty) * 100;
    }
} else {
    header('Location: index.php');
    exit;
}

// Create a unique receipt ID for traceability
$receiptId = strtoupper(($mode === 'buy_now' ? 'BN' : 'CRT')) . '_' . time();

// Create Razorpay Order via Orders API (Basic Auth with KEY_ID:KEY_SECRET)
$payload = json_encode([
    'amount'          => $amountPaise,
    'currency'        => RAZORPAY_CURRENCY,
    'receipt'         => $receiptId,
    'payment_capture' => 1  // let Razorpay auto-capture
]);

$ch = curl_init(RAZORPAY_API_ORDERS);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($response === false || $httpcode < 200 || $httpcode >= 300) {
    echo "<h2>Could not create Razorpay Order. Try again.</h2>";
    if ($response) echo "<pre>".htmlspecialchars($response)."</pre>";
    exit;
}
curl_close($ch);

$order = json_decode($response, true);
$razorpayOrderId = $order['id'] ?? null;
if (!$razorpayOrderId) {
    echo "<h2>Invalid response from Razorpay.</h2>";
    exit;
}

// Save order in session to use during verification
$_SESSION['rzp_order'] = [
    'order_id' => $razorpayOrderId,
    'amount'   => $amountPaise,
    'mode'     => $mode,
];

?>
<!DOCTYPE html>
<html>
<head><title>Redirecting to Payment…</title></head>
<body>
<h2>Redirecting to payment…</h2>

<!-- Razorpay Checkout script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
// Minimal Checkout options
var options = {
    key:        "<?php echo RAZORPAY_KEY_ID; ?>",
    amount:     "<?php echo $amountPaise; ?>",
    currency:   "<?php echo RAZORPAY_CURRENCY; ?>",
    name:       "Vending Machine",
    description:"Order: <?php echo $receiptId; ?>",
    order_id:   "<?php echo $razorpayOrderId; ?>",

    // Prefer redirect flow so that server receives POST on completion
    callback_url: "verify.php",
    redirect: true,

    // Optional prefill in test mode
    prefill: { name: "Test User", email: "test@example.com", contact: "9999999999" },
    notes:   { mode: "<?php echo $mode; ?>", receipt: "<?php echo $receiptId; ?>" }
};

var rzp = new Razorpay(options);
// Auto-open checkout
rzp.open();
</script>

<noscript>
  JavaScript required to proceed to payment.
</noscript>
</body>
</html>
