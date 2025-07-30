<?php
session_start();
include 'includes/db.php';
include 'includes/razorpay_config.php';

// Ensure required params
if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['razorpay_payment_id'], $_POST['razorpay_order_id'], $_POST['razorpay_signature'])
) {
    echo "<h2>Invalid payment response.</h2><a href='index.php'>Back</a>";
    exit;
}

$paymentId = $_POST['razorpay_payment_id'];
$orderId   = $_POST['razorpay_order_id'];
$signature = $_POST['razorpay_signature'];

// We stored our created order_id in session
if (!isset($_SESSION['rzp_order']['order_id']) || $_SESSION['rzp_order']['order_id'] !== $orderId) {
    echo "<h2>Order mismatch.</h2><a href='index.php'>Back</a>";
    exit;
}

// Verify signature: HMAC SHA256 of order_id|payment_id with KEY_SECRET
$expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);

if (!hash_equals($expectedSignature, $signature)) {
    echo "<h2>❌ Payment signature verification failed.</h2><a href='index.php'>Back</a>";
    exit;
}

// Signature OK → fulfill
$mode = $_SESSION['rzp_order']['mode'] ?? null;

// Double-check stock again (optional but safer), then deduct
if ($mode === 'buy_now' && isset($_SESSION['buy_now'])) {
    $pid = (int)$_SESSION['buy_now']['product_id'];
    $qty = (int)$_SESSION['buy_now']['quantity'];

    $rs  = mysqli_query($conn, "SELECT stock FROM products WHERE id={$pid} FOR UPDATE");
    $row = mysqli_fetch_assoc($rs);
    if (!$row || $row['stock'] < $qty) {
        echo "<h2>Payment successful, but item went out of stock. Contact support.</h2>";
        exit;
    }
    mysqli_query($conn, "UPDATE products SET stock = stock - {$qty} WHERE id={$pid}");
    unset($_SESSION['buy_now']);

} elseif ($mode === 'cart' && isset($_SESSION['cart'])) {
    // verify all lines then deduct
    $ok = true;
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $pid = (int)$product_id; $qty = (int)$qty;
        $rs  = mysqli_query($conn, "SELECT stock FROM products WHERE id={$pid} FOR UPDATE");
        $row = mysqli_fetch_assoc($rs);
        if (!$row || $row['stock'] < $qty) { $ok = false; $bad = $pid; break; }
    }
    if (!$ok) {
        echo "<h2>Payment successful, but Product ID {$bad} is out of stock. Contact support.</h2>";
        exit;
    }
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $pid = (int)$product_id; $qty = (int)$qty;
        mysqli_query($conn, "UPDATE products SET stock = stock - {$qty} WHERE id={$pid}");
    }
    unset($_SESSION['cart']);
}

// Clear order context and go to Thank You
unset($_SESSION['rzp_order']);

header("Location: thankyou.php");
exit;
