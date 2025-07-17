<?php
include 'includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vending Machine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff5f5; /* Light pink background */
            background-image: url('https://img.freepik.com/free-vector/hand-painted-watercolor-floral-background_23-2149004866.jpg?w=1380&t=st=1698765432~exp=1698766032~hmac=...');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            background-blend-mode: overlay;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            border: 1px solid #ffcccc;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 192, 203, 0.3);
        }
        .btn-primary {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
        }
        .btn-primary:hover {
            background-color: #ff5252;
            border-color: #ff5252;
        }
        .btn-success {
            background-color: #feca57;
            border-color: #feca57;
        }
        .btn-success:hover {
            background-color: #ffb142;
            border-color: #ffb142;
        }
        h1 {
            color: #d63031;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            font-weight: bold;
            margin: 20px 0;
        }
        .out-of-stock {
            color: #d63031;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">🌸 Vending Machine Products 🌸</h1>
        
        <div class="row">
            <?php
            $sql = "SELECT * FROM products";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="col-md-4 mb-4">';
                    echo '  <div class="card h-100 p-3">';
                    echo '    <img src="images/' . $row['image'] . '" class="card-img-top mx-auto" alt="' . $row['name'] . '" style="width: 150px; height: 150px; object-fit: contain;">';
                    echo '    <div class="card-body text-center">';
                    echo '      <h5 class="card-title">' . $row['name'] . '</h5>';
                    echo '      <p class="card-text">Price: ₹' . $row['price'] . '</p>';
                    
                    if($row['stock'] > 0){
                        echo '      <p class="card-text">Stock: ' . $row['stock'] . ' left</p>';
                        echo '      <form method="post" action="product-action.php">';
                        echo '        <input type="hidden" name="product_id" value="' . $row['id'] . '">';
                        echo '        <input type="hidden" name="product_name" value="' . $row['name'] . '">';
                        echo '        <input type="hidden" name="price" value="' . $row['price'] . '">';
                        echo '        <input type="hidden" name="stock" value="' . $row['stock'] . '">';
                        
                        echo '        <div class="mb-3">';
                        echo '          <label for="quantity" class="form-label">Quantity:</label>';
                        echo '          <input type="number" class="form-control" name="quantity" value="1" min="1" max="' . $row['stock'] . '">';
                        echo '        </div>';
                        
                        echo '        <button type="submit" name="action" value="Add to Cart" class="btn btn-primary me-2">Add to Cart</button>';
                        echo '        <button type="submit" name="action" value="Buy Now" class="btn btn-success">Buy Now</button>';
                        echo '      </form>';
                    } else {
                        echo '      <p class="out-of-stock">Out of Stock</p>';
                    }
                    
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No products found.</p></div>';
            }

            mysqli_close($conn);
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>