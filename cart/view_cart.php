<?php
require_once __DIR__ . '/../includes/header.php';
if (empty($_SESSION['user_id'])) {
  $_SESSION['message'] = 'Please sign in to view your cart.';
  header('Location: /GlamEssentials/user/login.php');
  exit;
}
$uid = intval($_SESSION['user_id']);
$sql = "SELECT sc.cart_id, sc.product_id, sc.quantity, p.product_name, p.price
        FROM shopping_cart sc
        JOIN products p ON p.product_id = sc.product_id
        WHERE sc.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
mysqli_stmt_close($stmt);

$total = 0;
foreach ($rows as $r) { $total += ($r['price'] * $r['quantity']); }
?>
<section class="container">
  <h1 style="margin:8px 0 16px;">Your Cart</h1>
  <?php require_once __DIR__ . '/../includes/alert.php'; ?>
  <div class="card" style="padding:12px;">
    <?php if (!$rows): ?>
      <p style="color:var(--color-muted);">Your cart is empty.</p>
      <p><a class="btn" href="/GlamEssentials/products.php">Shop products</a></p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid var(--border)">
            <th style="padding:10px 6px;">Product</th>
            <th style="padding:10px 6px;">Price</th>
            <th style="padding:10px 6px;">Qty</th>
            <th style="padding:10px 6px;">Subtotal</th>
            <th style="padding:10px 6px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): $sub = $r['price'] * $r['quantity']; ?>
          <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:10px 6px;">
              <a href="/GlamEssentials/product.php?id=<?php echo (int)$r['product_id']; ?>"><?php echo htmlspecialchars($r['product_name']); ?></a>
            </td>
            <td style="padding:10px 6px;">₱<?php echo number_format($r['price'],2); ?></td>
            <td style="padding:10px 6px;">
              <form method="post" action="/GlamEssentials/cart/cart_update.php" style="display:flex;gap:6px;align-items:center;">
                <input type="hidden" name="product_id" value="<?php echo (int)$r['product_id']; ?>" />
                <input class="input" style="width:70px" type="number" min="1" name="quantity" value="<?php echo (int)$r['quantity']; ?>" />
                <button class="btn" name="action" value="update">Update</button>
              </form>
            </td>
            <td style="padding:10px 6px;">₱<?php echo number_format($sub,2); ?></td>
            <td style="padding:10px 6px;">
              <form method="post" action="/GlamEssentials/cart/cart_update.php">
                <input type="hidden" name="product_id" value="<?php echo (int)$r['product_id']; ?>" />
                <button class="btn" style="background:#e11d48" name="action" value="remove">Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
        <a class="btn" href="/GlamEssentials/products.php">Continue shopping</a>
        <div style="font-weight:700;font-size:18px;">Total: ₱<?php echo number_format($total,2); ?></div>
      </div>
      <div style="margin-top:12px;display:flex;justify-content:flex-end;">
        <a class="btn" href="/GlamEssentials/cart/checkout.php">Proceed to Checkout</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
