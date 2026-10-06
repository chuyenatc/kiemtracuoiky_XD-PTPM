CREATE DATABASE IF NOT EXISTS hotel_mvc;
USE hotel_mvc;

CREATE TABLE users (
    id INT AUTO_INCREMENT KEY,
    username VARCHAR(50),
    password VARCHAR(255)
);
-- Tài khoản demo: admin / 123456
INSERT INTO users (username, password) VALUES ('admin', '123456');

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