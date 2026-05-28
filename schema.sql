CREATE DATABASE IF NOT EXISTS gs_family_db;
USE gs_family_db;

-- Users table for admin access
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Families table
CREATE TABLE IF NOT EXISTS families (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sno INT,
    family_code VARCHAR(20) UNIQUE NOT NULL, -- e.g., FAM-001
    family_number VARCHAR(50),
    address TEXT NOT NULL,
    house_number VARCHAR(50),
    road VARCHAR(100),
    gs_division VARCHAR(100),
    income_level VARCHAR(50) NOT NULL,
    contact_no VARCHAR(20),
    member_count INT DEFAULT 1,
    housing_condition TEXT,
    remarks TEXT,
    signature VARCHAR(255),
    is_homeless TINYINT(1) DEFAULT 0,
    is_disaster TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Family members table
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    nic VARCHAR(20),
    dob DATE,
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    occupation VARCHAR(100),
    contact_number VARCHAR(20),
    relationship VARCHAR(50), -- e.g., Head, Spouse, Son, Daughter
    person_house_number VARCHAR(50),
    aswesuma VARCHAR(100) DEFAULT '0',
    pmam INT DEFAULT 0,
    kidney_disease VARCHAR(100) DEFAULT '0',
    disabled VARCHAR(100) DEFAULT '0',
    is_widow TINYINT(1) DEFAULT 0,
    is_pregnant TINYINT(1) DEFAULT 0,
    is_elder TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
-- Using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$8WvS.H/gJjZt9P8O.D3V.OeFh4xO9Z.GjI/1oY7e1K.u2v7k7QWGe');

-- ============================================================
-- SCHEMA UPDATE (BUG 3 FIX) - Missing tables and columns
-- ============================================================

-- Add missing page_category column to families table
ALTER TABLE families ADD COLUMN IF NOT EXISTS page_category VARCHAR(50) DEFAULT NULL;

-- Persons table (used by upload processes)
CREATE TABLE IF NOT EXISTS persons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    nic VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    dob DATE DEFAULT NULL,
    age INT DEFAULT 0,
    occupation VARCHAR(100) DEFAULT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    person_house_number VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Person page records table (links persons to families and categories)
CREATE TABLE IF NOT EXISTS person_page_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    family_id INT NOT NULL,
    page_category VARCHAR(50) NOT NULL,
    person_sno INT DEFAULT NULL,
    aswesuma VARCHAR(100) DEFAULT '0',
    pmam INT DEFAULT 0,
    kidney_disease VARCHAR(100) DEFAULT '0',
    disabled VARCHAR(100) DEFAULT '0',
    is_widow TINYINT(1) DEFAULT 0,
    is_pregnant TINYINT(1) DEFAULT 0,
    is_elder VARCHAR(50) DEFAULT NULL,
    signature VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
);
