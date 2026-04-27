-- MBHaat.com Database Schema
-- Digital Product Selling Platform

CREATE DATABASE IF NOT EXISTS mbhaat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mbhaat;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_blocked TINYINT(1) DEFAULT 0,
    email_verified TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','editor') DEFAULT 'editor',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    file_path VARCHAR(500) DEFAULT NULL,
    file_token VARCHAR(100) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    downloads INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Product screenshots
CREATE TABLE product_screenshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Coupons table
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('flat','percentage') DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expiry_date DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0.00,
    tax DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    coupon_id INT DEFAULT NULL,
    payment_method ENUM('bkash','nagad','ssl','bank','visa','mastercard') NOT NULL,
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    transaction_id VARCHAR(200) DEFAULT NULL,
    status ENUM('pending','processing','completed','cancelled','refunded') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
);

-- Order items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    download_token VARCHAR(100) DEFAULT NULL,
    download_count INT DEFAULT 0,
    download_limit INT DEFAULT 5,
    download_expiry DATETIME DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart (session-based, for DB persistence)
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Support tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status ENUM('open','in_progress','closed') DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Ticket messages
CREATE TABLE ticket_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    sender ENUM('user','admin') DEFAULT 'user',
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);

-- FAQ
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) DEFAULT NULL,
    message TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default admin
INSERT INTO admins (name, email, password, role) VALUES
('Super Admin', 'admin@mbhaat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
-- Default password: password

-- Default categories
INSERT INTO categories (name, slug, description) VALUES
('E-Books', 'ebooks', 'Digital books and guides'),
('Templates', 'templates', 'Design and document templates'),
('Software', 'software', 'Apps and tools'),
('Courses', 'courses', 'Online learning materials'),
('Graphics', 'graphics', 'Designs, icons and illustrations');

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'MBHaat.com'),
('site_tagline', 'Premium Digital Products'),
('currency', 'BDT'),
('currency_symbol', '৳'),
('tax_rate', '0'),
('vat_rate', '0'),
('bkash_number', ''),
('nagad_number', ''),
('ssl_merchant_id', ''),
('ssl_merchant_pass', ''),
('bank_account', ''),
('download_limit', '5'),
('download_expiry_days', '30'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from', 'noreply@mbhaat.com'),
('footer_text', '© 2026 MBHaat.com — All rights reserved'),
('logo', 'assets/images/logo.png'),
('dark_mode_default', '0'),
('maintenance_mode', '0');

-- Sample FAQs
INSERT INTO faqs (question, answer, sort_order) VALUES
('How do I download my purchased product?', 'After successful payment, go to My Orders in your dashboard. Click the Download button next to your product. Downloads are available for 30 days.', 1),
('What payment methods are supported?', 'We support Bkash, Nagad, SSL Commerz, Bank Transfer, Visa and Mastercard.', 2),
('Can I get a refund?', 'Refund requests are reviewed case-by-case. Contact support within 7 days of purchase.', 3),
('How many times can I download a product?', 'By default, you can download each product up to 5 times within 30 days of purchase.', 4),
('Is my payment information secure?', 'Yes. We use SSL encryption and trusted payment gateways. We never store card details.', 5);

-- Sample testimonials
INSERT INTO testimonials (name, role, message, rating, sort_order) VALUES
('Rahim Uddin', 'Graphic Designer', 'Amazing collection of templates! Saved me hours of work. Highly recommended.', 5, 1),
('Fatema Begum', 'Entrepreneur', 'Fast download, secure payment. The e-books are excellent quality.', 5, 2),
('Karim Hossain', 'Developer', 'Great platform for digital products. Support team is very responsive.', 4, 3);
