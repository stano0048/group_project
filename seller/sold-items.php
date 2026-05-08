<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('seller');
require_not_suspended();
$user = get_user();
$page_title = 'Sold Items';

$stmt = $pdo->prepare("SELECT o.*, u.username as buyer_name, oi.price FROM orders o JOIN users u ON o.buyer_id = u.id JOIN order_items oi ON oi.order_id = o.id WHERE o.seller_id = ? AND o.order_status = 'sold' ORDER BY o.created_at DESC");
$stmt->execute([$user['id']]);
$sold = $stmt->fetchAll();

$total_earned = array_sum(array_column($sold, 'total_amount'));

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
            <li><a href="/seller/sold-items.php" class="active">Sold Items</a></li>
            <li><a href="/seller/feedback.php">Feedback</a></li>
            <li><a href="/seller/profile.php">Seller Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Sold Items</h1>

        <div class="stats-grid" style="margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-label">Total Sold</div>
                <div class="stat-value success"><?= count($sold) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Earned</div>
                <div class="stat-value success" style="font-size:22px;"><?= format_price($total_earned) ?></div>
            </div>
        </div>

        <?php if (empty($sold)): ?>
            <div class="empty-state">
                <h3>No sold items yet</h3>
                <p>Items you have sold will appear here.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Buyer</th><th>Amount</th><th>Delivery Location</th><th>Date Sold</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sold as $s): ?>
                                <tr>
                                    <td style="font-family:var(--font-mono); font-size:13px;">#<?= $s['id'] ?></td>
                                    <td style="font-weight:600;"><?= sanitize($s['buyer_name']) ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($s['total_amount']) ?></td>
                                    <td style="font-size:13px;"><?= sanitize($s['delivery_location']) ?></td>
                                    <td style="font-size:13px; color:var(--text-muted);"><?= time_ago($s['created_at']) ?></td>
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
