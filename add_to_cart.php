<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('store.php');
}

verify_csrf();

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$selectedSize = strtoupper(trim($_POST['size'] ?? ''));

$stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product || (int) $product['stock'] <= 0) {
    flash('error', 'That hoodie is not available.');
    redirect('store.php');
}

$availableSizes = product_sizes($product['size']);
if ($selectedSize === '' || !in_array($selectedSize, $availableSizes, true)) {
    flash('error', 'Please choose an available hoodie size.');
    redirect('store.php');
}

$quantity = min($quantity, (int) $product['stock']);
$pdo = db();
$user = current_user();

if ($user) {
    $existing = $pdo->prepare('SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? AND selected_size = ? LIMIT 1');
    $existing->execute([$user['id'], $productId, $selectedSize]);
} else {
    $existing = $pdo->prepare('SELECT * FROM cart_items WHERE session_id = ? AND product_id = ? AND selected_size = ? LIMIT 1');
    $existing->execute([$_SESSION['cart_token'], $productId, $selectedSize]);
}

$cartItem = $existing->fetch();

if ($cartItem) {
    $newQuantity = min((int) $cartItem['quantity'] + $quantity, (int) $product['stock']);
    $pdo->prepare('UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
        ->execute([$newQuantity, $cartItem['id']]);
} else {
    $pdo->prepare('INSERT INTO cart_items (user_id, session_id, product_id, selected_size, quantity) VALUES (?, ?, ?, ?, ?)')
        ->execute([
            $user['id'] ?? null,
            $user ? null : $_SESSION['cart_token'],
            $productId,
            $selectedSize,
            $quantity,
        ]);
}

log_activity('Cart add', 'Added ' . $product['name'] . " in size $selectedSize to cart.");
flash('success', $product['name'] . " in size $selectedSize was added to your cart.");
redirect('cart.php');
