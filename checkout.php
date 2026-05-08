<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
require_not_suspended();
$user = get_user();
$page_title = 'Checkout';

$stmt = $pdo->prepare("SELECT c.*, p.item_name, p.selling_price, p.is_negotiable, p.product_status, u.id as seller_id, u.username as seller_name, (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) as image FROM cart c JOIN products p ON c.product_id = p.id JOIN users u ON p.seller_id = u.id WHERE c.user_id = ? AND p.product_status = 'on_sale'");
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: /cart.php');
    exit;
}

$seller_id = $items[0]['seller_id'];
$all_same_seller = count(array_unique(array_column($items, 'seller_id'))) === 1;

$total = array_sum(array_map(fn($i) => $i['offer_price'] ?? $i['selling_price'], $items));
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery = trim($_POST['delivery_location'] ?? '');
    $phone = trim($_POST['buyer_phone'] ?? '');
    $instructions = trim($_POST['delivery_instructions'] ?? '');
    $delivery_time = trim($_POST['preferred_delivery_time'] ?? '');

    if (!$delivery || !$phone) {
        $error = 'Please fill in delivery location and phone number.';
    } else {
        $pdo->beginTransaction();
        try {
            $sellers = [];
            foreach ($items as $item) {
                $sellers[$item['seller_id']][] = $item;
            }

            foreach ($sellers as $sid => $sitems) {
                $stotal = array_sum(array_map(fn($i) => $i['offer_price'] ?? $i['selling_price'], $sitems));

                $pdo->prepare("INSERT INTO orders (buyer_id, seller_id, total_amount, delivery_location, buyer_phone, delivery_instructions, preferred_delivery_time) VALUES (?,?,?,?,?,?,?)")->execute([
                    $user['id'], $sid, $stotal, $delivery, $phone, $instructions, $delivery_time
                ]);
                $order_id = $pdo->lastInsertId();

                foreach ($sitems as $it) {
                    $price = $it['offer_price'] ?? $it['selling_price'];
                    $pdo->prepare("INSERT INTO order_items (order_id, product_id, price, quantity, subtotal) VALUES (?,?,?,?,?)")->execute([
                        $order_id, $it['product_id'], $price, $it['quantity'], $price * $it['quantity']
                    ]);
                }

                send_notification($pdo, $sid, 'New Order Received', 'You have received a new order #' . $order_id . '. Check your seller dashboard.');
            }

            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user['id']]);
            log_activity($pdo, $user['id'], 'Placed order(s) for ' . count($items) . ' item(s)');
            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to place order. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container-md">
        <h1 class="section-title">Checkout</h1>

        <?php if ($success): ?>
            <div class="card card-body" style="text-align:center; padding:48px;">
                <div style="font-size:48px; margin-bottom:16px; color:var(--success);">OK</div>
                <h2 style="font-size:22px; font-weight:800; margin-bottom:12px;">Order Placed Successfully!</h2>
                <p style="color:var(--text-muted); max-width:400px; margin:0 auto 20px;">The seller will contact you for delivery. Remember to only pay after receiving and confirming the item.</p>
                <div class="payment-banner" style="max-width:420px; margin:0 auto 24px;">
                    <div class="payment-banner-icon">!</div>
                    <div class="payment-banner-text">
                        <strong>Do not pay before delivery</strong>
                        <p>Payment should only be made after you physically receive the item.</p>
                    </div>
                </div>
                <div style="display:flex; gap:12px; justify-content:center;">
                    <a href="/user/my-orders.php" class="btn btn-primary">View My Orders</a>
                    <a href="/products.php" class="btn btn-outline">Continue Browsing</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 340px; gap:32px; align-items:start;">
                <div>
                    <div class="card" style="margin-bottom:24px;">
                        <div class="card-header">
                            <h2 class="card-title">Order Items</h2>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Product</th><th>Seller</th><th>Price</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:10px;">
                                                    <?php if ($item['image']): ?>
                                                        <img src="/<?= sanitize($item['image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius);">
                                                    <?php endif; ?>
                                                    <span style="font-weight:600; font-size:14px;"><?= sanitize($item['item_name']) ?></span>
                                                </div>
                                            </td>
                                            <td style="font-size:14px;"><?= sanitize($item['seller_name']) ?></td>
                                            <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($item['offer_price'] ?? $item['selling_price']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h2 class="card-title">Delivery Details</h2></div>
                        <div class="card-body">
                            <form method="POST" action="/checkout.php">
                                <div class="form-group">
                                    <label class="form-label">Delivery Location <span style="color:var(--danger);">*</span></label>
                                    <input type="text" name="delivery_location" class="form-control" placeholder="e.g. Karatina University Main Gate, Block D Room 12" value="<?= sanitize($_POST['delivery_location'] ?? '') ?>" required>
                                    <div class="form-hint">Be as specific as possible to help the seller find you.</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number <span style="color:var(--danger);">*</span></label>
                                    <input type="text" name="buyer_phone" class="form-control" placeholder="07XX XXX XXX" value="<?= sanitize($_POST['buyer_phone'] ?? $user['phone'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Preferred Delivery Time</label>
                                    <input type="text" name="preferred_delivery_time" class="form-control" placeholder="e.g. Today after 4PM, Tomorrow morning" value="<?= sanitize($_POST['preferred_delivery_time'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Additional Instructions</label>
                                    <textarea name="delivery_instructions" class="form-control" rows="3" placeholder="e.g. Call me when you arrive. I'll be at the library entrance."><?= sanitize($_POST['delivery_instructions'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block btn-lg">Place Order</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card card-body">
                        <h3 style="font-size:16px; font-weight:700; margin-bottom:16px;">Order Total</h3>
                        <div style="font-size:28px; font-weight:800; color:var(--primary); font-family:var(--font-mono); margin-bottom:20px;"><?= format_price($total) ?></div>
                        <div class="payment-banner">
                            <div class="payment-banner-icon">!</div>
                            <div class="payment-banner-text">
                                <strong>Pay after delivery only</strong>
                                <p>Do not pay before receiving and confirming the item.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
