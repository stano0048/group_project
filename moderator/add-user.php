<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('moderator');
$user = get_user();
$page_title = 'Add User';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uname  = trim($_POST['username'] ?? '');
    $fname  = trim($_POST['full_name'] ?? '');
    $adm    = trim($_POST['admission_number'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $pass   = $_POST['password'] ?? 'password123';
    $role   = in_array($_POST['role'] ?? '', ['user','seller']) ? $_POST['role'] : 'user';

    if (!$uname || !$fname || !$adm || !$email) {
        $error = 'Please fill in all required fields.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email=? OR username=? OR admission_number=?");
        $check->execute([$email,$uname,$adm]);
        if ($check->fetch()) {
            $error = 'A user with this email, username or admission number already exists.';
        } else {
            $pdo->prepare("INSERT INTO users (username,full_name,admission_number,email,phone,password,role) VALUES(?,?,?,?,?,?,?)")->execute([
                $uname,$fname,$adm,$email,$phone,password_hash($pass,PASSWORD_DEFAULT),$role
            ]);
            log_activity($pdo, $user['id'], 'Moderator added user: ' . $uname);
            $success = 'User ' . $uname . ' added successfully with password: ' . $pass;
        }
    }
}

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
            <li><a href="/moderator/add-user.php" class="active">Add User</a></li>
            <li><a href="/moderator/users.php">View Users</a></li>
            <li><a href="/admin/seller-applications.php">Seller Applications</a></li>
            <li><a href="/moderator/products.php">View Products</a></li>
            <li><a href="/moderator/reports.php">Reported Products</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Add New User</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>
        <div class="card" style="max-width:640px;">
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Admission Number <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="admission_number" class="form-control" value="<?= sanitize($_POST['admission_number'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span style="color:var(--danger);">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control">
                                <option value="user">Normal User</option>
                                <option value="seller">Seller</option>
                            </select>
                            <div class="form-hint">Moderators cannot assign admin or moderator roles.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Password</label>
                        <input type="text" name="password" class="form-control" value="password123">
                        <div class="form-hint">The user should change this password after first login.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
