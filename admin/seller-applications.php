<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role(['admin','moderator']);
$user = get_user();
$page_title = 'Seller Applications';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = (int)$_POST['app_id'];
    $action = $_POST['action'] ?? '';
    $allowed = ['approved','rejected','more_details'];

    if (in_array($action, $allowed)) {
        $app = $pdo->prepare("SELECT * FROM seller_applications WHERE id = ?");
        $app->execute([$app_id]);
        $app = $app->fetch();

        if ($app) {
            $pdo->prepare("UPDATE seller_applications SET status=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?")->execute([$action, $user['id'], $app_id]);

            if ($action === 'approved') {
                $pdo->prepare("UPDATE users SET role='seller' WHERE id=?")->execute([$app['user_id']]);
                send_notification($pdo, $app['user_id'], 'Seller Application Approved', 'Congratulations! Your seller application has been approved. You can now post products.');
                log_activity($pdo, $user['id'], 'Approved seller application for user #' . $app['user_id']);
            } elseif ($action === 'rejected') {
                send_notification($pdo, $app['user_id'], 'Seller Application Rejected', 'Your seller application was not approved at this time. You may apply again.');
                log_activity($pdo, $user['id'], 'Rejected seller application for user #' . $app['user_id']);
            } else {
                send_notification($pdo, $app['user_id'], 'More Details Required', 'The admin requires more information about your seller application. Please resubmit.');
            }
        }
    }
    header('Location: /admin/seller-applications.php');
    exit;
}

$filter = $_GET['status'] ?? 'pending';
$stmt = $pdo->prepare("SELECT sa.*, u.username, u.email, u.full_name as user_full_name FROM seller_applications sa JOIN users u ON sa.user_id = u.id WHERE sa.status = ? ORDER BY sa.created_at DESC");
$stmt->execute([$filter]);
$applications = $stmt->fetchAll();

$is_admin = $user['role'] === 'admin';
require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-<?= $user['role'] ?>" style="margin-top:4px; display:inline-block;"><?= ucfirst($user['role']) ?></div>
        </div>
        <?php if ($is_admin): ?>
            <div class="sidebar-section-title">Main</div>
            <ul class="sidebar-nav"><li><a href="/admin/dashboard.php">Dashboard</a></li></ul>
            <div class="sidebar-section-title">Users</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/users.php">Manage Users</a></li>
                <li><a href="/admin/seller-applications.php" class="active">Seller Applications</a></li>
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
                <li><a href="/admin/settings.php">Settings</a></li>
                <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
            </ul>
        <?php else: ?>
            <ul class="sidebar-nav">
                <li><a href="/moderator/dashboard.php">Dashboard</a></li>
                <li><a href="/moderator/users.php">View Users</a></li>
                <li><a href="/moderator/seller-applications.php" class="active">Seller Applications</a></li>
                <li><a href="/moderator/products.php">View Products</a></li>
                <li><a href="/moderator/reports.php">Reports</a></li>
                <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
            </ul>
        <?php endif; ?>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Seller Applications</h1>

        <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
            <?php foreach (['pending','approved','rejected','more_details'] as $s): ?>
                <a href="?status=<?= $s ?>" class="btn <?= $filter === $s ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= ucfirst(str_replace('_',' ',$s)) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($applications)): ?>
            <div class="empty-state"><h3>No <?= $filter ?> applications</h3></div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:16px;">
                <?php foreach ($applications as $app): ?>
                    <div class="card">
                        <div class="card-body">
                            <div style="display:grid; grid-template-columns:1fr auto; gap:20px; align-items:start;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                                        <h3 style="font-size:16px; font-weight:700;"><?= sanitize($app['full_name']) ?></h3>
                                        <span class="badge <?= $app['status'] === 'approved' ? 'badge-success' : ($app['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>"><?= ucfirst(str_replace('_',' ',$app['status'])) ?></span>
                                    </div>
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:13px; margin-bottom:12px;">
                                        <div><span style="color:var(--text-muted);">Username:</span> <?= sanitize($app['username']) ?></div>
                                        <div><span style="color:var(--text-muted);">Email:</span> <?= sanitize($app['email']) ?></div>
                                        <div><span style="color:var(--text-muted);">Admission:</span> <span style="font-family:var(--font-mono);"><?= sanitize($app['admission_number']) ?></span></div>
                                        <div><span style="color:var(--text-muted);">Phone:</span> <?= sanitize($app['phone']) ?></div>
                                        <div><span style="color:var(--text-muted);">WhatsApp:</span> <?= sanitize($app['whatsapp_number']) ?></div>
                                        <div><span style="color:var(--text-muted);">Applied:</span> <?= time_ago($app['created_at']) ?></div>
                                    </div>
                                    <?php if ($app['reason']): ?>
                                        <div style="padding:12px; background:var(--bg-section); border-radius:var(--radius); font-size:13px; color:var(--text-muted); margin-bottom:12px;">
                                            <strong style="color:var(--text);">Reason:</strong><br><?= nl2br(sanitize($app['reason'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($app['student_id_front']): ?>
                                        <div style="margin-bottom:12px;">
                                            <div style="font-size:12px; font-weight:600; margin-bottom:6px;">Student ID:</div>
                                            <img src="/<?= sanitize($app['student_id_front']) ?>" style="max-width:220px; border-radius:var(--radius); border:1px solid var(--border);">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($app['status'] === 'pending'): ?>
                                    <div style="display:flex; flex-direction:column; gap:8px; min-width:120px;">
                                        <form method="POST">
                                            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                            <input type="hidden" name="action" value="approved">
                                            <button type="submit" class="btn btn-success btn-sm btn-block confirm-action" data-confirm="Approve this seller application?">Approve</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button type="submit" class="btn btn-danger btn-sm btn-block confirm-action" data-confirm="Reject this application?">Reject</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                            <input type="hidden" name="action" value="more_details">
                                            <button type="submit" class="btn btn-outline btn-sm btn-block">Request Info</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
