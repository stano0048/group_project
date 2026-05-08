<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role(['moderator','admin']);
$user = get_user();
$page_title = 'View Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid    = (int)$_POST['product_id'];
    $action = $_POST['action'] ?? '';
    if ($action === 'hide') {
        $pdo->prepare("UPDATE products SET product_status='hidden' WHERE id=?")->execute([$pid]);
        log_activity($pdo, $user['id'], 'Moderator hid product #' . $pid);
    } elseif ($action === 'approve') {
        $pdo->prepare("UPDATE products SET product_status='on_sale' WHERE id=?")->execute([$pid]);
        log_activity($pdo, $user['id'], 'Moderator approved product #' . $pid);
    } elseif ($action === 'restore') {
        $pdo->prepare("UPDATE products SET product_status='on_sale' WHERE id=?")->execute([$pid]);
    }
    header('Location: /moderator/products.php');
    exit;
}

$filter = $_GET['status'] ?? '';
$where  = $filter ? "WHERE p.product_status=?" : '';
$params = $filter ? [$filter] : [];
$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name, (SELECT image_path FROM product_images WHERE product_id=p.id LIMIT 1) as img FROM products p JOIN users u ON p.seller_id=u.id $where ORDER BY p.created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-moderator" style="margin-top:4px; display:inline-block;">Moderator</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/moderator/dashboard.php">Dashboard</a></li>
            <li><a href="/moderator/add-user.php">Add User</a></li>
            <li><a href="/moderator/users.php">View Users</a></li>
            <li><a href="/admin/seller-applications.php">Seller Applications</a></li>
            <li><a href="/moderator/products.php" class="active">View Products</a></li>
            <li><a href="/moderator/reports.php">Reported Products</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">View Products</h1>
        <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
            <a href="/moderator/products.php" class="btn <?= !$filter?'btn-primary':'btn-outline' ?> btn-sm">All</a>
            <?php foreach (['pending_review','on_sale','hidden','rejected'] as $s): ?>
                <a href="?status=<?= $s ?>" class="btn <?= $filter===$s?'btn-primary':'btn-outline' ?> btn-sm"><?= ucfirst(str_replace('_',' ',$s)) ?></a>
            <?php endforeach; ?>
        </div>
        <?php if (empty($products)): ?>
            <div class="empty-state"><h3>No products found</h3></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Product</th><th>Seller</th><th>Price</th><th>Status</th><th>Posted</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <?php if ($p['img']): ?>
                                                <img src="/<?= sanitize($p['img']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:var(--radius);">
                                            <?php endif; ?>
                                            <span style="font-weight:600; font-size:13px;"><?= sanitize($p['item_name']) ?></span>
                                        </div>
                                    </td>
                                    <td style="font-size:13px;"><?= sanitize($p['seller_name']) ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono); font-size:13px;"><?= format_price($p['selling_price']) ?></td>
                                    <td>
                                        <?php $badge = match($p['product_status']) {
                                            'on_sale'=>'badge-success', 'pending_review'=>'badge-warning',
                                            'hidden','rejected'=>'badge-danger', default=>'badge-muted'
                                        }; ?>
                                        <span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_',' ',$p['product_status'])) ?></span>
                                    </td>
                                    <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($p['created_at']) ?></td>
                                    <td>
                                        <div style="display:flex; gap:4px;">
                                            <?php if ($p['product_status'] === 'pending_review'): ?>
                                                <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="approve"><button type="submit" class="btn btn-success btn-sm">Approve</button></form>
                                            <?php endif; ?>
                                            <?php if ($p['product_status'] === 'on_sale'): ?>
                                                <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="hide"><button type="submit" class="btn btn-warning btn-sm confirm-action" data-confirm="Hide this product?">Hide</button></form>
                                            <?php endif; ?>
                                            <?php if ($p['product_status'] === 'hidden'): ?>
                                                <form method="POST"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="restore"><button type="submit" class="btn btn-outline btn-sm">Restore</button></form>
                                            <?php endif; ?>
                                            <a href="/product-details.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" target="_blank">View</a>
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
