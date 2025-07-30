<?php
session_start();

// By now, verify.php has already cleared cart/buy_now and updated stock
// We just display a confirmation page.

?>
<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
</head>
<body>

<h1>✅ Thank you for your purchase!</h1>
<p>Your payment was successful and your order is being prepared.</p>

<a href="index.php">← Back to Products</a>

</body>
</html>
