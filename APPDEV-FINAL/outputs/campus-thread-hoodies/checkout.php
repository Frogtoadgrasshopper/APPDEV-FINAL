<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$items = get_cart_items();
if (!$items) {
    flash('error', 'Your cart is empty.');
    redirect('store.php');
}

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $customerName = trim($_POST['customer_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $contactNumbers = trim($_POST['contact_numbers'] ?? '');

    if ($customerName === '') {
        $errors[] = 'Customer name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if ($shippingAddress === '') {
        $errors[] = 'Shipping address is required.';
    }
    if ($contactNumbers === '') {
        $errors[] = 'Contact numbers are required.';
    }

    if (!$errors) {
        $_SESSION['checkout'] = [
            'customer_name' => $customerName,
            'email' => $email,
            'shipping_address' => $shippingAddress,
            'contact_numbers' => $contactNumbers,
        ];
        log_activity('Checkout details', 'Buyer submitted checkout delivery details.');
        redirect('payment.php');
    }
}

$checkout = $_SESSION['checkout'] ?? [
    'customer_name' => $user['complete_name'],
    'email' => $user['email'],
    'shipping_address' => $user['complete_address'],
    'contact_numbers' => $user['contact_numbers'],
];

$totals = cart_totals($items);
$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-title">
    <p class="eyebrow">Checkout</p>
    <h1>Delivery details</h1>
    <p>Confirm where your university hoodie order should be delivered.</p>
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
            <label>Customer name
                <input type="text" name="customer_name" value="<?= h($checkout['customer_name']) ?>" required>
            </label>
            <label>Email address
                <input type="email" name="email" value="<?= h($checkout['email']) ?>" required>
            </label>
            <label>Complete shipping address
                <textarea name="shipping_address" rows="4" required><?= h($checkout['shipping_address']) ?></textarea>
            </label>
            <label>Contact numbers
                <input type="text" name="contact_numbers" value="<?= h($checkout['contact_numbers']) ?>" required>
            </label>
            <button class="button primary full" type="submit">Continue to payment</button>
        </form>
    </div>
    <aside class="summary-panel">
        <h2>Cart total</h2>
        <div><span>Items</span><strong><?= cart_count() ?></strong></div>
        <div><span>Subtotal</span><strong><?= money($totals['subtotal']) ?></strong></div>
        <div><span>Delivery fee</span><strong><?= money($totals['delivery_fee']) ?></strong></div>
        <div class="total"><span>Total</span><strong><?= money($totals['total']) ?></strong></div>
    </aside>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
