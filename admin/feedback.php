<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'Feedback Overview';

$stmt = $pdo->query("SELECT f.*, b.username as buyer_name, s.username as seller_name FROM feedback f JOIN users b ON f.buyer_id=b.id JOIN users s ON f.seller_id=s.id ORDER BY f.created_at DESC");
$feedback = $stmt->fetchAll();

$pos = count(array_filter($feedback, fn($f)=>$f['feedback_type']==='positive'));
$neg = count(array_filter($feedback, fn($f)=>$f['feedback_type']==='negative'));
$neu = count(array_filter($feedback, fn($f)=>$f['feedback_type']==='neutral'));

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
            <li><a href="/admin/orders.php">Manage Orders</a></li>
            <li><a href="/admin/categories.php">Categories</a></li>
            <li><a href="/admin/reports.php">Reports</a></li>
        </ul>
        <div class="sidebar-section-title">System</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/feedback.php" class="active">Feedback</a></li>
            <li><a href="/admin/cms.php">CMS Pages</a></li>
            <li><a href="/admin/settings.php">Settings</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Feedback Overview</h1>
        <div class="stats-grid" style="margin-bottom:24px;">
            <div class="stat-card"><div class="stat-label">Positive</div><div class="stat-value success"><?= $pos ?></div></div>
            <div class="stat-card"><div class="stat-label">Neutral</div><div class="stat-value warning"><?= $neu ?></div></div>
            <div class="stat-card"><div class="stat-label">Negative</div><div class="stat-value danger"><?= $neg ?></div></div>
            <div class="stat-card"><div class="stat-label">Total</div><div class="stat-value"><?= count($feedback) ?></div></div>
        </div>
        <?php if (empty($feedback)): ?>
            <div class="empty-state"><h3>No feedback submitted yet</h3></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Order</th><th>Buyer</th><th>Seller</th><th>Type</th><th>Comment</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php foreach ($feedback as $fb): ?>
                                <tr>
                                    <td style="font-family:var(--font-mono); font-size:12px;">#<?= $fb['order_id'] ?></td>
                                    <td style="font-size:13px; font-weight:600;"><?= sanitize($fb['buyer_name']) ?></td>
                                    <td style="font-size:13px;"><?= sanitize($fb['seller_name']) ?></td>
                                    <td><span class="badge <?= $fb['feedback_type']==='positive'?'badge-success':($fb['feedback_type']==='negative'?'badge-danger':'badge-warning') ?>"><?= ucfirst($fb['feedback_type']) ?></span></td>
                                    <td style="font-size:12px; color:var(--text-muted); max-width:200px;"><?= sanitize($fb['comment'] ?? '—') ?></td>
                                    <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($fb['created_at']) ?></td>
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
