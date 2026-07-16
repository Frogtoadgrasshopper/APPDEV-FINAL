<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Home';
$products = db()->query("
    SELECT products.*, categories.name AS category_name
    FROM products
    JOIN categories ON categories.id = products.category_id
    WHERE products.is_active = 1
    ORDER BY products.stock DESC, products.name
    LIMIT 4
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <div class="hero-badge"><span></span> Designed by students, for students</div>
        <p class="eyebrow">Your campus. Your colors.</p>
        <h1>Wear your campus <em>story.</em></h1>
        <p>Soft, everyday university hoodies that feel right from the first lecture to the last org meeting.</p>
        <div class="hero-actions">
            <a class="button primary button-lg" href="<?= h(url('store.php')) ?>">Shop the collection <span aria-hidden="true">&rarr;</span></a>
            <a class="text-link" href="#featured">See what students love <span aria-hidden="true">&darr;</span></a>
        </div>
        <ul class="hero-points" aria-label="Shopping benefits">
            <li><span aria-hidden="true">&#10003;</span> Soft fleece</li>
            <li><span aria-hidden="true">&#10003;</span> Campus pickup</li>
            <li><span aria-hidden="true">&#10003;</span> Easy checkout</li>
        </ul>
    </div>
    <div class="hero-visual" aria-label="Featured Campus Varsity Hoodie">
        <div class="hero-shape hero-shape-one"></div>
        <div class="hero-shape hero-shape-two"></div>
        <span class="hero-sticker">Campus<br>favorite</span>
        <div class="hero-product">
        <img src="<?= h(url('assets/img/hoodie-varsity-navy.png')) ?>" alt="Navy Campus Varsity Hoodie">
        </div>
        <div class="hero-product-card">
            <div>
                <small>Best seller</small>
                <strong>Campus Varsity Hoodie</strong>
            </div>
            <span>PHP 850</span>
        </div>
    </div>
</section>

<section class="benefit-strip" aria-label="Store benefits">
    <div><span class="benefit-icon" aria-hidden="true">01</span><p><strong>Campus-ready comfort</strong><small>Midweight fleece for everyday wear</small></p></div>
    <div><span class="benefit-icon" aria-hidden="true">02</span><p><strong>Easy local delivery</strong><small>Only PHP 80 per order</small></p></div>
    <div><span class="benefit-icon" aria-hidden="true">03</span><p><strong>Pickup-friendly</strong><small>Choose campus pickup at payment</small></p></div>
</section>

<section class="section-head" id="featured">
    <div>
        <p class="eyebrow">Student favorites</p>
        <h2>Made to live in</h2>
        <p>Campus colors, soft fleece, and easy-to-wear fits.</p>
    </div>
    <a class="text-link dark" href="<?= h(url('store.php')) ?>">View all hoodies <span aria-hidden="true">&rarr;</span></a>
</section>

<section class="product-grid featured-grid">
    <?php foreach ($products as $product): ?>
        <article class="product-card">
            <div class="product-media">
                <img src="<?= h(url($product['image_url'])) ?>" alt="<?= h($product['name']) ?>" loading="lazy">
                <span class="stock-badge"><?= (int) $product['stock'] > 10 ? 'In stock' : 'Only ' . (int) $product['stock'] . ' left' ?></span>
            </div>
            <div class="product-info">
                <span class="tag"><?= h($product['category_name']) ?></span>
                <h3><?= h($product['name']) ?></h3>
                <p><?= h($product['description']) ?></p>
                <div class="product-meta">
                    <strong><?= money((float) $product['price']) ?></strong>
                    <span>Available now</span>
                </div>
                <a class="button primary full" href="<?= h(url('store.php')) ?>">Choose size <span aria-hidden="true">&rarr;</span></a>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="split-band">
    <div class="split-band-copy">
        <p class="eyebrow">Built for belonging</p>
        <h2>More than a hoodie. It is your campus story.</h2>
        <p>From college pride to org identity, find the layer that feels like yours.</p>
        <a class="button light" href="<?= h(url('store.php')) ?>">Find your hoodie</a>
    </div>
    <div class="split-band-art" aria-hidden="true">
        <span>CT</span>
        <span>06</span>
        <span>UNI</span>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
