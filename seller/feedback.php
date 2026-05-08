<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('seller');
require_not_suspended();
$user = get_user();
$page_title = 'Feedback';

$stmt = $pdo->prepare("SELECT f.*, u.username as buyer_name FROM feedback f JOIN users u ON f.buyer_id = u.id WHERE f.seller_id = ? ORDER BY f.created_at DESC");
$stmt->execute([$user['id']]);
$feedback = $stmt->fetchAll();

$pos = count(array_filter($feedback, fn($f) => $f['feedback_type'] === 'positive'));
$neg = count(array_filter($feedback, fn($f) => $f['feedback_type'] === 'negative'));
$neu = count(array_filter($feedback, fn($f) => $f['feedback_type'] === 'neutral'));

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
            <li><a href="/seller/feedback.php" class="active">Feedback</a></li>
            <li><a href="/seller/profile.php">Seller Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Feedback Received</h1>

        <div class="stats-grid" style="margin-bottom:24px;">
            <div class="stat-card"><div class="stat-label">Positive</div><div class="stat-value success"><?= $pos ?></div></div>
            <div class="stat-card"><div class="stat-label">Neutral</div><div class="stat-value warning"><?= $neu ?></div></div>
            <div class="stat-card"><div class="stat-label">Negative</div><div class="stat-value danger"><?= $neg ?></div></div>
        </div>

        <?php if (empty($feedback)): ?>
            <div class="empty-state"><h3>No feedback yet</h3><p>Feedback from buyers after successful sales will appear here.</p></div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ($feedback as $fb): ?>
                    <div class="card card-body">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                            <span class="badge <?= $fb['feedback_type'] === 'positive' ? 'badge-success' : ($fb['feedback_type'] === 'negative' ? 'badge-danger' : 'badge-warning') ?>"><?= ucfirst($fb['feedback_type']) ?></span>
                            <span style="font-weight:600; font-size:14px;"><?= sanitize($fb['buyer_name']) ?></span>
                            <span style="font-size:12px; color:var(--text-muted);"><?= time_ago($fb['created_at']) ?></span>
                        </div>
                        <?php if ($fb['comment']): ?>
                            <p style="font-size:14px; color:var(--text-muted);"><?= sanitize($fb['comment']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
