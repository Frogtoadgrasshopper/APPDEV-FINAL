<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'About';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-title">
    <p class="eyebrow">About the company</p>
    <h1>CampusThread Hoodies</h1>
    <p>CampusThread Hoodies is a student project company focused on one apparel product: university hoodies for students, faculty supporters, and campus organizations.</p>
</section>

<section class="about-grid">
    <article>
        <h2>Company information</h2>
        <p>Our shop offers categorized hoodie designs, a buyer cart and checkout flow, and a seller admin area for managing users, stock, prices, inventory reports, and audit logs.</p>
        <p>The project is built with plain PHP, structured database tables, form validation, and reusable page components.</p>
    </article>
    <article>
        <h2>Group members</h2>
        <ul class="member-list">
            <?php foreach ($GROUP_MEMBERS as $member): ?>
                <li><?= h($member) ?></li>
            <?php endforeach; ?>
        </ul>
    </article>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
