<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('seller');
require_not_suspended();
$user = get_user();
$page_title = 'Orders Received';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'];
    $allowed = ['accepted','rejected','delivering','sold','cancelled'];
    if (in_array($new_status, $allowed)) {
        $check = $pdo->prepare("SELECT id, buyer_id FROM orders WHERE id = ? AND seller_id = ?");
        $check->execute([$order_id, $user['id']]);
        $ord = $check->fetch();
        if ($ord) {
            $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$new_status, $order_id]);
            if ($new_status === 'sold') {
                $items = $pdo->prepare("SELECT product_id FROM order_items WHERE order_id = ?");
                $items->execute([$order_id]);
                foreach ($items->fetchAll() as $item) {
                    $pdo->prepare("UPDATE products SET product_status = 'sold' WHERE id = ?")->execute([$item['product_id']]);
                }
            }
            $notif_map = [
                'accepted' => ['Order Accepted', 'Your order #' . $order_id . ' has been accepted by the seller.'],
                'rejected' => ['Order Rejected', 'Your order #' . $order_id . ' was rejected by the seller.'],
                'delivering' => ['Order Out for Delivery', 'Your order #' . $order_id . ' is now out for delivery.'],
                'sold' => ['Order Completed', 'Your order #' . $order_id . ' has been marked as sold. Please leave feedback.'],
                'cancelled' => ['Order Cancelled', 'Your order #' . $order_id . ' has been cancelled.'],
            ];
            if (isset($notif_map[$new_status])) {
                send_notification($pdo, $ord['buyer_id'], $notif_map[$new_status][0], $notif_map[$new_status][1]);
            }
            log_activity($pdo, $user['id'], 'Updated order #' . $order_id . ' to ' . $new_status);
        }
    }
    header('Location: /seller/orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, u.username as buyer_name, u.phone as buyer_phone FROM orders o JOIN users u ON o.buyer_id = u.id WHERE o.seller_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

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
            <li><a href="/seller/orders.php" class="active">Orders Received</a></li>
            <li><a href="/seller/sold-items.php">Sold Items</a></li>
            <li><a href="/seller/feedback.php">Feedback</a></li>
            <li><a href="/seller/profile.php">Seller Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Orders Received</h1>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <h3>No orders yet</h3>
                <p>Orders placed on your products will appear here.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Buyer</th><th>Phone</th><th>Delivery Location</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td style="font-family:var(--font-mono); font-size:13px;">#<?= $o['id'] ?></td>
                                    <td style="font-weight:600; font-size:14px;"><?= sanitize($o['buyer_name']) ?></td>
                                    <td style="font-size:13px;"><?= sanitize($o['buyer_phone']) ?></td>
                                    <td style="font-size:13px; max-width:180px;"><?= sanitize($o['delivery_location']) ?>
                                        <?php if ($o['delivery_instructions']): ?>
                                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px;"><?= sanitize($o['delivery_instructions']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($o['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $badge = match($o['order_status']) {
                                            'pending' => 'badge-warning',
                                            'accepted' => 'badge-info',
                                            'delivering' => 'badge-info',
                                            'sold' => 'badge-success',
                                            'rejected','cancelled' => 'badge-danger',
                                            default => 'badge-muted'
                                        };
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= ucfirst($o['order_status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (in_array($o['order_status'], ['pending'])): ?>
                                            <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                                <form method="POST">
                                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                    <input type="hidden" name="new_status" value="accepted">
                                                    <button type="submit" name="update_status" class="btn btn-success btn-sm">Accept</button>
                                                </form>
                                                <form method="POST">
                                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                    <input type="hidden" name="new_status" value="rejected">
                                                    <button type="submit" name="update_status" class="btn btn-danger btn-sm confirm-action" data-confirm="Reject this order?">Reject</button>
                                                </form>
                                            </div>
                                        <?php elseif ($o['order_status'] === 'accepted'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <input type="hidden" name="new_status" value="delivering">
                                                <button type="submit" name="update_status" class="btn btn-primary btn-sm">Mark Delivering</button>
                                            </form>
                                        <?php elseif ($o['order_status'] === 'delivering'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <input type="hidden" name="new_status" value="sold">
                                                <button type="submit" name="update_status" class="btn btn-success btn-sm confirm-action" data-confirm="Mark this order as sold?">Mark Sold</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size:12px; color:var(--text-muted);">No actions</span>
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
