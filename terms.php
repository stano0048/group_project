<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Terms and Conditions';
$user = get_user();

$stmt = $pdo->prepare("SELECT page_content FROM cms_pages WHERE page_slug = 'terms'");
$stmt->execute();
$cms = $stmt->fetchColumn();

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container-md">
        <h1 class="section-title">Terms and Conditions</h1>
        <p class="section-subtitle">Please read these terms before using KarU Marketplace</p>
        <div class="card card-body" style="font-size:15px; line-height:2; color:var(--text-muted);">
            <?= nl2br(sanitize($cms)) ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
