-- ============================================================
-- Sales Dashboard – Database Setup
-- Import via phpMyAdmin or: mysql -u root -p < sales_dashboard.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS sales_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sales_dashboard;

-- -------------------------------------------------------
-- Table: products
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    price        DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock        INT NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table: orders
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_id   INT NOT NULL,
    quantity     INT NOT NULL DEFAULT 1,
    total_price  DECIMAL(10,2) NOT NULL DEFAULT 0,
    order_date   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Sample products
-- -------------------------------------------------------
INSERT INTO products (product_name, price, stock) VALUES
('MacBook Pro 14"',  59900.00, 15),
('iPhone 15 Pro',    42900.00, 30),
('iPad Air',         21900.00, 25),
('AirPods Pro',       9900.00, 50),
('Apple Watch S9',   14900.00, 20),
('Samsung Galaxy S24',38900.00, 18),
('Sony WH-1000XM5',   9500.00, 35),
('Dell XPS 15',      52000.00, 10);

-- -------------------------------------------------------
-- Sample orders (last 30 days)
-- -------------------------------------------------------
INSERT INTO orders (product_id, quantity, total_price, order_date) VALUES
(1, 1, 59900.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 2, 85800.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 3, 29700.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 1, 21900.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 2, 29800.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 1, 38900.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 2, 19000.00, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(2, 1, 42900.00, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 1, 59900.00, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(4, 5, 49500.00, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, 2, 43800.00, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(8, 1, 52000.00, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(5, 1, 14900.00, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 3, 128700.00,DATE_SUB(NOW(), INTERVAL 11 DAY)),
(6, 2, 77800.00, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(1, 2, 119800.00,DATE_SUB(NOW(), INTERVAL 13 DAY)),
(4, 4, 39600.00, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(7, 1, 9500.00,  DATE_SUB(NOW(), INTERVAL 15 DAY)),
(3, 3, 65700.00, DATE_SUB(NOW(), INTERVAL 16 DAY)),
(5, 2, 29800.00, DATE_SUB(NOW(), INTERVAL 17 DAY)),
(2, 1, 42900.00, DATE_SUB(NOW(), INTERVAL 18 DAY)),
(8, 2, 104000.00,DATE_SUB(NOW(), INTERVAL 19 DAY)),
(1, 1, 59900.00, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(6, 1, 38900.00, DATE_SUB(NOW(), INTERVAL 21 DAY)),
(4, 6, 59400.00, DATE_SUB(NOW(), INTERVAL 22 DAY)),
(3, 1, 21900.00, DATE_SUB(NOW(), INTERVAL 23 DAY)),
(2, 2, 85800.00, DATE_SUB(NOW(), INTERVAL 24 DAY)),
(5, 3, 44700.00, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(7, 4, 38000.00, DATE_SUB(NOW(), INTERVAL 26 DAY)),
(1, 1, 59900.00, DATE_SUB(NOW(), INTERVAL 27 DAY));
