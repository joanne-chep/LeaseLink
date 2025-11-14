
CREATE DATABASE IF NOT EXISTS LL2025;
USE LL2025;
CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255),
    phone VARCHAR(20),
    role ENUM('landlord', 'client', 'admin'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample Users
INSERT INTO User (first_name, last_name, email, password_hash, phone, role)
VALUES
('Amina', 'Kwesi', 'amina@example.com', 'hash123', '0244000000', 'landlord'),
('James', 'Mensah', 'jmensah@example.com', 'hash234', '0244556677', 'landlord'),
('Sarah', 'Owusu', 'sowusu@example.com', 'hash345', '0559988776', 'client'),
('David', 'Boateng', 'dboateng@example.com', 'hash456', '0541223344', 'client');

-- ============================================
-- PROPERTY TABLE
-- ============================================
CREATE TABLE Property (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    landlord_id INT,
    title VARCHAR(100),
    description TEXT,
    location VARCHAR(100),
    price DECIMAL(10,2),
    property_type ENUM('Apartment','House','Studio'),
    status ENUM('Available','Rented') DEFAULT 'Available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES User(user_id)
);

-- Sample Properties
INSERT INTO Property (landlord_id, title, description, location, price, property_type, status)
VALUES
(1, '2-Bedroom Apartment', 'Modern apartment with balcony.', 'Accra - East Legon', 1500.00, 'Apartment', 'Available'),
(1, 'Single Room Self-Contain', 'Neat and spacious room.', 'Accra - Madina', 450.00, 'Studio', 'Available'),
(2, '3-Bedroom House', 'Fully furnished house with garage.', 'Kumasi - Ahodwo', 2500.00, 'House', 'Rented');

-- ============================================
-- PROPERTY IMAGE TABLE
-- ============================================
CREATE TABLE PropertyImage (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    image_url VARCHAR(255),
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES Property(property_id)
);

-- Sample Images
INSERT INTO PropertyImage (property_id, image_url)
VALUES
(1, 'https://example.com/imgs/apartment1.jpg'),
(1, 'https://example.com/imgs/apartment1b.jpg'),
(2, 'https://example.com/imgs/studio1.jpg'),
(3, 'https://example.com/imgs/house1.jpg');

-- ============================================
-- VIEWING REQUEST TABLE
-- ============================================
CREATE TABLE ViewingRequest (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    property_id INT,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    preferred_time DATETIME,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    FOREIGN KEY (client_id) REFERENCES User(user_id),
    FOREIGN KEY (property_id) REFERENCES Property(property_id)
);

-- Sample Viewing Requests
INSERT INTO ViewingRequest (client_id, property_id, preferred_time, status)
VALUES
(3, 1, '2025-02-20 10:00:00', 'Pending'),
(4, 1, '2025-02-22 14:00:00', 'Approved'),
(3, 2, '2025-02-25 09:00:00', 'Pending'),
(4, 3, '2025-02-28 16:00:00', 'Rejected');


