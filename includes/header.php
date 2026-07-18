<?php
require_once __DIR__ . '/init.php';

$pageTitle = $pageTitle ?? APP_NAME;
$currentUser = current_user();
$cartCount = cart_count();
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

function nav_link_class(string $page, string $currentPage): string
{
    return $page === $currentPage ? 'nav-link active' : 'nav-link';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= h(url('assets/css/styles.css?v=' . filemtime(__DIR__ . '/../assets/css/styles.css'))) ?>">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<div class="announcement-bar">
    <div class="announcement-inner">
        <span>Made for campus life</span>
        <span class="announcement-divider" aria-hidden="true"></span>
        <span>Campus pickup available</span>
        <a href="<?= h(url('store.php')) ?>">Shop the collection <span aria-hidden="true">&rarr;</span></a>
    </div>
</div>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= h(url('index.php')) ?>" aria-label="<?= h(APP_NAME) ?> home">
            <img src="<?= h(url('assets/img/logo.svg')) ?>" alt="">
            <span>
                <strong>CampusThread</strong>
                <small>University Hoodie Co.</small>
            </span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" data-main-nav aria-label="Main navigation">
            <a class="<?= nav_link_class('index.php', $currentPage) ?>" href="<?= h(url('index.php')) ?>" <?= $currentPage === 'index.php' ? 'aria-current="page"' : '' ?>>Home</a>
            <a class="<?= nav_link_class('store.php', $currentPage) ?>" href="<?= h(url('store.php')) ?>" <?= $currentPage === 'store.php' ? 'aria-current="page"' : '' ?>>Shop</a>
            <a class="<?= nav_link_class('about.php', $currentPage) ?>" href="<?= h(url('about.php')) ?>" <?= $currentPage === 'about.php' ? 'aria-current="page"' : '' ?>>Our story</a>
            <?php if ($currentUser && is_admin($currentUser)): ?>
                <a class="nav-link" href="<?= h(url('admin/dashboard.php')) ?>">Admin</a>
            <?php endif; ?>
            <span class="nav-spacer" aria-hidden="true"></span>
            <?php if ($currentUser): ?>
                <span class="nav-welcome">Hi, <?= h(explode(' ', trim($currentUser['complete_name']))[0]) ?></span>
                <a class="nav-link" href="<?= h(url('logout.php')) ?>">Log out</a>
            <?php else: ?>
                <a class="nav-link" href="<?= h(url('login.php')) ?>">Log in</a>
                <a class="nav-cta" href="<?= h(url('register.php')) ?>">Create account</a>
            <?php endif; ?>
            <a class="cart-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>" href="<?= h(url('cart.php')) ?>" aria-label="Cart with <?= $cartCount ?> item<?= $cartCount === 1 ? '' : 's' ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.3 10.1a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H7M10 20h.01M17 20h.01"/></svg>
                <span>Cart</span>
                <span class="cart-pill"><?= $cartCount ?></span>
            </a>
        </nav>
    </div>
</header>
<main id="main-content">
<?php if ($message = flash('success')): ?>
    <div class="flash success"><?= h($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="flash error"><?= h($message) ?></div>
<?php endif; ?>
