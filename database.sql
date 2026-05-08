CREATE DATABASE IF NOT EXISTS karu_marketplace;
USE karu_marketplace;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    admission_number VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    whatsapp_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','moderator','seller','user') DEFAULT 'user',
    status ENUM('active','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE seller_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    admission_number VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    whatsapp_number VARCHAR(20),
    student_id_front VARCHAR(255) NOT NULL,
    reason TEXT,
    status ENUM('pending','approved','rejected','more_details') DEFAULT 'pending',
    reviewed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT,
    item_name VARCHAR(150) NOT NULL,
    specifications TEXT NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    is_negotiable TINYINT(1) DEFAULT 0,
    min_price DECIMAL(10,2) NULL,
    max_price DECIMAL(10,2) NULL,
    whatsapp_number VARCHAR(20),
    condition_status ENUM('New','Used Like New','Used Good','Used Fair','Damaged') DEFAULT 'Used Good',
    delivery_area VARCHAR(255),
    quantity INT DEFAULT 1,
    product_status ENUM('pending_review','on_sale','sold','hidden','rejected') DEFAULT 'pending_review',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    offer_price DECIMAL(10,2) NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_location VARCHAR(255) NOT NULL,
    buyer_phone VARCHAR(20),
    delivery_instructions TEXT,
    preferred_delivery_time VARCHAR(100),
    order_status ENUM('pending','accepted','rejected','delivering','delivered','sold','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid_after_delivery','cancelled') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    feedback_type ENUM('positive','neutral','negative') NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    reported_by INT NOT NULL,
    reason VARCHAR(150) NOT NULL,
    description TEXT,
    status ENUM('pending','reviewed','dismissed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (reported_by) REFERENCES users(id)
);

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE cms_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_title VARCHAR(150) NOT NULL,
    page_slug VARCHAR(100) UNIQUE NOT NULL,
    page_content TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO categories (name, description) VALUES
('Phones', 'Mobile phones and smartphones'),
('Laptops', 'Laptops and computers'),
('Clothes', 'Clothing and fashion items'),
('Shoes', 'Footwear'),
('Books', 'Textbooks and study materials'),
('Electronics', 'Electronic gadgets and accessories'),
('Furniture', 'Furniture and home items'),
('Food', 'Food and snacks'),
('Accessories', 'Bags, watches, and accessories'),
('Other', 'Miscellaneous items');

INSERT INTO cms_pages (page_title, page_slug, page_content) VALUES
('About KarU Marketplace', 'about', 'KarU Marketplace is a campus student marketplace designed for Karatina University students. Buy and sell items safely within our campus community. Our platform connects verified student sellers with buyers, ensuring safe and transparent transactions.'),
('Contact Us', 'contact', 'For any inquiries, reach us at: Email: marketplace@karu.ac.ke | Phone: +254 700 000 000 | Location: Karatina University, Main Campus, Karatina, Kenya.'),
('Safety Guidelines', 'safety', 'Always meet in safe, public campus locations. Do not pay before physically receiving the item. Confirm item condition before making payment. Report suspicious sellers to admin immediately. Use campus-approved meeting points such as the library entrance or main gate.'),
('Terms and Conditions', 'terms', 'By using KarU Marketplace, you agree to these terms. You must be a registered Karatina University student. Sellers must be verified and approved by admin. All transactions are final after buyer confirmation. KarU Marketplace does not facilitate online payments. Payment should only occur after physical delivery and inspection of items.');

INSERT INTO users (username, full_name, admission_number, email, phone, whatsapp_number, password, role, status) VALUES
('admin', 'KarU Admin', 'ADM001', 'admin@karu.ac.ke', '0700000000', '0700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');
