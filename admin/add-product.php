<?php
session_start();
include '../includes/db.php';
// include 'admin-auth.php'; // Uncomment if you implement access control

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_POST['image'];  // Just storing the image file name

    $sql = "INSERT INTO products (name, price, stock, image) 
            VALUES ('$name', '$price', '$stock', '$image')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Failed to add product.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fef6f9; /* soft pink */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 60px auto;
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #d63384;
            margin-bottom: 25px;
        }
        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #880e4f;
        }
        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #f3c6db;
            border-radius: 6px;
            font-size: 15px;
        }
        form input[type="submit"] {
            background: #e83e8c;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }
        form input[type="submit"]:hover {
            background: #c2185b;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #d63384;
            font-weight: bold;
            transition: 0.3s;
        }
        .back-link:hover {
            color: #ad1457;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Add New Product</h1>

    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

    <form method="post">
        <label for="name">Product Name</label>
        <input type="text" name="name" required>

        <label for="price">Price (₹)</label>
        <input type="number" name="price" required>

        <label for="stock">Stock</label>
        <input type="number" name="stock" required>

        <label for="image">Image File Name (inside /images/ folder)</label>
        <input type="text" name="image" required>

        <input type="submit" value="Add Product">
    </form>

    <a class="back-link" href="index.php">← Back to Dashboard</a>
</div>

</body>
</html>
