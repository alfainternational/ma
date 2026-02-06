-- ========================================
-- Marketing AI System - Database Schema
-- Version: 1.0.0
-- Charset: utf8mb4_unicode_ci
-- ========================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = 'utf8mb4_unicode_ci';

CREATE DATABASE IF NOT EXISTS marketing_ai
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE marketing_ai;

-- ========================================
-- 1. USERS TABLE
-- ========================================
DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS recommendations;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS analysis_results;
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS assessment_sessions;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS benchmarks;
DROP TABLE IF EXISTS patterns;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    company_name VARCHAR(100),
    role ENUM('admin', 'client', 'analyst') DEFAULT 'client',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expires TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    metadata JSON DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2. COMPANIES TABLE
-- ========================================
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    sector VARCHAR(50) NOT NULL,
    years_in_business DECIMAL(4,1),
    employee_count INT,
    annual_revenue DECIMAL(15,2),
    monthly_costs DECIMAL(15,2),
    location JSON,
    contact_info JSON,
    logo VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sector (sector),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. QUESTIONS TABLE (250 questions)
-- ========================================
CREATE TABLE questions (
    id VARCHAR(20) PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    subcategory VARCHAR(50),
    question_ar TEXT NOT NULL,
    question_en TEXT NOT NULL,
    type ENUM('single_choice', 'multiple_choice', 'numeric_input', 'text_input', 'scale_rating') NOT NULL,
    options JSON,
    validation_rules JSON,
    input_config JSON,
    help_text_ar TEXT,
    help_text_en TEXT,
    required BOOLEAN DEFAULT TRUE,
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    display_order INT,
    weight DECIMAL(3,2) DEFAULT 1.00,
    sector_specific VARCHAR(50),
    skip_logic JSON,
    follow_up_questions JSON,
    expert_usage JSON,
    ai_processing JSON,
    metadata JSON,
    active BOOLEAN DEFAULT TRUE,
    INDEX idx_category (category),
    INDEX idx_order (display_order),
    INDEX idx_priority (priority),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. ASSESSMENT SESSIONS TABLE
-- ========================================
CREATE TABLE assessment_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_uuid VARCHAR(36) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    company_id INT NOT NULL,
    session_type ENUM('full_assessment', 'quick_check') DEFAULT 'full_assessment',
    status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
    progress_percent INT DEFAULT 0,
    current_question_id VARCHAR(20),
    total_questions INT DEFAULT 0,
    answered_questions INT DEFAULT 0,
    context JSON DEFAULT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    metadata JSON DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_company (company_id),
    INDEX idx_uuid (session_uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5. ANSWERS TABLE
-- ========================================
CREATE TABLE answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    question_id VARCHAR(20) NOT NULL,
    answer_value TEXT,
    answer_normalized JSON,
    confidence_score DECIMAL(3,2) DEFAULT 1.00,
    source VARCHAR(50) DEFAULT 'user_input',
    time_taken_seconds INT,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id),
    UNIQUE KEY unique_session_question (session_id, question_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6. ANALYSIS RESULTS TABLE
-- ========================================
CREATE TABLE analysis_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    scores JSON NOT NULL,
    patterns_detected JSON,
    expert_insights JSON,
    strategic_recommendation JSON,
    predictions JSON,
    overall_summary TEXT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7. ALERTS TABLE
-- ========================================
CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    alert_type ENUM('critical', 'high', 'warning', 'info', 'opportunity') NOT NULL,
    alert_id VARCHAR(50),
    severity INT CHECK (severity BETWEEN 1 AND 10),
    category VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    recommendation TEXT,
    expert_id VARCHAR(50),
    pattern_id VARCHAR(50),
    acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    INDEX idx_severity (severity DESC),
    INDEX idx_type (alert_type),
    INDEX idx_acknowledged (acknowledged)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 8. RECOMMENDATIONS TABLE
-- ========================================
CREATE TABLE recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    layer ENUM('strategic', 'tactical', 'execution') NOT NULL,
    priority INT DEFAULT 0,
    category VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    action_items JSON,
    specific_steps JSON,
    expected_investment DECIMAL(15,2),
    expected_return VARCHAR(100),
    expected_roi VARCHAR(50),
    timeline VARCHAR(50),
    assigned_expert VARCHAR(50),
    status ENUM('pending', 'accepted', 'implemented', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    INDEX idx_layer (layer),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 9. REPORTS TABLE
-- ========================================
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    company_id INT NOT NULL,
    report_type ENUM('executive_summary', 'detailed_analysis', 'action_plan', 'monthly_performance', 'competitive_intelligence') NOT NULL,
    title VARCHAR(255),
    content JSON,
    pdf_path VARCHAR(255),
    status ENUM('generating', 'ready', 'failed') DEFAULT 'generating',
    generated_at TIMESTAMP NULL,
    viewed_at TIMESTAMP NULL,
    downloaded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_type (report_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 10. BENCHMARKS TABLE
-- ========================================
CREATE TABLE benchmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sector VARCHAR(50) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,2),
    metric_range_min DECIMAL(10,2),
    metric_range_max DECIMAL(10,2),
    unit VARCHAR(20),
    description_ar TEXT,
    source VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_sector_metric (sector, metric_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 11. PATTERNS TABLE
-- ========================================
CREATE TABLE patterns (
    id VARCHAR(50) PRIMARY KEY,
    pattern_name VARCHAR(100) NOT NULL,
    pattern_name_ar VARCHAR(200),
    conditions JSON NOT NULL,
    inference TEXT NOT NULL,
    inference_ar TEXT,
    recommended_action TEXT,
    recommended_action_ar TEXT,
    min_match_ratio DECIMAL(3,2) DEFAULT 1.00,
    severity VARCHAR(20) DEFAULT 'medium',
    assigned_experts JSON,
    detected_count INT DEFAULT 0,
    success_rate DECIMAL(3,2),
    active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 12. ACTIVITY LOG TABLE
-- ========================================
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 13. SETTINGS TABLE
-- ========================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string',
    description_ar TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- DEFAULT DATA
-- ========================================

-- Default Admin User (password: Admin@123)
INSERT INTO users (email, password, full_name, role, status, email_verified) VALUES
('admin@marketingai.com', '$2y$12$LJ3m4bKgYGxQZ9vQm3hMnOJzMFqKj.RvGqzMbvhBxvKqxqVjHPpTi', 'مدير النظام', 'admin', 'active', TRUE);

-- Default Settings
INSERT INTO settings (setting_key, setting_value, setting_type, description_ar) VALUES
('site_name', 'Marketing AI System', 'string', 'اسم الموقع'),
('site_name_ar', 'نظام الذكاء الاصطناعي التسويقي', 'string', 'اسم الموقع بالعربية'),
('default_language', 'ar', 'string', 'اللغة الافتراضية'),
('questions_per_page', '1', 'integer', 'عدد الأسئلة في الصفحة'),
('auto_save_interval', '30', 'integer', 'فاصل الحفظ التلقائي بالثواني'),
('analysis_confidence_threshold', '0.75', 'float', 'حد الثقة للتحليل'),
('max_sessions_per_company', '10', 'integer', 'الحد الأقصى للجلسات لكل شركة'),
('enable_real_time_alerts', '1', 'boolean', 'تفعيل التنبيهات الفورية');

-- Benchmarks Data (for all 8 sectors)
INSERT INTO benchmarks (sector, metric_name, metric_value, metric_range_min, metric_range_max, unit, description_ar) VALUES
-- Education
('education', 'marketing_budget_percent', 12.00, 8.00, 20.00, '%', 'نسبة الميزانية التسويقية من الإيرادات'),
('education', 'cac', 500.00, 200.00, 1000.00, 'SAR', 'تكلفة اكتساب العميل'),
('education', 'expected_roi', 4.00, 2.00, 6.00, 'x', 'العائد المتوقع على الاستثمار'),
('education', 'avg_revenue_per_student', 30000.00, 15000.00, 60000.00, 'SAR', 'متوسط الإيراد لكل طالب'),
('education', 'retention_rate', 85.00, 70.00, 95.00, '%', 'معدل الاستبقاء'),
-- Healthcare
('healthcare', 'marketing_budget_percent', 15.00, 10.00, 25.00, '%', 'نسبة الميزانية التسويقية'),
('healthcare', 'cac', 1500.00, 500.00, 3000.00, 'SAR', 'تكلفة اكتساب العميل'),
('healthcare', 'expected_roi', 5.00, 3.00, 8.00, 'x', 'العائد المتوقع على الاستثمار'),
('healthcare', 'avg_patient_value', 5000.00, 2000.00, 15000.00, 'SAR', 'متوسط قيمة المريض'),
('healthcare', 'retention_rate', 75.00, 60.00, 90.00, '%', 'معدل الاستبقاء'),
-- F&B
('fnb', 'marketing_budget_percent', 8.00, 5.00, 15.00, '%', 'نسبة الميزانية التسويقية'),
('fnb', 'cac', 150.00, 50.00, 300.00, 'SAR', 'تكلفة اكتساب العميل'),
('fnb', 'expected_roi', 3.00, 2.00, 5.00, 'x', 'العائد المتوقع على الاستثمار'),
('fnb', 'avg_ticket', 75.00, 30.00, 200.00, 'SAR', 'متوسط الفاتورة'),
('fnb', 'retention_rate', 60.00, 40.00, 80.00, '%', 'معدل الاستبقاء'),
-- Retail
('retail', 'marketing_budget_percent', 10.00, 5.00, 15.00, '%', 'نسبة الميزانية التسويقية'),
('retail', 'cac', 300.00, 100.00, 600.00, 'SAR', 'تكلفة اكتساب العميل'),
('retail', 'expected_roi', 4.00, 2.50, 6.00, 'x', 'العائد المتوقع على الاستثمار'),
('retail', 'avg_order_value', 250.00, 100.00, 500.00, 'SAR', 'متوسط قيمة الطلب'),
('retail', 'retention_rate', 55.00, 35.00, 75.00, '%', 'معدل الاستبقاء'),
-- Professional Services
('professional_services', 'marketing_budget_percent', 15.00, 10.00, 25.00, '%', 'نسبة الميزانية التسويقية'),
('professional_services', 'cac', 3000.00, 1000.00, 5000.00, 'SAR', 'تكلفة اكتساب العميل'),
('professional_services', 'expected_roi', 6.00, 4.00, 10.00, 'x', 'العائد المتوقع على الاستثمار'),
('professional_services', 'avg_contract_value', 50000.00, 20000.00, 200000.00, 'SAR', 'متوسط قيمة العقد'),
('professional_services', 'retention_rate', 80.00, 65.00, 95.00, '%', 'معدل الاستبقاء'),
-- Real Estate
('real_estate', 'marketing_budget_percent', 12.00, 8.00, 20.00, '%', 'نسبة الميزانية التسويقية'),
('real_estate', 'cac', 5000.00, 2000.00, 10000.00, 'SAR', 'تكلفة اكتساب العميل'),
('real_estate', 'expected_roi', 5.00, 3.00, 8.00, 'x', 'العائد المتوقع على الاستثمار'),
('real_estate', 'avg_deal_value', 500000.00, 200000.00, 2000000.00, 'SAR', 'متوسط قيمة الصفقة'),
('real_estate', 'retention_rate', 40.00, 20.00, 60.00, '%', 'معدل الاستبقاء'),
-- Fitness
('fitness', 'marketing_budget_percent', 15.00, 10.00, 25.00, '%', 'نسبة الميزانية التسويقية'),
('fitness', 'cac', 300.00, 100.00, 600.00, 'SAR', 'تكلفة اكتساب العميل'),
('fitness', 'expected_roi', 4.00, 2.00, 6.00, 'x', 'العائد المتوقع على الاستثمار'),
('fitness', 'avg_membership_value', 4000.00, 2000.00, 10000.00, 'SAR', 'متوسط قيمة الاشتراك السنوي'),
('fitness', 'retention_rate', 65.00, 45.00, 85.00, '%', 'معدل الاستبقاء'),
-- Crafts
('crafts', 'marketing_budget_percent', 12.00, 8.00, 18.00, '%', 'نسبة الميزانية التسويقية'),
('crafts', 'cac', 500.00, 200.00, 1000.00, 'SAR', 'تكلفة اكتساب العميل'),
('crafts', 'expected_roi', 4.00, 2.50, 7.00, 'x', 'العائد المتوقع على الاستثمار'),
('crafts', 'avg_order_value', 350.00, 100.00, 1000.00, 'SAR', 'متوسط قيمة الطلب'),
('crafts', 'retention_rate', 50.00, 30.00, 70.00, '%', 'معدل الاستبقاء');
