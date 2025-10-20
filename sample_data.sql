-- Sample Data for Testing FitVerse Nutritionist Management
-- Run this after setting up the main database structure

USE fitverse_db;

-- Insert sample nutritionists (passwords are 'password123')
INSERT INTO user_table (name, email, phone, password, role, gender, dob, status) VALUES 
('Dr. Sarah Johnson', 'sarah.johnson@fitverse.com', '+94771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutritionist', 'female', '1985-03-15', 'Active'),
('Dr. Michael Chen', 'michael.chen@fitverse.com', '+94772345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutritionist', 'male', '1982-07-22', 'Active'),
('Dr. Priya Patel', 'priya.patel@fitverse.com', '+94773456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutritionist', 'female', '1988-11-08', 'Active'),
('Dr. James Wilson', 'james.wilson@fitverse.com', '+94774567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutritionist', 'male', '1980-05-12', 'Active'),
('Dr. Lisa Rodriguez', 'lisa.rodriguez@fitverse.com', '+94775678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutritionist', 'female', '1990-09-25', 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Get the user IDs for the nutritionists we just inserted
SET @sarah_id = (SELECT user_id FROM user_table WHERE email = 'sarah.johnson@fitverse.com');
SET @michael_id = (SELECT user_id FROM user_table WHERE email = 'michael.chen@fitverse.com');
SET @priya_id = (SELECT user_id FROM user_table WHERE email = 'priya.patel@fitverse.com');
SET @james_id = (SELECT user_id FROM user_table WHERE email = 'james.wilson@fitverse.com');
SET @lisa_id = (SELECT user_id FROM user_table WHERE email = 'lisa.rodriguez@fitverse.com');

-- Insert nutritionist professional details
INSERT INTO nutritionist (nutritionist_id, gender, dob, nic, specialization, experience_years, certification, qualifications, status) VALUES 
(@sarah_id, 'female', '1985-03-15', '198507512345', 'Sports Nutrition', 8, 'Certified Sports Nutritionist (CISSN)', 'MSc in Sports Nutrition, BSc in Dietetics, Certified Strength and Conditioning Specialist', 'active'),
(@michael_id, 'male', '1982-07-22', '198220412346', 'Clinical Nutrition', 12, 'Registered Dietitian Nutritionist (RDN)', 'PhD in Clinical Nutrition, MSc in Human Nutrition, Board Certified Specialist in Sports Dietetics', 'active'),
(@priya_id, 'female', '1988-11-08', '198831212347', 'Weight Management', 6, 'Certified Nutrition Specialist (CNS)', 'MSc in Nutrition Science, BSc in Food Science, Certified Diabetes Educator', 'pending'),
(@james_id, 'male', '1980-05-12', '198013212348', 'Pediatric Nutrition', 15, 'Board Certified Pediatric Nutritionist', 'PhD in Pediatric Nutrition, MSc in Child Development, Registered Dietitian', 'active'),
(@lisa_id, 'female', '1990-09-25', '199026812349', 'Plant-Based Nutrition', 4, 'Certified Plant-Based Nutritionist', 'MSc in Plant-Based Nutrition, BSc in Biology, Certified Holistic Nutritionist', 'inactive')
ON DUPLICATE KEY UPDATE specialization = VALUES(specialization);

-- Insert some sample clients for testing assignments
INSERT INTO user_table (name, email, phone, password, role, gender, dob, status) VALUES 
('John Smith', 'john.smith@email.com', '+94776789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Client', 'male', '1992-04-18', 'Active'),
('Emma Davis', 'emma.davis@email.com', '+94777890123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Client', 'female', '1995-08-30', 'Active'),
('Alex Johnson', 'alex.johnson@email.com', '+94778901234', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Client', 'other', '1988-12-05', 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Get client IDs
SET @john_id = (SELECT user_id FROM user_table WHERE email = 'john.smith@email.com');
SET @emma_id = (SELECT user_id FROM user_table WHERE email = 'emma.davis@email.com');
SET @alex_id = (SELECT user_id FROM user_table WHERE email = 'alex.johnson@email.com');

-- Insert client details
INSERT INTO client (client_id, gender, dob, height, weight, fitness_goal, activity_level, status) VALUES 
(@john_id, 'male', '1992-04-18', 175.50, 78.20, 'Build muscle mass and improve strength', 'moderately_active', 'active'),
(@emma_id, 'female', '1995-08-30', 162.00, 58.50, 'Weight loss and improved cardiovascular health', 'lightly_active', 'active'),
(@alex_id, 'other', '1988-12-05', 170.00, 65.80, 'Maintain current weight and improve nutrition', 'very_active', 'active')
ON DUPLICATE KEY UPDATE fitness_goal = VALUES(fitness_goal);

-- Sample client-nutritionist assignments
INSERT INTO client_nutritionist (client_id, nutritionist_id, status, notes) VALUES 
(@john_id, @sarah_id, 'active', 'Focus on muscle building nutrition plan'),
(@emma_id, @priya_id, 'active', 'Weight management program with meal planning'),
(@alex_id, @lisa_id, 'active', 'Plant-based nutrition transition plan')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- Display summary
SELECT 'Sample Data Insertion Complete' as Status;
SELECT 'Nutritionists Created:' as Info, COUNT(*) as Count FROM nutritionist;
SELECT 'Clients Created:' as Info, COUNT(*) as Count FROM client;
SELECT 'Active Assignments:' as Info, COUNT(*) as Count FROM client_nutritionist WHERE status = 'active';

-- Show sample nutritionist data
SELECT 
    CONCAT('NT', LPAD(n.nutritionist_id, 5, '0')) as ID,
    u.name,
    u.email,
    n.specialization,
    n.experience_years,
    n.status
FROM nutritionist n
JOIN user_table u ON n.nutritionist_id = u.user_id
ORDER BY u.name;