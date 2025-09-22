<?php
session_start();
include 'includes/db.php';
include 'includes/razorpay_config.php';

$mode = null;
$amountPaise = 0;

if (isset($_SESSION['buy_now']) || (isset($_POST['product_id']) && isset($_POST['quantity']))) {
    $mode = 'buy_now';
    if (isset($_SESSION['buy_now'])) {
        $p = $_SESSION['buy_now'];
        $pid = (int)$p['product_id'];
        $qty = (int)$p['quantity'];
    } else {
        $pid = (int)$_POST['product_id'];
        $qty = (int)$_POST['quantity'];
    }
    $rs = mysqli_query($conn, "SELECT stock, price FROM products WHERE id={$pid}");
    $row = mysqli_fetch_assoc($rs);
    if (!$row || $row['stock'] < $qty) {
        echo "<h2>❌ Not enough stock.</h2><a href='index.php'>Back</a>";
        exit;
    }
    $amountPaise = ((int)$row['price'] * $qty) * 100;

    // Update stock before session is destroyed
    mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");

} elseif (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $mode = 'cart';
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $pid = (int)$product_id;
        $qty = (int)$qty;
        $rs = mysqli_query($conn, "SELECT stock, price FROM products WHERE id={$pid}");
        $row = mysqli_fetch_assoc($rs);
        if (!$row || $row['stock'] < $qty) {
            echo "<h2>❌ Not enough stock for product ID: $pid</h2><a href='cart.php'>Back</a>";
            exit;
        }
        $amountPaise += ((int)$row['price'] * $qty) * 100;
        // Update stock before session is destroyed
        mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");
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
curl_close($ch);

$order = json_decode($response, true);
$razorpayOrderId = $order['id'] ?? null;

if (!$razorpayOrderId) {
    echo "<h2>⚠️ Razorpay Order creation failed.</h2>";
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

<h2>Redirecting to Razorpay...</h2>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    key: "<?php echo RAZORPAY_KEY_ID; ?>",
    amount: "<?php echo $amountPaise; ?>",
    currency: "<?php echo RAZORPAY_CURRENCY; ?>",
    name: "Vending Machine",
    description: "Order: <?php echo $receiptId; ?>",
    order_id: "<?php echo $razorpayOrderId; ?>",
    callback_url: "http://localhost/mini_project/verify.php?oid=<?php echo $razorpayOrderId; ?>",
    redirect: true,
    prefill: {
        name: "Test User",
        email: "test@example.com",
        contact: "9999999999"
    },
    notes: {
        mode: "<?php echo $mode; ?>",
        product_id: "<?php echo $pid; ?>",
        quantity: "<?php echo $qty; ?>"
    }
};

var rzp = new Razorpay(options);
rzp.open();
</script>

</body>
</html>
