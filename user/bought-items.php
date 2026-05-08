<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();
require_not_suspended();
$user = get_user();
$page_title = 'Bought Items';

$stmt = $pdo->prepare("SELECT o.*, u.username as seller_name FROM orders o JOIN users u ON o.seller_id = u.id WHERE o.buyer_id = ? AND o.order_status = 'sold' ORDER BY o.created_at DESC");
$stmt->execute([$user['id']]);
$bought = $stmt->fetchAll();

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
            <li><a href="/user/bought-items.php" class="active">Bought Items</a></li>
            <li><a href="/user/apply-seller.php">Apply to Sell</a></li>
            <li><a href="/user/feedback.php">Feedback Given</a></li>
            <li><a href="/user/profile.php">Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Bought Items</h1>

        <?php if (empty($bought)): ?>
            <div class="empty-state">
                <h3>No items bought yet</h3>
                <p>Items you have successfully purchased will appear here.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Seller</th><th>Amount</th><th>Delivery Location</th><th>Date</th><th>Feedback</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bought as $b): ?>
                                <?php
                                $fb_check = $pdo->prepare("SELECT id FROM feedback WHERE order_id = ? AND buyer_id = ?");
                                $fb_check->execute([$b['id'], $user['id']]);
                                $has_feedback = $fb_check->fetch();
                                ?>
                                <tr>
                                    <td style="font-family:var(--font-mono); font-size:13px;">#<?= $b['id'] ?></td>
                                    <td style="font-weight:600;"><?= sanitize($b['seller_name']) ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($b['total_amount']) ?></td>
                                    <td style="font-size:13px;"><?= sanitize($b['delivery_location']) ?></td>
                                    <td style="font-size:13px; color:var(--text-muted);"><?= time_ago($b['created_at']) ?></td>
                                    <td>
                                        <?php if ($has_feedback): ?>
                                            <span class="badge badge-success">Submitted</span>
                                        <?php else: ?>
                                            <a href="/user/feedback.php?order=<?= $b['id'] ?>" class="btn btn-primary btn-sm">Leave Feedback</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
