-- Marketing AI System Database Schema (MySQL 8.0+)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` CHAR(36) PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50),
    `role` ENUM('client', 'admin', 'analyst') DEFAULT 'client',
    `status` ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    `email_verified` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL,
    `settings` JSON NULL, -- Stores preferred theme, language, etc.
    `metadata` JSON NULL -- Extra info
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_users_email ON `users`(`email`);
CREATE INDEX idx_users_status ON `users`(`status`);

-- 2. Companies Table
CREATE TABLE IF NOT EXISTS `companies` (
    `id` CHAR(36) PRIMARY KEY,
    `user_id` CHAR(36) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `sector` VARCHAR(100) NOT NULL, -- education, healthcare, fnb, retail, etc.
    `legal_name` VARCHAR(255),
    `registration_number` VARCHAR(100),
    `founded_year` INT,
    `employee_count` INT,
    `location` JSON NULL, -- {country, city, address}
    `contact_info` JSON NULL, -- {phone, email, website}
    `logo_url` VARCHAR(500),
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_companies_sector ON `companies`(`sector`);

-- 3. Assessment Sessions Table
CREATE TABLE IF NOT EXISTS `assessment_sessions` (
    `id` CHAR(36) PRIMARY KEY,
    `company_id` CHAR(36) NOT NULL,
    `user_id` CHAR(36) NOT NULL,
    `session_type` VARCHAR(50) DEFAULT 'full_assessment',
    `status` ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    `progress_percent` INT DEFAULT 0,
    `current_question_id` VARCHAR(100) NULL,
    `context` JSON NULL, -- industry, urgency, business_stage
    `metadata` JSON NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_sessions_status ON `assessment_sessions`(`status`);

-- 4. Questions Table
CREATE TABLE IF NOT EXISTS `questions` (
    `id` VARCHAR(100) PRIMARY KEY, -- Q_FIN_001
    `category` VARCHAR(100) NOT NULL,
    `subcategory` VARCHAR(100),
    `question_ar` TEXT NOT NULL,
    `question_en` TEXT NOT NULL,
    `question_type` VARCHAR(50) NOT NULL, -- multiple_choice, scale_rating, numeric_input, text_input
    `required` BOOLEAN DEFAULT TRUE,
    `priority` ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    `display_order` INT DEFAULT 0,
    `help_text_ar` TEXT,
    `help_text_en` TEXT,
    `validation_rules` JSON NULL,
    `options` JSON NULL, -- [{value, label_ar, label_en}]
    `metadata` JSON NULL, -- weight, affects, unlocks
    `sector_specific` VARCHAR(100) NULL, -- null means all
    `active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_questions_category ON `questions`(`category`);
CREATE INDEX idx_questions_sector ON `questions`(`sector_specific`);

-- 5. Answers Table
CREATE TABLE IF NOT EXISTS `answers` (
    `id` CHAR(36) PRIMARY KEY,
    `session_id` CHAR(36) NOT NULL,
    `question_id` VARCHAR(100) NOT NULL,
    `answer_value` TEXT NULL,
    `answer_normalized` JSON NULL,
    `confidence_score` DECIMAL(3,2) DEFAULT 1.00,
    `source` VARCHAR(50) DEFAULT 'user_input',
    `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `time_taken_seconds` INT DEFAULT 0,
    `metadata` JSON NULL,
    UNIQUE KEY `unique_session_question` (`session_id`, `question_id`),
    FOREIGN KEY (`session_id`) REFERENCES `assessment_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Analysis Results Table
CREATE TABLE IF NOT EXISTS `analysis_results` (
    `id` CHAR(36) PRIMARY KEY,
    `session_id` CHAR(36) NOT NULL,
    `analysis_type` VARCHAR(100) NOT NULL, -- financial, market, digital, brand, etc.
    `expert_id` VARCHAR(100) NOT NULL, -- chief_strategist, etc.
    `scores` JSON NULL, -- {maturity: 85, risk: 2, etc.}
    `insights` JSON NULL, -- [{type, text, severity}]
    `flags` JSON NULL, -- red/green flags
    `recommendations` JSON NULL, -- specific to this analysis
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `assessment_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Alerts Table
CREATE TABLE IF NOT EXISTS `alerts` (
    `id` CHAR(36) PRIMARY KEY,
    `session_id` CHAR(36) NOT NULL,
    `alert_type` ENUM('info', 'warning', 'high', 'critical') DEFAULT 'info',
    `category` VARCHAR(100),
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `severity` INT DEFAULT 1, -- 1-10
    `pattern_id` VARCHAR(100),
    `assigned_expert` VARCHAR(100),
    `recommendation` TEXT,
    `acknowledged` BOOLEAN DEFAULT FALSE,
    `acknowledged_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `assessment_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Recommendations Table (Consolidated Strategic Recommendations)
CREATE TABLE IF NOT EXISTS `recommendations` (
    `id` CHAR(36) PRIMARY KEY,
    `session_id` CHAR(36) NOT NULL,
    `layer` ENUM('strategic', 'tactical', 'execution') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `priority` INT DEFAULT 1,
    `impact` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `effort` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `metadata` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `assessment_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Reports Table
CREATE TABLE IF NOT EXISTS `reports` (
    `id` CHAR(36) PRIMARY KEY,
    `session_id` CHAR(36) NOT NULL,
    `company_id` CHAR(36) NOT NULL,
    `report_type` ENUM('executive', 'detailed', 'action_plan', 'monthly') NOT NULL,
    `title` VARCHAR(255),
    `content` JSON NULL, -- Complete report structure
    `pdf_url` VARCHAR(500),
    `status` ENUM('generating', 'ready', 'failed') DEFAULT 'generating',
    `generated_at` TIMESTAMP NULL,
    `viewed_at` TIMESTAMP NULL,
    `metadata` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `assessment_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Benchmarks Table
CREATE TABLE IF NOT EXISTS `benchmarks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sector` VARCHAR(100) NOT NULL,
    `metric_key` VARCHAR(100) NOT NULL,
    `metric_name` VARCHAR(255) NOT NULL,
    `average_value` DECIMAL(15,2),
    `top_performers_value` DECIMAL(15,2),
    `unit` VARCHAR(50),
    `source` VARCHAR(255),
    `metadata` JSON NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Patterns Table
CREATE TABLE IF NOT EXISTS `patterns` (
    `id` VARCHAR(100) PRIMARY KEY, -- PAT_GROWTH_001
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `trigger_logic` JSON NOT NULL, -- Rules for detection
    `category` VARCHAR(100),
    `active` BOOLEAN DEFAULT TRUE,
    `metadata` JSON NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Activity Log Table
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` CHAR(36),
    `action` VARCHAR(255) NOT NULL,
    `entity_type` VARCHAR(100),
    `entity_id` CHAR(36),
    `details` JSON NULL,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Subscriptions (Mapping current docs)
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` CHAR(36) PRIMARY KEY,
    `user_id` CHAR(36) NOT NULL,
    `plan_type` VARCHAR(50) NOT NULL,
    `status` VARCHAR(50) DEFAULT 'active',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `auto_renew` BOOLEAN DEFAULT TRUE,
    `limits` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
