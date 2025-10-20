-- FitVerse Database Setup
-- This file contains the SQL commands to create the necessary tables

-- Create database (run this first)
CREATE DATABASE IF NOT EXISTS fitverse_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitverse_db;

-- User Table (Main user accounts)
CREATE TABLE IF NOT EXISTS user_table (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('Client', 'Trainer', 'Nutritionist', 'Supervisor', 'Admin') NOT NULL,
    gender ENUM('male', 'female', 'other') DEFAULT NULL,
    dob DATE DEFAULT NULL,
    status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Nutritionist Table (Professional details for nutritionists)
CREATE TABLE IF NOT EXISTS nutritionist (
    nutritionist_id INT PRIMARY KEY,
    gender ENUM('male', 'female', 'other') NOT NULL,
    dob DATE NOT NULL,
    nic VARCHAR(20) UNIQUE NOT NULL,
    nic_image_path VARCHAR(500),
    specialization VARCHAR(255) NOT NULL,
    experience_years INT NOT NULL DEFAULT 0,
    certification VARCHAR(500),
    qualifications TEXT,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nutritionist_id) REFERENCES user_table(user_id) ON DELETE CASCADE
);

-- Trainer Table (Professional details for trainers)
CREATE TABLE IF NOT EXISTS trainer (
    trainer_id INT PRIMARY KEY,
    gender ENUM('male', 'female', 'other') NOT NULL,
    dob DATE NOT NULL,
    nic VARCHAR(20) UNIQUE NOT NULL,
    nic_image_path VARCHAR(500),
    specialization VARCHAR(255) NOT NULL,
    experience_years INT NOT NULL DEFAULT 0,
    certification VARCHAR(500),
    qualifications TEXT,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES user_table(user_id) ON DELETE CASCADE
);

-- Client Table (Client details)
CREATE TABLE IF NOT EXISTS client (
    client_id INT PRIMARY KEY,
    gender ENUM('male', 'female', 'other') NOT NULL,
    dob DATE NOT NULL,
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    fitness_goal TEXT,
    medical_conditions TEXT,
    dietary_restrictions TEXT,
    activity_level ENUM('sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extremely_active') DEFAULT 'sedentary',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES user_table(user_id) ON DELETE CASCADE
);

-- Client-Nutritionist Assignment Table
CREATE TABLE IF NOT EXISTS client_nutritionist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    nutritionist_id INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES client(client_id) ON DELETE CASCADE,
    FOREIGN KEY (nutritionist_id) REFERENCES nutritionist(nutritionist_id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_assignment (client_id, nutritionist_id, status)
);

-- Client-Trainer Assignment Table
CREATE TABLE IF NOT EXISTS client_trainer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    trainer_id INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES client(client_id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES trainer(trainer_id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_assignment (client_id, trainer_id, status)
);

-- Insert sample supervisor user (password: 'supervisor123')
INSERT INTO user_table (name, email, phone, password, role, status) VALUES 
('Supervisor Admin', 'supervisor@fitverse.com', '+94771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Supervisor', 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Create indexes for better performance
CREATE INDEX idx_user_email ON user_table(email);
CREATE INDEX idx_user_role ON user_table(role);
CREATE INDEX idx_nutritionist_status ON nutritionist(status);
CREATE INDEX idx_trainer_status ON trainer(status);
CREATE INDEX idx_client_status ON client(status);

-- Create upload directories (you'll need to create these manually on your server)
-- uploads/
-- uploads/nic_images/
-- uploads/certifications/
-- uploads/profile_images/