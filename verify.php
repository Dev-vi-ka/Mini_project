<?php
session_start();
include 'includes/db.php';
include 'includes/razorpay_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['razorpay_payment_id'], $_POST['razorpay_order_id'], $_POST['razorpay_signature'])
) {
    echo "<h2>Invalid payment response.</h2><a href='index.php'>Back</a>";
    exit;
}

$paymentId = $_POST['razorpay_payment_id'];
$orderId   = $_POST['razorpay_order_id'];
$signature = $_POST['razorpay_signature'];

// ✅ Fallback to GET param if session was lost
$expectedOrder = $_SESSION['rzp_order']['order_id'] ?? $_GET['oid'] ?? '';

if ($expectedOrder !== $orderId) {
    echo "<h2>Order mismatch. Please contact support.</h2>";
    exit;
}

// Signature verification
$expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
if (!hash_equals($expectedSignature, $signature)) {
    echo "<h2>Signature verification failed.</h2>";
    exit;
}

// ✅ Fulfill the order
$mode = $_SESSION['rzp_order']['mode'] ?? ($_POST['notes']['mode'] ?? '');

mysqli_begin_transaction($conn);

try {
    // --- BUY NOW MODE ---
    if ($mode === 'buy_now') {
        // Get product and quantity from session or Razorpay notes
        if (isset($_SESSION['buy_now'])) {
            $pid = intval($_SESSION['buy_now']['product_id']);
            $qty = intval($_SESSION['buy_now']['quantity']);
        } else {
            $notes = [];
            if (isset($_POST['notes'])) {
                if (is_array($_POST['notes'])) {
                    $notes = $_POST['notes'];
                } elseif (is_string($_POST['notes'])) {
                    $notes = json_decode($_POST['notes'], true) ?? [];
                }
            }
            if (isset($notes['product_id'], $notes['quantity'])) {
                $pid = intval($notes['product_id']);
                $qty = intval($notes['quantity']);
            } else {
                throw new Exception("Could not determine product or quantity.");
            }
        }

        // Treat Buy Now as a cart with one item
        $buy_now_cart = [$pid => $qty];
        $ok = true;
        foreach ($buy_now_cart as $pid => $qty) {
            $rs = mysqli_query($conn, "SELECT stock FROM products WHERE id = $pid FOR UPDATE");
            $row = mysqli_fetch_assoc($rs);
            if (!$row || $row['stock'] < $qty) {
                $ok = false;
                $bad = $pid;
                break;
            }
        }
        // if (!$ok) {
        //     throw new Exception("Payment succeeded, but Product ID $bad is out of stock.");
        // }
        foreach ($buy_now_cart as $pid => $qty) {
            mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");
        }
        unset($_SESSION['buy_now']);

    // --- CART MODE ---
    } elseif ($mode === 'cart' && isset($_SESSION['cart'])) {
        $ok = true;
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $pid = intval($pid);
            $qty = intval($qty);
            $rs = mysqli_query($conn, "SELECT stock FROM products WHERE id = $pid FOR UPDATE");
            $row = mysqli_fetch_assoc($rs);
            if (!$row || $row['stock'] < $qty) {
                $ok = false;
                $bad = $pid;
                break;
            }
        }

        if (!$ok) {
            throw new Exception("Payment succeeded, but Product ID $bad is out of stock.");
        }

        foreach ($_SESSION['cart'] as $pid => $qty) {
            $pid = intval($pid);
            $qty = intval($qty);
            mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");
        }

        unset($_SESSION['cart']);
    }

    // ✅ Commit transaction
    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<h2>" . htmlspecialchars($e->getMessage()) . "</h2>";
    exit;
}

// Clear Razorpay session
unset($_SESSION['rzp_order']);

header("Location: thankyou.php");
exit;
