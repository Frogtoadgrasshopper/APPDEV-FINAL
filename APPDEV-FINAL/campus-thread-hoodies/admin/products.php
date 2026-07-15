<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$products = db()->query("
    SELECT products.*, categories.name AS category_name
    FROM products
    JOIN categories ON categories.id = products.category_id
    ORDER BY products.is_active DESC, categories.name, products.name
")->fetchAll();

$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="admin-page-head">
    <div>
        <p class="eyebrow">Stock admin</p>
        <h1>Hoodie stocks and prices</h1>
        <p>Add products, modify prices, and update remaining stock.</p>
    </div>
    <a class="button primary" href="<?= h(url('admin/product_form.php')) ?>">Add product</a>
</section>

<section class="summary-table">
    <table>
        <thead>
        <tr>
            <th>SKU</th>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= h($product['sku']) ?></td>
                <td><?= h($product['name']) ?></td>
                <td><?= h($product['category_name']) ?></td>
                <td><?= money((float) $product['price']) ?></td>
                <td><?= (int) $product['stock'] ?></td>
                <td><?= (int) $product['is_active'] === 1 ? 'Active' : 'Hidden' ?></td>
                <td><a href="<?= h(url('admin/product_form.php?id=' . (int) $product['id'])) ?>">Modify</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
