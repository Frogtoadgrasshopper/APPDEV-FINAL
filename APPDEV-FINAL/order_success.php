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
    <span class="success-icon" aria-hidden="true">&#10003;</span>
    <p class="eyebrow">Order confirmed</p>
    <h1>Your campus look is on its way.</h1>
    <p>Thanks for ordering with CampusThread. Keep your order number <strong><?= h($order['order_no']) ?></strong> for reference.</p>
    <div class="order-status"><span>Payment status</span><strong><?= h($order['payment_status']) ?></strong></div>
</section>

<section class="summary-table">
    <h2>Ordered hoodies</h2>
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>Size</th>
            <th>Qty</th>
            <th>Subtotal</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?= h($item['product_name']) ?></td>
                <td><?= h($item['selected_size']) ?></td>
                <td><?= (int) $item['quantity'] ?></td>
                <td><?= money((float) $item['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="receipt-total">
        <span>Order total</span>
        <strong><?= money((float) $order['total']) ?></strong>
    </div>
    <div class="success-actions">
        <a class="button primary" href="<?= h(url('store.php')) ?>">Continue shopping</a>
        <a class="button secondary" href="<?= h(url('index.php')) ?>">Back to home</a>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
