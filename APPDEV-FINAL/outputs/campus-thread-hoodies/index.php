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
        <p class="eyebrow">One apparel product: university hoodies</p>
        <h1>Campus-ready hoodies for every college, club, and class day.</h1>
        <p>CampusThread Hoodies sells comfortable university-themed hoodies with simple ordering for buyers and complete stock control for the seller team.</p>
        <div class="hero-actions">
            <a class="button primary" href="<?= h(url('store.php')) ?>">Shop hoodies</a>
            <a class="button ghost" href="<?= h(url('about.php')) ?>">Meet Group 4</a>
        </div>
    </div>
    <div class="hero-product">
        <img src="<?= h(url('assets/img/hoodie-navy.svg')) ?>" alt="Campus varsity hoodie">
        <div>
            <strong>Campus Varsity Hoodie</strong>
            <span>Soft fleece, embroidered chest mark, PHP 850.00</span>
        </div>
    </div>
</section>

<section class="section-head">
    <div>
        <p class="eyebrow">Featured stock</p>
        <h2>Ready for pickup or delivery</h2>
    </div>
    <a href="<?= h(url('store.php')) ?>">View all products</a>
</section>

<section class="product-grid">
    <?php foreach ($products as $product): ?>
        <article class="product-card">
            <img src="<?= h(url($product['image_url'])) ?>" alt="<?= h($product['name']) ?>">
            <div class="product-info">
                <span class="tag"><?= h($product['category_name']) ?></span>
                <h3><?= h($product['name']) ?></h3>
                <p><?= h($product['description']) ?></p>
                <div class="product-meta">
                    <strong><?= money((float) $product['price']) ?></strong>
                    <span><?= (int) $product['stock'] ?> left</span>
                </div>
                <form action="<?= h(url('add_to_cart.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button class="button full" type="submit">Add to cart</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="split-band">
    <div>
        <h2>Seller controls are included.</h2>
        <p>Admins can manage users, update hoodie prices and stock levels, and view inventory plus audit log reports.</p>
    </div>
    <a class="button primary" href="<?= h(url('login.php')) ?>">Open admin login</a>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
