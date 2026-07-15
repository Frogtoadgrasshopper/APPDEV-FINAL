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
<section class="form-shell narrow">
    <div class="form-panel">
        <p class="eyebrow">Account access</p>
        <h1>Log in</h1>
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
                <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
            </label>
            <label>Password
                <input type="password" name="password" required>
            </label>
            <button class="button primary full" type="submit">Login</button>
        </form>
        <p class="muted">Need an account? <a href="<?= h(url('register.php')) ?>">Register here</a>.</p>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
