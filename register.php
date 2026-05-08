<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Register';

if (is_logged_in()) {
    header('Location: ' . dashboard_url(get_user()['role']));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $admission = trim($_POST['admission_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $whatsapp = trim($_POST['whatsapp_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$username || !$full_name || !$admission || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? OR admission_number = ?");
        $check->execute([$email, $username, $admission]);
        if ($check->fetch()) {
            $error = 'An account with this email, username, or admission number already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, full_name, admission_number, email, phone, whatsapp_number, password) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$username, $full_name, $admission, $email, $phone, $whatsapp, $hashed]);
            $new_id = $pdo->lastInsertId();
            log_activity($pdo, $new_id, 'New user registered');
            header('Location: /login.php?registered=1');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container-sm">
        <div class="card">
            <div class="card-header">
                <div>
                    <h1 style="font-size:22px; font-weight:800;">Create an Account</h1>
                    <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">Join KarU Marketplace as a student buyer or apply to become a seller.</p>
                </div>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= sanitize($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/register.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="Your full name" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="Choose a username" value="<?= sanitize($_POST['username'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Admission Number <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="admission_number" class="form-control" placeholder="e.g. SCT-1-0001-1/2022" value="<?= sanitize($_POST['admission_number'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address <span style="color:var(--danger);">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="Your email address" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="07XX XXX XXX" value="<?= sanitize($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" class="form-control" placeholder="07XX XXX XXX" value="<?= sanitize($_POST['whatsapp_number'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Password <span style="color:var(--danger);">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password <span style="color:var(--danger);">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                        </div>
                    </div>

                    <div class="notice-box" style="margin-bottom:20px;">
                        <strong>Student Registration</strong>
                        Use your Karatina University student details. You can apply to become a seller after registration.
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>

                <div style="text-align:center; margin-top:20px; font-size:14px; color:var(--text-muted);">
                    Already have an account? <a href="/login.php" style="font-weight:600;">Login here</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
