<?php
session_start();
include '../includes/db.php';
// include 'admin-auth.php'; // Uncomment when you set up login protection

// Handle stock refill submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_POST['add_stock'])) {
    $product_id = $_POST['product_id'];
    $add_stock = $_POST['add_stock'];

    $sql = "UPDATE products SET stock = stock + $add_stock WHERE id = $product_id";
    mysqli_query($conn, $sql);

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>

<h1>Admin Dashboard</h1>

<a href="add-product.php">+ Add New Product</a> | 
<a href="logout.php">Logout</a>

<hr>

<?php
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div style='margin-bottom: 20px;'>";
        echo "<img src='../images/" . $row['image'] . "' alt='" . $row['name'] . "' width='100'><br>";
        echo "<strong>" . $row['name'] . "</strong><br>";
        echo "Price: ₹" . $row['price'] . "<br>";
        echo "Stock: " . $row['stock'] . "<br><br>";

        // Refill stock form
        echo "<form method='post' action='dashboard.php' style='display:inline-block;'>";
        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
        echo "Add Stock: <input type='number' name='add_stock' value='1' min='1' required>";
        echo "<input type='submit' value='Update Stock'>";
        echo "</form><br><br>";

        // Edit link
        echo "<a href='edit-product.php?id=" . $row['id'] . "'>Edit</a> | ";

        // Delete form with confirmation
        echo "<form method='post' action='delete-product.php' style='display:inline;'>";
        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
        echo "<input type='submit' value='Delete' onclick=\"return confirm('Are you sure you want to delete this product?');\">";
        echo "</form>";

        echo "</div><hr>";
    }
} else {
    echo "No products found.";
}

mysqli_close($conn);
?>

</body>
</html>
