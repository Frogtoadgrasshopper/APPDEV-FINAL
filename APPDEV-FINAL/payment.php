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
                    INSERT INTO order_items (order_id, product_id, product_name, selected_size, price, quantity, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['selected_size'],
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
    <p class="eyebrow">Final step</p>
    <h1>Choose how you would like to pay.</h1>
    <p>Select the option that is most convenient for you.</p>
</section>

<ol class="checkout-steps" aria-label="Checkout progress">
    <li class="complete"><span>&#10003;</span> Cart</li>
    <li class="complete"><span>&#10003;</span> Delivery</li>
    <li class="active"><span>3</span> Payment</li>
</ol>

<section class="checkout-layout">
    <div class="form-panel">
        <div class="panel-heading">
            <div><span class="panel-number">3</span><div><h2>Payment method</h2><p>No online payment details are required.</p></div></div>
        </div>
        <?php if ($errors): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?= h($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="stacked-form">
            <?= csrf_field() ?>
            <fieldset class="choice-list payment-choices">
                <legend class="sr-only">Payment method</legend>
                <label><input type="radio" name="payment_method" value="Cash on Delivery" required><span class="choice-mark"></span><span><strong>Cash on Delivery</strong><small>Pay when your order arrives</small></span><b>COD</b></label>
                <label><input type="radio" name="payment_method" value="Campus Pickup Payment"><span class="choice-mark"></span><span><strong>Pay at Campus Pickup</strong><small>Settle your total when you collect</small></span><b>PICKUP</b></label>
                <label><input type="radio" name="payment_method" value="Bank Transfer Reservation"><span class="choice-mark"></span><span><strong>Bank Transfer Reservation</strong><small>Reserve now and receive transfer instructions</small></span><b>BANK</b></label>
            </fieldset>
            <button class="button primary full button-lg" type="submit">Place my order <span aria-hidden="true">&rarr;</span></button>
            <a class="text-link dark form-back" href="<?= h(url('checkout.php')) ?>"><span aria-hidden="true">&larr;</span> Back to delivery</a>
        </form>
    </div>
    <aside class="summary-panel">
        <p class="eyebrow">Your order</p>
        <h2>Payment summary</h2>
        <div><span>Subtotal</span><strong><?= money($totals['subtotal']) ?></strong></div>
        <div><span>Standard delivery</span><strong><?= money($totals['delivery_fee']) ?></strong></div>
        <div class="total"><span>Total</span><strong><?= money($totals['total']) ?></strong></div>
        <p class="demo-note">You will pay using the option selected above. No card details are stored on this website.</p>
    </aside>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
