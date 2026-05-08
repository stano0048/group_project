<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
require_not_suspended();
$user = get_user();
$page_title = 'My Cart';

if (isset($_GET['add'])) {
    $pid = (int)$_GET['add'];
    $check = $pdo->prepare("SELECT * FROM products WHERE id = ? AND product_status = 'on_sale'");
    $check->execute([$pid]);
    if ($check->fetch()) {
        $exist = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $exist->execute([$user['id'], $pid]);
        if (!$exist->fetch()) {
            $pdo->prepare("INSERT INTO cart (user_id, product_id) VALUES (?,?)")->execute([$user['id'], $pid]);
        }
    }
    header('Location: /cart.php');
    exit;
}

if (isset($_GET['wishlist'])) {
    $pid = (int)$_GET['wishlist'];
    $exist = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $exist->execute([$user['id'], $pid]);
    if ($exist->fetch()) {
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$user['id'], $pid]);
    } else {
        $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?,?)")->execute([$user['id'], $pid]);
    }
    header('Location: /product-details.php?id=' . $pid);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([(int)$_POST['remove'], $user['id']]);
    header('Location: /cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_offer'])) {
    $cart_id = (int)$_POST['cart_id'];
    $offer = (float)$_POST['offer_price'];
    $pdo->prepare("UPDATE cart SET offer_price = ? WHERE id = ? AND user_id = ?")->execute([$offer ?: null, $cart_id, $user['id']]);
    header('Location: /cart.php');
    exit;
}

$stmt = $pdo->prepare("SELECT c.*, p.item_name, p.selling_price, p.is_negotiable, p.min_price, p.max_price, p.product_status, p.quantity as stock, u.username as seller_name, u.id as seller_id, (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) as image FROM cart c JOIN products p ON c.product_id = p.id JOIN users u ON p.seller_id = u.id WHERE c.user_id = ?");
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();

$total = array_sum(array_map(fn($i) => $i['offer_price'] ?? $i['selling_price'], $items));

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">My Cart</h1>

        <?php if (empty($items)): ?>
            <div class="empty-state">
                <h3>Your cart is empty</h3>
                <p>Browse products and add items you'd like to buy.</p>
                <a href="/products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns:1fr 300px; gap:32px; align-items:start;">
                <div>
                    <div class="card">
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Seller</th>
                                        <th>Price</th>
                                        <th>Your Offer</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <?php if ($item['image']): ?>
                                                        <img src="/<?= sanitize($item['image']) ?>" style="width:52px;height:52px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div style="font-weight:600; font-size:14px;"><?= sanitize($item['item_name']) ?></div>
                                                        <?php if ($item['product_status'] !== 'on_sale'): ?>
                                                            <span class="badge badge-danger" style="font-size:10px;">No longer available</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="font-size:14px;"><?= sanitize($item['seller_name']) ?></td>
                                            <td style="font-weight:700; font-family:var(--font-mono);"><?= format_price($item['selling_price']) ?></td>
                                            <td>
                                                <?php if ($item['is_negotiable']): ?>
                                                    <form method="POST" style="display:flex; gap:6px; align-items:center;">
                                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                                        <input type="number" name="offer_price" class="form-control" style="width:110px; padding:6px 10px; font-size:13px;" placeholder="Offer" value="<?= $item['offer_price'] ?? '' ?>" min="<?= $item['min_price'] ?>" max="<?= $item['max_price'] ?>">
                                                        <button type="submit" name="update_offer" value="1" class="btn btn-outline btn-sm">Set</button>
                                                    </form>
                                                    <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">Range: <?= format_price($item['min_price']) ?> - <?= format_price($item['max_price']) ?></div>
                                                <?php else: ?>
                                                    <span style="font-size:13px; color:var(--text-muted);">Fixed price</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST">
                                                    <input type="hidden" name="remove" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm confirm-action" data-confirm="Remove this item from cart?">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card card-body">
                        <h3 style="font-size:17px; font-weight:700; margin-bottom:16px;">Order Summary</h3>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:14px;">
                            <span>Items (<?= count($items) ?>)</span>
                            <span style="font-family:var(--font-mono);"><?= format_price($total) ?></span>
                        </div>
                        <hr style="border:none; border-top:1px solid var(--border); margin:12px 0;">
                        <div style="display:flex; justify-content:space-between; font-weight:800; font-size:16px; margin-bottom:20px;">
                            <span>Estimated Total</span>
                            <span style="font-family:var(--font-mono); color:var(--primary);"><?= format_price($total) ?></span>
                        </div>
                        <div class="payment-banner" style="margin-bottom:16px;">
                            <div class="payment-banner-icon">!</div>
                            <div class="payment-banner-text">
                                <strong>Pay after delivery only</strong>
                                <p>Do not pay before receiving the item.</p>
                            </div>
                        </div>
                        <a href="/checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
