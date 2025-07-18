-- Insert products
INSERT INTO products (name, price) VALUES
('Laptop', 1200.00),
('Smartphone', 800.00),
('Keyboard', 50.00),
('Mouse', 30.00),
('Monitor', 300.00),
('Printer', 150.00),
('Desk Lamp', 45.00),
('USB Cable', 10.00),
('External HDD', 100.00),
('Webcam', 75.00);

-- Insert warehouses
INSERT INTO warehouses (name) VALUES
('New York Warehouse'),
('Los Angeles Warehouse'),
('Chicago Warehouse'),
('Houston Warehouse'),
('Miami Warehouse'),
('Seattle Warehouse'),
('Denver Warehouse'),
('Boston Warehouse'),
('Atlanta Warehouse'),
('San Francisco Warehouse');

-- Insert orders
INSERT INTO orders (customer, created_at, completed_at, warehouse_id, status) VALUES
('Alice', '2025-07-01 10:00:00', '2025-07-02 15:00:00', 1, 'completed'),
('Bob', '2025-07-03 12:30:00', NULL, 2, 'active'),
('Charlie', '2025-07-04 09:45:00', '2025-07-05 18:00:00', 3, 'completed'),
('Diana', '2025-07-05 14:00:00', NULL, 4, 'active'),
('Eve', '2025-07-06 11:15:00', '2025-07-07 09:00:00', 5, 'completed'),
('Frank', '2025-07-07 16:45:00', NULL, 6, 'active'),
('Grace', '2025-07-08 10:30:00', '2025-07-09 13:00:00', 7, 'completed'),
('Henry', '2025-07-09 08:20:00', NULL, 8, 'canceled'),
('Ivy', '2025-07-10 17:00:00', NULL, 9, 'active'),
('Jack', '2025-07-11 13:10:00', NULL, 10, 'active');

-- Insert order_items
INSERT INTO order_items (order_id, product_id, count) VALUES
(1, 1, 1),
(1, 3, 2),
(2, 2, 1),
(2, 5, 1),
(3, 4, 3),
(3, 10, 1),
(4, 6, 1),
(4, 7, 2),
(5, 8, 5),
(5, 9, 1),
(6, 1, 1),
(6, 2, 1),
(7, 3, 2),
(8, 4, 1),
(9, 5, 1),
(10, 6, 3);

-- Insert stocks
INSERT INTO stocks (product_id, warehouse_id, stock) VALUES
(1, 1, 50),
(2, 2, 40),
(3, 3, 70),
(4, 4, 90),
(5, 5, 20),
(6, 6, 15),
(7, 7, 35),
(8, 8, 80),
(9, 9, 25),
(10, 10, 10);
