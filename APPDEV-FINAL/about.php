<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'About';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-title">
    <p class="eyebrow">Our story</p>
    <h1>Campus pride, made comfortable.</h1>
    <p>CampusThread is a student-built hoodie shop for students, faculty supporters, and campus organizations who want to wear where they belong.</p>
</section>

<section class="about-grid">
    <article class="about-story">
        <span class="about-number">06</span>
        <p class="eyebrow">Why we made it</p>
        <h2>A hoodie can feel like home.</h2>
        <p>Some of the best campus memories happen in the simplest uniform: a dependable hoodie. We created CampusThread to make college, club, and university designs easier to browse and order.</p>
        <p>Every part of the experience—from discovering a design to confirming delivery—was shaped to feel clear, welcoming, and made for campus life.</p>
        <a class="button primary" href="<?= h(url('store.php')) ?>">Shop the collection</a>
    </article>
    <article class="about-team">
        <p class="eyebrow">The team</p>
        <h2>Built by Group 6</h2>
        <p>Designed and developed as our final application development project.</p>
        <ul class="member-list">
            <?php foreach ($GROUP_MEMBERS as $member): ?>
                <li><?= h($member) ?></li>
            <?php endforeach; ?>
        </ul>
    </article>
</section>

<section class="value-grid">
    <article><span>01</span><h3>Comfort first</h3><p>Campus-ready fleece that works from early classes to late meetings.</p></article>
    <article><span>02</span><h3>Easy to choose</h3><p>Clear colors, sizes, prices, and stock so you always know what you are ordering.</p></article>
    <article><span>03</span><h3>Simple checkout</h3><p>A guided ordering flow with delivery and pickup-friendly payment choices.</p></article>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
