<?php
include 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vending Machine</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
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
            background-color: #f9f9fb;
            background-image: url('public/background images/basic bg img.png');
            background-repeat: no-repeat;
            background-position: top right;
            background-size: contain;
            background-attachment: fixed;
            font-size: 14px;
        }
        .product-card {
            border-radius: 15px;
            padding: 15px;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            height: 100%;
        }
        @media (min-width: 768px) {
            .product-card {
                padding: 20px;
            }
        }
        .product-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
        @media (min-width: 768px) {
            .product-image {
                height: 200px;
            }
        }
        .btn-pink {
            background-color: #dc8b8b;
            color: white;
            font-weight: 600;
            min-height: 44px;
        }
        .btn-yellow {
            background-color: #c6ad4c;
            color: white;
            font-weight: 600;
            min-height: 44px;
        }
        .btn-pink:hover { background-color: #c76f6f; }
        .btn-yellow:hover { background-color: #a89338; }
        .title {
            font-size: 1.5rem !important;
        }
        @media (min-width: 768px) {
            .title {
                font-size: 2rem !important;
            }
        }

        .app-title {
        font-weight: 700;
        font-size: 2.4rem;
        background: linear-gradient(90deg, #ff1f71, #ff86c8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-align: center;
        margin: 60px auto 10px;
        letter-spacing: 1px;
        position: relative;
    }
    .app-title::after {
        content: "";
        display: block;
        width: 100px;
        height: 4px;
        background: #ff1f71;
        border-radius: 4px;
        margin: 12px auto 0;
        box-shadow: 0 0 8px rgba(255, 31, 113, 0.6);
    }
    .title-bg{
        background-color: rgba(255, 255, 255, 0.8);
    }
    </style>
</head>
<body>

<section class="section" style="padding: 1.5rem 1rem;">
    <div class="container" style="padding: 0 15px;">
        <div class="title-bg">
            <h1 class="title has-text-centered app-title">Vending Machine Products</h1>
        </div>
        

        <div class="columns is-multiline">
        <?php
        $sql = "SELECT * FROM products";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
        ?>
            <div class="column is-12-mobile is-6-tablet is-4-desktop">
                <div class="product-card has-text-centered">
                    <figure class="image is-4by3 mb-3">
                        <img class="product-image" src="public/product <?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    </figure>

                    <p class="title is-5"><?php echo $row['name']; ?></p>

                    <?php if ($row['stock'] > 0) { ?>
                        <form method="post" action="product-action.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="stock" value="<?php echo $row['stock']; ?>">

                            <div class="columns is-mobile is-vcentered is-centered mb-3">
                                <div class="column is-narrow">
                                    <p class="is-size-7">Quantity</p>
                                    <input type="number" name="quantity" class="input is-small" 
                                           value="1" min="1" max="<?php echo $row['stock']; ?>" style="width: 60px;">
                                </div>
                                <div class="column is-narrow">
                                    <p class="is-size-7">Price</p>
                                    <strong>₹<?php echo $row['price']; ?></strong>
                                </div>
                                <div class="column is-narrow">
                                    <p class="is-size-7">Stock</p>
                                    <strong><?php echo $row['stock']; ?></strong>
                                </div>
                            </div>

                            <div class="buttons is-centered mt-2">
                                <button class="button btn-pink" name="action" value="add_to_cart">Add to Cart</button>
                                <button class="button btn-yellow" name="action" value="buy_now">Buy Now</button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <p class="has-text-danger mt-2"><strong>Out of Stock</strong></p>
                    <?php } ?>
                </div>
            </div>
        <?php
            }
        } else {
            echo "<p>No products found.</p>";
        }

        mysqli_close($conn);
        ?>
        </div>
    </div>
</section>

</body>
</html>
