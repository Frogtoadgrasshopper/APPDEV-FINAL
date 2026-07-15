<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (DB_DRIVER === 'mysql') {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }

    $databaseDir = dirname(DB_PATH);
    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    initialize_sqlite_database($pdo);

    return $pdo;
}

function initialize_sqlite_database(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            complete_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            complete_address TEXT NOT NULL DEFAULT '',
            contact_numbers TEXT NOT NULL DEFAULT '',
            role TEXT NOT NULL DEFAULT 'buyer',
            status TEXT NOT NULL DEFAULT 'active',
            email_verified INTEGER NOT NULL DEFAULT 0,
            email_token TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            slug TEXT NOT NULL UNIQUE,
            description TEXT NOT NULL DEFAULT ''
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            sku TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            color TEXT NOT NULL,
            size TEXT NOT NULL,
            price REAL NOT NULL,
            stock INTEGER NOT NULL DEFAULT 0,
            description TEXT NOT NULL,
            image_url TEXT NOT NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        );

        CREATE TABLE IF NOT EXISTS cart_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            session_id TEXT,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            order_no TEXT NOT NULL UNIQUE,
            customer_name TEXT NOT NULL,
            email TEXT NOT NULL,
            shipping_address TEXT NOT NULL,
            contact_numbers TEXT NOT NULL,
            subtotal REAL NOT NULL,
            delivery_fee REAL NOT NULL,
            total REAL NOT NULL,
            payment_method TEXT NOT NULL,
            payment_status TEXT NOT NULL DEFAULT 'Pending',
            order_status TEXT NOT NULL DEFAULT 'Processing',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            price REAL NOT NULL,
            quantity INTEGER NOT NULL,
            subtotal REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            actor_name TEXT NOT NULL,
            role TEXT NOT NULL,
            action TEXT NOT NULL,
            details TEXT NOT NULL,
            ip_address TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
    ");

    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }

    seed_sqlite_database($pdo);
}

function seed_sqlite_database(PDO $pdo): void
{
    $pdo->beginTransaction();

    $users = [
        ['Campus Admin', 'admin@campusthread.test', 'Admin@123', 'Admin Office, University Campus', '09170000001', 'super_admin', 1],
        ['Seller Manager', 'seller@campusthread.test', 'Seller@123', 'Seller Office, University Campus', '09170000002', 'admin', 1],
        ['Sample Buyer', 'buyer@campusthread.test', 'Buyer@123', '123 University Avenue, Manila', '09170000003', 'buyer', 1],
    ];

    $insertUser = $pdo->prepare("
        INSERT INTO users (complete_name, email, password_hash, complete_address, contact_numbers, role, email_verified)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($users as $user) {
        $insertUser->execute([
            $user[0],
            $user[1],
            password_hash($user[2], PASSWORD_DEFAULT),
            $user[3],
            $user[4],
            $user[5],
            $user[6],
        ]);
    }

    $categories = [
        ['College Collection', 'college', 'Hoodies styled for university colleges and departments.'],
        ['Varsity Classics', 'varsity', 'Everyday campus colors with stitched varsity details.'],
        ['Limited Editions', 'limited', 'Short-run hoodie drops for events and school spirit days.'],
        ['Club and Org', 'club-org', 'Designs for student clubs, organizations, and teams.'],
    ];

    $insertCategory = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
    foreach ($categories as $category) {
        $insertCategory->execute($category);
    }

    $products = [
        [1, 'CT-COL-ENG-M', 'Engineering Crest Hoodie', 'Maroon', 'M', 890.00, 32, 'Midweight fleece hoodie with embroidered engineering crest.', 'assets/img/hoodie-maroon.svg'],
        [1, 'CT-COL-BUS-L', 'Business School Hoodie', 'Forest', 'L', 920.00, 24, 'Clean university hoodie with gold business school lettering.', 'assets/img/hoodie-forest.svg'],
        [2, 'CT-VAR-NAVY-M', 'Campus Varsity Hoodie', 'Navy', 'M', 850.00, 45, 'Classic pullover hoodie with bold campus varsity type.', 'assets/img/hoodie-navy.svg'],
        [2, 'CT-VAR-ASH-S', 'Library Night Hoodie', 'Ash Gray', 'S', 790.00, 18, 'Soft ash hoodie made for late-night study sessions.', 'assets/img/hoodie-ash.svg'],
        [3, 'CT-LTD-FOUND-L', 'Founders Week Hoodie', 'Cream', 'L', 980.00, 12, 'Limited founders week release with sleeve patch details.', 'assets/img/hoodie-cream.svg'],
        [4, 'CT-ORG-ART-M', 'Arts Guild Hoodie', 'Teal', 'M', 870.00, 27, 'Student org hoodie with a bright woven-style chest mark.', 'assets/img/hoodie-teal.svg'],
    ];

    $insertProduct = $pdo->prepare("
        INSERT INTO products (category_id, sku, name, color, size, price, stock, description, image_url)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($products as $product) {
        $insertProduct->execute($product);
    }

    $pdo->prepare("
        INSERT INTO audit_logs (user_id, actor_name, role, action, details, ip_address)
        VALUES (1, 'Campus Admin', 'super_admin', 'Seed database', 'Initial sample accounts and hoodie inventory were created.', '127.0.0.1')
    ")->execute();

    $pdo->commit();
}
