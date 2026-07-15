<?php
require_once __DIR__ . '/includes/init.php';

$user = current_user();
if ($user) {
    log_activity('Logout', 'User logged out.', $user);
}

session_unset();
session_destroy();
session_start();
flash('success', 'You have been logged out.');
redirect('index.php');
