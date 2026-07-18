<?php require_once __DIR__ . '/init.php'; ?>
</main>
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a class="brand brand-light" href="<?= h(url('index.php')) ?>">
                <img src="<?= h(url('assets/img/logo.svg')) ?>" alt="">
                <span><strong>CampusThread</strong><small>University Hoodie Co.</small></span>
            </a>
            <p>Comfortable university hoodies made for lectures, org nights, and everything in between.</p>
        </div>
        <div class="footer-links">
            <div>
                <strong>Shop</strong>
                <a href="<?= h(url('store.php')) ?>">All hoodies</a>
                <a href="<?= h(url('cart.php')) ?>">Your cart</a>
                <a href="<?= h(url('about.php')) ?>">Our story</a>
            </div>
            <div>
                <strong>Account</strong>
                <?php if (current_user()): ?>
                    <a href="<?= h(url('logout.php')) ?>">Log out</a>
                <?php else: ?>
                    <a href="<?= h(url('login.php')) ?>">Log in</a>
                    <a href="<?= h(url('register.php')) ?>">Create account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> <?= h(GROUP_NAME) ?> &middot; Group <?= h(GROUP_NUMBER) ?></span>
        <p>This website is for educational purposes only and is a requirement for our final project.</p>
    </div>
</footer>
<script src="<?= h(url('assets/js/app.js?v=' . filemtime(__DIR__ . '/../assets/js/app.js'))) ?>"></script>
</body>
</html>
