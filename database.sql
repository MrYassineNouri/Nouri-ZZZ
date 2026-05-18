-- ============================================================
-- Restaurant Booking System - Database
-- ============================================================

CREATE DATABASE IF NOT EXISTS restaurant_db;
USE restaurant_db;

-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CATEGORIES TABLE
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- PRODUCTS (menu items) TABLE
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT,
    available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- RESERVATIONS TABLE
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    persons INT NOT NULL,
    notes TEXT,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ORDERS TABLE
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','preparing','delivered','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ORDER ITEMS TABLE
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
-- Password for both: "password"

INSERT INTO categories (name) VALUES ('Starters'), ('Main Course'), ('Desserts'), ('Drinks');

INSERT INTO products (name, description, price, category_id) VALUES
('Garlic Bread', 'Toasted bread with garlic butter', 4.99, 1),
('Caesar Salad', 'Romaine lettuce, croutons, Caesar dressing', 8.99, 1),
('Grilled Chicken', 'Served with seasonal vegetables', 15.99, 2),
('Beef Burger', 'Double patty with cheese, lettuce, tomato', 13.99, 2),
('Pasta Carbonara', 'Classic Italian pasta with egg sauce', 12.99, 2),
('Chocolate Fondant', 'Warm chocolate cake with vanilla ice cream', 6.99, 3),
('Cheesecake', 'New York style with berry coulis', 5.99, 3),
('Fresh Lemonade', 'Homemade with fresh lemons', 3.49, 4),
('Sparkling Water', 'Still or sparkling', 2.49, 4);

INSERT INTO reservations (user_id, name, date, time, persons, status) VALUES
(2, 'John Doe', '2025-06-01', '19:00:00', 2, 'confirmed'),
(2, 'John Doe', '2025-06-10', '20:30:00', 4, 'pending');

INSERT INTO orders (user_id, total_price, status) VALUES (2, 28.98, 'delivered');
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (1, 3, 1, 15.99), (1, 1, 1, 4.99), (1, 8, 2, 3.49);
