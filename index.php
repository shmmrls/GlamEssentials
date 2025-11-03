<?php
session_start();
// load page-specific CSS through header.php
$pageCss = 'index.css';
include('./includes/header.php');
include('./includes/config.php');

// --- Check login ---
$is_logged_in = isset($_SESSION['userId']);
$user_id = $is_logged_in ? $_SESSION['userId'] : 0;

// --- Category Filter ---
$sql_categories = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $sql_categories);

echo '<div class="container">';
echo '<h2 class="text-center mb-4">Shop Best Sellers</h2>';

echo '<div class="category-filter mb-4 text-center">';
echo '<form method="GET" action="">';
echo '<label for="category">Filter by Category:</label> ';
echo '<select id="category" name="category" onchange="this.form.submit()" class="form-select d-inline-block w-auto">';
echo '<option value="all">All Categories</option>';
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $selected = (isset($_GET["category"]) && $_GET["category"] == $cat["category_id"]) ? "selected" : "";
    echo "<option value='{$cat['category_id']}' {$selected}>{$cat['category_name']}</option>";
}
echo '</select>';
echo '</form>';
echo '</div>';

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

// --- Load Products ---
$category_filter = isset($_GET['category']) && $_GET['category'] != 'all' ? "WHERE p.category_id=" . intval($_GET['category']) : '';
$sql = "SELECT p.product_id, p.product_name, p.description, p.price, p.main_img_name, i.quantity, i.unit, i.reorder_level,
        CASE 
            WHEN i.quantity = 0 THEN 'Out of Stock' 
            WHEN i.quantity < i.reorder_level THEN 'Low Stock' 
            ELSE 'In Stock' 
        END AS stock_status
        FROM products p
        JOIN inventory i ON p.product_id = i.product_id
        $category_filter
        ORDER BY p.created_at DESC";
$results = mysqli_query($conn, $sql);
?>

<div class="product-slider-container">
    <button class="slider-btn prev" id="slidePrev">&#10094;</button>
    <div class="product-row-container" id="productSlider">
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

                // Images
                $sql_images = "SELECT img_name FROM product_images WHERE product_id={$product_id} LIMIT 1";
                $res_images = mysqli_query($conn, $sql_images);
                $img = mysqli_fetch_assoc($res_images);
                $main_img = $img ? "./item/{$img['img_name']}" : "./assets/default.png";

                // JS protection
                $add_onclick = $is_logged_in ? '' : "alert('Please log in first to add items to cart.'); return false;";

                echo '<div class="product-card-horizontal">';
                echo '<form method="POST" action="./cart/cart_update.php">';
                echo "<img src='{$main_img}' alt='{$row['product_name']}' class='product-image'>";
                echo "<h3 class='product-title'>{$row['product_name']}</h3>";
                echo "<p class='product-rating'>{$avg_rating} ⭐ <span>({$total_reviews} Ratings)</span></p>";
                echo "<p class='product-price'>₱" . number_format($row['price'], 2) . "</p>";

                // Optional dropdown for variant/size
                echo "<div class='product-option'>
                        <label>Select size:</label>
                        <select class='form-select'>
                            <option>Jumbo Size</option>
                        </select>
                      </div>";

                echo "<input type='hidden' name='item_id' value='{$product_id}'>";
                echo "<input type='hidden' name='type' value='add'>";
                echo "<button type='submit' class='btn btn-dark add-btn' onclick=\"{$add_onclick}\">Add To Bag</button>";
                echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center">No products available.</p>';
        }
        ?>
    </div>
    <button class="slider-btn next" id="slideNext">&#10095;</button>
</div>

<?php include('./includes/footer.php'); ?>

<script>
// --- Smooth Slider Navigation ---
document.getElementById('slideNext').addEventListener('click', function() {
  document.getElementById('productSlider').scrollBy({ left: 300, behavior: 'smooth' });
});
document.getElementById('slidePrev').addEventListener('click', function() {
  document.getElementById('productSlider').scrollBy({ left: -300, behavior: 'smooth' });
});
</script>
