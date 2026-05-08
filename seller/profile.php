<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('seller');
require_not_suspended();
$user = get_user();
$page_title = 'Seller Profile';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $whatsapp = trim($_POST['whatsapp_number'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$full_name || !$email) {
        $error = 'Full name and email are required.';
    } else {
        $pdo->prepare("UPDATE users SET full_name=?, phone=?, whatsapp_number=?, email=? WHERE id=?")->execute([$full_name, $phone, $whatsapp, $email, $user['id']]);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $_SESSION['user'] = $stmt->fetch();
        $user = get_user();
        $success = 'Profile updated successfully.';
    }

    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'Passwords do not match.';
        } else {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $user['id']]);
            $success = 'Password updated successfully.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-seller" style="margin-top:4px; display:inline-block;">Seller</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/seller/dashboard.php">Dashboard</a></li>
            <li><a href="/seller/post-product.php">Post Product</a></li>
            <li><a href="/seller/my-products.php">My Products</a></li>
            <li><a href="/seller/orders.php">Orders Received</a></li>
            <li><a href="/seller/sold-items.php">Sold Items</a></li>
            <li><a href="/seller/feedback.php">Feedback</a></li>
            <li><a href="/seller/profile.php" class="active">Seller Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Seller Profile</h1>

        <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?= sanitize($user['username']) ?>" disabled>
                            <div class="form-hint">Username cannot be changed.</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admission Number</label>
                            <input type="text" class="form-control" value="<?= sanitize($user['admission_number']) ?>" disabled>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" class="form-control" value="<?= sanitize($user['whatsapp_number']) ?>">
                        </div>
                    </div>
                    <hr style="margin:20px 0; border:none; border-top:1px solid var(--border);">
                    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px;">Change Password</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
