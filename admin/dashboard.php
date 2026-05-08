<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'Admin Dashboard';

$stats = [
    'users'       => $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn(),
    'sellers'     => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn(),
    'products'    => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'sold'        => $pdo->query("SELECT COUNT(*) FROM products WHERE product_status = 'sold'")->fetchColumn(),
    'pending_apps'=> $pdo->query("SELECT COUNT(*) FROM seller_applications WHERE status = 'pending'")->fetchColumn(),
    'reports'     => $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
    'orders'      => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'on_sale'     => $pdo->query("SELECT COUNT(*) FROM products WHERE product_status = 'on_sale'")->fetchColumn(),
];

$recent_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_logs  = $pdo->query("SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 8")->fetchAll();

require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-admin" style="margin-top:4px; display:inline-block;">Admin</div>
        </div>
        <div class="sidebar-section-title">Main</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/dashboard.php" class="active">Dashboard</a></li>
        </ul>
        <div class="sidebar-section-title">Users</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/users.php">Manage Users</a></li>
            <li><a href="/admin/seller-applications.php">Seller Applications <?php if ($stats['pending_apps'] > 0): ?><span class="notif-badge" style="position:static; margin-left:4px;"><?= $stats['pending_apps'] ?></span><?php endif; ?></a></li>
        </ul>
        <div class="sidebar-section-title">Marketplace</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/products.php">Manage Products</a></li>
            <li><a href="/admin/orders.php">Manage Orders</a></li>
            <li><a href="/admin/categories.php">Categories</a></li>
            <li><a href="/admin/reports.php">Reports <?php if ($stats['reports'] > 0): ?><span class="notif-badge" style="position:static; margin-left:4px;"><?= $stats['reports'] ?></span><?php endif; ?></a></li>
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
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Admin Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Total Users</div><div class="stat-value"><?= $stats['users'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Verified Sellers</div><div class="stat-value success"><?= $stats['sellers'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Total Products</div><div class="stat-value primary"><?= $stats['products'] ?></div></div>
            <div class="stat-card"><div class="stat-label">On Sale</div><div class="stat-value success"><?= $stats['on_sale'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Products Sold</div><div class="stat-value"><?= $stats['sold'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Pending Applications</div><div class="stat-value warning"><?= $stats['pending_apps'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Pending Reports</div><div class="stat-value danger"><?= $stats['reports'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Total Orders</div><div class="stat-value"><?= $stats['orders'] ?></div></div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Users</h2>
                    <a href="/admin/users.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
                        <tbody>
                            <?php foreach ($recent_users as $u): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600; font-size:13px;"><?= sanitize($u['username']) ?></div>
                                        <div style="font-size:11px; color:var(--text-muted);"><?= sanitize($u['email']) ?></div>
                                    </td>
                                    <td><span class="nav-role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                    <td><span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                                    <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($u['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2 class="card-title">Activity Log</h2></div>
                <div style="display:flex; flex-direction:column;">
                    <?php foreach ($recent_logs as $log): ?>
                        <div style="padding:12px 20px; border-bottom:1px solid var(--border);">
                            <div style="font-size:13px; color:var(--text);"><?= sanitize($log['action']) ?></div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                <?= $log['username'] ? sanitize($log['username']) : 'System' ?> &middot; <?= time_ago($log['created_at']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
