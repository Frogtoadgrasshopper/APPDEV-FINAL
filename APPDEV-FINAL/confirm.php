<?php
require_once __DIR__ . '/includes/init.php';

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    flash('error', 'Confirmation token is missing.');
    redirect('login.php');
}

$stmt = db()->prepare('SELECT * FROM users WHERE email_token = ? LIMIT 1');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    flash('error', 'Confirmation link is invalid or already used.');
    redirect('login.php');
}

db()->prepare('UPDATE users SET email_verified = 1, email_token = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
    ->execute([$user['id']]);

log_activity('Email confirmation', 'Buyer confirmed the registered email address.', $user);
flash('success', 'Your email is confirmed. You may now log in.');
redirect('login.php');
