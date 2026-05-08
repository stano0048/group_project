<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('moderator');
$user = get_user();
$page_title = 'View Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid    = (int)$_POST['user_id'];
    $action = $_POST['action'] ?? '';
    $target = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $target->execute([$tid]);
    $target = $target->fetch();
    if ($target && !in_array($target['role'], ['admin','moderator'])) {
        if ($action === 'promote_seller') {
            $pdo->prepare("UPDATE users SET role='seller' WHERE id=?")->execute([$tid]);
            log_activity($pdo, $user['id'], 'Moderator promoted ' . $target['username'] . ' to seller');
        } elseif ($action === 'suspend') {
            $pdo->prepare("UPDATE users SET status='suspended' WHERE id=?")->execute([$tid]);
            log_activity($pdo, $user['id'], 'Moderator suspended ' . $target['username']);
        } elseif ($action === 'activate') {
            $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$tid]);
        }
    }
    header('Location: /moderator/users.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

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
            <li><a href="/moderator/users.php" class="active">View Users</a></li>
            <li><a href="/admin/seller-applications.php">Seller Applications</a></li>
            <li><a href="/moderator/products.php">View Products</a></li>
            <li><a href="/moderator/reports.php">Reported Products</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">View Users</h1>
        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>User</th><th>Admission</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600; font-size:13px;"><?= sanitize($u['username']) ?></div>
                                    <div style="font-size:11px; color:var(--text-muted);"><?= sanitize($u['email']) ?></div>
                                </td>
                                <td style="font-size:12px; font-family:var(--font-mono);"><?= sanitize($u['admission_number']) ?></td>
                                <td><span class="nav-role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                <td><span class="badge <?= $u['status']==='active'?'badge-success':'badge-danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                                <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($u['created_at']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'user'): ?>
                                        <div style="display:flex; gap:4px;">
                                            <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="promote_seller"><button type="submit" class="btn btn-success btn-sm">Make Seller</button></form>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="suspend"><button type="submit" class="btn btn-warning btn-sm">Suspend</button></form>
                                            <?php else: ?>
                                                <form method="POST"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="activate"><button type="submit" class="btn btn-outline btn-sm">Activate</button></form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--text-muted);">No actions</span>
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
<?php require_once '../includes/footer.php'; ?>
