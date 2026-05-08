<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('seller');
require_not_suspended();
$user = get_user();
$page_title = 'Seller Dashboard';

$sid = $user['id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$stmt->execute([$sid]);
$total_products = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND product_status = 'on_sale'");
$stmt->execute([$sid]);
$on_sale = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND product_status = 'sold'");
$stmt->execute([$sid]);
$total_sold = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE seller_id = ? AND order_status = 'pending'");
$stmt->execute([$sid]);
$pending_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(oi.subtotal) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.seller_id = ? AND o.order_status = 'sold'");
$stmt->execute([$sid]);
$total_earned = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM feedback WHERE seller_id = ?");
$stmt->execute([$sid]);
$total_feedback = $stmt->fetchColumn();

$recent_orders = $pdo->prepare("SELECT o.*, u.username as buyer_name, u.phone as buyer_phone FROM orders o JOIN users u ON o.buyer_id = u.id WHERE o.seller_id = ? ORDER BY o.created_at DESC LIMIT 5");
$recent_orders->execute([$sid]);
$recent_orders = $recent_orders->fetchAll();

$current_page = 'dashboard';
require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700; color:var(--text);"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-seller" style="margin-top:4px; display:inline-block;">Seller</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/seller/dashboard.php" class="active">Dashboard</a></li>
            <li><a href="/seller/post-product.php">Post Product</a></li>
            <li><a href="/seller/my-products.php">My Products</a></li>
            <li><a href="/seller/orders.php">Orders Received</a></li>
            <li><a href="/seller/sold-items.php">Sold Items</a></li>
            <li><a href="/seller/feedback.php">Feedback</a></li>
            <li><a href="/seller/profile.php">Seller Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Seller Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?= $total_products ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">On Sale</div>
                <div class="stat-value primary"><?= $on_sale ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Products Sold</div>
                <div class="stat-value success"><?= $total_sold ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value warning"><?= $pending_orders ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Earned</div>
                <div class="stat-value success" style="font-size:22px;"><?= format_price($total_earned) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Feedback Received</div>
                <div class="stat-value"><?= $total_feedback ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Orders</h2>
                <a href="/seller/orders.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <?php if (empty($recent_orders)): ?>
                <div class="empty-state" style="padding:40px;">
                    <p>No orders received yet.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Buyer</th><th>Amount</th><th>Location</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $o): ?>
                                <tr>
                                    <td style="font-family:var(--font-mono); font-size:13px;">#<?= $o['id'] ?></td>
                                    <td><?= sanitize($o['buyer_name']) ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($o['total_amount']) ?></td>
                                    <td style="font-size:13px;"><?= sanitize($o['delivery_location']) ?></td>
                                    <td><span class="badge <?= $o['order_status'] === 'pending' ? 'badge-warning' : ($o['order_status'] === 'sold' ? 'badge-success' : 'badge-info') ?>"><?= ucfirst($o['order_status']) ?></span></td>
                                    <td style="font-size:13px; color:var(--text-muted);"><?= time_ago($o['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
