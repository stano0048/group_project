<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart.']);
    exit;
}

$user       = get_user();
$product_id = (int)($_POST['product_id'] ?? 0);
$offer      = isset($_POST['offer_price']) && $_POST['offer_price'] !== '' ? (float)$_POST['offer_price'] : null;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id=? AND product_status='on_sale'");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product is not available.']);
    exit;
}

if ($product['seller_id'] === $user['id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot add your own product to cart.']);
    exit;
}

if ($offer !== null && $product['is_negotiable']) {
    if ($offer < (float)$product['min_price']) {
        echo json_encode(['success' => false, 'message' => 'Your offer is below the seller\'s minimum price.']);
        exit;
    }
    if ($offer > (float)$product['max_price']) {
        echo json_encode(['success' => false, 'message' => 'Your offer exceeds the seller\'s maximum price.']);
        exit;
    }
}

$exist = $pdo->prepare("SELECT id FROM cart WHERE user_id=? AND product_id=?");
$exist->execute([$user['id'], $product_id]);

if ($exist->fetch()) {
    if ($offer !== null) {
        $pdo->prepare("UPDATE cart SET offer_price=? WHERE user_id=? AND product_id=?")->execute([$offer, $user['id'], $product_id]);
        echo json_encode(['success' => true, 'message' => 'Cart updated with your offer.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Item is already in your cart.']);
    }
} else {
    $pdo->prepare("INSERT INTO cart (user_id, product_id, offer_price) VALUES(?,?,?)")->execute([$user['id'], $product_id, $offer]);
    echo json_encode(['success' => true, 'message' => 'Item added to cart successfully.']);
}
