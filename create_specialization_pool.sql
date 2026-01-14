-- Migration: Create specialization_pool table
-- Purpose: Allows admins to create a pool of specializations that can be assigned to users and teams
-- Author: System
-- Date: 2026-01-14

-- Create specialization_pool table
CREATE TABLE IF NOT EXISTS specialization_pool (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    department VARCHAR(255) NULL,
    college VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_specialization (name, department, college),
    INDEX idx_college (college),
    INDEX idx_department (department),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create user_specializations table to track specialization assignments
CREATE TABLE IF NOT EXISTS user_specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    specialization_id INT NOT NULL,
    assigned_by INT UNSIGNED NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    UNIQUE KEY unique_user_specialization (user_id, specialization_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specialization_pool(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create team_specializations table to track team specialization assignments
CREATE TABLE IF NOT EXISTS team_specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT UNSIGNED NOT NULL,
    specialization_id INT NOT NULL,
    assigned_by INT UNSIGNED NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    UNIQUE KEY unique_team_specialization (team_id, specialization_id),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specialization_pool(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some sample specializations (optional - can be removed in production)
INSERT INTO specialization_pool (name, description, department, college, is_active) VALUES
('Artificial Intelligence', 'Focus on AI and machine learning technologies', 'Computer Science', 'College of Science', 1),
('Cybersecurity', 'Information security and network protection', 'Computer Science', 'College of Science', 1),
('Software Engineering', 'Development methodologies and software design', 'Computer Science', 'College of Science', 1),
('Construction Technology and Management', 'Building construction and project management', 'Civil Engineering', 'College of Engineering', 1),
('Structural Engineering', 'Design and analysis of structures', 'Civil Engineering', 'College of Engineering', 1)
ON DUPLICATE KEY UPDATE name=name;
