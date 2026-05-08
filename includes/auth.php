<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

function require_role($role) {
    require_login();
    $user = get_user();
    if (is_array($role)) {
        if (!in_array($user['role'], $role)) {
            header('Location: /index.php');
            exit;
        }
    } else {
        if ($user['role'] !== $role) {
            header('Location: /index.php');
            exit;
        }
    }
}

function require_not_suspended() {
    $user = get_user();
    if ($user && $user['status'] === 'suspended') {
        session_destroy();
        header('Location: /login.php?error=suspended');
        exit;
    }
}

function dashboard_url($role) {
    switch ($role) {
        case 'admin': return '/admin/dashboard.php';
        case 'moderator': return '/moderator/dashboard.php';
        case 'seller': return '/seller/dashboard.php';
        default: return '/user/dashboard.php';
    }
}

function format_price($amount) {
    return 'KES ' . number_format($amount, 2);
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 604800) return floor($time / 86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
