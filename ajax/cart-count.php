<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['count' => 0]);
    exit;
}

$user = get_user();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id=?");
$stmt->execute([$user['id']]);
echo json_encode(['count' => (int)$stmt->fetchColumn()]);
