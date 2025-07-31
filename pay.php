<?php
session_start();
include 'includes/db.php';
include 'includes/razorpay_config.php'; // contains RAZORPAY_KEY_ID, SECRET, CURRENCY, and API URL

$mode = null;
$amountPaise = 0;  // Razorpay expects amount in paise

// --- BUY NOW ---
if (isset($_SESSION['buy_now'])) {
    $mode = 'buy_now';
    $p = $_SESSION['buy_now']; // product_id, quantity
    $pid = (int)$p['product_id'];
    $qty = (int)$p['quantity'];

    $rs = mysqli_query($conn, "SELECT stock, price FROM products WHERE id={$pid}");
    $row = mysqli_fetch_assoc($rs);
    if (!$row || $row['stock'] < $qty) {
        echo "<h2>❌ Not enough stock.</h2><a href='index.php'>Back</a>";
        exit;
    }

    $amountPaise = ((int)$row['price'] * $qty) * 100;

} elseif (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $mode = 'cart';

    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $pid = (int)$product_id;
        $qty = (int)$qty;

        $rs = mysqli_query($conn, "SELECT stock, price FROM products WHERE id={$pid}");
        $row = mysqli_fetch_assoc($rs);
        if (!$row || $row['stock'] < $qty) {
            echo "<h2>❌ Not enough stock (Product ID: {$pid}).</h2><a href='cart.php'>Back</a>";
            exit;
        }

        $amountPaise += ((int)$row['price'] * $qty) * 100;
    }
} else {
    header('Location: index.php');
    exit;
}

$receiptId = strtoupper(($mode === 'buy_now' ? 'BN' : 'CRT')) . '_' . time();

$payload = json_encode([
    'amount'          => $amountPaise,
    'currency'        => RAZORPAY_CURRENCY,
    'receipt'         => $receiptId,
    'payment_capture' => 1
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
    echo "<h2>Invalid Razorpay response.</h2>";
    exit;
}

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

<!-- Razorpay Checkout -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    key:        "<?php echo RAZORPAY_KEY_ID; ?>",
    amount:     "<?php echo $amountPaise; ?>",
    currency:   "<?php echo RAZORPAY_CURRENCY; ?>",
    name:       "Vending Machine",
    description:"Order: <?php echo $receiptId; ?>",
    order_id:   "<?php echo $razorpayOrderId; ?>",
    callback_url: "verify.php",
    redirect: true,
    prefill: {
        name: "Test User",
        email: "test@example.com",
        contact: "9999999999"
    },
    notes: {
        mode: "<?php echo $mode; ?>",
        receipt: "<?php echo $receiptId; ?>"
    },

    // ✅ Force-enable UPI and disable others for clarity
    method: {
        upi: true,
        card: true,
        netbanking: true,
        wallet: true,
    }
};

var rzp = new Razorpay(options);
rzp.open();
</script>

<noscript>
  Please enable JavaScript to continue with payment.
</noscript>
</body>
</html>
