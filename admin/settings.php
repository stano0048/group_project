<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'Settings';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password'])) {
        $msg_type = 'danger';
        $msg = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $msg_type = 'danger';
        $msg = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $msg_type = 'danger';
        $msg = 'Passwords do not match.';
    } else {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        log_activity($pdo, $user['id'], 'Admin changed own password');
        $msg_type = 'success';
        $msg = 'Password changed successfully.';
    }
}

$log_count = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();

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
            <li><a href="/admin/feedback.php">Feedback</a></li>
            <li><a href="/admin/cms.php">CMS Pages</a></li>
            <li><a href="/admin/settings.php" class="active">Settings</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Admin Settings</h1>

        <?php if ($msg): ?><div class="alert alert-<?= $msg_type ?? 'success' ?>"><?= sanitize($msg) ?></div><?php endif; ?>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Change Admin Password</h2></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
            <div>
                <div class="card card-body" style="margin-bottom:20px;">
                    <h3 style="font-size:15px; font-weight:700; margin-bottom:12px;">System Info</h3>
                    <div style="display:flex; flex-direction:column; gap:10px; font-size:14px;">
                        <div style="display:flex; justify-content:space-between;"><span style="color:var(--text-muted);">Admin Username</span><span style="font-weight:600;"><?= sanitize($user['username']) ?></span></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:var(--text-muted);">Admin Email</span><span style="font-weight:600;"><?= sanitize($user['email']) ?></span></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:var(--text-muted);">Total Activity Logs</span><span style="font-weight:600; font-family:var(--font-mono);"><?= $log_count ?></span></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:var(--text-muted);">PHP Version</span><span style="font-weight:600; font-family:var(--font-mono);"><?= phpversion() ?></span></div>
                    </div>
                </div>
                <div class="card card-body">
                    <h3 style="font-size:15px; font-weight:700; margin-bottom:12px;">Default Login Credentials</h3>
                    <div style="font-size:13px; color:var(--text-muted); line-height:2;">
                        <div><strong style="color:var(--text);">Email:</strong> admin@karu.ac.ke</div>
                        <div><strong style="color:var(--text);">Password:</strong> password (bcrypt hashed default)</div>
                        <div style="margin-top:8px; font-size:12px; color:var(--danger);">Change the default password immediately after first login.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
