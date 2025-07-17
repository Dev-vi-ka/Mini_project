<?php
include 'includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vending Machine</title>
</head>
<body>

<h1>Vending Machine Products</h1>

<?php
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div style='margin-bottom: 20px;'>";
        echo "<img src='images/" . $row['image'] . "' alt='" . $row['name'] . "' width='100'><br>";
        echo "<strong>" . $row['name'] . "</strong><br>";
        echo "Price: ₹" . $row['price'] . "<br>";

        if($row['stock'] > 0){
            echo "Stock: " . $row['stock'] . " left<br>";

            // Single form for both actions
            echo "<form method='post' action='product-action.php'>";
            echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
            echo "<input type='hidden' name='product_name' value='" . $row['name'] . "'>";
            echo "<input type='hidden' name='price' value='" . $row['price'] . "'>";
            echo "<input type='hidden' name='stock' value='" . $row['stock'] . "'>";

            echo "Quantity: <input type='number' name='quantity' value='1' min='1' max='" . $row['stock'] . "'><br>";

            echo "<input type='submit' name='action' value='Add to Cart'>";
            echo "<input type='submit' name='action' value='Buy Now'>";
            echo "</form>";

        } else {
            echo "<strong>Out of Stock</strong>";
        }

        echo "</div><hr>";
    }
} else {
    echo "No products found.";
}

mysqli_close($conn);
?>

</body>
</html>
