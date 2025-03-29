-- Create database
CREATE DATABASE IF NOT EXISTS campascompass;
USE campascompass;

-- Create users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15),
    user_type ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create colleges table with updated structure
CREATE TABLE colleges (
    ranking INT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    contact VARCHAR(100),
    fees DECIMAL(10,2),
    location TEXT NOT NULL,
    maplink VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create courses table with updated foreign key
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    college_ranking INT,
    course_name VARCHAR(200) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    fee_structure TEXT,
    seats_available INT,
    FOREIGN KEY (college_ranking) REFERENCES colleges(ranking) ON DELETE CASCADE
);

-- Create reviews table with updated foreign key
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    college_ranking INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (college_ranking) REFERENCES colleges(ranking) ON DELETE CASCADE
);

-- Create saved_colleges table with updated foreign key
CREATE TABLE saved_colleges (
    user_id INT,
    college_ranking INT,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, college_ranking),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (college_ranking) REFERENCES colleges(ranking) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, user_type) 
VALUES (
    'admin',
    'admin@collegefinder.com',
    '$2y$10$8tN.cur.LSLJ0Sd9JVKLH.q8PU.HvGTrX.GDwkNwH7oiHXQLW4WvG',
    'System Admin',
    'admin'
);
