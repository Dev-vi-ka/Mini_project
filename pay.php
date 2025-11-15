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

    // NOTE: Do NOT decrement stock here. Stock is decremented after
    // successful payment in `verify.php` to avoid double-subtraction.

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
    // Use popup flow and handle server verification via AJAX
    // No callback_url when using handler/redirect:false
    redirect: false,
    handler: function (response){
        var form = new URLSearchParams();
        form.append('razorpay_payment_id', response.razorpay_payment_id);
        form.append('razorpay_order_id', response.razorpay_order_id);
        if(response.razorpay_signature){ form.append('razorpay_signature', response.razorpay_signature); }

        fetch('verify.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: form.toString()
        }).then(function(res){
            var ct = res.headers.get('content-type') || '';
            if (!res.ok) {
                return res.text().then(function(txt){ throw new Error('Server error ' + res.status + ': ' + txt); });
            }
            if (ct.indexOf('application/json') !== -1) {
                return res.json();
            }
            return res.text().then(function(txt){ throw new Error('Expected JSON response but received: ' + txt); });
        }).then(function(data){
            if (data && data.success) {
                window.location.href = data.redirect || 'thankyou.php';
            } else {
                var msg = data && data.message ? data.message : 'Unknown';
                alert('Payment verification failed: ' + msg);
                console.error('verify response', data);
            }
        }).catch(function(err){
            console.error('Verification fetch error:', err);
            alert('Payment verification error: ' + (err && err.message ? err.message : 'Check server logs'));
        });
    },
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
