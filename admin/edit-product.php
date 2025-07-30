<?php
session_start();
include '../includes/db.php';
// include 'admin-auth.php'; // Uncomment when using auth

// Check if ID is passed
if (!isset($_GET['id'])) {
    echo "Product ID not specified.";
    exit();
}

$product_id = $_GET['id'];

// Fetch product info
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo "Product not found.";
    exit();
}

$product = mysqli_fetch_assoc($result);

// Handle update form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_POST['image'];

    $update = "UPDATE products SET 
                name = '$name', 
                price = '$price', 
                stock = '$stock', 
                image = '$image' 
               WHERE id = $product_id";

    if (mysqli_query($conn, $update)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Failed to update product.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
</head>
<body>

<h1>Edit Product</h1>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post">
    Name: <input type="text" name="name" value="<?php echo $product['name']; ?>" required><br><br>
    Price (₹): <input type="number" name="price" value="<?php echo $product['price']; ?>" required><br><br>
    Stock: <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required><br><br>
    Image Filename (from /images/): <input type="text" name="image" value="<?php echo $product['image']; ?>" required><br><br>
    <input type="submit" value="Update Product">
</form>

<br>
<a href="index.php">← Back to Dashboard</a>

</body>
</html>
