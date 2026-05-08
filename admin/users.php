<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');
$user = get_user();
$page_title = 'Manage Users';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $target = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $target->execute([$target_id]);
    $target = $target->fetch();

    if ($target && $target['role'] !== 'admin') {
        switch ($action) {
            case 'suspend':
                $pdo->prepare("UPDATE users SET status='suspended' WHERE id=?")->execute([$target_id]);
                log_activity($pdo, $user['id'], 'Suspended user: ' . $target['username']);
                $msg = 'User suspended.';
                break;
            case 'activate':
                $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$target_id]);
                log_activity($pdo, $user['id'], 'Activated user: ' . $target['username']);
                $msg = 'User activated.';
                break;
            case 'promote_moderator':
                $pdo->prepare("UPDATE users SET role='moderator' WHERE id=?")->execute([$target_id]);
                log_activity($pdo, $user['id'], 'Promoted ' . $target['username'] . ' to moderator');
                $msg = 'User promoted to moderator.';
                break;
            case 'promote_seller':
                $pdo->prepare("UPDATE users SET role='seller' WHERE id=?")->execute([$target_id]);
                log_activity($pdo, $user['id'], 'Promoted ' . $target['username'] . ' to seller');
                $msg = 'User promoted to seller.';
                break;
            case 'demote_user':
                $pdo->prepare("UPDATE users SET role='user' WHERE id=?")->execute([$target_id]);
                log_activity($pdo, $user['id'], 'Demoted ' . $target['username'] . ' to user');
                $msg = 'User demoted to normal user.';
                break;
            case 'delete':
                $pdo->prepare("DELETE FROM users WHERE id=? AND role != 'admin'")->execute([$target_id]);
                log_activity($pdo, $user['id'], 'Deleted user: ' . $target['username']);
                $msg = 'User deleted.';
                break;
        }
    } elseif ($action === 'add_user') {
        $uname = trim($_POST['new_username'] ?? '');
        $fname = trim($_POST['new_full_name'] ?? '');
        $adm   = trim($_POST['new_admission'] ?? '');
        $email = trim($_POST['new_email'] ?? '');
        $pass  = $_POST['new_password'] ?? 'password123';
        $role  = $_POST['new_role'] ?? 'user';

        if ($uname && $fname && $adm && $email) {
            $check = $pdo->prepare("SELECT id FROM users WHERE email=? OR username=? OR admission_number=?");
            $check->execute([$email, $uname, $adm]);
            if ($check->fetch()) {
                $msg = 'User with this email, username or admission number already exists.';
            } else {
                $pdo->prepare("INSERT INTO users (username,full_name,admission_number,email,password,role) VALUES(?,?,?,?,?,?)")->execute([
                    $uname, $fname, $adm, $email, password_hash($pass, PASSWORD_DEFAULT), $role
                ]);
                log_activity($pdo, $user['id'], 'Admin added new user: ' . $uname);
                $msg = 'User added successfully.';
            }
        }
    }
    header('Location: /admin/users.php?msg=' . urlencode($msg));
    exit;
}

if (isset($_GET['msg'])) $msg = $_GET['msg'];

$search = trim($_GET['search'] ?? '');
$filter_role = $_GET['role'] ?? '';
$where = [];
$params = [];
if ($search) {
    $where[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ? OR admission_number LIKE ?)";
    $params = array_fill(0, 4, "%$search%");
}
if ($filter_role) {
    $where[] = "role = ?";
    $params[] = $filter_role;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $pdo->prepare("SELECT * FROM users $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

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
            <li><a href="/admin/dashboard.php">Dashboard</a></li>
        </ul>
        <div class="sidebar-section-title">Users</div>
        <ul class="sidebar-nav">
            <li><a href="/admin/users.php" class="active">Manage Users</a></li>
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
            <li><a href="/admin/settings.php">Settings</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <h1 style="font-size:22px; font-weight:800;">Manage Users</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addUserModal').style.display='flex'">Add User</button>
        </div>

        <?php if ($msg): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>

        <div class="search-bar" style="margin-bottom:20px;">
            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?= sanitize($search) ?>">
                <select name="role" class="form-control" style="max-width:160px;">
                    <option value="">All Roles</option>
                    <?php foreach (['admin','moderator','seller','user'] as $r): ?>
                        <option value="<?= $r ?>" <?= $filter_role === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="/admin/users.php" class="btn btn-outline">Clear</a>
            </form>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>User</th><th>Admission</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600; font-size:13px;"><?= sanitize($u['username']) ?></div>
                                    <div style="font-size:11px; color:var(--text-muted);"><?= sanitize($u['email']) ?></div>
                                    <div style="font-size:11px; color:var(--text-muted);"><?= sanitize($u['full_name']) ?></div>
                                </td>
                                <td style="font-size:12px; font-family:var(--font-mono);"><?= sanitize($u['admission_number']) ?></td>
                                <td style="font-size:13px;"><?= sanitize($u['phone']) ?></td>
                                <td><span class="nav-role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                <td><span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                                <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($u['created_at']) ?></td>
                                <td>
                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            <?php if ($u['status'] === 'active'): ?>
                                                <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="suspend"><button type="submit" class="btn btn-warning btn-sm confirm-action" data-confirm="Suspend this user?">Suspend</button></form>
                                            <?php else: ?>
                                                <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="activate"><button type="submit" class="btn btn-success btn-sm">Activate</button></form>
                                            <?php endif; ?>
                                            <?php if ($u['role'] === 'user'): ?>
                                                <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="promote_seller"><button type="submit" class="btn btn-outline btn-sm">Make Seller</button></form>
                                                <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="promote_moderator"><button type="submit" class="btn btn-outline btn-sm">Make Mod</button></form>
                                            <?php elseif (in_array($u['role'], ['seller','moderator'])): ?>
                                                <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="demote_user"><button type="submit" class="btn btn-outline btn-sm">Demote</button></form>
                                            <?php endif; ?>
                                            <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="delete"><button type="submit" class="btn btn-danger btn-sm confirm-action" data-confirm="Delete this user permanently?">Delete</button></form>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--text-muted);">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="addUserModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:560px; max-height:90vh; overflow-y:auto;">
        <div class="card-header">
            <h2 class="card-title">Add New User</h2>
            <button onclick="document.getElementById('addUserModal').style.display='none'" class="btn btn-outline btn-sm">Close</button>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="new_full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="new_username" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Admission Number</label>
                        <input type="text" name="new_admission" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="new_email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="new_role" class="form-control">
                            <option value="user">User</option>
                            <option value="seller">Seller</option>
                            <option value="moderator">Moderator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Password</label>
                        <input type="text" name="new_password" class="form-control" value="password123">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
