<?php
require_once __DIR__ . '/includes/init.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = ? LIMIT 1');
    $stmt->execute([$email, 'active']);
    $user = $stmt->fetch();

    if (!$user || !password_matches($password, $user['password_hash'])) {
        $errors[] = 'Invalid email or password.';
    } elseif ((int) $user['email_verified'] !== 1) {
        $errors[] = 'Please confirm your email before logging in.';
    } else {
        $_SESSION['user_id'] = (int) $user['id'];
        merge_guest_cart_into_user((int) $user['id']);
        log_activity('Login', 'User logged in.', $user);
        flash('success', 'Welcome back, ' . $user['complete_name'] . '.');
        redirect(is_admin($user) ? 'admin/dashboard.php' : 'store.php');
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-shell">
    <aside class="auth-promo">
        <p class="eyebrow">Welcome back</p>
        <h1>Your next campus favorite is waiting.</h1>
        <p>Log in to continue checkout and keep your order connected to your account.</p>
        <ul>
            <li><span>&#10003;</span> Faster delivery details</li>
            <li><span>&#10003;</span> Secure account access</li>
            <li><span>&#10003;</span> Easy campus ordering</li>
        </ul>
        <img src="<?= h(url('assets/img/hoodie-business-green.png')) ?>" alt="Forest green university hoodie">
    </aside>
    <div class="form-panel auth-panel">
        <p class="eyebrow">Account access</p>
        <h2>Log in to CampusThread</h2>
        <p class="muted">Good to see you again. Enter your details below.</p>
        <?php if ($errors): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?= h($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="stacked-form">
            <?= csrf_field() ?>
            <label>Email address
                <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" autocomplete="email" placeholder="you@example.com" required>
            </label>
            <label>Password
                <input type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
            </label>
            <button class="button primary full button-lg" type="submit">Log in <span aria-hidden="true">&rarr;</span></button>
        </form>
        <p class="auth-switch">New to CampusThread? <a href="<?= h(url('register.php')) ?>">Create an account</a></p>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
