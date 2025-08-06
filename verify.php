<?php
session_start();
include 'includes/db.php';
include 'includes/razorpay_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['razorpay_payment_id'], $_POST['razorpay_order_id'], $_POST['razorpay_signature'])
) {
    echo "<h2>❌ Invalid payment response.</h2><a href='index.php'>Back</a>";
    exit;
}

$paymentId = $_POST['razorpay_payment_id'];
$orderId   = $_POST['razorpay_order_id'];
$signature = $_POST['razorpay_signature'];

// ✅ FIXED: fallback to GET param if session was lost
$expectedOrder = $_SESSION['rzp_order']['order_id'] ?? $_GET['oid'] ?? '';

if ($expectedOrder !== $orderId) {
    echo "<h2>⚠️ Order mismatch. Please contact support.</h2>";
    exit;
}

// Signature check
$expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
if (!hash_equals($expectedSignature, $signature)) {
    echo "<h2>❌ Signature verification failed.</h2>";
    exit;
}

// ✅ Fulfill the order
$mode = $_SESSION['rzp_order']['mode'] ?? $_POST['notes']['mode'] ?? '';

// --- BUY NOW ---
if ($mode === 'buy_now' && isset($_SESSION['buy_now'])) {
    $pid = (int)$_SESSION['buy_now']['product_id'];
    $qty = (int)$_SESSION['buy_now']['quantity'];

    $rs = mysqli_query($conn, "SELECT stock FROM products WHERE id={$pid} FOR UPDATE");
    $row = mysqli_fetch_assoc($rs);
    if ($row && $row['stock'] >= $qty) {
        mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");
    } else {
        echo "<h2>✅ Payment succeeded but item went out of stock. Contact support.</h2>";
        exit;
    }

    unset($_SESSION['buy_now']);

// --- CART MODE ---
} elseif ($mode === 'cart' && isset($_SESSION['cart'])) {
    $ok = true;
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        $rs = mysqli_query($conn, "SELECT stock FROM products WHERE id = $pid FOR UPDATE");
        $row = mysqli_fetch_assoc($rs);
        if (!$row || $row['stock'] < $qty) {
            $ok = false; $bad = $pid; break;
        }
    }

    if (!$ok) {
        echo "<h2>✅ Payment succeeded, but Product ID $bad is out of stock.</h2>";
        exit;
    }

    foreach ($_SESSION['cart'] as $pid => $qty) {
        mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");
    }

    unset($_SESSION['cart']);
}

// Clear Razorpay session
unset($_SESSION['rzp_order']);

header("Location: thankyou.php");
exit;
    