-- Create Database
CREATE DATABASE IF NOT EXISTS form_builder;
USE form_builder;

-- Forms Table
CREATE TABLE IF NOT EXISTS forms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    structure_json TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Form Submissions Table
CREATE TABLE IF NOT EXISTS form_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    form_id INT NOT NULL,
    response_json TEXT NOT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
);

-- Sample Data (Optional)
INSERT INTO forms (title, description, structure_json) VALUES 
(
    'Contact Form',
    'Please fill out this contact form',
    '[{"label":"Name","type":"text","required":true},{"label":"Email","type":"email","required":true},{"label":"Age","type":"number","required":false},{"label":"Gender","type":"dropdown","options":["Male","Female","Other"],"required":true},{"label":"Subscribe","type":"checkbox","options":["Yes, I want to subscribe"],"required":false}]'

);
