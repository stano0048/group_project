<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();
require_not_suspended();
$user = get_user();
$page_title = 'Feedback';

$order_id = (int)($_GET['order'] ?? 0);
$error = '';
$success = '';
$order = null;

if ($order_id) {
    $stmt = $pdo->prepare("SELECT o.*, u.username as seller_name FROM orders o JOIN users u ON o.seller_id = u.id WHERE o.id = ? AND o.buyer_id = ? AND o.order_status = 'sold'");
    $stmt->execute([$order_id, $user['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        $error = 'Order not found or not eligible for feedback.';
    } else {
        $already = $pdo->prepare("SELECT id FROM feedback WHERE order_id = ? AND buyer_id = ?");
        $already->execute([$order_id, $user['id']]);
        if ($already->fetch()) {
            $error = 'You have already submitted feedback for this order.';
            $order = null;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $oid = (int)$_POST['order_id'];
    $type = $_POST['feedback_type'] ?? '';
    $comment = trim($_POST['comment'] ?? '');

    $stmt = $pdo->prepare("SELECT o.seller_id FROM orders o WHERE o.id = ? AND o.buyer_id = ? AND o.order_status = 'sold'");
    $stmt->execute([$oid, $user['id']]);
    $ord = $stmt->fetch();

    if (!$ord || !in_array($type, ['positive','neutral','negative'])) {
        $error = 'Invalid feedback submission.';
    } else {
        $already = $pdo->prepare("SELECT id FROM feedback WHERE order_id = ? AND buyer_id = ?");
        $already->execute([$oid, $user['id']]);
        if ($already->fetch()) {
            $error = 'You have already submitted feedback for this order.';
        } else {
            $pdo->prepare("INSERT INTO feedback (order_id, buyer_id, seller_id, feedback_type, comment) VALUES (?,?,?,?,?)")->execute([
                $oid, $user['id'], $ord['seller_id'], $type, $comment
            ]);
            send_notification($pdo, $ord['seller_id'], 'New Feedback Received', 'A buyer has left ' . $type . ' feedback on order #' . $oid);
            log_activity($pdo, $user['id'], 'Left ' . $type . ' feedback for order #' . $oid);
            $success = 'Thank you for your feedback!';
            $order = null;
        }
    }
}

$all_feedback = $pdo->prepare("SELECT f.*, u.username as seller_name FROM feedback f JOIN users u ON f.seller_id = u.id WHERE f.buyer_id = ? ORDER BY f.created_at DESC");
$all_feedback->execute([$user['id']]);
$all_feedback = $all_feedback->fetchAll();

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
            <li><a href="/user/apply-seller.php">Apply to Sell</a></li>
            <li><a href="/user/feedback.php" class="active">Feedback Given</a></li>
            <li><a href="/user/profile.php">Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Feedback</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <div class="card" style="margin-bottom:32px;">
                <div class="card-header">
                    <h2 class="card-title">Leave Feedback for Order #<?= $order['id'] ?></h2>
                </div>
                <div class="card-body">
                    <div style="margin-bottom:16px; padding:14px; background:var(--bg-section); border-radius:var(--radius); font-size:14px;">
                        <strong>Seller:</strong> <?= sanitize($order['seller_name']) ?> &nbsp;&nbsp;
                        <strong>Amount:</strong> <?= format_price($order['total_amount']) ?>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <div class="form-group">
                            <label class="form-label">Feedback Type <span style="color:var(--danger);">*</span></label>
                            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                                <?php foreach (['positive','neutral','negative'] as $type): ?>
                                    <label class="form-check" style="padding:10px 16px; border:2px solid var(--border); border-radius:var(--radius); cursor:pointer;">
                                        <input type="radio" name="feedback_type" value="<?= $type ?>" required>
                                        <span style="font-weight:600; text-transform:capitalize;"><?= $type ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Comment (optional)</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this seller. Was the item as described? Was delivery smooth?"><?= sanitize($_POST['comment'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h2 class="card-title">Feedback History</h2></div>
            <?php if (empty($all_feedback)): ?>
                <div class="empty-state" style="padding:40px;"><p>You haven't submitted any feedback yet.</p></div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column;">
                    <?php foreach ($all_feedback as $fb): ?>
                        <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                                <span class="badge <?= $fb['feedback_type'] === 'positive' ? 'badge-success' : ($fb['feedback_type'] === 'negative' ? 'badge-danger' : 'badge-warning') ?>"><?= ucfirst($fb['feedback_type']) ?></span>
                                <span style="font-size:13px; font-weight:600;">To: <?= sanitize($fb['seller_name']) ?></span>
                                <span style="font-size:12px; color:var(--text-muted);"><?= time_ago($fb['created_at']) ?></span>
                            </div>
                            <?php if ($fb['comment']): ?>
                                <p style="font-size:13px; color:var(--text-muted);"><?= sanitize($fb['comment']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
