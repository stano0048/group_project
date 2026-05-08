<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Contact';
$user = get_user();

$stmt = $pdo->prepare("SELECT page_content FROM cms_pages WHERE page_slug = 'contact'");
$stmt->execute();
$cms = $stmt->fetchColumn();

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container-md">
        <h1 class="section-title">Contact Us</h1>
        <p class="section-subtitle">Get in touch with the KarU Marketplace team</p>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:32px;">
            <div>
                <div class="card card-body" style="margin-bottom:20px;">
                    <h3 style="font-size:16px; font-weight:700; margin-bottom:12px;">Contact Information</h3>
                    <p style="font-size:14px; color:var(--text-muted); line-height:1.8;"><?= nl2br(sanitize($cms)) ?></p>
                </div>
                <div class="card card-body">
                    <h3 style="font-size:16px; font-weight:700; margin-bottom:12px;">Report an Issue</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin-bottom:12px;">If you encounter a suspicious seller or product, please use the report button on the product page, or contact admin directly.</p>
                    <?php if ($user): ?>
                        <a href="/products.php" class="btn btn-outline btn-sm">Browse Products</a>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-primary btn-sm">Login to Report</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card card-body">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:16px;">Quick Links</h3>
                <ul style="list-style:none; display:flex; flex-direction:column; gap:10px;">
                    <li><a href="/safety.php" class="btn btn-outline btn-sm btn-block" style="justify-content:flex-start;">Safety Guidelines</a></li>
                    <li><a href="/terms.php" class="btn btn-outline btn-sm btn-block" style="justify-content:flex-start;">Terms and Conditions</a></li>
                    <li><a href="/members.php" class="btn btn-outline btn-sm btn-block" style="justify-content:flex-start;">Our Development Team</a></li>
                    <li><a href="/about.php" class="btn btn-outline btn-sm btn-block" style="justify-content:flex-start;">About KarU Marketplace</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
