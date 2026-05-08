<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Home';
$user = get_user();

$stmt = $pdo->query("SELECT p.*, u.username as seller_name, c.name as category_name, pi.image_path as main_image FROM products p JOIN users u ON p.seller_id = u.id LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN product_images pi ON pi.id = (SELECT id FROM product_images WHERE product_id = p.id LIMIT 1) WHERE p.product_status = 'on_sale' ORDER BY p.created_at DESC LIMIT 8");
$featured = $stmt->fetchAll();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE product_status = 'on_sale'");
$total_products = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller' AND status = 'active'");
$total_sellers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'sold'");
$total_sold = $stmt->fetchColumn();

require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">
                Campus Student<br><span>Marketplace</span>
            </h1>
            <p class="hero-subtitle">
                Buy and sell products safely within your campus community. Post items, place orders, track sales, and connect with verified student sellers at Karatina University.
            </p>
            <div class="hero-actions">
                <a href="/products.php" class="btn btn-primary btn-lg">Browse Products</a>
                <?php if (!$user): ?>
                    <a href="/login.php" class="btn btn-outline btn-lg">Login to Sell</a>
                <?php elseif ($user['role'] === 'seller'): ?>
                    <a href="/seller/post-product.php" class="btn btn-success btn-lg">Post a Product</a>
                <?php elseif ($user['role'] === 'user'): ?>
                    <a href="/user/apply-seller.php" class="btn btn-outline btn-lg">Apply to Sell</a>
                <?php endif; ?>
            </div>
            <div class="hero-notice">
                <strong>Payment Safety Notice:</strong> Do not pay before receiving the item. All payments should be made only after physical delivery and inspection.
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stats-grid" style="grid-template-columns: repeat(3,1fr); max-width:600px;">
            <div class="stat-card">
                <div class="stat-label">Products Listed</div>
                <div class="stat-value primary"><?= $total_products ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Verified Sellers</div>
                <div class="stat-value success"><?= $total_sellers ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Items Sold</div>
                <div class="stat-value"><?= $total_sold ?></div>
            </div>
        </div>

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <h2 class="section-title" style="margin-bottom:4px;">Latest Listings</h2>
                <p class="section-subtitle" style="margin-bottom:0;">Recently posted products from verified student sellers</p>
            </div>
            <a href="/products.php" class="btn btn-outline">View All</a>
        </div>

        <?php if (empty($featured)): ?>
            <div class="empty-state">
                <h3>No products listed yet</h3>
                <p>Be the first to list a product on KarU Marketplace.</p>
                <?php if ($user && $user['role'] === 'seller'): ?>
                    <a href="/seller/post-product.php" class="btn btn-primary">Post Product</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($featured as $p): ?>
                    <div class="product-card">
                        <?php if ($p['main_image']): ?>
                            <img src="/<?= sanitize($p['main_image']) ?>" alt="<?= sanitize($p['item_name']) ?>" class="product-card-img">
                        <?php else: ?>
                            <div class="product-card-img-placeholder">No image available</div>
                        <?php endif; ?>
                        <div class="product-card-body">
                            <div class="product-card-name"><?= sanitize($p['item_name']) ?></div>
                            <div class="product-card-seller">By <?= sanitize($p['seller_name']) ?> &middot; <?= sanitize($p['category_name'] ?? 'Other') ?></div>
                            <div class="product-card-price"><?= format_price($p['selling_price']) ?></div>
                            <div class="product-card-spec"><?= sanitize($p['specifications']) ?></div>
                            <div style="display:flex; gap:6px; margin-bottom:12px;">
                                <?php if ($p['is_negotiable']): ?>
                                    <span class="badge badge-success">Negotiable</span>
                                <?php endif; ?>
                                <span class="badge badge-muted"><?= sanitize($p['condition_status']) ?></span>
                            </div>
                            <div class="product-card-footer">
                                <a href="/product-details.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;">View Details</a>
                                <a href="/cart.php?add=<?= $p['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Simple, safe student-to-student trading</p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:24px;">
            <div class="card card-body" style="text-align:center;">
                <div style="font-size:32px; margin-bottom:12px;">1</div>
                <h3 style="font-size:16px; font-weight:700; margin-bottom:8px;">Register &amp; Verify</h3>
                <p style="font-size:14px; color:var(--text-muted);">Create an account using your student details. Apply to become a verified seller.</p>
            </div>
            <div class="card card-body" style="text-align:center;">
                <div style="font-size:32px; margin-bottom:12px;">2</div>
                <h3 style="font-size:16px; font-weight:700; margin-bottom:8px;">Browse &amp; Order</h3>
                <p style="font-size:14px; color:var(--text-muted);">Search products, add to cart, and place orders with your delivery details.</p>
            </div>
            <div class="card card-body" style="text-align:center;">
                <div style="font-size:32px; margin-bottom:12px;">3</div>
                <h3 style="font-size:16px; font-weight:700; margin-bottom:8px;">Meet &amp; Receive</h3>
                <p style="font-size:14px; color:var(--text-muted);">The seller delivers to your agreed location on campus. Inspect the item first.</p>
            </div>
            <div class="card card-body" style="text-align:center;">
                <div style="font-size:32px; margin-bottom:12px;">4</div>
                <h3 style="font-size:16px; font-weight:700; margin-bottom:8px;">Pay After Delivery</h3>
                <p style="font-size:14px; color:var(--text-muted);">Pay only after you have received and confirmed the item is as described.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
