<?php
session_start();

// By now, verify.php has already cleared cart/buy_now and updated stock
// We just display a confirmation page.
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: #fef6f9; /* soft pink */
            font-size: 14px;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        @media (min-width: 768px) {
            .container {
                margin: 80px auto;
                padding: 40px;
            }
        }
        h1 {
            color: #d63384;
            font-size: 24px;
            margin-bottom: 15px;
        }
        @media (min-width: 768px) {
            h1 {
                font-size: 28px;
                margin-bottom: 20px;
            }
        }
        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }
        @media (min-width: 768px) {
            p {
                font-size: 18px;
                margin-bottom: 30px;
            }
        }
        a {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #e83e8c;
            color: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            transition: background 0.3s;
            min-height: 44px;
            width: 100%;
        }
        @media (min-width: 768px) {
            a {
                width: auto;
                padding: 12px 25px;
            }
        }
        a:hover {
            background: #c2185b;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #28a745; /* green check for success */
        }
        @media (min-width: 768px) {
            .icon {
                font-size: 50px;
                margin-bottom: 20px;
            }
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
