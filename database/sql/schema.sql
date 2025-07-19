DROP DATABASE IF EXISTS maxmoll_test_assignment;
CREATE DATABASE maxmoll_test_assignment;

USE maxmoll_test_assignment;

DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price FLOAT NOT NULL
);

DROP TABLE IF EXISTS warehouses;
CREATE TABLE warehouses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(255) NOT NULL CHECK (status IN ('active', 'completed', 'canceled'))
);

DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    count INT NOT NULL
);

DROP TABLE IF EXISTS stocks;
CREATE TABLE stocks (
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    stock INT NOT NULL,
    PRIMARY KEY (product_id, warehouse_id)
);

-- Добавление внешних ключей
ALTER TABLE orders
    ADD CONSTRAINT fk_orders_warehouse
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id);

ALTER TABLE order_items
    ADD CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id),
    ADD CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id);

ALTER TABLE stocks
    ADD CONSTRAINT fk_stocks_product
    FOREIGN KEY (product_id) REFERENCES products(id),
    ADD CONSTRAINT fk_stocks_warehouse
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id);
