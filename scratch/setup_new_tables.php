<?php
require_once 'includes/config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS persons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        nic VARCHAR(20),
        gender ENUM('Male', 'Female', 'Other'),
        dob DATE,
        age INT,
        occupation VARCHAR(100),
        contact_number VARCHAR(20),
        person_house_number VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS person_page_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        person_id INT NOT NULL,
        family_id INT NOT NULL,
        page_category VARCHAR(50) NOT NULL, -- widow, elderly, etc.
        person_sno INT,
        aswesuma VARCHAR(100) DEFAULT '0',
        pmam INT DEFAULT 0,
        kidney_disease VARCHAR(100) DEFAULT '0',
        disabled VARCHAR(100) DEFAULT '0',
        is_widow TINYINT(1) DEFAULT 0,
        is_pregnant TINYINT(1) DEFAULT 0,
        is_elder VARCHAR(50) DEFAULT '0',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
    )");

    echo "New tables created successfully.\n";
} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}
