<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$users = db()->query('SELECT * FROM users ORDER BY role DESC, complete_name')->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="admin-page-head">
    <div>
        <p class="eyebrow">System admin</p>
        <h1>Users and roles</h1>
        <p>Add or modify users who can perform admin roles.</p>
    </div>
    <a class="button primary" href="<?= h(url('admin/user_form.php')) ?>">Add user</a>
</section>

<section class="summary-table">
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= h($user['complete_name']) ?></td>
                <td><?= h($user['email']) ?></td>
                <td><span class="tag"><?= h($user['role']) ?></span></td>
                <td><?= h($user['status']) ?></td>
                <td><?= (int) $user['email_verified'] === 1 ? 'Verified' : 'Pending' ?></td>
                <td><a href="<?= h(url('admin/user_form.php?id=' . (int) $user['id'])) ?>">Modify</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
