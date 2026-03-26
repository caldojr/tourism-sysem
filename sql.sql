CREATE DATABASE IF NOT EXISTS zanzibar_admin;
USE zanzibar_admin;

CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    country VARCHAR(50) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE admin_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('hotel', 'transport', 'safari') NOT NULL,
    region ENUM('tanzania', 'zanzibar') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    deleted TINYINT(1) DEFAULT 0;
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_post_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    region ENUM('tanzania', 'zanzibar') NOT NULL,
    selected_safaris JSON,
    selected_hotels JSON,
    selected_transports JSON,
    total_travelers INT NOT NULL,
    special_requests TEXT,
    status ENUM('pending', 'approved', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(10,2) DEFAULT NULL,
    hotel_room_number VARCHAR(50) DEFAULT NULL,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
