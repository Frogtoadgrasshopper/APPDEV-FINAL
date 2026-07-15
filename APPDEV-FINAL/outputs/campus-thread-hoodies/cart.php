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
    <p class="eyebrow">Buyer cart</p>
    <h1>Your hoodie cart</h1>
    <p>Review quantities before checkout.</p>
</section>

<?php if (!$items): ?>
    <section class="empty-state">
        <h2>Your cart is empty</h2>
        <p>Add a university hoodie from the store to begin.</p>
        <a class="button primary" href="<?= h(url('store.php')) ?>">Go to store</a>
    </section>
<?php else: ?>
    <form method="post" class="cart-layout">
        <?= csrf_field() ?>
        <div class="cart-list">
            <?php foreach ($items as $item): ?>
                <article class="cart-row">
                    <img src="<?= h(url($item['image_url'])) ?>" alt="<?= h($item['name']) ?>">
                    <div>
                        <h2><?= h($item['name']) ?></h2>
                        <p><?= h($item['color']) ?> / <?= h($item['size']) ?> / <?= money((float) $item['price']) ?></p>
                        <span><?= (int) $item['stock'] ?> in stock</span>
                    </div>
                    <label>Qty
                        <input type="number" name="quantities[<?= (int) $item['id'] ?>]" min="0" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['quantity'] ?>">
                    </label>
                    <strong><?= money((float) $item['price'] * (int) $item['quantity']) ?></strong>
                </article>
            <?php endforeach; ?>
        </div>
        <aside class="summary-panel">
            <h2>Order summary</h2>
            <div><span>Subtotal</span><strong><?= money($totals['subtotal']) ?></strong></div>
            <div><span>Delivery fee</span><strong><?= money($totals['delivery_fee']) ?></strong></div>
            <div class="total"><span>Total</span><strong><?= money($totals['total']) ?></strong></div>
            <button class="button ghost full" type="submit">Update cart</button>
            <a class="button primary full" href="<?= h(url('checkout.php')) ?>">Proceed to checkout</a>
        </aside>
    </form>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
