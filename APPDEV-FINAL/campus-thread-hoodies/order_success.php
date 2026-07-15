<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int) ($_SESSION['last_order_id'] ?? 0);
if ($orderId <= 0) {
    redirect('store.php');
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$orderId, current_user()['id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('store.php');
}

$items = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
$items->execute([$orderId]);
$orderItems = $items->fetchAll();

$pageTitle = 'Order Complete';
require_once __DIR__ . '/includes/header.php';
?>
<section class="success-page">
    <p class="eyebrow">Order complete</p>
    <h1>Thank you for ordering.</h1>
    <p>Your order number is <strong><?= h($order['order_no']) ?></strong>. Payment status is currently <strong><?= h($order['payment_status']) ?></strong>.</p>
</section>

<section class="summary-table">
    <h2>Ordered hoodies</h2>
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Subtotal</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?= h($item['product_name']) ?></td>
                <td><?= (int) $item['quantity'] ?></td>
                <td><?= money((float) $item['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="receipt-total">
        <span>Total paid later</span>
        <strong><?= money((float) $order['total']) ?></strong>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
