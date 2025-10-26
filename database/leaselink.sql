-- SQL script to create leaselink and tables for Sprint 1
CREATE DATABASE IF NOT EXISTS leaselink_db;
USE leaselink_db;

-- Landlords table
CREATE TABLE IF NOT EXISTS landlords (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clients (tenants) table
CREATE TABLE IF NOT EXISTS clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Properties (fake data for listings)
CREATE TABLE IF NOT EXISTS properties (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  location VARCHAR(150),
  price VARCHAR(50),
  description TEXT,
  image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample properties
INSERT INTO properties (title, location, price, description, image_url) VALUES
('2 Bedroom Apartment', 'East Legon', 'GHS 2,000 / month', 'Spacious 2 bedroom apartment with balcony and modern kitchen.', 'https://placehold.co/600x400'),
('Studio Apartment', 'Madina', 'GHS 1,200 / month', 'Comfortable studio close to transport and markets.', 'https://placehold.co/600x400'),
('3 Bedroom Townhouse', 'Airport Residential', 'GHS 3,500 / month', 'Secure compound, parking and fenced yard.', 'https://placehold.co/600x400');

-- Insert a sample landlord and client 
INSERT INTO landlords (name, email, password) VALUES
('Ajak Pachol', 'landlord@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe');
-- prehased password for "password123"

INSERT INTO clients (name, email, password) VALUES
('Sample Client', 'client@example.com', '$2y$10$ObuyYgvTB0VtiI0qTIajf.wX2fTGMtl/GLfhj0QHQHnbVhPyeeUIe');
-- prehashed password
