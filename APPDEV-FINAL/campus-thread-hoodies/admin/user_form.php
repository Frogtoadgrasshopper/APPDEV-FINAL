<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$editing = $id > 0;
$errors = [];

if ($editing) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $userRecord = $stmt->fetch();
    if (!$userRecord) {
        flash('error', 'User not found.');
        redirect('admin/users.php');
    }
} else {
    $userRecord = [
        'complete_name' => '',
        'email' => '',
        'complete_address' => '',
        'contact_numbers' => '',
        'role' => 'buyer',
        'status' => 'active',
        'email_verified' => 1,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $completeName = trim($_POST['complete_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $completeAddress = trim($_POST['complete_address'] ?? '');
    $contactNumbers = trim($_POST['contact_numbers'] ?? '');
    $role = $_POST['role'] ?? 'buyer';
    $status = $_POST['status'] ?? 'active';
    $emailVerified = isset($_POST['email_verified']) ? 1 : 0;

    if ($completeName === '') {
        $errors[] = 'Complete name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (!$editing && strlen($password) < 8) {
        $errors[] = 'Password is required and must be at least 8 characters.';
    }
    if ($editing && $password !== '' && strlen($password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if (!in_array($role, ['buyer', 'admin', 'super_admin'], true)) {
        $errors[] = 'Selected role is invalid.';
    }
    if (!in_array($status, ['active', 'inactive'], true)) {
        $errors[] = 'Selected status is invalid.';
    }

    $duplicate = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $duplicate->execute([$email, $id]);
    if ($email !== '' && $duplicate->fetch()) {
        $errors[] = 'Another user already has that email address.';
    }

    if ((int) (current_user()['id'] ?? 0) === $id && $status !== 'active') {
        $errors[] = 'You cannot deactivate your own account while logged in.';
    }

    if (!$errors) {
        if ($editing) {
            $fields = [
                'complete_name = ?',
                'email = ?',
                'complete_address = ?',
                'contact_numbers = ?',
                'role = ?',
                'status = ?',
                'email_verified = ?',
                'updated_at = CURRENT_TIMESTAMP',
            ];
            $params = [$completeName, $email, $completeAddress, $contactNumbers, $role, $status, $emailVerified];

            if ($password !== '') {
                $fields[] = 'password_hash = ?';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $params[] = $id;
            db()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
            log_activity('User modified', "Updated user $email.");
            flash('success', 'User updated.');
        } else {
            db()->prepare("
                INSERT INTO users (complete_name, email, password_hash, complete_address, contact_numbers, role, status, email_verified)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $completeName,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $completeAddress,
                $contactNumbers,
                $role,
                $status,
                $emailVerified,
            ]);
            log_activity('User added', "Added user $email with role $role.");
            flash('success', 'User added.');
        }

        redirect('admin/users.php');
    }

    $userRecord = array_merge($userRecord, [
        'complete_name' => $completeName,
        'email' => $email,
        'complete_address' => $completeAddress,
        'contact_numbers' => $contactNumbers,
        'role' => $role,
        'status' => $status,
        'email_verified' => $emailVerified,
    ]);
}

$pageTitle = $editing ? 'Modify User' : 'Add User';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-shell">
    <div class="form-panel">
        <p class="eyebrow">System admin</p>
        <h1><?= $editing ? 'Modify user' : 'Add user' ?></h1>
        <?php if ($errors): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?= h($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="stacked-form">
            <?= csrf_field() ?>
            <label>Complete name
                <input type="text" name="complete_name" value="<?= h($userRecord['complete_name']) ?>" required>
            </label>
            <label>Email address
                <input type="email" name="email" value="<?= h($userRecord['email']) ?>" required>
            </label>
            <label><?= $editing ? 'New password (leave blank to keep current)' : 'Password' ?>
                <input type="password" name="password" <?= $editing ? '' : 'required' ?> minlength="8">
            </label>
            <div class="form-grid">
                <label>Role
                    <select name="role">
                        <option value="buyer" <?= $userRecord['role'] === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                        <option value="admin" <?= $userRecord['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="super_admin" <?= $userRecord['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                </label>
                <label>Status
                    <select name="status">
                        <option value="active" <?= $userRecord['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $userRecord['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </label>
            </div>
            <label>Complete address
                <textarea name="complete_address" rows="3"><?= h($userRecord['complete_address']) ?></textarea>
            </label>
            <label>Contact numbers
                <input type="text" name="contact_numbers" value="<?= h($userRecord['contact_numbers']) ?>">
            </label>
            <label class="check-line">
                <input type="checkbox" name="email_verified" value="1" <?= (int) $userRecord['email_verified'] === 1 ? 'checked' : '' ?>>
                Email verified
            </label>
            <div class="button-row">
                <button class="button primary" type="submit">Save user</button>
                <a class="button ghost" href="<?= h(url('admin/users.php')) ?>">Cancel</a>
            </div>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
