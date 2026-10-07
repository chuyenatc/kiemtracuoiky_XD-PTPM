CREATE DATABASE IF NOT EXISTS hotel_mvc;
USE hotel_mvc;

-- CSDL da tao tu phien ban cu: them role, danh dau tai khoan admin va unique username
-- ALTER TABLE users ADD COLUMN role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer';
-- UPDATE users SET role = 'admin' WHERE username = 'admin';
-- ALTER TABLE users ADD UNIQUE KEY uq_users_username (username);
CREATE TABLE users (
    id INT AUTO_INCREMENT KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer'
);
-- Tài khoản demo: admin / 123456
INSERT INTO users (username, password, role) VALUES ('admin', '123456', 'admin');

CREATE TABLE rooms (
    id INT AUTO_INCREMENT KEY,
    room_name VARCHAR(20),
    status ENUM('Trống', 'Đang thuê') DEFAULT 'Trống'
);
INSERT INTO rooms (room_name) VALUES ('101'), ('102'), ('201'), ('202');

CREATE TABLE bookings (
    id INT AUTO_INCREMENT KEY,
    room_id INT,
    customer_name VARCHAR(100),
    check_in DATE,
    check_out DATE,
    status ENUM('Active', 'Completed') DEFAULT 'Active'
);