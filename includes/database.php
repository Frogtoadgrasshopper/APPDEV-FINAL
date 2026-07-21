<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function db() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $driver = defined('DB_DRIVER') ? DB_DRIVER : 'sqlite';
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $port = defined('DB_PORT') ? DB_PORT : '3306';
    $name = defined('DB_NAME') ? DB_NAME : 'campus_thread_hoodies';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';
    $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

    if ($driver === 'mysql') {
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=' . $charset;

        $sslCa = getenv('DB_SSL_CA');
        if (!$sslCa) {
            $sslCa = file_exists('/etc/ssl/certs/ca-certificates.crt')
                ? '/etc/ssl/certs/ca-certificates.crt'
                : '/etc/ssl/cert.pem';
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_SSL_CA => $sslCa,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    }

    $databaseDir = __DIR__ . '/../database';
    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0777, true);
    }

    $sqlitePath = defined('DB_PATH') ? DB_PATH : ($databaseDir . '/campus_thread_demo.sqlite');

    $pdo = new PDO('sqlite:' . $sqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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

        CREATE TABLE IF NOT EXISTS app_meta (
            meta_key TEXT PRIMARY KEY,
            meta_value TEXT NOT NULL
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
            selected_size TEXT NOT NULL DEFAULT '',
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
            selected_size TEXT NOT NULL DEFAULT '',
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

    ensure_sqlite_column($pdo, 'cart_items', 'selected_size', "TEXT NOT NULL DEFAULT ''");
    ensure_sqlite_column($pdo, 'order_items', 'selected_size', "TEXT NOT NULL DEFAULT ''");

    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        seed_sqlite_database($pdo);
    }

    sync_sqlite_product_catalog($pdo);
}

function ensure_sqlite_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $allowedTables = ['cart_items', 'order_items'];
    if (!in_array($table, $allowedTables, true)) {
        throw new InvalidArgumentException('Unsupported migration table.');
    }

    $columns = $pdo->query("PRAGMA table_info($table)")->fetchAll();
    foreach ($columns as $existingColumn) {
        if ($existingColumn['name'] === $column) {
            return;
        }
    }

    $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
}

function campus_product_catalog(): array
{
    return [
        [1, 'CT-COL-ENG-M', 'Engineering Crest Hoodie', 'Maroon', 'S,M,L,XL', 990.00, 32, 'Heavyweight cotton fleece with a gold embroidered engineering crest, double-layer hood, and reinforced ribbing.', 'assets/img/hoodie-engineering-maroon.png'],
        [1, 'CT-COL-BUS-L', 'Business School Hoodie', 'Forest Green', 'S,M,L,XL', 1020.00, 24, 'Premium forest fleece with a gold shield embroidery, soft brushed interior, and structured everyday fit.', 'assets/img/hoodie-business-green.png'],
        [1, 'CT-COL-CS', 'Computer Science Circuit Hoodie', 'Black', 'S,M,L,XL', 1050.00, 28, 'Dense black fleece with teal circuit embroidery, metal-tipped drawcords, and a clean modern campus fit.', 'assets/img/hoodie-compsci-black.png'],
        [1, 'CT-COL-NUR', 'Nursing Care Hoodie', 'Sage Green', 'S,M,L,XL', 980.00, 22, 'Soft sage fleece with an embroidered care emblem, cream drawcords, and a relaxed all-day fit.', 'assets/img/hoodie-nursing-sage.png'],
        [2, 'CT-VAR-NAVY-M', 'Campus Varsity Hoodie', 'Navy', 'S,M,L,XL', 950.00, 45, 'Classic heavyweight pullover with a tactile chenille C patch and gold varsity sleeve stripes.', 'assets/img/hoodie-varsity-navy.png'],
        [2, 'CT-VAR-ASH-S', 'Library Night Hoodie', 'Ash Gray', 'S,M,L,XL', 890.00, 18, 'Relaxed brushed-fleece hoodie with a minimal open-book embroidery for cool study nights.', 'assets/img/hoodie-library-ash.png'],
        [2, 'CT-VAR-INT', 'Intramurals Torch Hoodie', 'Royal Blue', 'S,M,L,XL', 990.00, 30, 'Athletic performance fleece with a torch embroidery and crisp white-and-gold sleeve stripes.', 'assets/img/hoodie-intramurals-blue.png'],
        [3, 'CT-LTD-FOUND-L', 'Founders Week Hoodie', 'Heritage Cream', 'S,M,L,XL', 1150.00, 12, 'Limited heavyweight cream release with original heritage pennant and woven sleeve patches.', 'assets/img/hoodie-founders-cream.png'],
        [4, 'CT-ORG-ART-M', 'Arts Guild Hoodie', 'Teal', 'S,M,L,XL', 970.00, 27, 'Rich teal fleece finished with a coral-and-gold embroidered art mark and tonal drawcords.', 'assets/img/hoodie-arts-teal.png'],
        [4, 'CT-ORG-DBT', 'Debate Society Zip Hoodie', 'Charcoal', 'S,M,L,XL', 1090.00, 16, 'Premium full-zip charcoal fleece with cream hood lining, metal hardware, and laurel embroidery.', 'assets/img/hoodie-debate-charcoal.png'],
    ];
}

function sync_sqlite_product_catalog(PDO $pdo): void
{
    $version = (int) ($pdo->query("SELECT meta_value FROM app_meta WHERE meta_key = 'catalog_version'")->fetchColumn() ?: 0);
    if ($version >= 2) {
        return;
    }

    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare("
            INSERT OR IGNORE INTO products (category_id, sku, name, color, size, price, stock, description, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $update = $pdo->prepare("
            UPDATE products
            SET category_id = ?, name = ?, color = ?, size = ?, price = ?, stock = ?, description = ?, image_url = ?, is_active = 1, updated_at = CURRENT_TIMESTAMP
            WHERE sku = ?
        ");

        foreach (campus_product_catalog() as $product) {
            $insert->execute($product);
            $update->execute([
                $product[0], $product[2], $product[3], $product[4], $product[5],
                $product[6], $product[7], $product[8], $product[1],
            ]);
        }

        $pdo->prepare("INSERT OR REPLACE INTO app_meta (meta_key, meta_value) VALUES ('catalog_version', '2')")->execute();
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
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

    $products = campus_product_catalog();

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
