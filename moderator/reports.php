<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('moderator');
$user = get_user();
$page_title = 'Reported Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid    = (int)$_POST['report_id'];
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['reviewed','dismissed'])) {
        $pdo->prepare("UPDATE reports SET status=? WHERE id=?")->execute([$action, $rid]);
        log_activity($pdo, $user['id'], 'Moderator updated report #' . $rid . ' to ' . $action);
    }
    header('Location: /moderator/reports.php');
    exit;
}

$filter = $_GET['status'] ?? 'pending';
$stmt = $pdo->prepare("SELECT r.*, p.item_name, p.id as pid, u.username as reporter_name FROM reports r JOIN products p ON r.product_id=p.id JOIN users u ON r.reported_by=u.id WHERE r.status=? ORDER BY r.created_at DESC");
$stmt->execute([$filter]);
$reports = $stmt->fetchAll();

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
            <li><a href="/moderator/products.php">View Products</a></li>
            <li><a href="/moderator/reports.php" class="active">Reported Products</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Reported Products</h1>

        <div style="display:flex; gap:8px; margin-bottom:24px;">
            <?php foreach (['pending','reviewed','dismissed'] as $s): ?>
                <a href="?status=<?= $s ?>" class="btn <?= $filter===$s?'btn-primary':'btn-outline' ?> btn-sm"><?= ucfirst($s) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($reports)): ?>
            <div class="empty-state"><h3>No <?= sanitize($filter) ?> reports</h3><p>All clear for now.</p></div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ($reports as $r): ?>
                    <div class="card card-body">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                            <div>
                                <div style="font-weight:700; font-size:15px; margin-bottom:4px;"><?= sanitize($r['item_name']) ?></div>
                                <div style="font-size:13px; color:var(--text-muted); margin-bottom:6px;">
                                    Reported by <strong><?= sanitize($r['reporter_name']) ?></strong> &middot; <?= time_ago($r['created_at']) ?>
                                </div>
                                <div style="margin-bottom:6px;">
                                    <span class="badge badge-danger" style="font-size:12px;"><?= sanitize($r['reason']) ?></span>
                                </div>
                                <?php if ($r['description']): ?>
                                    <div style="font-size:13px; color:var(--text-muted); margin-bottom:10px;"><?= sanitize($r['description']) ?></div>
                                <?php endif; ?>
                                <div style="display:flex; gap:8px;">
                                    <a href="/product-details.php?id=<?= $r['pid'] ?>" class="btn btn-outline btn-sm" target="_blank">View Product</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="action" value="reviewed">
                                        <button type="submit" class="btn btn-success btn-sm">Mark Reviewed</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="action" value="dismissed">
                                        <button type="submit" class="btn btn-outline btn-sm">Dismiss</button>
                                    </form>
                                </div>
                            </div>
                            <span class="badge <?= $r['status']==='pending'?'badge-warning':($r['status']==='reviewed'?'badge-success':'badge-muted') ?>"><?= ucfirst($r['status']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
