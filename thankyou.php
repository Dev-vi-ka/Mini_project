<?php
session_start();

// By now, verify.php has already cleared cart/buy_now and updated stock
// We just display a confirmation page.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fef6f9; /* soft pink */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 80px auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d63384;
            font-size: 28px;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #555;
            margin-bottom: 30px;
        }
        a {
            display: inline-block;
            text-decoration: none;
            background: #e83e8c;
            color: #fff;
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 16px;
            transition: background 0.3s;
        }
        a:hover {
            background: #c2185b;
        }
        .icon {
            font-size: 50px;
            margin-bottom: 20px;
            color: #28a745; /* green check for success */
        }
    </style>
</head>
<body>

<div class="container">
    <div class="icon">✅</div>
    <h1>Thank you for your purchase!</h1>
    <p>Your payment was successful and your order is being prepared.</p>
    <a href="index.php">← Back to Products</a>
</div>

</body>
</html>
