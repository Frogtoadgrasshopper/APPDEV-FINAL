<?php
require_once __DIR__ . '/includes/init.php';

$errors = [];
$confirmationLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $completeName = trim($_POST['complete_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $completeAddress = trim($_POST['complete_address'] ?? '');
    $contactNumbers = trim($_POST['contact_numbers'] ?? '');

    if ($completeName === '') {
        $errors[] = 'Complete name is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password and confirm password must match.';
    }

    if ($completeAddress === '') {
        $errors[] = 'Complete address is required.';
    }

    if ($contactNumbers === '') {
        $errors[] = 'Contact numbers are required.';
    }

    $existing = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $existing->execute([$email]);
    if ($email !== '' && $existing->fetch()) {
        $errors[] = 'That email address is already registered.';
    }

    if (!$errors) {
        $token = bin2hex(random_bytes(32));
        $stmt = db()->prepare("
            INSERT INTO users (complete_name, email, password_hash, complete_address, contact_numbers, role, email_verified, email_token)
            VALUES (?, ?, ?, ?, ?, 'buyer', 0, ?)
        ");
        $stmt->execute([
            $completeName,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $completeAddress,
            $contactNumbers,
            $token,
        ]);

        $userId = (int) db()->lastInsertId();
        $confirmationLink = rtrim(APP_URL, '/') . url('confirm.php?token=' . urlencode($token));
        $sent = send_confirmation_email($email, $completeName, $token);
        log_activity('Buyer registration', "New buyer account registered for $email.", [
            'id' => $userId,
            'complete_name' => $completeName,
            'role' => 'buyer',
        ]);

        if ($sent) {
            flash('success', 'Registration successful. Please check your email to confirm your account.');
            redirect('login.php');
        }

        flash('success', 'Registration successful. This local server could not send email, so use the confirmation link shown below.');
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-shell register-shell">
    <aside class="auth-promo">
        <p class="eyebrow">Join CampusThread</p>
        <h1>Campus comfort starts here.</h1>
        <p>Create an account once, then enjoy a smoother checkout whenever a new design catches your eye.</p>
        <ul>
            <li><span>&#10003;</span> Save delivery details</li>
            <li><span>&#10003;</span> Checkout with confidence</li>
            <li><span>&#10003;</span> Join the campus community</li>
        </ul>
        <img src="<?= h(url('assets/img/hoodie-engineering-maroon.png')) ?>" alt="Maroon university hoodie">
    </aside>
    <div class="form-panel auth-panel">
        <p class="eyebrow">Create an account</p>
        <h2>Let us get you campus-ready.</h2>
        <p class="muted">We will ask you to confirm your email before your first login.</p>

        <?php if ($errors): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?= h($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($confirmationLink): ?>
            <div class="notice success">
                <p>Local confirmation link:</p>
                <a href="<?= h($confirmationLink) ?>"><?= h($confirmationLink) ?></a>
            </div>
        <?php endif; ?>

        <form method="post" class="stacked-form">
            <?= csrf_field() ?>
            <label>Full name
                <input type="text" name="complete_name" value="<?= h($_POST['complete_name'] ?? '') ?>" autocomplete="name" placeholder="Your full name" required>
            </label>
            <label>Email address
                <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" autocomplete="email" placeholder="you@example.com" required>
            </label>
            <div class="form-grid">
                <label>Password
                    <input type="password" name="password" minlength="8" autocomplete="new-password" placeholder="At least 8 characters" required>
                </label>
                <label>Confirm password
                    <input type="password" name="confirm_password" minlength="8" autocomplete="new-password" placeholder="Repeat password" required>
                </label>
            </div>
            <label>Complete address
                <textarea name="complete_address" rows="3" autocomplete="street-address" placeholder="House/unit, street, barangay, city" required><?= h($_POST['complete_address'] ?? '') ?></textarea>
            </label>
            <label>Contact number
                <input type="tel" name="contact_numbers" value="<?= h($_POST['contact_numbers'] ?? '') ?>" autocomplete="tel" placeholder="09XX XXX XXXX" required>
            </label>
            <button class="button primary full button-lg" type="submit">Create my account <span aria-hidden="true">&rarr;</span></button>
        </form>
        <p class="auth-switch">Already have an account? <a href="<?= h(url('login.php')) ?>">Log in</a></p>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
