<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('seller');
require_not_suspended();
$user = get_user();
$page_title = 'My Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $pid = (int)$_POST['delete_product'];
    $check = $pdo->prepare("SELECT id FROM products WHERE id = ? AND seller_id = ?");
    $check->execute([$pid, $user['id']]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$pid]);
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$pid]);
        log_activity($pdo, $user['id'], 'Deleted product #' . $pid);
    }
    header('Location: /seller/my-products.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) as main_image FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$user['id']]);
$products = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-seller" style="margin-top:4px; display:inline-block;">Seller</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/seller/dashboard.php">Dashboard</a></li>
            <li><a href="/seller/post-product.php">Post Product</a></li>
            <li><a href="/seller/my-products.php" class="active">My Products</a></li>
            <li><a href="/seller/orders.php">Orders Received</a></li>
            <li><a href="/seller/sold-items.php">Sold Items</a></li>
            <li><a href="/seller/feedback.php">Feedback</a></li>
            <li><a href="/seller/profile.php">Seller Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <h1 style="font-size:22px; font-weight:800;">My Products</h1>
            <a href="/seller/post-product.php" class="btn btn-primary">Post New Product</a>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <h3>No products yet</h3>
                <p>Start posting products to begin selling.</p>
                <a href="/seller/post-product.php" class="btn btn-primary">Post Product</a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Product</th><th>Category</th><th>Price</th><th>Qty</th><th>Status</th><th>Posted</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <?php if ($p['main_image']): ?>
                                                <img src="/<?= sanitize($p['main_image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius);">
                                            <?php endif; ?>
                                            <span style="font-weight:600; font-size:14px;"><?= sanitize($p['item_name']) ?></span>
                                        </div>
                                    </td>
                                    <td style="font-size:13px;"><?= sanitize($p['category_name'] ?? 'Other') ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($p['selling_price']) ?></td>
                                    <td><?= (int)$p['quantity'] ?></td>
                                    <td>
                                        <?php
                                        $badge = match($p['product_status']) {
                                            'on_sale' => 'badge-success',
                                            'sold' => 'badge-muted',
                                            'pending_review' => 'badge-warning',
                                            'rejected' => 'badge-danger',
                                            'hidden' => 'badge-danger',
                                            default => 'badge-muted'
                                        };
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $p['product_status'])) ?></span>
                                    </td>
                                    <td style="font-size:13px; color:var(--text-muted);"><?= time_ago($p['created_at']) ?></td>
                                    <td>
                                        <div style="display:flex; gap:6px;">
                                            <a href="/product-details.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">View</a>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="delete_product" value="<?= $p['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm confirm-action" data-confirm="Delete this product?">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
