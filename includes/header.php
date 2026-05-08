<?php
require_once __DIR__ . '/auth.php';
$user = get_user();
$current_page = basename($_SERVER['PHP_SELF']);

$unread_notifications = 0;
if ($user && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user['id']]);
    $unread_notifications = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'KarU Marketplace' ?> - KarU Marketplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <?= $extra_head ?? '' ?>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="/index.php" class="nav-logo">
            <span class="logo-k">K</span>arU <span class="logo-m">Market</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li><a href="/index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="/products.php" class="<?= $current_page === 'products.php' ? 'active' : '' ?>">Products</a></li>
            <?php if ($user): ?>
                <li><a href="/cart.php" class="<?= $current_page === 'cart.php' ? 'active' : '' ?>">Cart</a></li>
                <li><a href="/user/my-orders.php" class="<?= $current_page === 'my-orders.php' ? 'active' : '' ?>">My Orders</a></li>
                <li>
                    <a href="<?= dashboard_url($user['role']) ?>" class="nav-dashboard">
                        Dashboard
                        <?php if ($unread_notifications > 0): ?>
                            <span class="notif-badge"><?= $unread_notifications ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-user-info">
                    <span class="nav-username"><?= sanitize($user['username']) ?></span>
                    <span class="nav-role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                </li>
                <li><a href="/logout.php" class="nav-logout">Logout</a></li>
            <?php else: ?>
                <li><a href="/about.php">About</a></li>
                <li><a href="/contact.php">Contact</a></li>
                <li><a href="/login.php" class="nav-btn-outline">Login</a></li>
                <li><a href="/register.php" class="nav-btn-primary">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<main class="main-content">
