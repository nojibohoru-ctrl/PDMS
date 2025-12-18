-- PDMS installation SQL
CREATE DATABASE IF NOT EXISTS pdms;
USE pdms;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','purchasing','delivery','manager') NOT NULL,
    status VARCHAR(20) DEFAULT 'Active'
);

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    contact VARCHAR(50),
    email VARCHAR(100),
    products TEXT,
    address TEXT
);

CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    product VARCHAR(100),
    quantity INT,
    date DATE,
    status VARCHAR(20) DEFAULT 'Pending',
    notes TEXT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product VARCHAR(100),
    scheduled_date DATE,
    status VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
);

-- insert a default admin user with legacy md5 password 'admin123' for compatibility
INSERT INTO users (username, password, role) VALUES ('admin', MD5('admin123'), 'admin');
