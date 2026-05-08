<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (is_logged_in()) {
    log_activity($pdo, $_SESSION['user_id'], 'User logged out');
}

session_destroy();
header('Location: /login.php');
exit;
