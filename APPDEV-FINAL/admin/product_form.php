<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$editing = $id > 0;
$errors = [];
$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$imageOptions = [
    'assets/img/hoodie-engineering-maroon.png',
    'assets/img/hoodie-business-green.png',
    'assets/img/hoodie-compsci-black.png',
    'assets/img/hoodie-nursing-sage.png',
    'assets/img/hoodie-varsity-navy.png',
    'assets/img/hoodie-library-ash.png',
    'assets/img/hoodie-intramurals-blue.png',
    'assets/img/hoodie-founders-cream.png',
    'assets/img/hoodie-arts-teal.png',
    'assets/img/hoodie-debate-charcoal.png',
];

if ($editing) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $productRecord = $stmt->fetch();
    if (!$productRecord) {
        flash('error', 'Product not found.');
        redirect('admin/products.php');
    }
} else {
    $productRecord = [
        'category_id' => $categories[0]['id'] ?? 1,
        'sku' => '',
        'name' => '',
        'color' => '',
        'size' => '',
        'price' => '',
        'stock' => 0,
        'description' => '',
        'image_url' => $imageOptions[0],
        'is_active' => 1,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $sku = strtoupper(trim($_POST['sku'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $size = implode(',', array_map('strtoupper', product_sizes((string) ($_POST['size'] ?? ''))));
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? $imageOptions[0]);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($categoryId <= 0) {
        $errors[] = 'Category is required.';
    }
    if ($sku === '') {
        $errors[] = 'SKU is required.';
    }
    if ($name === '') {
        $errors[] = 'Product name is required.';
    }
    if ($color === '') {
        $errors[] = 'Color is required.';
    }
    if ($size === '') {
        $errors[] = 'Size is required.';
    }
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero.';
    }
    if ($stock < 0) {
        $errors[] = 'Stock cannot be negative.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }
    if (!in_array($imageUrl, $imageOptions, true)) {
        $errors[] = 'Selected image is invalid.';
    }

    $duplicate = db()->prepare('SELECT id FROM products WHERE sku = ? AND id <> ? LIMIT 1');
    $duplicate->execute([$sku, $id]);
    if ($sku !== '' && $duplicate->fetch()) {
        $errors[] = 'Another product already uses that SKU.';
    }

    if (!$errors) {
        if ($editing) {
            db()->prepare("
                UPDATE products
                SET category_id = ?, sku = ?, name = ?, color = ?, size = ?, price = ?, stock = ?, description = ?, image_url = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ")->execute([$categoryId, $sku, $name, $color, $size, $price, $stock, $description, $imageUrl, $isActive, $id]);
            log_activity('Product modified', "Updated $name price to " . money($price) . " and stock to $stock.");
            flash('success', 'Product updated.');
        } else {
            db()->prepare("
                INSERT INTO products (category_id, sku, name, color, size, price, stock, description, image_url, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([$categoryId, $sku, $name, $color, $size, $price, $stock, $description, $imageUrl, $isActive]);
            log_activity('Product added', "Added hoodie product $name with $stock stock.");
            flash('success', 'Product added.');
        }

        redirect('admin/products.php');
    }

    $productRecord = array_merge($productRecord, [
        'category_id' => $categoryId,
        'sku' => $sku,
        'name' => $name,
        'color' => $color,
        'size' => $size,
        'price' => $price,
        'stock' => $stock,
        'description' => $description,
        'image_url' => $imageUrl,
        'is_active' => $isActive,
    ]);
}

$pageTitle = $editing ? 'Modify Product' : 'Add Product';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-shell">
    <div class="form-panel">
        <p class="eyebrow">Stock admin</p>
        <h1><?= $editing ? 'Modify hoodie stock' : 'Add hoodie stock' ?></h1>
        <?php if ($errors): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?= h($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="stacked-form">
            <?= csrf_field() ?>
            <div class="form-grid">
                <label>SKU
                    <input type="text" name="sku" value="<?= h($productRecord['sku']) ?>" required>
                </label>
                <label>Category
                    <select name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) $productRecord['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                <?= h($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <label>Product name
                <input type="text" name="name" value="<?= h($productRecord['name']) ?>" required>
            </label>
            <div class="form-grid">
                <label>Color
                    <input type="text" name="color" value="<?= h($productRecord['color']) ?>" required>
                </label>
                <label>Available sizes
                    <input type="text" name="size" value="<?= h($productRecord['size']) ?>" placeholder="S,M,L,XL" required>
                    <small>Separate sizes with commas.</small>
                </label>
            </div>
            <div class="form-grid">
                <label>Price
                    <input type="number" name="price" min="1" step="0.01" value="<?= h((string) $productRecord['price']) ?>" required>
                </label>
                <label>Stock quantity
                    <input type="number" name="stock" min="0" value="<?= h((string) $productRecord['stock']) ?>" required>
                </label>
            </div>
            <label>Description
                <textarea name="description" rows="4" required><?= h($productRecord['description']) ?></textarea>
            </label>
            <label>Product image
                <select name="image_url">
                    <?php foreach ($imageOptions as $image): ?>
                        <option value="<?= h($image) ?>" <?= $productRecord['image_url'] === $image ? 'selected' : '' ?>><?= h(basename($image)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="check-line">
                <input type="checkbox" name="is_active" value="1" <?= (int) $productRecord['is_active'] === 1 ? 'checked' : '' ?>>
                Show this product in the store
            </label>
            <div class="button-row">
                <button class="button primary" type="submit">Save product</button>
                <a class="button ghost" href="<?= h(url('admin/products.php')) ?>">Cancel</a>
            </div>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
