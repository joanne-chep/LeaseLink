

CREATE DATABASE IF NOT EXISTS leaselink_db;
USE leaselink_db;

-- Drop existing tables if they exist to allow recreation
-- (Be careful with this in production environments!)
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS view_requests;
DROP TABLE IF EXISTS property_amenities;
DROP TABLE IF EXISTS amenities;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS landlords;
DROP TABLE IF EXISTS clients; 

-- 1. users Table (Consolidating Client, Landlord, Admin roles)
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('client', 'landlord', 'admin') NOT NULL,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  phone_number VARCHAR(20),
  profile_picture_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. properties Table
CREATE TABLE properties (
  property_id INT AUTO_INCREMENT PRIMARY KEY,
  landlord_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  address VARCHAR(255) NOT NULL,
  city VARCHAR(100) NOT NULL,
  state_province VARCHAR(100),
  zip_code VARCHAR(20),
  num_bedrooms INT,
  num_bathrooms DECIMAL(3,1),
  square_footage INT,
  rent_price DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'GHS' NOT NULL,
  property_type ENUM('apartment', 'house', 'condo', 'townhouse', 'studio', 'other') NOT NULL,
  status ENUM('available', 'rented', 'pending', 'inactive') DEFAULT 'available' NOT NULL,
  main_image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (landlord_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 3. view_requests Table
CREATE TABLE view_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  property_id INT NOT NULL,
  requested_date_time DATETIME NOT NULL,
  status ENUM('pending', 'approved', 'denied', 'completed', 'cancelled') DEFAULT 'pending' NOT NULL,
  landlord_notes TEXT,
  client_notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- 4. bookings Table
CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  property_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  monthly_rent DECIMAL(10,2) NOT NULL,
  security_deposit DECIMAL(10,2),
  status ENUM('pending', 'approved', 'rejected', 'active', 'completed', 'cancelled') DEFAULT 'pending' NOT NULL,
  lease_agreement_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- 5. reviews Table
CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  property_id INT NOT NULL,
  rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Auxiliary Tables (Recommended)

-- 6. property_images Table
CREATE TABLE property_images (
  image_id INT AUTO_INCREMENT PRIMARY KEY,
  property_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  description VARCHAR(255),
  is_main BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- 7. amenities Table
CREATE TABLE amenities (
  amenity_id INT AUTO_INCREMENT PRIMARY KEY,
  amenity_name VARCHAR(100) NOT NULL UNIQUE
);

-- 8. property_amenities Junction Table
CREATE TABLE property_amenities (
  property_id INT NOT NULL,
  amenity_id INT NOT NULL,
  PRIMARY KEY (property_id, amenity_id),
  FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE,
  FOREIGN KEY (amenity_id) REFERENCES amenities(amenity_id) ON DELETE CASCADE
);

-- Insert sample users (pre-hashed password for "password123")
-- password123 hash: $2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe
INSERT INTO users (username, email, password_hash, user_type, first_name, last_name) VALUES
('landlord_user', 'landlord@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe', 'landlord', 'Ajak', 'Pachol'),
('client_user', 'client@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe', 'client', 'Sample', 'Client'),
('admin_user', 'admin@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe', 'admin', 'Admin', 'User');

-- Insert sample properties (linking to the sample landlord)
-- Assuming landlord_user (id=1) is the landlord
INSERT INTO properties (landlord_id, title, description, address, city, rent_price, property_type, main_image_url, num_bedrooms, num_bathrooms, square_footage) VALUES
(1, '2 Bedroom Apartment', 'Spacious 2 bedroom apartment with balcony and modern kitchen.', '123 Main St', 'East Legon', 2000.00, 'apartment', 'https://placehold.co/600x400', 2, 2.0, 1200),
(1, 'Studio Apartment', 'Comfortable studio close to transport and markets.', '456 Oak Ave', 'Madina', 1200.00, 'studio', 'https://placehold.co/600x400', 1, 1.0, 600),
(1, '3 Bedroom Townhouse', 'Secure compound, parking and fenced yard.', '789 Pine Ln', 'Airport Residential', 3500.00, 'townhouse', 'https://placehold.co/600x400', 3, 2.5, 2000);

-- Insert sample property images for the first property
INSERT INTO property_images (property_id, image_url, description, is_main) VALUES
(1, 'https://placehold.co/600x400/FF0000/FFFFFF/png', 'Living Room', TRUE),
(1, 'https://placehold.co/600x400/00FF00/FFFFFF/png', 'Kitchen', FALSE),
(1, 'https://placehold.co/600x400/0000FF/FFFFFF/png', 'Bedroom', FALSE);

-- Insert sample amenities
INSERT INTO amenities (amenity_name) VALUES
('Swimming Pool'),
('Gym'),
('Parking'),
('Pet Friendly'),
('Balcony'),
('Air Conditioning');

-- Link properties to amenities
INSERT INTO property_amenities (property_id, amenity_id) VALUES
(1, (SELECT amenity_id FROM amenities WHERE amenity_name = 'Parking')),
(1, (SELECT amenity_id FROM amenities WHERE amenity_name = 'Balcony')),
(2, (SELECT amenity_id FROM amenities WHERE amenity_name = 'Air Conditioning')),
(3, (SELECT amenity_id FROM amenities WHERE amenity_name = 'Parking')),
(3, (SELECT amenity_id FROM amenities WHERE amenity_name = 'Gym'));

-- Insert sample viewing requests (client_user (id=2), property_id=1)
INSERT INTO view_requests (client_id, property_id, requested_date_time, status) VALUES
(2, 1, '2025-12-01 10:00:00', 'pending'),
(2, 2, '2025-12-03 14:30:00', 'approved');

-- Insert sample bookings (client_user (id=2), property_id=3)
INSERT INTO bookings (client_id, property_id, start_date, end_date, monthly_rent, security_deposit, status) VALUES
(2, 3, '2026-01-01', '2026-12-31', 3500.00, 7000.00, 'active');

-- Insert sample reviews (client_user (id=2), property_id=1)
INSERT INTO reviews (client_id, property_id, rating, comment) VALUES
(2, 1, 4, 'Great apartment, very spacious and good location!');
