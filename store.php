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
<section class="page-title store-title">
    <div>
        <p class="eyebrow">The campus collection</p>
        <h1>Find your everyday layer.</h1>
        <p>Soft fleece, easy fits, and designs made to represent the communities you belong to.</p>
    </div>
    <div class="store-title-note">
        <strong><?= count($products) ?></strong>
        <span>hoodie design<?= count($products) === 1 ? '' : 's' ?> available</span>
    </div>
</section>

<section class="store-toolbar">
    <div class="category-tabs" aria-label="Product categories">
        <a class="<?= $categorySlug === '' ? 'active' : '' ?>" href="<?= h(url('store.php')) ?>">All hoodies</a>
        <?php foreach ($categories as $category): ?>
            <a class="<?= $categorySlug === $category['slug'] ? 'active' : '' ?>" href="<?= h(url('store.php?category=' . urlencode($category['slug']))) ?>">
                <?= h($category['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <span class="result-count"><?= count($products) ?> result<?= count($products) === 1 ? '' : 's' ?></span>
</section>

<section class="product-grid">
    <?php foreach ($products as $product): ?>
        <article class="product-card">
            <div class="product-media">
                <img src="<?= h(url($product['image_url'])) ?>" alt="<?= h($product['name']) ?>" loading="lazy">
                <span class="stock-badge <?= (int) $product['stock'] <= 5 ? 'low' : '' ?>">
                    <?= (int) $product['stock'] > 10 ? 'In stock' : ((int) $product['stock'] > 0 ? 'Only ' . (int) $product['stock'] . ' left' : 'Sold out') ?>
                </span>
            </div>
            <div class="product-info">
                <span class="tag"><?= h($product['category_name']) ?></span>
                <h2><?= h($product['name']) ?></h2>
                <p><?= h($product['description']) ?></p>
                <dl class="product-specs">
                    <div><dt>Color</dt><dd><?= h($product['color']) ?></dd></div>
                    <div><dt>Sizes</dt><dd><?= h(implode(' / ', product_sizes($product['size']))) ?></dd></div>
                    <div><dt>Stock</dt><dd><?= (int) $product['stock'] ?></dd></div>
                </dl>
                <div class="product-meta">
                    <strong><?= money((float) $product['price']) ?></strong>
                    <span><?= (int) $product['stock'] > 0 ? 'Ready to order' : 'Currently unavailable' ?></span>
                </div>
                <form action="<?= h(url('add_to_cart.php')) ?>" method="post" class="cart-add-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <label>
                        Size
                        <select name="size" required aria-label="Size for <?= h($product['name']) ?>">
                            <?php foreach (product_sizes($product['size']) as $size): ?>
                                <option value="<?= h($size) ?>"><?= h($size) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Qty
                        <input type="number" name="quantity" min="1" max="<?= max(1, (int) $product['stock']) ?>" value="1">
                    </label>
                    <button class="button primary full" type="submit" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>>Add to cart <span aria-hidden="true">+</span></button>
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

<section class="shop-help">
    <div><span aria-hidden="true">?</span><p><strong>Not sure which one to choose?</strong><small>Every hoodie uses the same comfortable, campus-ready fit.</small></p></div>
    <a class="button secondary" href="<?= h(url('about.php')) ?>">Learn about CampusThread</a>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
