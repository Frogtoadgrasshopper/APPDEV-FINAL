<?php
require_once __DIR__ . '/includes/init.php';

$categorySlug = trim($_GET['category'] ?? '');
$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$params = [];
$sql = "
    SELECT products.*, categories.name AS category_name, categories.slug
    FROM products
    JOIN categories ON categories.id = products.category_id
    WHERE products.is_active = 1
";

if ($categorySlug !== '') {
    $sql .= ' AND categories.slug = ?';
    $params[] = $categorySlug;
}

$sql .= ' ORDER BY categories.name, products.name';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Store';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-title">
    <p class="eyebrow">Buyer store</p>
    <h1>University hoodies by category</h1>
    <p>Choose one hoodie design, add it to cart, and continue to checkout when ready.</p>
</section>

<section class="category-tabs" aria-label="Product categories">
    <a class="<?= $categorySlug === '' ? 'active' : '' ?>" href="<?= h(url('store.php')) ?>">All</a>
    <?php foreach ($categories as $category): ?>
        <a class="<?= $categorySlug === $category['slug'] ? 'active' : '' ?>" href="<?= h(url('store.php?category=' . urlencode($category['slug']))) ?>">
            <?= h($category['name']) ?>
        </a>
    <?php endforeach; ?>
</section>

<section class="product-grid">
    <?php foreach ($products as $product): ?>
        <article class="product-card">
            <img src="<?= h(url($product['image_url'])) ?>" alt="<?= h($product['name']) ?>">
            <div class="product-info">
                <span class="tag"><?= h($product['category_name']) ?></span>
                <h2><?= h($product['name']) ?></h2>
                <p><?= h($product['description']) ?></p>
                <dl class="product-specs">
                    <div><dt>Color</dt><dd><?= h($product['color']) ?></dd></div>
                    <div><dt>Size</dt><dd><?= h($product['size']) ?></dd></div>
                    <div><dt>Stock</dt><dd><?= (int) $product['stock'] ?></dd></div>
                </dl>
                <div class="product-meta">
                    <strong><?= money((float) $product['price']) ?></strong>
                    <span><?= (int) $product['stock'] > 0 ? 'Available' : 'Sold out' ?></span>
                </div>
                <form action="<?= h(url('add_to_cart.php')) ?>" method="post" class="cart-add-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <label>
                        Qty
                        <input type="number" name="quantity" min="1" max="<?= max(1, (int) $product['stock']) ?>" value="1">
                    </label>
                    <button class="button full" type="submit" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>>Add to cart</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php if (!$products): ?>
    <section class="empty-state">
        <h2>No hoodies found</h2>
        <p>Try another category or ask the seller admin to add more stock.</p>
    </section>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
