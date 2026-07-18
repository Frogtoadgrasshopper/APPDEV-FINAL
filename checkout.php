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
    <p class="eyebrow">Secure checkout</p>
    <h1>Where should we send your hoodies?</h1>
    <p>Confirm your contact and delivery details below.</p>
</section>

<ol class="checkout-steps" aria-label="Checkout progress">
    <li class="complete"><span>&#10003;</span> Cart</li>
    <li class="active"><span>2</span> Delivery</li>
    <li><span>3</span> Payment</li>
</ol>

<section class="checkout-layout">
    <div class="form-panel">
        <div class="panel-heading">
            <div><span class="panel-number">2</span><div><h2>Delivery information</h2><p>We will use these details for your order.</p></div></div>
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
            <label>Full name
                <input type="text" name="customer_name" value="<?= h($checkout['customer_name']) ?>" autocomplete="name" required>
            </label>
            <label>Email address
                <input type="email" name="email" value="<?= h($checkout['email']) ?>" autocomplete="email" required>
                <small>We will send your order details here.</small>
            </label>
            <label>Complete delivery address
                <textarea name="shipping_address" rows="4" autocomplete="street-address" required><?= h($checkout['shipping_address']) ?></textarea>
            </label>
            <label>Contact number
                <input type="tel" name="contact_numbers" value="<?= h($checkout['contact_numbers']) ?>" autocomplete="tel" required>
            </label>
            <button class="button primary full button-lg" type="submit">Continue to payment <span aria-hidden="true">&rarr;</span></button>
            <a class="text-link dark form-back" href="<?= h(url('cart.php')) ?>"><span aria-hidden="true">&larr;</span> Back to cart</a>
        </form>
    </div>
    <aside class="summary-panel">
        <p class="eyebrow">Your order</p>
        <h2>Order summary</h2>
        <div><span>Items</span><strong><?= cart_count() ?></strong></div>
        <div><span>Subtotal</span><strong><?= money($totals['subtotal']) ?></strong></div>
        <div><span>Standard delivery</span><strong><?= money($totals['delivery_fee']) ?></strong></div>
        <div class="total"><span>Total</span><strong><?= money($totals['total']) ?></strong></div>
        <p class="summary-note"><span aria-hidden="true">&#10003;</span> Simple, secure checkout</p>
    </aside>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
