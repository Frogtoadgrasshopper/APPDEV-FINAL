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
(1, 'CT-COL-ENG-M', 'Engineering Crest Hoodie', 'Maroon', 'M', 890.00, 32, 'Midweight fleece hoodie with embroidered engineering crest.', 'assets/img/hoodie-maroon.svg'),
(1, 'CT-COL-BUS-L', 'Business School Hoodie', 'Forest', 'L', 920.00, 24, 'Clean university hoodie with gold business school lettering.', 'assets/img/hoodie-forest.svg'),
(2, 'CT-VAR-NAVY-M', 'Campus Varsity Hoodie', 'Navy', 'M', 850.00, 45, 'Classic pullover hoodie with bold campus varsity type.', 'assets/img/hoodie-navy.svg'),
(2, 'CT-VAR-ASH-S', 'Library Night Hoodie', 'Ash Gray', 'S', 790.00, 18, 'Soft ash hoodie made for late-night study sessions.', 'assets/img/hoodie-ash.svg'),
(3, 'CT-LTD-FOUND-L', 'Founders Week Hoodie', 'Cream', 'L', 980.00, 12, 'Limited founders week release with sleeve patch details.', 'assets/img/hoodie-cream.svg'),
(4, 'CT-ORG-ART-M', 'Arts Guild Hoodie', 'Teal', 'M', 870.00, 27, 'Student org hoodie with a bright woven-style chest mark.', 'assets/img/hoodie-teal.svg');

INSERT INTO audit_logs (user_id, actor_name, role, action, details, ip_address)
VALUES (1, 'Campus Admin', 'super_admin', 'Seed database', 'Initial sample accounts and hoodie inventory were created.', '127.0.0.1');
