
USE webtech_2025A_ajak_panchol;

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


-- Users Table (Clients, Landlords, Admins)
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('client', 'landlord', 'admin') NOT NULL,
  approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  phone_number VARCHAR(20),
  profile_picture_url VARCHAR(255),
  id_document_url VARCHAR(255),
  ownership_document_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties Table
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

-- Property Images Table
CREATE TABLE property_images (
  image_id INT AUTO_INCREMENT PRIMARY KEY,
  property_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  description VARCHAR(255),
  is_main TINYINT(1) DEFAULT 0,
  FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- View Requests Table
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

-- Bookings Table
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

-- Reviews Table
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

-- Amenities Table
CREATE TABLE amenities (
  amenity_id INT AUTO_INCREMENT PRIMARY KEY,
  amenity_name VARCHAR(100) NOT NULL UNIQUE
);

-- Property Amenities Junction Table
CREATE TABLE property_amenities (
  property_id INT NOT NULL,
  amenity_id INT NOT NULL,
  PRIMARY KEY (property_id, amenity_id),
  FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE,
  FOREIGN KEY (amenity_id) REFERENCES amenities(amenity_id) ON DELETE CASCADE
);


-- Password for all accounts: password123 (except admin: admin123)

INSERT INTO users (username, email, password_hash, user_type, approval_status, first_name, last_name) VALUES
('Ajak Panchol', 'landlord@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe', 'landlord', 'approved', 'Ajak', 'Panchol'),
('Sample Client', 'client@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe', 'client', 'approved', 'Sample', 'Client'),
('Admin User', 'admin@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe', 'admin', 'approved', 'Admin', 'User');


INSERT INTO properties (landlord_id, title, description, address, city, state_province, zip_code, num_bedrooms, num_bathrooms, square_footage, rent_price, currency, property_type, main_image_url, status) VALUES
-- East Legon Properties
(1, 'Modern 2 Bedroom Apartment - East Legon', 'Beautiful modern apartment with balcony, modern kitchen, and security. Located in a quiet neighborhood with easy access to amenities.', '123 East Legon Avenue', 'East Legon', 'Greater Accra', 'GA-123-4567', 2, 2.0, 1200, 2000.00, 'GHS', 'apartment', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600&h=400&fit=crop', 'available'),
(1, 'Spacious 3 Bedroom House - East Legon', 'Large family home with garden, parking space, and modern finishes. Perfect for families looking for space and comfort.', '456 Legon Road', 'East Legon', 'Greater Accra', 'GA-123-4568', 3, 3.0, 2000, 3500.00, 'GHS', 'house', 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&h=400&fit=crop', 'available'),

-- Airport Residential Properties
(1, 'Luxury 3 Bedroom Townhouse - Airport Residential', 'Secure compound, gated community with parking and fenced yard. High-end finishes and modern amenities.', '789 Airport Road', 'Airport Residential', 'Greater Accra', 'GA-234-5678', 3, 2.5, 2500, 4500.00, 'GHS', 'townhouse', 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&h=400&fit=crop', 'available'),
(1, 'Elegant 4 Bedroom Villa - Airport Residential', 'Premium villa with swimming pool, large garden, and 24/7 security. Ideal for executives and diplomats.', '321 Aviation Avenue', 'Airport Residential', 'Greater Accra', 'GA-234-5679', 4, 4.0, 3500, 6500.00, 'GHS', 'house', 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=600&h=400&fit=crop', 'available'),

-- Madina Properties
(1, 'Cozy Studio Apartment - Madina', 'Comfortable studio close to transport and markets. Perfect for students or young professionals on a budget.', '101 Madina Road', 'Madina', 'Greater Accra', 'GA-345-6789', 1, 1.0, 500, 1200.00, 'GHS', 'studio', 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=600&h=400&fit=crop', 'available'),
(1, '2 Bedroom Apartment - Madina', 'Well-maintained apartment with good ventilation and natural light. Close to schools and shopping centers.', '202 Oyarifa Road', 'Madina', 'Greater Accra', 'GA-345-6790', 2, 2.0, 900, 1800.00, 'GHS', 'apartment', 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&h=400&fit=crop', 'available'),

-- Cantonments Properties
(1, 'Executive 2 Bedroom Condo - Cantonments', 'Modern condo with gym access, swimming pool, and concierge service. Prime location in Cantonments.', '555 Cantonments Close', 'Cantonments', 'Greater Accra', 'GA-456-7890', 2, 2.0, 1100, 3200.00, 'GHS', 'condo', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600&h=400&fit=crop', 'available'),
(1, 'Chic 1 Bedroom Apartment - Cantonments', 'Stylish apartment in a secure building with modern amenities. Walking distance to restaurants and cafes.', '777 Labone Avenue', 'Cantonments', 'Greater Accra', 'GA-456-7891', 1, 1.5, 700, 2500.00, 'GHS', 'apartment', 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=600&h=400&fit=crop', 'available'),

-- Labone Properties
(1, '3 Bedroom Family Home - Labone', 'Spacious family home with private compound, parking, and close to international schools.', '888 Labone Street', 'Labone', 'Greater Accra', 'GA-567-8901', 3, 3.0, 1800, 3800.00, 'GHS', 'house', 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=600&h=400&fit=crop', 'available'),
(1, '2 Bedroom Duplex - Labone', 'Modern duplex with private entrance, balcony, and parking space. Safe and quiet neighborhood.', '999 Labone Road', 'Labone', 'Greater Accra', 'GA-567-8902', 2, 2.0, 1000, 2800.00, 'GHS', 'townhouse', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&h=400&fit=crop', 'available'),

-- Tema Properties
(1, 'Affordable 2 Bedroom Apartment - Tema', 'Budget-friendly apartment in Tema Community 1. Close to the port and industrial area. Great value for money.', '123 Community 1 Road', 'Tema', 'Greater Accra', 'GA-TM-1234', 2, 1.5, 850, 1500.00, 'GHS', 'apartment', 'https://images.unsplash.com/photo-1560449752-91541e11a8b8?w=600&h=400&fit=crop', 'available'),
(1, '3 Bedroom Bungalow - Tema', 'Comfortable bungalow with large compound, suitable for families. Safe neighborhood with good schools nearby.', '456 Community 5 Street', 'Tema', 'Greater Accra', 'GA-TM-1235', 3, 2.5, 1500, 2800.00, 'GHS', 'house', 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop', 'available'),

-- Dansoman Properties
(1, '2 Bedroom Flat - Dansoman', 'Well-located flat in Dansoman with good transport links. Ideal for working professionals.', '789 Dansoman Road', 'Dansoman', 'Greater Accra', 'GA-DN-7890', 2, 1.0, 750, 1400.00, 'GHS', 'apartment', 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600&h=400&fit=crop', 'available'),
(1, '4 Bedroom House - Dansoman', 'Large family house with big compound, perfect for extended families. Quiet residential area.', '321 Dansoman Street', 'Dansoman', 'Greater Accra', 'GA-DN-7891', 4, 3.5, 2200, 3200.00, 'GHS', 'house', 'https://images.unsplash.com/photo-1600607687644-c7171b42498b?w=600&h=400&fit=crop', 'available'),

-- Osu Properties
(1, '1 Bedroom Studio - Osu', 'Modern studio in the heart of Osu, close to nightlife, restaurants, and the beach. Perfect for young professionals.', '555 Osu Oxford Street', 'Osu', 'Greater Accra', 'GA-OS-5555', 1, 1.0, 550, 1800.00, 'GHS', 'studio', 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?w=600&h=400&fit=crop', 'available'),
(1, '2 Bedroom Apartment - Osu', 'Bright apartment with sea view, modern kitchen, and security. Located in vibrant Osu neighborhood.', '777 Osu Road', 'Osu', 'Greater Accra', 'GA-OS-5556', 2, 2.0, 950, 2400.00, 'GHS', 'apartment', 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=600&h=400&fit=crop', 'available'),

-- Spintex Properties
(1, '3 Bedroom Townhouse - Spintex', 'Modern townhouse in gated community with security, parking, and recreational facilities.', '111 Spintex Road', 'Spintex', 'Greater Accra', 'GA-SP-1111', 3, 2.5, 1600, 3600.00, 'GHS', 'townhouse', 'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=600&h=400&fit=crop', 'available'),
(1, '2 Bedroom Apartment - Spintex', 'Comfortable apartment with good natural light and ventilation. Close to shopping malls and offices.', '222 Spintex Avenue', 'Spintex', 'Greater Accra', 'GA-SP-1112', 2, 2.0, 1050, 2200.00, 'GHS', 'apartment', 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=600&h=400&fit=crop', 'available'),

-- Adenta Properties
(1, 'Affordable 2 Bedroom House - Adenta', 'Budget-friendly house with compound, ideal for small families. Quiet residential area with good schools.', '333 Adenta Road', 'Adenta', 'Greater Accra', 'GA-AD-3333', 2, 1.5, 800, 1600.00, 'GHS', 'house', 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&h=400&fit=crop', 'available'),
(1, '3 Bedroom Bungalow - Adenta', 'Spacious bungalow with large compound, perfect for families. Safe and peaceful neighborhood.', '444 Adenta Street', 'Adenta', 'Greater Accra', 'GA-AD-3334', 3, 2.0, 1400, 2600.00, 'GHS', 'house', 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=600&h=400&fit=crop', 'available');


INSERT INTO property_images (property_id, image_url, description, is_main) VALUES
(1, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600&h=400&fit=crop', 'Living Room', 1),
(1, 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=600&h=400&fit=crop', 'Kitchen', 0),
(1, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=600&h=400&fit=crop', 'Bedroom', 0);



INSERT INTO amenities (amenity_name) VALUES
('Swimming Pool'),
('Gym'),
('Parking'),
('Pet Friendly'),
('Balcony'),
('Air Conditioning');


INSERT INTO property_amenities (property_id, amenity_id) VALUES
(1, 3), -- Parking
(1, 5), -- Balcony
(2, 3), -- Parking
(3, 3), -- Parking
(3, 2); -- Gym


-- Sample viewing request
INSERT INTO view_requests (client_id, property_id, requested_date_time, status) VALUES
(2, 1, '2025-12-15 10:00:00', 'pending');

-- Sample booking
INSERT INTO bookings (client_id, property_id, start_date, end_date, monthly_rent, security_deposit, status) VALUES
(2, 3, '2026-01-01', '2026-12-31', 4500.00, 9000.00, 'pending');

-- Sample review
INSERT INTO reviews (client_id, property_id, rating, comment) VALUES
(2, 1, 5, 'Excellent apartment! Very spacious and great location.');


-- You can now login with:
-- Landlord: landlord@example.com / password123
-- Client: client@example.com / password123
-- Admin: admin@example.com / password123
-- ============================================
