-- SQL schema for Job Awareness demo
-- Create database (run as root or appropriate user)
CREATE DATABASE IF NOT EXISTS `job_awareness` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `job_awareness`;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `company` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `keywords` VARCHAR(512) DEFAULT NULL,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sample job
INSERT INTO `jobs` (title, company, location, description, created_at) VALUES
('Frontend Engineer','Acme Corp','Remote','Work on building beautiful user interfaces',NOW());
