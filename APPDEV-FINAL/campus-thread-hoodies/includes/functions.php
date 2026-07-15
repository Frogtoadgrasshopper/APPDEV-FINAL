<?php
declare(strict_types=1);

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
}

function money(float $amount): string
{
    return 'PHP ' . number_format($amount, 2);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        flash('error', 'The form expired. Please try again.');
        redirect('index.php');
    }
}

function password_matches(string $password, string $storedHash): bool
{
    if (substr($storedHash, 0, 7) === 'sha256$') {
        return hash_equals(substr($storedHash, 7), hash('sha256', $password));
    }

    return password_verify($password, $storedHash);
}

function current_user(): ?array
{
    static $cachedUser = null;

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    if ($cachedUser !== null && (int) $cachedUser['id'] === (int) $_SESSION['user_id']) {
        return $cachedUser;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND status = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id'], 'active']);
    $cachedUser = $stmt->fetch() ?: null;

    return $cachedUser;
}

function require_login(): void
{
    if (!current_user()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'], true)) {
        flash('error', 'Admin access is required for that page.');
        redirect('login.php');
    }
}

function is_admin(?array $user = null): bool
{
    $user = $user ?: current_user();
    return $user && in_array($user['role'], ['admin', 'super_admin'], true);
}

function log_activity(string $action, string $details, ?array $actor = null): void
{
    $actor = $actor ?: current_user();
    $stmt = db()->prepare("
        INSERT INTO audit_logs (user_id, actor_name, role, action, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $actor['id'] ?? null,
        $actor['complete_name'] ?? 'Guest',
        $actor['role'] ?? 'guest',
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? 'local',
    ]);
}

function cart_owner_clause(): array
{
    $user = current_user();
    if ($user) {
        return ['user_id = ?', [$user['id']]];
    }

    return ['session_id = ?', [$_SESSION['cart_token']]];
}

function cart_count(): int
{
    [$where, $params] = cart_owner_clause();
    $stmt = db()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE $where");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function get_cart_items(): array
{
    [$where, $params] = cart_owner_clause();
    $stmt = db()->prepare("
        SELECT cart_items.*, products.name, products.price, products.stock, products.image_url, products.color, products.size
        FROM cart_items
        JOIN products ON products.id = cart_items.product_id
        WHERE $where
        ORDER BY cart_items.created_at DESC
    ");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function cart_totals(array $items): array
{
    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += (float) $item['price'] * (int) $item['quantity'];
    }

    $deliveryFee = $subtotal > 0 ? 80.0 : 0.0;
    return [
        'subtotal' => $subtotal,
        'delivery_fee' => $deliveryFee,
        'total' => $subtotal + $deliveryFee,
    ];
}

function merge_guest_cart_into_user(int $userId): void
{
    $sessionId = $_SESSION['cart_token'] ?? '';
    if ($sessionId === '') {
        return;
    }

    $pdo = db();
    $items = $pdo->prepare('SELECT product_id, quantity FROM cart_items WHERE session_id = ?');
    $items->execute([$sessionId]);

    foreach ($items->fetchAll() as $item) {
        $existing = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?');
        $existing->execute([$userId, $item['product_id']]);
        $row = $existing->fetch();

        if ($row) {
            $pdo->prepare('UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
                ->execute([(int) $row['quantity'] + (int) $item['quantity'], $row['id']]);
        } else {
            $pdo->prepare('UPDATE cart_items SET user_id = ?, session_id = NULL, updated_at = CURRENT_TIMESTAMP WHERE session_id = ? AND product_id = ?')
                ->execute([$userId, $sessionId, $item['product_id']]);
        }
    }

    $pdo->prepare('DELETE FROM cart_items WHERE session_id = ?')->execute([$sessionId]);
}

function send_confirmation_email(string $email, string $name, string $token): bool
{
    $link = rtrim(APP_URL, '/') . url('confirm.php?token=' . urlencode($token));
    $subject = APP_NAME . ' email confirmation';
    $message = "Hello $name,\n\nPlease confirm your CampusThread account by opening this link:\n$link\n\nThank you.";
    $headers = 'From: ' . MAIL_FROM . "\r\n";

    return @mail($email, $subject, $message, $headers);
}
