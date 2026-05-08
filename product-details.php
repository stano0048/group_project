<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$user = get_user();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /products.php'); exit; }

$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name, u.whatsapp_number as seller_wa, c.name as category_name FROM products p JOIN users u ON p.seller_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.product_status = 'on_sale'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { header('Location: /products.php'); exit; }

$images = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC");
$images->execute([$id]);
$images = $images->fetchAll();

$feedback = $pdo->prepare("SELECT f.*, u.username FROM feedback f JOIN users u ON f.buyer_id = u.id WHERE f.seller_id = ? ORDER BY f.created_at DESC LIMIT 5");
$feedback->execute([$product['seller_id']]);
$feedback = $feedback->fetchAll();

$page_title = sanitize($product['item_name']);

$in_wishlist = false;
if ($user) {
    $wl = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wl->execute([$user['id'], $id]);
    $in_wishlist = (bool)$wl->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_reason'])) {
    if (!$user) { header('Location: /login.php'); exit; }
    $reason = $_POST['report_reason'];
    $desc = trim($_POST['report_description'] ?? '');
    $stmt = $pdo->prepare("INSERT INTO reports (product_id, reported_by, reason, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $user['id'], $reason, $desc]);
    $report_success = true;
}

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div style="margin-bottom:20px;">
            <a href="/products.php" class="btn btn-outline btn-sm">Back to Products</a>
        </div>

        <?php if (isset($report_success)): ?>
            <div class="alert alert-success">Your report has been submitted. Thank you for keeping the marketplace safe.</div>
        <?php endif; ?>

        <div class="product-detail-grid">
            <div class="product-images">
                <?php if (!empty($images)): ?>
                    <img src="/<?= sanitize($images[0]['image_path']) ?>" alt="<?= sanitize($product['item_name']) ?>" class="product-main-img" id="mainProductImg">
                    <?php if (count($images) > 1): ?>
                        <div class="product-thumbs">
                            <?php foreach ($images as $i => $img): ?>
                                <img src="/<?= sanitize($img['image_path']) ?>" alt="Photo <?= $i+1 ?>" class="product-thumb <?= $i === 0 ? 'active' : '' ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="width:100%;height:400px;background:var(--bg-section);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;">No images available</div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1 class="product-title"><?= sanitize($product['item_name']) ?></h1>

                <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
                    <span class="badge badge-info"><?= sanitize($product['category_name'] ?? 'Other') ?></span>
                    <span class="badge badge-muted"><?= sanitize($product['condition_status']) ?></span>
                    <?php if ($product['is_negotiable']): ?>
                        <span class="badge badge-success">Negotiable</span>
                    <?php endif; ?>
                </div>

                <div class="product-price"><?= format_price($product['selling_price']) ?></div>

                <?php if ($product['is_negotiable'] && $product['min_price'] && $product['max_price']): ?>
                    <div style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
                        Price Range: <?= format_price($product['min_price']) ?> &mdash; <?= format_price($product['max_price']) ?>
                    </div>
                <?php endif; ?>

                <div class="product-meta">
                    <div class="product-meta-row">
                        <span class="product-meta-label">Seller</span>
                        <span><?= sanitize($product['seller_name']) ?></span>
                    </div>
                    <div class="product-meta-row">
                        <span class="product-meta-label">Delivery Area</span>
                        <span><?= sanitize($product['delivery_area'] ?: 'Not specified') ?></span>
                    </div>
                    <div class="product-meta-row">
                        <span class="product-meta-label">Quantity Available</span>
                        <span><?= (int)$product['quantity'] ?></span>
                    </div>
                    <div class="product-meta-row">
                        <span class="product-meta-label">Posted</span>
                        <span><?= date('M j, Y \a\t g:i A', strtotime($product['created_at'])) ?></span>
                    </div>
                    <?php $wa = $product['whatsapp_number'] ?: $product['seller_wa']; ?>
                    <?php if ($wa): ?>
                        <div class="product-meta-row">
                            <span class="product-meta-label">WhatsApp</span>
                            <a href="https://wa.me/254<?= ltrim(preg_replace('/^0/', '', $wa), '0') ?>" target="_blank" style="color:var(--success);font-weight:600;"><?= sanitize($wa) ?></a>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom:20px;">
                    <div style="font-size:13px; font-weight:600; margin-bottom:8px;">Specifications</div>
                    <div class="product-spec-box"><?= nl2br(sanitize($product['specifications'])) ?></div>
                </div>

                <div id="cart_msg" class="alert" style="display:none;"></div>

                <?php if ($product['is_negotiable'] && $product['min_price'] && $product['max_price']): ?>
                    <div class="form-group">
                        <label class="form-label">Your Offer Price (KES)</label>
                        <input type="number" id="offer_price" class="form-control" placeholder="Enter your offer" data-min="<?= $product['min_price'] ?>" data-max="<?= $product['max_price'] ?>" min="<?= $product['min_price'] ?>" max="<?= $product['max_price'] ?>">
                        <div id="offer_msg" class="form-hint"></div>
                    </div>
                <?php endif; ?>

                <?php if ($user): ?>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button class="btn btn-primary add-to-cart-btn" data-product="<?= $product['id'] ?>" style="flex:1;">Add to Cart</button>
                        <a href="/checkout.php?product=<?= $product['id'] ?>" class="btn btn-success" style="flex:1;">Order Now</a>
                        <a href="/cart.php?wishlist=<?= $product['id'] ?>" class="btn btn-outline btn-sm"><?= $in_wishlist ? 'Saved' : 'Save' ?></a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <a href="/login.php">Login</a> to add this item to cart or place an order.
                    </div>
                <?php endif; ?>

                <div style="margin-top:24px;">
                    <div class="payment-banner">
                        <div class="payment-banner-icon">!</div>
                        <div class="payment-banner-text">
                            <strong>Do not pay before delivery</strong>
                            <p>Payment should only be made after you have received and confirmed the item in person.</p>
                        </div>
                    </div>
                </div>

                <?php if ($user && $user['id'] !== $product['seller_id']): ?>
                    <details style="margin-top:16px;">
                        <summary style="cursor:pointer; font-size:13px; color:var(--danger); font-weight:600;">Report this product</summary>
                        <div style="margin-top:12px; padding:16px; background:var(--bg-section); border-radius:var(--radius); border:1px solid var(--border);">
                            <form method="POST">
                                <div class="form-group">
                                    <label class="form-label">Reason</label>
                                    <select name="report_reason" class="form-control" required>
                                        <option value="">Select reason</option>
                                        <option>Fake product</option>
                                        <option>Wrong price</option>
                                        <option>Inappropriate image</option>
                                        <option>Seller is suspicious</option>
                                        <option>Product already sold</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description (optional)</label>
                                    <textarea name="report_description" class="form-control" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
                            </form>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($feedback)): ?>
            <div style="margin-top:48px;">
                <h2 style="font-size:20px; font-weight:700; margin-bottom:20px;">Seller Feedback</h2>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($feedback as $fb): ?>
                        <div class="card card-body" style="padding:16px;">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <span class="badge <?= $fb['feedback_type'] === 'positive' ? 'badge-success' : ($fb['feedback_type'] === 'negative' ? 'badge-danger' : 'badge-warning') ?>"><?= ucfirst($fb['feedback_type']) ?></span>
                                <span style="font-size:13px; font-weight:600;"><?= sanitize($fb['username']) ?></span>
                                <span style="font-size:12px; color:var(--text-muted);"><?= time_ago($fb['created_at']) ?></span>
                            </div>
                            <?php if ($fb['comment']): ?>
                                <p style="font-size:14px; color:var(--text-muted);"><?= sanitize($fb['comment']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
