<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$items = get_cart_items();
if (!$items) {
    flash('error', 'Your cart is empty.');
    redirect('store.php');
}

if (empty($_SESSION['checkout'])) {
    flash('error', 'Please complete checkout details first.');
    redirect('checkout.php');
}

$checkout = $_SESSION['checkout'];
$totals = cart_totals($items);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $paymentMethod = trim($_POST['payment_method'] ?? '');

    if ($paymentMethod === '') {
        $errors[] = 'Please select a payment method.';
    }

    if (!$errors) {
        $pdo = db();

        try {
            $pdo->beginTransaction();
            $orderNo = 'CT-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $orderStmt = $pdo->prepare("
                INSERT INTO orders (user_id, order_no, customer_name, email, shipping_address, contact_numbers, subtotal, delivery_fee, total, payment_method)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $orderStmt->execute([
                current_user()['id'],
                $orderNo,
                $checkout['customer_name'],
                $checkout['email'],
                $checkout['shipping_address'],
                $checkout['contact_numbers'],
                $totals['subtotal'],
                $totals['delivery_fee'],
                $totals['total'],
                $paymentMethod,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            foreach ($items as $item) {
                $productStmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
                $productStmt->execute([$item['product_id']]);
                $product = $productStmt->fetch();

                if (!$product || (int) $product['stock'] < (int) $item['quantity']) {
                    throw new RuntimeException('Insufficient stock for ' . $item['name'] . '.');
                }

                $lineTotal = (float) $item['price'] * (int) $item['quantity'];
                $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?)
                ")->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $lineTotal,
                ]);

                $pdo->prepare('UPDATE products SET stock = stock - ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
                    ->execute([$item['quantity'], $item['product_id']]);
            }

            [$where, $ownerParams] = cart_owner_clause();
            $pdo->prepare("DELETE FROM cart_items WHERE $where")->execute($ownerParams);
            $pdo->commit();

            unset($_SESSION['checkout']);
            $_SESSION['last_order_id'] = $orderId;
            log_activity('Payment recorded', "Buyer placed order $orderNo using $paymentMethod.");
            flash('success', 'Order placed successfully.');
            redirect('order_success.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Payment';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-title">
    <p class="eyebrow">Payment page</p>
    <h1>Select payment method</h1>
    <p>No payment API is connected yet. This page records the selected manual payment option for the project demo.</p>
</section>

<section class="checkout-layout">
    <div class="form-panel">
        <?php if ($errors): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?= h($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="stacked-form">
            <?= csrf_field() ?>
            <fieldset class="choice-list">
                <legend>Payment method</legend>
                <label><input type="radio" name="payment_method" value="Cash on Delivery" required> Cash on Delivery</label>
                <label><input type="radio" name="payment_method" value="Campus Pickup Payment"> Campus Pickup Payment</label>
                <label><input type="radio" name="payment_method" value="Bank Transfer Reservation"> Bank Transfer Reservation</label>
            </fieldset>
            <button class="button primary full" type="submit">Place order</button>
        </form>
    </div>
    <aside class="summary-panel">
        <h2>Payment summary</h2>
        <div><span>Subtotal</span><strong><?= money($totals['subtotal']) ?></strong></div>
        <div><span>Delivery fee</span><strong><?= money($totals['delivery_fee']) ?></strong></div>
        <div class="total"><span>Total</span><strong><?= money($totals['total']) ?></strong></div>
    </aside>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
