CREATE DATABASE IF NOT EXISTS campus_thread_hoodies
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE campus_thread_hoodies;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complete_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    complete_address TEXT NOT NULL,
    contact_numbers VARCHAR(80) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'buyer',
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    email_token VARCHAR(120) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(140) NOT NULL,
    color VARCHAR(60) NOT NULL,
    size VARCHAR(30) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    description TEXT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(120) NULL,
    product_id INT NOT NULL,
    selected_size VARCHAR(12) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    order_no VARCHAR(40) NOT NULL UNIQUE,
    customer_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    shipping_address TEXT NOT NULL,
    contact_numbers VARCHAR(80) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(80) NOT NULL,
    payment_status VARCHAR(40) NOT NULL DEFAULT 'Pending',
    order_status VARCHAR(40) NOT NULL DEFAULT 'Processing',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(140) NOT NULL,
    selected_size VARCHAR(12) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    actor_name VARCHAR(120) NOT NULL,
    role VARCHAR(30) NOT NULL,
    action VARCHAR(120) NOT NULL,
    details TEXT NOT NULL,
    ip_address VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (complete_name, email, password_hash, complete_address, contact_numbers, role, status, email_verified) VALUES
('Campus Admin', 'admin@campusthread.test', 'sha256$e86f78a8a3caf0b60d8e74e5942aa6d86dc150cd3c03338aef25b7d2d7e3acc7', 'Admin Office, University Campus', '09170000001', 'super_admin', 'active', 1),
('Seller Manager', 'seller@campusthread.test', 'sha256$bd28c94800c2be055b3329f8dd63a3d5a4137c0def2517bf4fce85eb11e62853', 'Seller Office, University Campus', '09170000002', 'admin', 'active', 1),
('Sample Buyer', 'buyer@campusthread.test', 'sha256$396fcdf7cfc32eebcd16361352a2a5d2aedeaf76b1be9a15a7d2a7d4fd69f257', '123 University Avenue, Manila', '09170000003', 'buyer', 'active', 1);

INSERT INTO categories (name, slug, description) VALUES
('College Collection', 'college', 'Hoodies styled for university colleges and departments.'),
('Varsity Classics', 'varsity', 'Everyday campus colors with stitched varsity details.'),
('Limited Editions', 'limited', 'Short-run hoodie drops for events and school spirit days.'),
('Club and Org', 'club-org', 'Designs for student clubs, organizations, and teams.');

INSERT INTO products (category_id, sku, name, color, size, price, stock, description, image_url) VALUES
(1, 'CT-COL-ENG-M', 'Engineering Crest Hoodie', 'Maroon', 'S,M,L,XL', 990.00, 32, 'Heavyweight cotton fleece with a gold embroidered engineering crest, double-layer hood, and reinforced ribbing.', 'assets/img/hoodie-engineering-maroon.png'),
(1, 'CT-COL-BUS-L', 'Business School Hoodie', 'Forest Green', 'S,M,L,XL', 1020.00, 24, 'Premium forest fleece with a gold shield embroidery, soft brushed interior, and structured everyday fit.', 'assets/img/hoodie-business-green.png'),
(1, 'CT-COL-CS', 'Computer Science Circuit Hoodie', 'Black', 'S,M,L,XL', 1050.00, 28, 'Dense black fleece with teal circuit embroidery, metal-tipped drawcords, and a clean modern campus fit.', 'assets/img/hoodie-compsci-black.png'),
(1, 'CT-COL-NUR', 'Nursing Care Hoodie', 'Sage Green', 'S,M,L,XL', 980.00, 22, 'Soft sage fleece with an embroidered care emblem, cream drawcords, and a relaxed all-day fit.', 'assets/img/hoodie-nursing-sage.png'),
(2, 'CT-VAR-NAVY-M', 'Campus Varsity Hoodie', 'Navy', 'S,M,L,XL', 950.00, 45, 'Classic heavyweight pullover with a tactile chenille C patch and gold varsity sleeve stripes.', 'assets/img/hoodie-varsity-navy.png'),
(2, 'CT-VAR-ASH-S', 'Library Night Hoodie', 'Ash Gray', 'S,M,L,XL', 890.00, 18, 'Relaxed brushed-fleece hoodie with a minimal open-book embroidery for cool study nights.', 'assets/img/hoodie-library-ash.png'),
(2, 'CT-VAR-INT', 'Intramurals Torch Hoodie', 'Royal Blue', 'S,M,L,XL', 990.00, 30, 'Athletic performance fleece with a torch embroidery and crisp white-and-gold sleeve stripes.', 'assets/img/hoodie-intramurals-blue.png'),
(3, 'CT-LTD-FOUND-L', 'Founders Week Hoodie', 'Heritage Cream', 'S,M,L,XL', 1150.00, 12, 'Limited heavyweight cream release with original heritage pennant and woven sleeve patches.', 'assets/img/hoodie-founders-cream.png'),
(4, 'CT-ORG-ART-M', 'Arts Guild Hoodie', 'Teal', 'S,M,L,XL', 970.00, 27, 'Rich teal fleece finished with a coral-and-gold embroidered art mark and tonal drawcords.', 'assets/img/hoodie-arts-teal.png'),
(4, 'CT-ORG-DBT', 'Debate Society Zip Hoodie', 'Charcoal', 'S,M,L,XL', 1090.00, 16, 'Premium full-zip charcoal fleece with cream hood lining, metal hardware, and laurel embroidery.', 'assets/img/hoodie-debate-charcoal.png');

INSERT INTO audit_logs (user_id, actor_name, role, action, details, ip_address)
VALUES (1, 'Campus Admin', 'super_admin', 'Seed database', 'Initial sample accounts and hoodie inventory were created.', '127.0.0.1');
