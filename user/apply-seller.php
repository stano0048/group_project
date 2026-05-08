<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();
require_not_suspended();
$user = get_user();
$page_title = 'Apply to Become Seller';

if ($user['role'] === 'seller') {
    header('Location: /seller/dashboard.php');
    exit;
}

$existing = $pdo->prepare("SELECT * FROM seller_applications WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$existing->execute([$user['id']]);
$application = $existing->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$application || $application['status'] === 'rejected')) {
    $full_name = trim($_POST['full_name'] ?? '');
    $admission = trim($_POST['admission_number'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $whatsapp = trim($_POST['whatsapp_number'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if (!$full_name || !$admission || !$phone) {
        $error = 'Please fill in all required fields.';
    } elseif (empty($_FILES['student_id_front']['tmp_name'])) {
        $error = 'Please upload your student ID front image.';
    } else {
        $upload_dir = '../assets/uploads/applications/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mime = mime_content_type($_FILES['student_id_front']['tmp_name']);
        if (!in_array($mime, $allowed)) {
            $error = 'Invalid file type. Only images are accepted.';
        } else {
            $ext = pathinfo($_FILES['student_id_front']['name'], PATHINFO_EXTENSION);
            $filename = 'id_' . $user['id'] . '_' . uniqid() . '.' . $ext;
            $dest = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['student_id_front']['tmp_name'], $dest)) {
                $pdo->prepare("INSERT INTO seller_applications (user_id, full_name, admission_number, phone, whatsapp_number, student_id_front, reason) VALUES (?,?,?,?,?,?,?)")->execute([
                    $user['id'], $full_name, $admission, $phone, $whatsapp,
                    'assets/uploads/applications/' . $filename, $reason
                ]);
                log_activity($pdo, $user['id'], 'Submitted seller application');
                header('Location: /user/apply-seller.php?submitted=1');
                exit;
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-user" style="margin-top:4px; display:inline-block;">Buyer</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/user/dashboard.php">Dashboard</a></li>
            <li><a href="/cart.php">My Cart</a></li>
            <li><a href="/user/my-orders.php">My Orders</a></li>
            <li><a href="/user/bought-items.php">Bought Items</a></li>
            <li><a href="/user/apply-seller.php" class="active">Apply to Sell</a></li>
            <li><a href="/user/feedback.php">Feedback Given</a></li>
            <li><a href="/user/profile.php">Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Apply to Become a Seller</h1>

        <?php if (isset($_GET['submitted'])): ?>
            <div class="alert alert-success">
                Your seller application has been submitted successfully. Please wait for admin or moderator approval. You will be notified once reviewed.
            </div>
        <?php endif; ?>

        <?php if ($application && $application['status'] === 'pending'): ?>
            <div class="card card-body" style="text-align:center; padding:48px;">
                <div style="font-size:48px; margin-bottom:16px;">...</div>
                <h2 style="font-size:20px; font-weight:800; margin-bottom:8px;">Application Under Review</h2>
                <p style="color:var(--text-muted);">Your seller application is currently being reviewed by our team. You will be notified once a decision is made.</p>
                <div style="margin-top:16px;">
                    <span class="badge badge-warning" style="font-size:13px; padding:6px 14px;">Pending Review</span>
                </div>
                <p style="font-size:13px; color:var(--text-muted); margin-top:12px;">Submitted <?= time_ago($application['created_at']) ?></p>
            </div>

        <?php elseif ($application && $application['status'] === 'approved'): ?>
            <div class="alert alert-success">Your application was approved. You are now a seller.</div>

        <?php elseif ($application && $application['status'] === 'more_details'): ?>
            <div class="alert alert-warning">The admin has requested more details about your application. Please resubmit with additional information.</div>

        <?php else: ?>
            <?php if ($application && $application['status'] === 'rejected'): ?>
                <div class="alert alert-danger">Your previous application was rejected. You may submit a new application.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Seller Application Form</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" style="margin-bottom:24px;">
                        Becoming a seller allows you to post products and sell items to fellow students. Your application will be reviewed by an admin or moderator before approval.
                    </div>

                    <form method="POST" action="/user/apply-seller.php" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                                <input type="text" name="full_name" class="form-control" placeholder="Your full name as on student ID" value="<?= sanitize($_POST['full_name'] ?? $user['full_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Admission Number <span style="color:var(--danger);">*</span></label>
                                <input type="text" name="admission_number" class="form-control" placeholder="e.g. SCT-1-0001-1/2022" value="<?= sanitize($_POST['admission_number'] ?? $user['admission_number']) ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Phone Number <span style="color:var(--danger);">*</span></label>
                                <input type="text" name="phone" class="form-control" placeholder="07XX XXX XXX" value="<?= sanitize($_POST['phone'] ?? $user['phone']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" name="whatsapp_number" class="form-control" placeholder="07XX XXX XXX" value="<?= sanitize($_POST['whatsapp_number'] ?? $user['whatsapp_number']) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Student ID Front Image <span style="color:var(--danger);">*</span></label>
                            <div class="upload-zone" onclick="document.getElementById('id_upload').click();">
                                <input type="file" name="student_id_front" id="id_upload" class="upload-input" data-preview="id_preview" accept="image/*" required>
                                <div style="font-size:28px; color:var(--text-muted);">+</div>
                                <p>Click to upload your student ID front image</p>
                                <p style="font-size:12px;">Accepted: JPG, PNG, GIF, WebP</p>
                            </div>
                            <div class="upload-preview" id="id_preview"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reason for Becoming a Seller</label>
                            <textarea name="reason" class="form-control" rows="4" placeholder="Tell us why you want to become a seller on KarU Marketplace. What items do you plan to sell?"><?= sanitize($_POST['reason'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
