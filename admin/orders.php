<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'Manage Orders';

$filter = $_GET['status'] ?? '';
$where  = $filter ? "WHERE o.order_status = ?" : '';
$params = $filter ? [$filter] : [];
$stmt = $pdo->prepare("SELECT o.*, b.username as buyer_name, s.username as seller_name FROM orders o JOIN users b ON o.buyer_id=b.id JOIN users s ON o.seller_id=s.id $where ORDER BY o.created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

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
            <li><a href="/admin/products.php">Manage Products</a></li>
            <li><a href="/admin/orders.php" class="active">Manage Orders</a></li>
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
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Manage Orders</h1>
        <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
            <a href="/admin/orders.php" class="btn <?= !$filter ? 'btn-primary':'btn-outline' ?> btn-sm">All</a>
            <?php foreach (['pending','accepted','delivering','delivered','sold','rejected','cancelled'] as $s): ?>
                <a href="?status=<?= $s ?>" class="btn <?= $filter===$s?'btn-primary':'btn-outline' ?> btn-sm"><?= ucfirst($s) ?></a>
            <?php endforeach; ?>
        </div>
        <?php if (empty($orders)): ?>
            <div class="empty-state"><h3>No orders found</h3></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Order ID</th><th>Buyer</th><th>Seller</th><th>Amount</th><th>Delivery Location</th><th>Status</th><th>Payment</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td style="font-family:var(--font-mono); font-size:12px;">#<?= $o['id'] ?></td>
                                    <td style="font-size:13px; font-weight:600;"><?= sanitize($o['buyer_name']) ?></td>
                                    <td style="font-size:13px;"><?= sanitize($o['seller_name']) ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($o['total_amount']) ?></td>
                                    <td style="font-size:12px; max-width:140px;"><?= sanitize($o['delivery_location']) ?></td>
                                    <td><span class="badge <?= in_array($o['order_status'],['pending']) ? 'badge-warning' : ($o['order_status']==='sold'?'badge-success':($o['order_status']==='cancelled'?'badge-danger':'badge-info')) ?>"><?= ucfirst($o['order_status']) ?></span></td>
                                    <td><span class="badge badge-warning" style="font-size:10px;"><?= ucfirst(str_replace('_',' ',$o['payment_status'])) ?></span></td>
                                    <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($o['created_at']) ?></td>
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
