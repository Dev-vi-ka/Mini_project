<?php
session_start();
include '../includes/db.php';
// include 'admin-auth.php'; // Uncomment when using auth

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Delete product by ID
    $sql = "DELETE FROM products WHERE id = $product_id";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Failed to delete product.";
    }
} else {
    echo "Invalid request.";
}
?>
