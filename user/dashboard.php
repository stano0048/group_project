<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role(['user','seller','moderator','admin']);
require_not_suspended();
$user = get_user();
$page_title = 'My Dashboard';
$uid = $user['id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ?");
$stmt->execute([$uid]);
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND order_status = 'pending'");
$stmt->execute([$uid]);
$pending = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND order_status = 'sold'");
$stmt->execute([$uid]);
$bought = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND order_status = 'cancelled'");
$stmt->execute([$uid]);
$cancelled = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE buyer_id = ? AND order_status = 'sold'");
$stmt->execute([$uid]);
$total_spent = $stmt->fetchColumn() ?? 0;

$recent_orders = $pdo->prepare("SELECT o.*, u.username as seller_name FROM orders o JOIN users u ON o.seller_id = u.id WHERE o.buyer_id = ? ORDER BY o.created_at DESC LIMIT 5");
$recent_orders->execute([$uid]);
$recent_orders = $recent_orders->fetchAll();

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$uid]);
$notifs = $notifs->fetchAll();

$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);

require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-user" style="margin-top:4px; display:inline-block;">Buyer</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/user/dashboard.php" class="active">Dashboard</a></li>
            <li><a href="/cart.php">My Cart</a></li>
            <li><a href="/user/my-orders.php">My Orders</a></li>
            <li><a href="/user/bought-items.php">Bought Items</a></li>
            <li><a href="/user/apply-seller.php">Apply to Sell</a></li>
            <li><a href="/user/feedback.php">Feedback Given</a></li>
            <li><a href="/user/profile.php">Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">My Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Total Orders</div><div class="stat-value"><?= $total_orders ?></div></div>
            <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value warning"><?= $pending ?></div></div>
            <div class="stat-card"><div class="stat-label">Items Bought</div><div class="stat-value success"><?= $bought ?></div></div>
            <div class="stat-card"><div class="stat-label">Cancelled</div><div class="stat-value danger"><?= $cancelled ?></div></div>
            <div class="stat-card"><div class="stat-label">Total Spent</div><div class="stat-value primary" style="font-size:20px;"><?= format_price($total_spent) ?></div></div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Orders</h2>
                    <a href="/user/my-orders.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <?php if (empty($recent_orders)): ?>
                    <div class="empty-state" style="padding:40px;"><p>No orders yet. <a href="/products.php">Browse products</a></p></div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Order</th><th>Seller</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($recent_orders as $o): ?>
                                    <tr>
                                        <td style="font-family:var(--font-mono); font-size:12px;">#<?= $o['id'] ?></td>
                                        <td style="font-size:13px;"><?= sanitize($o['seller_name']) ?></td>
                                        <td style="font-weight:700; font-size:13px; font-family:var(--font-mono);"><?= format_price($o['total_amount']) ?></td>
                                        <td><span class="badge <?= in_array($o['order_status'],['pending']) ? 'badge-warning' : ($o['order_status'] === 'sold' ? 'badge-success' : 'badge-info') ?>"><?= ucfirst($o['order_status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header"><h2 class="card-title">Notifications</h2></div>
                <?php if (empty($notifs)): ?>
                    <div class="empty-state" style="padding:40px;"><p>No notifications yet.</p></div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column;">
                        <?php foreach ($notifs as $n): ?>
                            <div style="padding:14px 20px; border-bottom:1px solid var(--border);">
                                <div style="font-size:13px; font-weight:600;"><?= sanitize($n['title']) ?></div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;"><?= sanitize($n['message']) ?></div>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:4px;"><?= time_ago($n['created_at']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
