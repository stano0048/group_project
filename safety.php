<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Safety Guidelines';
$user = get_user();

$stmt = $pdo->prepare("SELECT page_content FROM cms_pages WHERE page_slug = 'safety'");
$stmt->execute();
$cms = $stmt->fetchColumn();

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container-md">
        <h1 class="section-title">Safety Guidelines</h1>
        <p class="section-subtitle">Stay safe when buying and selling on KarU Marketplace</p>

        <div class="alert alert-warning" style="font-size:15px; font-weight:600;">
            Important: Always read and follow these safety rules before completing any transaction.
        </div>

        <div class="card card-body" style="margin-bottom:24px; font-size:15px; line-height:2; color:var(--text-muted);">
            <?= nl2br(sanitize($cms)) ?>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px;">
            <div class="card card-body" style="border-left:4px solid var(--success);">
                <h3 style="font-size:15px; font-weight:700; margin-bottom:8px; color:var(--success);">Do This</h3>
                <ul style="font-size:14px; color:var(--text-muted); padding-left:18px; line-height:2;">
                    <li>Meet at campus public locations</li>
                    <li>Inspect item before paying</li>
                    <li>Confirm item matches description</li>
                    <li>Report suspicious listings</li>
                    <li>Check seller feedback</li>
                </ul>
            </div>
            <div class="card card-body" style="border-left:4px solid var(--danger);">
                <h3 style="font-size:15px; font-weight:700; margin-bottom:8px; color:var(--danger);">Never Do This</h3>
                <ul style="font-size:14px; color:var(--text-muted); padding-left:18px; line-height:2;">
                    <li>Pay before receiving the item</li>
                    <li>Send money via M-Pesa before delivery</li>
                    <li>Meet sellers in isolated locations</li>
                    <li>Share personal banking details</li>
                    <li>Ignore bad seller feedback</li>
                </ul>
            </div>
        </div>

        <div class="payment-banner" style="margin-top:32px;">
            <div class="payment-banner-icon">!</div>
            <div class="payment-banner-text">
                <strong>Core Rule: Pay Only After Delivery</strong>
                <p>The KarU Marketplace does not process any payments online. All payments must be made in person, after the buyer physically receives and confirms the item is as described.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
