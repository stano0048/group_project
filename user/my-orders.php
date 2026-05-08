<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();
require_not_suspended();
$user = get_user();
$page_title = 'My Orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $oid = (int)$_POST['cancel_order'];
    $check = $pdo->prepare("SELECT id, order_status FROM orders WHERE id = ? AND buyer_id = ?");
    $check->execute([$oid, $user['id']]);
    $ord = $check->fetch();
    if ($ord && in_array($ord['order_status'], ['pending','accepted'])) {
        $pdo->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?")->execute([$oid]);
        log_activity($pdo, $user['id'], 'Cancelled order #' . $oid);
    }
    header('Location: /user/my-orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, u.username as seller_name FROM orders o JOIN users u ON o.seller_id = u.id WHERE o.buyer_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

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
            <li><a href="/user/my-orders.php" class="active">My Orders</a></li>
            <li><a href="/user/bought-items.php">Bought Items</a></li>
            <li><a href="/user/apply-seller.php">Apply to Sell</a></li>
            <li><a href="/user/feedback.php">Feedback Given</a></li>
            <li><a href="/user/profile.php">Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">My Orders</h1>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <h3>No orders yet</h3>
                <p>Browse products and place your first order.</p>
                <a href="/products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Seller</th><th>Amount</th><th>Delivery Location</th><th>Status</th><th>Payment</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td style="font-family:var(--font-mono); font-size:13px;">#<?= $o['id'] ?></td>
                                    <td style="font-weight:600; font-size:14px;"><?= sanitize($o['seller_name']) ?></td>
                                    <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($o['total_amount']) ?></td>
                                    <td style="font-size:13px; max-width:160px;"><?= sanitize($o['delivery_location']) ?></td>
                                    <td>
                                        <?php $badge = match($o['order_status']) {
                                            'pending' => 'badge-warning',
                                            'accepted','delivering' => 'badge-info',
                                            'sold' => 'badge-success',
                                            'rejected','cancelled' => 'badge-danger',
                                            default => 'badge-muted'
                                        }; ?>
                                        <span class="badge <?= $badge ?>"><?= ucfirst($o['order_status']) ?></span>
                                    </td>
                                    <td><span class="badge badge-warning"><?= ucfirst(str_replace('_',' ',$o['payment_status'])) ?></span></td>
                                    <td style="font-size:12px; color:var(--text-muted);"><?= time_ago($o['created_at']) ?></td>
                                    <td>
                                        <?php if (in_array($o['order_status'], ['pending','accepted'])): ?>
                                            <form method="POST">
                                                <input type="hidden" name="cancel_order" value="<?= $o['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm confirm-action" data-confirm="Cancel this order?">Cancel</button>
                                            </form>
                                        <?php elseif ($o['order_status'] === 'sold'): ?>
                                            <a href="/user/feedback.php?order=<?= $o['id'] ?>" class="btn btn-outline btn-sm">Leave Feedback</a>
                                        <?php else: ?>
                                            <span style="font-size:12px; color:var(--text-muted);">-</span>
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
