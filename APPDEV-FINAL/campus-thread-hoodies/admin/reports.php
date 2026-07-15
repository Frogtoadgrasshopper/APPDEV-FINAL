<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$inventory = db()->query("
    SELECT products.*, categories.name AS category_name
    FROM products
    JOIN categories ON categories.id = products.category_id
    ORDER BY products.stock ASC, products.name
")->fetchAll();

$logs = db()->query('SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100')->fetchAll();
$currentAdmin = current_user();

$pageTitle = 'Reports';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="admin-page-head">
    <div>
        <p class="eyebrow">Reports</p>
        <h1>Inventory and audit log</h1>
        <p>Currently logged in: <?= h($currentAdmin['complete_name']) ?>. All important system activities are listed below.</p>
    </div>
    <a class="button ghost" href="<?= h(url('admin/dashboard.php')) ?>">Back to dashboard</a>
</section>

<section class="summary-table">
    <h2>Remaining inventory report</h2>
    <table>
        <thead>
        <tr>
            <th>SKU</th>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Remaining</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($inventory as $product): ?>
            <tr>
                <td><?= h($product['sku']) ?></td>
                <td><?= h($product['name']) ?></td>
                <td><?= h($product['category_name']) ?></td>
                <td><?= money((float) $product['price']) ?></td>
                <td class="<?= (int) $product['stock'] <= 10 ? 'low-stock' : '' ?>"><?= (int) $product['stock'] ?></td>
                <td><?= (int) $product['is_active'] === 1 ? 'Active' : 'Hidden' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="summary-table">
    <h2>Audit log report</h2>
    <table>
        <thead>
        <tr>
            <th>Date and time</th>
            <th>Actor</th>
            <th>Role</th>
            <th>Action</th>
            <th>Details</th>
            <th>IP</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= h($log['created_at']) ?></td>
                <td><?= h($log['actor_name']) ?></td>
                <td><?= h($log['role']) ?></td>
                <td><?= h($log['action']) ?></td>
                <td><?= h($log['details']) ?></td>
                <td><?= h($log['ip_address']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
