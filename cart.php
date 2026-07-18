<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$where, $ownerParams] = cart_owner_clause();
    $pdo = db();

    foreach (($_POST['quantities'] ?? []) as $itemId => $quantity) {
        $itemId = (int) $itemId;
        $quantity = (int) $quantity;

        $stmt = $pdo->prepare("
            SELECT cart_items.*, products.stock
            FROM cart_items
            JOIN products ON products.id = cart_items.product_id
            WHERE cart_items.id = ? AND $where
            LIMIT 1
        ");
        $stmt->execute(array_merge([$itemId], $ownerParams));
        $item = $stmt->fetch();

        if (!$item) {
            continue;
        }

        if ($quantity <= 0) {
            $pdo->prepare('DELETE FROM cart_items WHERE id = ?')->execute([$itemId]);
        } else {
            $pdo->prepare('UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
                ->execute([min($quantity, (int) $item['stock']), $itemId]);
        }
    }

    log_activity('Cart update', 'Updated cart quantities.');
    flash('success', 'Cart updated.');
    redirect('cart.php');
}

$items = get_cart_items();
$totals = cart_totals($items);

$pageTitle = 'Cart';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-title">
    <p class="eyebrow">Your bag</p>
    <h1>Almost campus-ready.</h1>
    <p>Review your hoodies and quantities before continuing.</p>
</section>

<ol class="checkout-steps" aria-label="Checkout progress">
    <li class="active"><span>1</span> Cart</li>
    <li><span>2</span> Delivery</li>
    <li><span>3</span> Payment</li>
</ol>

<?php if (!$items): ?>
    <section class="empty-state">
        <span class="empty-icon" aria-hidden="true">CT</span>
        <h2>Your bag is waiting for a hoodie</h2>
        <p>Browse the campus collection and find a design that feels like yours.</p>
        <a class="button primary button-lg" href="<?= h(url('store.php')) ?>">Explore hoodies <span aria-hidden="true">&rarr;</span></a>
    </section>
<?php else: ?>
    <form method="post" class="cart-layout">
        <?= csrf_field() ?>
        <div class="cart-list">
            <?php foreach ($items as $item): ?>
                <article class="cart-row">
                    <div class="cart-image"><img src="<?= h(url($item['image_url'])) ?>" alt="<?= h($item['name']) ?>" loading="lazy"></div>
                    <div class="cart-details">
                        <span class="tag">Campus hoodie</span>
                        <h2><?= h($item['name']) ?></h2>
                        <p><?= h($item['color']) ?> &middot; Size <?= h($item['selected_size']) ?></p>
                        <span class="in-stock"><i></i> In stock and ready to order</span>
                    </div>
                    <label class="quantity-label">Quantity
                        <input type="number" name="quantities[<?= (int) $item['id'] ?>]" min="0" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['quantity'] ?>">
                        <small>Use 0 to remove</small>
                    </label>
                    <div class="cart-price">
                        <strong><?= money((float) $item['price'] * (int) $item['quantity']) ?></strong>
                        <small><?= money((float) $item['price']) ?> each</small>
                    </div>
                </article>
            <?php endforeach; ?>
            <a class="text-link dark continue-shopping" href="<?= h(url('store.php')) ?>"><span aria-hidden="true">&larr;</span> Continue shopping</a>
        </div>
        <aside class="summary-panel">
            <p class="eyebrow">Your total</p>
            <h2>Order summary</h2>
            <div><span>Subtotal <small><?= cart_count() ?> item<?= cart_count() === 1 ? '' : 's' ?></small></span><strong><?= money($totals['subtotal']) ?></strong></div>
            <div><span>Standard delivery</span><strong><?= money($totals['delivery_fee']) ?></strong></div>
            <div class="total"><span>Total</span><strong><?= money($totals['total']) ?></strong></div>
            <a class="button primary full button-lg" href="<?= h(url('checkout.php')) ?>">
                <?= current_user() ? 'Continue to delivery' : 'Log in to checkout' ?> <span aria-hidden="true">&rarr;</span>
            </a>
            <button class="button subtle full" type="submit">Update quantities</button>
            <p class="summary-note"><span aria-hidden="true">&#10003;</span> <?= current_user() ? 'Your items are reserved after checkout' : 'An account keeps your order details secure' ?></p>
        </aside>
    </form>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
