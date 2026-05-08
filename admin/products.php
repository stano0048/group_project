<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'Manage Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid    = (int)$_POST['product_id'];
    $action = $_POST['action'] ?? '';
    $status_map = ['approve' => 'on_sale', 'hide' => 'hidden', 'reject' => 'rejected', 'restore' => 'on_sale'];
    if (isset($status_map[$action])) {
        $pdo->prepare("UPDATE products SET product_status=? WHERE id=?")->execute([$status_map[$action], $pid]);
        log_activity($pdo, $user['id'], 'Admin set product #' . $pid . ' to ' . $status_map[$action]);
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$pid]);
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$pid]);
        log_activity($pdo, $user['id'], 'Admin deleted product #' . $pid);
    }
    header('Location: /admin/products.php');
    exit;
}

$filter = $_GET['status'] ?? '';
$where  = $filter ? "WHERE p.product_status = ?" : '';
$params = $filter ? [$filter] : [];
$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name, c.name as cat_name, (SELECT image_path FROM product_images WHERE product_id=p.id LIMIT 1) as img FROM products p JOIN users u ON p.seller_id=u.id LEFT JOIN categories c ON p.category_id=c.id $where ORDER BY p.created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-admin" style="margin-top:4px; display:inline-block;">Admin</div>
        </div>
        <div class="sidebar-section-title">Main</div>
        <ul class="sidebar-nav"><li><a href="/admin/dashboard.php">Dashboard</a></li></ul>
        <div class="sidebar-section-title">Users</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/users.php">Manage Users</a></li>
            <li><a href="/admin/seller-applications.php">Seller Applications</a></li>
        </ul>
        <div class="sidebar-section-title">Marketplace</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/products.php" class="active">Manage Products</a></li>
            <li><a href="/admin/orders.php">Manage Orders</a></li>
            <li><a href="/admin/categories.php">Categories</a></li>
            <li><a href="/admin/reports.php">Reports</a></li>
        </ul>
        <div class="sidebar-section-title">System</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/feedback.php">Feedback</a></li>
            <li><a href="/admin/cms.php">CMS Pages</a></li>
            <li><a href="/admin/settings.php">Settings</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Manage Products</h1>

        <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
            <a href="/admin/products.php" class="btn <?= !$filter ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
            <?php foreach (['pending_review','on_sale','sold','hidden','rejected'] as $s): ?>
                <a href="?status=<?= $s ?>" class="btn <?= $filter === $s ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= ucfirst(str_replace('_',' ',$s)) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-state"><h3>No products found</h3></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Product</th><th>Seller</th><th>Category</th><th>Price</th><th>Status</th><th>Posted</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <?php if ($p['img']): ?>
                                                <img src="/<?= sanitize($p['img']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius);">
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight:600; font-size:13px;"><?= sanitize($p['item_name']) ?></div>
                                                <div style="font-size:11px; color:var(--text-muted);"><?= sanitize($p['condition_status']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size:13px;"><?= sanitize($p['seller_name']) ?></td>
                                    <td style="font-size:13px;"><?= sanitize($p['cat_name'] ?? 'Other') ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono); font-size:13px;"><?= format_price($p['selling_price']) ?></td>
                                    <td>
                                        <?php $badge = match($p['product_status']) {
                                            'on_sale' => 'badge-success',
                                            'pending_review' => 'badge-warning',
                                            'sold' => 'badge-muted',
                                            default => 'badge-danger'
                                        }; ?>
                                        <span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_',' ',$p['product_status'])) ?></span>
                                    </td>
                                    <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($p['created_at']) ?></td>
                                    <td>
                                        <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                            <?php if ($p['product_status'] === 'pending_review'): ?>
                                                <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="approve"><button type="submit" class="btn btn-success btn-sm">Approve</button></form>
                                                <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="reject"><button type="submit" class="btn btn-danger btn-sm">Reject</button></form>
                                            <?php elseif ($p['product_status'] === 'on_sale'): ?>
                                                <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="hide"><button type="submit" class="btn btn-warning btn-sm">Hide</button></form>
                                            <?php elseif (in_array($p['product_status'],['hidden','rejected'])): ?>
                                                <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="restore"><button type="submit" class="btn btn-success btn-sm">Restore</button></form>
                                            <?php endif; ?>
                                            <a href="/product-details.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">View</a>
                                            <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="delete"><button type="submit" class="btn btn-danger btn-sm confirm-action" data-confirm="Delete this product permanently?">Delete</button></form>
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
