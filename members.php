<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Our Team';
$user = get_user();

$members = [
    ['name' => 'Stanley Makanga', 'admission' => 'P101/4437G/23', 'role' => 'Project Lead & Full-Stack Developer', 'initials' => 'MS', 'color' => '#2563eb'],
    ['name' => 'Benard Ondari', 'admission' => 'P101/3745G/24', 'role' => 'Frontend Developer', 'initials' => 'BO', 'color' => '#16a34a'],
    ['name' => 'Clement Mwangi', 'admission' => 'P101/4789G/24', 'role' => 'Backend Developer', 'initials' => 'CM', 'color' => '#7c3aed'],
    ['name' => 'Mwangi Simon', 'admission' => 'P101/3727G/24', 'role' => 'Frontend Developer', 'initials' => 'MS', 'color' => '#db2777'],
    ['name' => 'Cornelius Musyoka', 'admission' => 'P101/4766G/24', 'role' => 'Backend Developer', 'initials' => 'MC', 'color' => '#059669'],
    ['name' => 'Vincent Maithya', 'admission' => 'P101/4915G/24', 'role' => 'System Analyst & Tester', 'initials' => 'VM', 'color' => '#d97706'],
    ['name' => 'Cyprian Omolo', 'admission' => 'P101/3757G/24', 'role' => 'Database Designer', 'initials' => 'CO', 'color' => '#0891b2'],
    ['name' => 'Stephanie Mucheke', 'admission' => 'P101/3712G/24', 'role' => 'Documentation & Research', 'initials' => 'SM', 'color' => '#dc2626'],
    ['name' => 'Newtone Atamba', 'admission' => 'P101/4718G/24', 'role' => 'Security & Authentication', 'initials' => 'NA', 'color' => '#7c3aed'],
    ['name' => 'Boniface Kaniu', 'admission' => 'P101/4910G/24', 'role' => 'Backend Developer', 'initials' => 'BK', 'color' => '#ff3aed'],
    ['name' => 'Kelvin Mungai', 'admission' => 'P101/3687G/24', 'role' => 'QA Tester', 'initials' => 'KM', 'color' => '#0f766e'],
    [ 'name' => 'Stacey Chelagat', 'admission' => 'P101/4383G/23', 'role' => 'Backups and Recovery',  'initials' => 'SC', 'color' => '#052646'],
    [ 'name' => 'Samuel Mwangi', 'admission' => 'P101/4578G/24', 'role' => 'UI Designer',  'initials' => 'SM', 'color' => '#ff4646'],
    ['name' => 'Alvin Kipchumba', 'admission' => 'P101/4909G/24', 'role' => 'Database Admin', 'initials' => 'AL', 'color' => '#dd1098'],
];

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div style="text-align:center; margin-bottom:48px;">
            <h1 class="section-title">Our Development Team</h1>
            <p class="section-subtitle" style="max-width:560px; margin:0 auto;">KarU Marketplace was developed by Group as part of the Web Design and Development course at Karatina University, School of Computing and Informatics.</p>
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
            <p style="color:var(--text-muted); font-size:14px;">Web Design &amp; Development &mdash; Group Project &mdash; <?= date('Y') ?></p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
