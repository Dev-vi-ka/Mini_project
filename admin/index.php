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

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9fb;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            background: #e91e63; /* professional pink tone */
            color: white;
            padding: 16px 30px;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        nav {
            margin-top: 8px;
        }
        nav a {
            color: white;
            margin: 0 12px;
            text-decoration: none;
            font-weight: 500;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .product-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .product-card img {
            max-width: 120px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .product-card strong {
            font-size: 18px;
            color: #e91e63;
            display: block;
            margin-bottom: 6px;
        }
        .product-meta {
            font-size: 14px;
            color: #555;
        }
        form {
            margin-top: 12px;
        }
        input[type="number"] {
            width: 70px;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 8px;
        }
        input[type="submit"] {
            background: #e91e63;
            border: none;
            padding: 7px 14px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        input[type="submit"]:hover {
            background: #c2185b;
        }
        a.action-link {
            display: inline-block;
            margin-top: 10px;
            margin-right: 12px;
            color: #e91e63;
            font-weight: 500;
            text-decoration: none;
        }
        a.action-link:hover {
            text-decoration: underline;
        }
        .delete-btn {
            background: #f44336;
            margin-left: 5px;
        }
        .delete-btn:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="add-product.php">Add New Product</a> | 
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
<?php
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div class='product-card'>";
    echo "<img src='../public/product " . $row['image'] . "' alt='" . $row['name'] . "'><br>";
        echo "<strong>" . $row['name'] . "</strong>";
        echo "<div class='product-meta'>Price: ₹" . $row['price'] . " | Stock: " . $row['stock'] . "</div>";

        // Refill stock form
        echo "<form method='post' action='index.php'>";
        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
        echo "<input type='number' name='add_stock' value='1' min='1' required>";
        echo "<input type='submit' value='Update Stock'>";
        echo "</form>";

        // Edit link
        echo "<a href='edit-product.php?id=" . $row['id'] . "' class='action-link'>Edit</a>";

        // Delete form with confirmation
        echo "<form method='post' action='delete-product.php' style='display:inline;'>";
        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
        echo "<input type='submit' value='Delete' class='delete-btn' onclick=\"return confirm('Are you sure you want to delete this product?');\">";
        echo "</form>";

        echo "</div>";
    }
} else {
    echo "<p>No products found.</p>";
}

mysqli_close($conn);
?>
</div>

</body>
</html>
