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
</head>
<body>

<h1>Add New Product</h1>

<?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<form method="post">
    Product Name: <input type="text" name="name" required><br><br>
    Price (₹): <input type="number" name="price" required><br><br>
    Stock: <input type="number" name="stock" required><br><br>
    Image File Name (inside /images/ folder): <input type="text" name="image" required><br><br>
    <input type="submit" value="Add Product">
</form>

<br>
<a href="index.php">← Back to Dashboard</a>

</body>
</html>
