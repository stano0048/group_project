<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'About';
$user = get_user();

$stmt = $pdo->prepare("SELECT page_content FROM cms_pages WHERE page_slug = 'about'");
$stmt->execute();
$cms = $stmt->fetchColumn();

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container-md">
        <h1 class="section-title">About KarU Marketplace</h1>
        <p class="section-subtitle">Campus Student Marketplace for Karatina University</p>
        <div class="card card-body" style="font-size:15px; line-height:1.8; color:var(--text-muted);">
            <?= nl2br(sanitize($cms)) ?>
        </div>
        <div style="margin-top:32px; display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px;">
            <div class="card card-body" style="text-align:center;">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:8px;">For Students</h3>
                <p style="font-size:14px; color:var(--text-muted);">Exclusively for Karatina University students. Buy and sell safely within your campus community.</p>
            </div>
            <div class="card card-body" style="text-align:center;">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:8px;">Verified Sellers</h3>
                <p style="font-size:14px; color:var(--text-muted);">All sellers are approved by our admin team. Avoid dealing with unknown or unverified sellers.</p>
            </div>
            <div class="card card-body" style="text-align:center;">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:8px;">Pay After Delivery</h3>
                <p style="font-size:14px; color:var(--text-muted);">Our marketplace does not handle payments. Pay only after you have physically received and confirmed the item.</p>
            </div>
        </div>
        <div style="margin-top:32px; text-align:center;">
            <a href="/members.php" class="btn btn-primary">Meet Our Development Team</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
