<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'Manage Categories';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name) {
            $pdo->prepare("INSERT INTO categories (name,description) VALUES(?,?)")->execute([$name,$desc]);
            log_activity($pdo, $user['id'], 'Added category: ' . $name);
            $msg = 'Category added.';
        }
    } elseif ($action === 'delete') {
        $cid = (int)$_POST['category_id'];
        $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$cid]);
        $msg = 'Category deleted.';
    } elseif ($action === 'edit') {
        $cid  = (int)$_POST['category_id'];
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $pdo->prepare("UPDATE categories SET name=?,description=? WHERE id=?")->execute([$name,$desc,$cid]);
        $msg = 'Category updated.';
    }
    header('Location: /admin/categories.php?msg=' . urlencode($msg));
    exit;
}
if (isset($_GET['msg'])) $msg = $_GET['msg'];

$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();

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
            <li><a href="/admin/categories.php" class="active">Categories</a></li>
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
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Manage Categories</h1>
        <?php if ($msg): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Add New Category</h2></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">All Categories</h2></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Name</th><th>Products</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600; font-size:14px;"><?= sanitize($c['name']) ?></div>
                                        <?php if ($c['description']): ?>
                                            <div style="font-size:12px; color:var(--text-muted);"><?= sanitize($c['description']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $c['product_count'] ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm confirm-action" data-confirm="Delete category '<?= sanitize($c['name']) ?>'?">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
