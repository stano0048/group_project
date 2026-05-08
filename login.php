<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Login';

if (is_logged_in()) {
    header('Location: ' . dashboard_url(get_user()['role']));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'suspended') {
                $error = 'Your account has been suspended. Please contact admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                log_activity($pdo, $user['id'], 'User logged in');
                $redirect = $_SESSION['redirect_after_login'] ?? dashboard_url($user['role']);
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Invalid email/username or password.';
        }
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'suspended') {
    $error = 'Your account has been suspended.';
}

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container-sm">
        <div class="card">
            <div class="card-header">
                <div>
                    <h1 style="font-size:22px; font-weight:800;">Login to KarU Marketplace</h1>
                    <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">Welcome back. Enter your credentials to continue.</p>
                </div>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= sanitize($error) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">Account created successfully. Please log in.</div>
                <?php endif; ?>

                <form method="POST" action="/login.php">
                    <div class="form-group">
                        <label class="form-label">Email or Username</label>
                        <input type="text" name="email" class="form-control" placeholder="Enter email or username" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>

                <div style="text-align:center; margin-top:20px; font-size:14px; color:var(--text-muted);">
                    Don't have an account? <a href="/register.php" style="font-weight:600;">Register here</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
