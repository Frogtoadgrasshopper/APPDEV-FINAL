<?php
require_once __DIR__ . '/init.php';

$pageTitle = $pageTitle ?? APP_NAME;
$currentUser = current_user();
$cartCount = cart_count();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= h(url('assets/css/styles.css')) ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= h(url('index.php')) ?>" aria-label="<?= h(APP_NAME) ?> home">
        <img src="<?= h(url('assets/img/logo.svg')) ?>" alt="<?= h(GROUP_NAME) ?> logo">
        <span>
            <strong><?= h(GROUP_NAME) ?></strong>
            <small>University Hoodie Shop</small>
        </span>
    </a>
    <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open menu">Menu</button>
    <nav class="main-nav" data-main-nav>
        <a href="<?= h(url('index.php')) ?>">Home</a>
        <a href="<?= h(url('store.php')) ?>">Store</a>
        <a href="<?= h(url('cart.php')) ?>">Cart <span class="cart-pill"><?= $cartCount ?></span></a>
        <a href="<?= h(url('about.php')) ?>">About</a>
        <?php if ($currentUser): ?>
            <?php if (is_admin($currentUser)): ?>
                <a href="<?= h(url('admin/dashboard.php')) ?>">Seller Admin</a>
            <?php endif; ?>
            <a href="<?= h(url('logout.php')) ?>">Logout</a>
        <?php else: ?>
            <a href="<?= h(url('login.php')) ?>">Login</a>
            <a class="nav-cta" href="<?= h(url('register.php')) ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main>
<?php if ($message = flash('success')): ?>
    <div class="flash success"><?= h($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="flash error"><?= h($message) ?></div>
<?php endif; ?>
