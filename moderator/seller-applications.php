<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('moderator');

header('Location: /admin/seller-applications.php');
exit;
