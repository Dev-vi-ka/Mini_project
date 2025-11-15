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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            color: #333;
            font-size: 14px;
            min-height: 100vh;
        }
        header {
            background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
            color: white;
            padding: 20px 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);
        }
        @media (min-width: 768px) {
            header {
                padding: 30px 30px;
            }
        }
        header h1 {
            margin: 0 0 15px 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        @media (min-width: 768px) {
            header h1 {
                font-size: 32px;
                margin: 0 0 20px 0;
            }
        }
        nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 13px;
        }
        @media (min-width: 768px) {
            nav {
                font-size: 15px;
                gap: 30px;
            }
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
        }
        nav a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 15px;
        }
        @media (min-width: 768px) {
            .container {
                margin: 40px auto;
                padding: 0 20px;
            }
        }
        .product-card {
            background: white;
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 12px;
            border: 2px solid #f0f0f0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            display: grid;
            grid-template-columns: 85px 1fr;
            gap: 16px;
            align-items: start;
            transition: all 0.3s ease;
        }
        .product-card:hover {
            box-shadow: 0 6px 20px rgba(233, 30, 99, 0.15);
            border-color: #e91e63;
            transform: translateY(-2px);
        }
        @media (min-width: 768px) {
            .product-card {
                padding: 20px;
                margin-bottom: 18px;
                grid-template-columns: 130px 1fr;
                gap: 20px;
            }
        }
        .product-image-wrapper {
            grid-column: 1;
            grid-row: 1 / span 3;
            overflow: hidden;
            border-radius: 8px;
            background: #f5f5f5;
        }
        .product-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }
        .product-info {
            grid-column: 2;
            grid-row: 1;
        }
        .product-card strong {
            font-size: 15px;
            color: #e91e63;
            display: block;
            margin-bottom: 6px;
            word-break: break-word;
            font-weight: 600;
        }
        @media (min-width: 768px) {
            .product-card strong {
                font-size: 18px;
                margin-bottom: 8px;
            }
        }
        .product-meta {
            font-size: 12px;
            color: #666;
            word-break: break-word;
            line-height: 1.5;
        }
        @media (min-width: 768px) {
            .product-meta {
                font-size: 14px;
            }
        }
        .product-actions {
            grid-column: 2;
            grid-row: 2;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 8px;
        }
        @media (min-width: 768px) {
            .product-actions {
                margin-top: 0;
                gap: 10px;
            }
        }
        .product-actions form {
            margin: 0;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }
        input[type="number"] {
            width: 65px;
            padding: 8px 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: border-color 0.3s;
        }
        input[type="number"]:focus {
            outline: none;
            border-color: #e91e63;
        }
        @media (min-width: 768px) {
            input[type="number"] {
                width: 75px;
                font-size: 14px;
                padding: 8px 10px;
            }
        }
        input[type="submit"] {
            background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%);
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            min-height: 38px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
        }
        input[type="submit"]:hover {
            background: linear-gradient(135deg, #d81b60 0%, #c2185b 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
        }
        input[type="submit"]:active {
            transform: translateY(0);
        }
        @media (min-width: 768px) {
            input[type="submit"] {
                padding: 9px 16px;
                font-size: 14px;
                min-height: 40px;
            }
        }
        .action-buttons {
            grid-column: 2;
            grid-row: 3;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
            align-items: center;
        }
        @media (min-width: 768px) {
            .action-buttons {
                margin-top: 6px;
                gap: 10px;
            }
        }
        a.action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #e91e63;
            font-weight: 600;
            text-decoration: none;
            font-size: 12px;
            padding: 8px 12px;
            border: 2px solid #e91e63;
            border-radius: 6px;
            transition: all 0.3s ease;
            min-height: 38px;
            background: white;
        }
        a.action-link:hover {
            background: #e91e63;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3);
        }
        @media (min-width: 768px) {
            a.action-link {
                font-size: 14px;
                padding: 9px 14px;
                min-height: 40px;
            }
        }
        .delete-btn {
            background: linear-gradient(135deg, #f44336 0%, #e53935 100%);
            border: none;
            color: white;
            min-height: 38px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }
        .delete-btn:hover {
            background: linear-gradient(135deg, #e53935 0%, #d32f2f 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
        }
        .delete-btn:active {
            transform: translateY(0);
        }
        @media (min-width: 768px) {
            .delete-btn {
                min-height: 40px;
                padding: 9px 14px;
                font-size: 14px;
            }
        }
        .app-title {
            font-weight: 700;
            font-size: 26px;
            color: white;
            text-align: center;
            margin: 0;
            letter-spacing: 0.5px;
        }
        @media (min-width: 768px) {
            .app-title {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1 class="app-title">Admin Dashboard</h1>
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
        
        // Image wrapper
        echo "<div class='product-image-wrapper'>";
        echo "<img src='../public/product " . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
        echo "</div>";
        
        // Product info
        echo "<div class='product-info'>";
        echo "<strong>" . htmlspecialchars($row['name']) . "</strong>";
        echo "<div class='product-meta'>Price: ₹" . $row['price'] . " | Stock: " . $row['stock'] . "</div>";
        echo "</div>";

        // Stock update form
        echo "<div class='product-actions'>";
        echo "<form method='post' action='index.php' style='display: flex; gap: 6px; align-items: center;'>";
        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
        echo "<input type='number' name='add_stock' value='1' min='1' required>";
        echo "<input type='submit' value='Update Stock'>";
        echo "</form>";
        echo "</div>";

        // Action buttons (Edit & Delete)
        echo "<div class='action-buttons'>";
        echo "<a href='edit-product.php?id=" . $row['id'] . "' class='action-link'>Edit</a>";
        echo "<form method='post' action='delete-product.php' style='display:inline;'>";
        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
        echo "<input type='submit' value='Delete' class='delete-btn' onclick=\"return confirm('Are you sure you want to delete this product?');\">";
        echo "</form>";
        echo "</div>";

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
