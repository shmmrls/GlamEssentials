<?php

$pageCss = 'index.css';
include('./includes/header.php');
include('./includes/config.php');
?>
<style>
<?php include('./includes/style/index.css'); ?>
</style>
<link rel="stylesheet" href="./includes/style/hero.css">

<section class="hero">
    <div class="hero-overlay">
        <div class="hero-content">
            <h1>DISCOVER ELEGANCE</h1>
            <p>Premium salon essentials curated for the modern professional.</p>
            <a href="item/index.php" class="btn btn-primary">Shop Now</a>
        </div>
    </div>
</section>
<?php
// ...existing code...

include('./includes/config.php');

// --- Check login ---
$is_logged_in = isset($_SESSION['userId']);
$user_id = $is_logged_in ? $_SESSION['userId'] : 0;

// --- Category Filter ---
$sql_categories = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $sql_categories);

echo '<div class="container">';
$sql_all_categories = "SELECT * FROM categories";
$all_categories_result = mysqli_query($conn, $sql_all_categories);

while ($category = mysqli_fetch_assoc($all_categories_result)) {
    echo "<div class='category-section mb-5'>";
    echo "<h2 class='text-center mb-4'>{$category['category_name']}</h2>";

// --- Mini-cart summary ---
if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0) {
    $total_items = 0;
    $total_price = 0;
    foreach ($_SESSION["cart_products"] as $item) {
        $total_items += $item["item_qty"];
        $total_price += $item["item_price"] * $item["item_qty"];
    }
    echo "<div class='cart-summary mb-4 text-center'>
            <strong>Cart:</strong> {$total_items} items | 
            <strong>Total:</strong> ₱" . number_format($total_price, 2) . "
            <a href='./cart/view_cart.php' class='btn btn-primary btn-sm ms-2'>View Cart</a>
          </div>";
}

    // --- Load Featured Products for this category ---
    $sql = "SELECT p.product_id, p.product_name, p.description, p.price, p.main_img_name, i.quantity, i.unit, i.reorder_level,
            CASE 
                WHEN i.quantity = 0 THEN 'Out of Stock' 
                WHEN i.quantity < i.reorder_level THEN 'Low Stock' 
                ELSE 'In Stock' 
            END AS stock_status
            FROM products p
            JOIN inventory i ON p.product_id = i.product_id
            WHERE p.category_id = {$category['category_id']}
            ORDER BY p.created_at DESC
            LIMIT 5";  // Limit to 5 products per category
    $results = mysqli_query($conn, $sql);
    ?>

    <div class="product-slider-container">
        <button class="slider-btn prev" id="slidePrev_<?php echo $category['category_id']; ?>">&#10094;</button>
        <div class="product-row-container" id="productSlider_<?php echo $category['category_id']; ?>">
        <?php
        if ($results && mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_assoc($results)) {
                $product_id = $row['product_id'];

                // Reviews
                $sql_reviews = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id={$product_id}";
                $res_review = mysqli_query($conn, $sql_reviews);
                $review = mysqli_fetch_assoc($res_review);
                $avg_rating = $review['avg_rating'] ? number_format($review['avg_rating'], 1) : 'N/A';
                $total_reviews = $review['total_reviews'];

                // Get image path using main_img_name
                $imgName = $row['main_img_name'];
                $main_img = './assets/default.png';
                
                if (!empty($imgName)) {
                    $productImagesDir = 'C:/infomanagement/htdocs/GlamEssentials/item/products/';
                    // Check common image extensions
                    $extensions = ['.jpg', '.png', '.webp'];
                    
                    foreach ($extensions as $ext) {
                        $fullPath = $productImagesDir . $imgName . $ext;
                        if (file_exists($fullPath)) {
                            $main_img = './item/products/' . $imgName . $ext;
                            break;
                        }
                    }
                }

                // JS protection
                $add_onclick = $is_logged_in ? '' : "alert('Please log in first to add items to cart.'); return false;";

                echo '<div class="product-card-horizontal">';
                echo '<form method="POST" action="./cart/cart_update.php">';
                echo "<img src='{$main_img}' alt='{$row['product_name']}' class='product-image'>";
                echo "<h3 class='product-title'>{$row['product_name']}</h3>";
                echo "<p class='product-rating'>{$avg_rating} ⭐ <span>({$total_reviews} Ratings)</span></p>";
                echo "<p class='product-price'>₱" . number_format($row['price'], 2) . "</p>";

                // Optional dropdown for variant/size

                echo "<input type='hidden' name='item_id' value='{$product_id}'>";
                echo "<input type='hidden' name='type' value='add'>";
                echo "<button type='submit' style='background: black; color: white; border: none; padding: 8px 16px; width: 100%; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 0; font-size: 0.9rem;' onclick=\"{$add_onclick}\">Add To Bag</button>";
                echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center">No products available.</p>';
        }
        ?>
    </div>
        <button class="slider-btn next" id="slideNext_<?php echo $category['category_id']; ?>">&#10095;</button>
    </div>
</div>
<?php
}  // End of category while loop
?>

<?php include('./includes/footer.php'); ?>

<script>
// --- Smooth Slider Navigation for each category ---
<?php
mysqli_data_seek($all_categories_result, 0);
while ($category = mysqli_fetch_assoc($all_categories_result)) {
    echo "
    document.getElementById('slideNext_" . $category['category_id'] . "').addEventListener('click', function() {
        document.getElementById('productSlider_" . $category['category_id'] . "').scrollBy({ left: 300, behavior: 'smooth' });
    });
    document.getElementById('slidePrev_" . $category['category_id'] . "').addEventListener('click', function() {
        document.getElementById('productSlider_" . $category['category_id'] . "').scrollBy({ left: -300, behavior: 'smooth' });
    });\n";
}
?>
</script>
</script>
