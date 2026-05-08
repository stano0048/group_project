<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Our Team';
$user = get_user();

$members = [
    ['name' => 'Stanley Mwangi', 'admission' => 'SCT-1-0001-1/2023', 'role' => 'Project Lead & Full-Stack Developer', 'initials' => 'SM', 'color' => '#2563eb'],
    ['name' => 'Faith Wanjiku', 'admission' => 'SCT-1-0002-1/2023', 'role' => 'Frontend Developer & UI Designer', 'initials' => 'FW', 'color' => '#16a34a'],
    ['name' => 'Brian Kamau', 'admission' => 'SCT-1-0003-1/2023', 'role' => 'Backend Developer & Database Admin', 'initials' => 'BK', 'color' => '#7c3aed'],
    ['name' => 'Grace Njeri', 'admission' => 'SCT-1-0004-1/2023', 'role' => 'Frontend Developer', 'initials' => 'GN', 'color' => '#db2777'],
    ['name' => 'Kevin Odhiambo', 'admission' => 'SCT-1-0005-1/2023', 'role' => 'Backend Developer', 'initials' => 'KO', 'color' => '#059669'],
    ['name' => 'Mercy Akinyi', 'admission' => 'SCT-1-0006-1/2023', 'role' => 'System Analyst & Tester', 'initials' => 'MA', 'color' => '#d97706'],
    ['name' => 'John Mutua', 'admission' => 'SCT-1-0007-1/2023', 'role' => 'Database Designer', 'initials' => 'JM', 'color' => '#0891b2'],
    ['name' => 'Alice Wairimu', 'admission' => 'SCT-1-0008-1/2023', 'role' => 'Documentation & Research', 'initials' => 'AW', 'color' => '#dc2626'],
    ['name' => 'Peter Kipchoge', 'admission' => 'SCT-1-0009-1/2023', 'role' => 'Security & Authentication', 'initials' => 'PK', 'color' => '#7c3aed'],
    ['name' => 'Diana Chebet', 'admission' => 'SCT-1-0010-1/2023', 'role' => 'QA Tester & Presenter', 'initials' => 'DC', 'color' => '#0f766e'],
];

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div style="text-align:center; margin-bottom:48px;">
            <h1 class="section-title">Our Development Team</h1>
            <p class="section-subtitle" style="max-width:560px; margin:0 auto;">KarU Marketplace was developed by Group 10 as part of the Web Design and Development course at Karatina University, School of Computing and Informatics.</p>
        </div>

        <div class="members-grid">
            <?php foreach ($members as $i => $m): ?>
                <div class="member-card">
                    <div class="member-avatar" style="background:<?= $m['color'] ?>;">
                        <?= $m['initials'] ?>
                    </div>
                    <div class="member-name"><?= sanitize($m['name']) ?></div>
                    <div class="member-admission"><?= sanitize($m['admission']) ?></div>
                    <div class="member-role-tag"><?= sanitize($m['role']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:48px; text-align:center; padding:32px; background:var(--bg-section); border-radius:var(--radius-lg); border:1px solid var(--border);">
            <h2 style="font-size:20px; font-weight:800; margin-bottom:8px;">KarU Marketplace</h2>
            <p style="color:var(--text-muted); font-size:14px; margin-bottom:4px;">Campus Student Marketplace System</p>
            <p style="color:var(--text-muted); font-size:14px; margin-bottom:4px;">Karatina University &mdash; School of Computing and Informatics</p>
            <p style="color:var(--text-muted); font-size:14px;">Web Design &amp; Development &mdash; Group 10 Project &mdash; <?= date('Y') ?></p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
