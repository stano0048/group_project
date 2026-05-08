<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('moderator');
$user = get_user();
$page_title = 'Moderator Dashboard';

$stats = [
    'users'        => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'pending_apps' => $pdo->query("SELECT COUNT(*) FROM seller_applications WHERE status='pending'")->fetchColumn(),
    'products'     => $pdo->query("SELECT COUNT(*) FROM products WHERE product_status='on_sale'")->fetchColumn(),
    'reports'      => $pdo->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn(),
    'pending_prod' => $pdo->query("SELECT COUNT(*) FROM products WHERE product_status='pending_review'")->fetchColumn(),
];

require_once '../includes/header.php';
?>
<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-moderator" style="margin-top:4px; display:inline-block;">Moderator</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/moderator/dashboard.php" class="active">Dashboard</a></li>
            <li><a href="/moderator/add-user.php">Add User</a></li>
            <li><a href="/moderator/users.php">View Users</a></li>
            <li><a href="/admin/seller-applications.php">Seller Applications <?php if ($stats['pending_apps'] > 0): ?><span class="notif-badge" style="position:static;margin-left:4px;"><?= $stats['pending_apps'] ?></span><?php endif; ?></a></li>
            <li><a href="/moderator/products.php">View Products</a></li>
            <li><a href="/moderator/reports.php">Reported Products <?php if ($stats['reports'] > 0): ?><span class="notif-badge" style="position:static;margin-left:4px;"><?= $stats['reports'] ?></span><?php endif; ?></a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Moderator Dashboard</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Total Users</div><div class="stat-value"><?= $stats['users'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Pending Applications</div><div class="stat-value warning"><?= $stats['pending_apps'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Products On Sale</div><div class="stat-value primary"><?= $stats['products'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Pending Review</div><div class="stat-value warning"><?= $stats['pending_prod'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Pending Reports</div><div class="stat-value danger"><?= $stats['reports'] ?></div></div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="card card-body">
                <h3 style="font-size:15px; font-weight:700; margin-bottom:12px;">Quick Actions</h3>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <a href="/admin/seller-applications.php?status=pending" class="btn btn-primary btn-sm">Review Seller Applications</a>
                    <a href="/moderator/products.php?status=pending_review" class="btn btn-outline btn-sm">Review Pending Products</a>
                    <a href="/moderator/reports.php" class="btn btn-outline btn-sm">View Reports</a>
                    <a href="/moderator/add-user.php" class="btn btn-outline btn-sm">Add New User</a>
                </div>
            </div>
            <div class="card card-body">
                <h3 style="font-size:15px; font-weight:700; margin-bottom:12px;">Moderator Restrictions</h3>
                <ul style="font-size:13px; color:var(--text-muted); padding-left:18px; line-height:2;">
                    <li>Cannot delete admin accounts</li>
                    <li>Cannot promote users to admin</li>
                    <li>Cannot promote users to moderator</li>
                    <li>Cannot access system settings</li>
                    <li>Cannot permanently delete records</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
