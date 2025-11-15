<?php
session_start();
include 'includes/db.php';
include 'includes/razorpay_config.php';
include 'includes/twilio.php';

// Detect AJAX (popup) requests that expect JSON
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Friendly GET page when visiting the callback URL directly
    $oid = $_GET['oid'] ?? '';
    echo "<h2>Payment callback expects a POST from Razorpay.</h2>";
    if ($oid) {
        echo "<p>Order ID: <strong>" . htmlspecialchars($oid) . "</strong></p>";
    }
    echo "<p><a href='index.php'>Back to shop</a></p>";
    exit;
}

// POST: signature may be present (redirect flow) or absent (popup flow)
$paymentId = $_POST['razorpay_payment_id'] ?? '';
$orderId   = $_POST['razorpay_order_id'] ?? '';
$signature = $_POST['razorpay_signature'] ?? '';

if (!$paymentId || !$orderId) {
    if ($isAjax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing payment_id or order_id']);
    } else {
        echo "<h2>Invalid payment response.</h2><a href='index.php'>Back</a>";
    }
    exit;
}

// ✅ Fallback to GET param if session was lost
$expectedOrder = $_SESSION['rzp_order']['order_id'] ?? $_GET['oid'] ?? '';

if ($expectedOrder !== '' && $expectedOrder !== $orderId) {
    $log = "[" . date('c') . "] ORDER_MISMATCH -> expected={$expectedOrder} got={$orderId}\n";
    @file_put_contents(__DIR__ . '/razor-debug.txt', $log, FILE_APPEND);
    if ($isAjax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order mismatch']);
    } else {
        echo "<h2>Order mismatch. Please contact support.</h2>";
    }
    exit;
}

// Verify signature if present; otherwise verify payment server-side via Razorpay API
$verified = false;
if (!empty($signature)) {
    $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
    if (hash_equals($expectedSignature, $signature)) {
        $verified = true;
    } else {
        $log = "[" . date('c') . "] SIG_FAIL -> order={$orderId} payment={$paymentId} signature={$signature}\n";
        @file_put_contents(__DIR__ . '/razor-debug.txt', $log, FILE_APPEND);
    }
} else {
    // Server-side check: fetch payment info from Razorpay and ensure it's captured and linked to this order
    $ch = curl_init('https://api.razorpay.com/v1/payments/' . $paymentId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http === 200) {
        $p = json_decode($resp, true);
        if (isset($p['order_id'], $p['status']) && $p['order_id'] === $orderId && $p['status'] === 'captured') {
            $verified = true;
        } else {
            $log = "[" . date('c') . "] PAYMENT_NOT_CAPTURED_OR_MISMATCH -> resp=" . trim($resp) . "\n";
            @file_put_contents(__DIR__ . '/razor-debug.txt', $log, FILE_APPEND);
        }
    } else {
        $log = "[" . date('c') . "] RAZORPAY_API_ERROR code={$http} resp=" . trim($resp) . "\n";
        @file_put_contents(__DIR__ . '/razor-debug.txt', $log, FILE_APPEND);
    }
}

if (!$verified) {
    if ($isAjax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Verification failed']);
    } else {
        echo "<h2>Signature/payment verification failed.</h2>";
    }
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
            // Check remaining stock and alert if low
            $rs2 = mysqli_query($conn, "SELECT name, stock FROM products WHERE id = $pid");
            $r2 = mysqli_fetch_assoc($rs2);
            if ($r2) {
                $remaining = intval($r2['stock']);
                if (defined('LOW_STOCK_ALERTS_ENABLED') && LOW_STOCK_ALERTS_ENABLED && defined('LOW_STOCK_THRESHOLD') && $remaining <= LOW_STOCK_THRESHOLD) {
                    $productName = $r2['name'];
                    $msg = "Low stock alert: Product '" . $productName . "' (ID: $pid) has only $remaining left.";
                    $res = send_whatsapp_message(defined('TWILIO_WHATSAPP_TO') ? TWILIO_WHATSAPP_TO : '', $msg);
                    $log = "[" . date('c') . "] LOW_STOCK_ALERT -> pid={$pid} name=" . addslashes($productName) . " remaining={$remaining} res=" . substr($res['response'], 0, 400) . "\n";
                    @file_put_contents(__DIR__ . '/razor-debug.txt', $log, FILE_APPEND);
                }
            }
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
            // Check remaining stock and alert if low
            $rs2 = mysqli_query($conn, "SELECT name, stock FROM products WHERE id = $pid");
            $r2 = mysqli_fetch_assoc($rs2);
            if ($r2) {
                $remaining = intval($r2['stock']);
                if (defined('LOW_STOCK_ALERTS_ENABLED') && LOW_STOCK_ALERTS_ENABLED && defined('LOW_STOCK_THRESHOLD') && $remaining <= LOW_STOCK_THRESHOLD) {
                    $productName = $r2['name'];
                    $msg = "Low stock alert: Product '" . $productName . "' (ID: $pid) has only $remaining left.";
                    $res = send_whatsapp_message(defined('TWILIO_WHATSAPP_TO') ? TWILIO_WHATSAPP_TO : '', $msg);
                    $log = "[" . date('c') . "] LOW_STOCK_ALERT -> pid={$pid} name=" . addslashes($productName) . " remaining={$remaining} res=" . substr($res['response'], 0, 400) . "\n";
                    @file_put_contents(__DIR__ . '/razor-debug.txt', $log, FILE_APPEND);
                }
            }
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

// --- ESP32 vending trigger (optional) ---
// If you have an ESP32 on the local network that should be triggered
// when a payment is successful, send a simple HTTP request to it.
// This uses @file_get_contents (as your snippet). We also append a
// short log to `razor-debug.txt` so failures are visible.
if (!empty($paymentId)) {
    $esp_ip = "http://10.104.42.84/vend?token=1"; // change as needed
    $esp_response = @file_get_contents($esp_ip);

    $logEntry = "[" . date('c') . "] ESP_TRIGGER -> URL: $esp_ip | Response: ";
    if ($esp_response === false) {
        $logEntry .= "ERROR\n";
    } else {
        $logEntry .= trim($esp_response) . "\n";
    }

    // Append to debug log in project root (same file used for other Razorpay debug in repo)
    @file_put_contents(__DIR__ . '/razor-debug.txt', $logEntry, FILE_APPEND);
}

// Respond to AJAX (popup) requests with JSON to avoid HTML being parsed as JSON
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'redirect' => 'thankyou.php']);
} else {
    header("Location: thankyou.php");
}
exit;
