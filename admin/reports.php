<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role(['admin','moderator']);
$user = get_user();
$page_title = 'Product Reports';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid = (int)$_POST['report_id'];
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['reviewed','dismissed'])) {
        $pdo->prepare("UPDATE reports SET status=? WHERE id=?")->execute([$action,$rid]);
        log_activity($pdo, $user['id'], 'Updated report #' . $rid . ' to ' . $action);
    }
    header('Location: /admin/reports.php');
    exit;
}

$filter = $_GET['status'] ?? 'pending';
$stmt = $pdo->prepare("SELECT r.*, p.item_name, u.username as reporter_name FROM reports r JOIN products p ON r.product_id=p.id JOIN users u ON r.reported_by=u.id WHERE r.status=? ORDER BY r.created_at DESC");
$stmt->execute([$filter]);
$reports = $stmt->fetchAll();

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
            <div class="sidebar-section-title">Marketplace</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/products.php">Manage Products</a></li>
                <li><a href="/admin/reports.php" class="active">Reports</a></li>
            </ul>
            <div class="sidebar-section-title">System</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/cms.php">CMS Pages</a></li>
                <li><a href="/admin/settings.php">Settings</a></li>
                <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
            </ul>
        <?php else: ?>
            <ul class="sidebar-nav">
                <li><a href="/moderator/dashboard.php">Dashboard</a></li>
                <li><a href="/moderator/products.php">Products</a></li>
                <li><a href="/moderator/reports.php" class="active">Reports</a></li>
                <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
            </ul>
        <?php endif; ?>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Product Reports</h1>
        <div style="display:flex; gap:8px; margin-bottom:24px;">
            <?php foreach (['pending','reviewed','dismissed'] as $s): ?>
                <a href="?status=<?= $s ?>" class="btn <?= $filter===$s?'btn-primary':'btn-outline' ?> btn-sm"><?= ucfirst($s) ?></a>
            <?php endforeach; ?>
        </div>
        <?php if (empty($reports)): ?>
            <div class="empty-state"><h3>No <?= $filter ?> reports</h3></div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ($reports as $r): ?>
                    <div class="card card-body">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
                            <div>
                                <div style="font-weight:700; font-size:15px; margin-bottom:4px;"><?= sanitize($r['item_name']) ?></div>
                                <div style="font-size:13px; color:var(--text-muted); margin-bottom:4px;">Reported by <strong><?= sanitize($r['reporter_name']) ?></strong> &middot; <?= time_ago($r['created_at']) ?></div>
                                <div style="font-size:13px; margin-bottom:6px;"><strong>Reason:</strong> <?= sanitize($r['reason']) ?></div>
                                <?php if ($r['description']): ?>
                                    <div style="font-size:13px; color:var(--text-muted);"><?= sanitize($r['description']) ?></div>
                                <?php endif; ?>
                                <div style="margin-top:8px;">
                                    <a href="/product-details.php?id=<?= $r['product_id'] ?>" class="btn btn-outline btn-sm" target="_blank">View Product</a>
                                </div>
                            </div>
                            <?php if ($r['status'] === 'pending'): ?>
                                <div style="display:flex; flex-direction:column; gap:6px; min-width:120px;">
                                    <form method="POST"><input type="hidden" name="report_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="reviewed"><button type="submit" class="btn btn-success btn-sm btn-block">Mark Reviewed</button></form>
                                    <form method="POST"><input type="hidden" name="report_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="dismissed"><button type="submit" class="btn btn-outline btn-sm btn-block">Dismiss</button></form>
                                </div>
                            <?php else: ?>
                                <span class="badge <?= $r['status']==='reviewed'?'badge-success':'badge-muted' ?>"><?= ucfirst($r['status']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
