<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'CMS Pages';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_id  = (int)$_POST['page_id'];
    $content  = trim($_POST['page_content'] ?? '');
    $title    = trim($_POST['page_title'] ?? '');
    if ($page_id && $content) {
        $pdo->prepare("UPDATE cms_pages SET page_title=?, page_content=? WHERE id=?")->execute([$title, $content, $page_id]);
        log_activity($pdo, $user['id'], 'Updated CMS page #' . $page_id);
        $msg = 'Page updated successfully.';
    }
    header('Location: /admin/cms.php?msg=' . urlencode($msg));
    exit;
}
if (isset($_GET['msg'])) $msg = $_GET['msg'];

$pages = $pdo->query("SELECT * FROM cms_pages ORDER BY id")->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    foreach ($pages as $p) {
        if ($p['id'] === (int)$_GET['edit']) { $editing = $p; break; }
    }
}

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
            <li><a href="/admin/cms.php" class="active">CMS Pages</a></li>
            <li><a href="/admin/settings.php">Settings</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">CMS Pages</h1>
        <?php if ($msg): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
        <div style="display:grid; grid-template-columns: 280px 1fr; gap:24px; align-items:start;">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Pages</h2></div>
                <ul style="list-style:none;">
                    <?php foreach ($pages as $p): ?>
                        <li>
                            <a href="/admin/cms.php?edit=<?= $p['id'] ?>" style="display:block; padding:12px 20px; border-bottom:1px solid var(--border); font-size:14px; font-weight:500; color:<?= (isset($_GET['edit']) && (int)$_GET['edit']===$p['id'])? 'var(--primary)':'var(--text)' ?>;">
                                <?= sanitize($p['page_title']) ?>
                                <div style="font-size:11px; font-family:var(--font-mono); color:var(--text-muted); margin-top:2px;">/<?= sanitize($p['page_slug']) ?></div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <?php if ($editing): ?>
                    <div class="card">
                        <div class="card-header"><h2 class="card-title">Edit: <?= sanitize($editing['page_title']) ?></h2></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="page_id" value="<?= $editing['id'] ?>">
                                <div class="form-group">
                                    <label class="form-label">Page Title</label>
                                    <input type="text" name="page_title" class="form-control" value="<?= sanitize($editing['page_title']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Page Content</label>
                                    <textarea name="page_content" class="form-control" rows="14" required><?= sanitize($editing['page_content']) ?></textarea>
                                    <div class="form-hint">Last updated: <?= date('M j, Y g:i A', strtotime($editing['updated_at'])) ?></div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state"><h3>Select a page to edit</h3><p>Click any page from the list on the left to start editing.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
