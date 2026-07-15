<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$stats = [
    'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'stock' => (int) db()->query('SELECT COALESCE(SUM(stock), 0) FROM products WHERE is_active = 1')->fetchColumn(),
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
];

$recentLogs = db()->query('SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 6')->fetchAll();

$pageTitle = 'Seller Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="admin-hero">
    <div>
        <p class="eyebrow">Seller part</p>
        <h1>Admin dashboard</h1>
        <p>Manage hoodie stock, prices, admin users, inventory reports, and audit logs.</p>
    </div>
    <div class="admin-actions">
        <a class="button primary" href="<?= h(url('admin/products.php')) ?>">Manage stock</a>
        <a class="button ghost" href="<?= h(url('admin/reports.php')) ?>">View reports</a>
    </div>
</section>

<section class="stat-grid">
    <article><span>Users</span><strong><?= $stats['users'] ?></strong></article>
    <article><span>Products</span><strong><?= $stats['products'] ?></strong></article>
    <article><span>Active stock</span><strong><?= $stats['stock'] ?></strong></article>
    <article><span>Orders</span><strong><?= $stats['orders'] ?></strong></article>
</section>

<section class="admin-grid">
    <article class="admin-card">
        <h2>User admin</h2>
        <p>Add or modify users and assign buyer, admin, or super admin roles.</p>
        <a href="<?= h(url('admin/users.php')) ?>">Open users</a>
    </article>
    <article class="admin-card">
        <h2>Stock admin</h2>
        <p>Add hoodie products, update prices, change quantities, and hide unavailable stock.</p>
        <a href="<?= h(url('admin/products.php')) ?>">Open products</a>
    </article>
    <article class="admin-card">
        <h2>Reports</h2>
        <p>Review remaining inventory and check the audit trail of system activities.</p>
        <a href="<?= h(url('admin/reports.php')) ?>">Open reports</a>
    </article>
</section>

<section class="summary-table">
    <h2>Recent audit activity</h2>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Details</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recentLogs as $log): ?>
            <tr>
                <td><?= h($log['created_at']) ?></td>
                <td><?= h($log['actor_name']) ?></td>
                <td><?= h($log['action']) ?></td>
                <td><?= h($log['details']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
