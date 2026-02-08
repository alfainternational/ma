-- ============================================================================
-- Marketing AI System v2 - Complete Database Schema
-- Generated: 2026-02-08
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. Database Creation
-- ----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS marketingai_v2
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE marketingai_v2;

-- ----------------------------------------------------------------------------
-- 2. UUID Helper Functions
-- ----------------------------------------------------------------------------
DROP FUNCTION IF EXISTS BIN_TO_UUID;
DELIMITER //
CREATE FUNCTION BIN_TO_UUID(b BINARY(16))
RETURNS CHAR(36)
DETERMINISTIC
NO SQL
BEGIN
  DECLARE hex CHAR(32);
  SET hex = HEX(b);
  RETURN LOWER(CONCAT(
    SUBSTR(hex, 1, 8), '-',
    SUBSTR(hex, 9, 4), '-',
    SUBSTR(hex, 13, 4), '-',
    SUBSTR(hex, 17, 4), '-',
    SUBSTR(hex, 21)
  ));
END //
DELIMITER ;

DROP FUNCTION IF EXISTS UUID_TO_BIN;
DELIMITER //
CREATE FUNCTION UUID_TO_BIN(s CHAR(36))
RETURNS BINARY(16)
DETERMINISTIC
NO SQL
BEGIN
  RETURN UNHEX(REPLACE(s, '-', ''));
END //
DELIMITER ;

-- ----------------------------------------------------------------------------
-- 3. Tables
-- ----------------------------------------------------------------------------

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE users (
  id               BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  email            VARCHAR(255)  NOT NULL,
  password_hash    VARCHAR(255)  NOT NULL,
  full_name        VARCHAR(255)  NOT NULL,
  phone            VARCHAR(50)   NULL,
  role             ENUM('client','admin','analyst') NOT NULL DEFAULT 'client',
  status           ENUM('active','suspended','banned') NOT NULL DEFAULT 'active',
  email_verified_at TIMESTAMP    NULL DEFAULT NULL,
  last_login_at    TIMESTAMP     NULL DEFAULT NULL,
  settings         JSON          NULL DEFAULT ('{}'),
  created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at       TIMESTAMP     NULL DEFAULT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uk_email (email),
  INDEX idx_email (email),
  INDEX idx_status (status),
  INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: password_resets
-- --------------------------------------------------------
CREATE TABLE password_resets (
  email      VARCHAR(255) NOT NULL,
  token      VARCHAR(255) NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_email (email),
  INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: companies
-- --------------------------------------------------------
CREATE TABLE companies (
  id                   BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  user_id              BINARY(16)    NOT NULL,
  name                 VARCHAR(255)  NOT NULL,
  industry             VARCHAR(100)  NOT NULL,
  legal_name           VARCHAR(255)  NULL,
  tax_number           VARCHAR(100)  NULL,
  founded_year         YEAR          NULL,
  employee_count_range ENUM('1-10','11-50','51-200','201-500','500+') NULL,
  annual_revenue_range ENUM('<100k','100k-500k','500k-1m','1m-5m','5m+') NULL,
  location_country     VARCHAR(100)  NULL,
  location_city        VARCHAR(100)  NULL,
  website_url          VARCHAR(500)  NULL,
  phone                VARCHAR(50)   NULL,
  logo_url             VARCHAR(500)  NULL,
  status               ENUM('active','archived') NOT NULL DEFAULT 'active',
  created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at           TIMESTAMP     NULL DEFAULT NULL,

  PRIMARY KEY (id),
  INDEX idx_industry (industry),
  INDEX idx_user_id (user_id),
  CONSTRAINT fk_companies_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: subscriptions
-- --------------------------------------------------------
CREATE TABLE subscriptions (
  id           BINARY(16)  NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  user_id      BINARY(16)  NULL,
  plan_type    ENUM('free','basic','pro','enterprise') NOT NULL DEFAULT 'free',
  status       ENUM('active','suspended','cancelled','expired') NOT NULL DEFAULT 'active',
  started_at   TIMESTAMP   NULL DEFAULT NULL,
  renews_at    TIMESTAMP   NULL DEFAULT NULL,
  expires_at   TIMESTAMP   NULL DEFAULT NULL,
  auto_renew   BOOLEAN     NOT NULL DEFAULT TRUE,
  limits       JSON        NULL,
  billing_info JSON        NULL,
  created_at   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_user (user_id),
  INDEX idx_status (status),
  CONSTRAINT fk_subscriptions_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: assessment_sessions
-- --------------------------------------------------------
CREATE TABLE assessment_sessions (
  id                   BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  company_id           BINARY(16)    NULL,
  user_id              BINARY(16)    NULL,
  session_name         VARCHAR(255)  NULL,
  session_type         ENUM('full','quick','specific') NOT NULL DEFAULT 'full',
  status               ENUM('draft','in_progress','completed','abandoned') NOT NULL DEFAULT 'draft',
  current_question_id  VARCHAR(100)  NULL,
  questions_answered   INT           NOT NULL DEFAULT 0,
  questions_total      INT           NOT NULL DEFAULT 0,
  progress_percentage  DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
  started_at           TIMESTAMP     NULL DEFAULT NULL,
  completed_at         TIMESTAMP     NULL DEFAULT NULL,
  context_data         JSON          NULL,
  created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at           TIMESTAMP     NULL DEFAULT NULL,

  PRIMARY KEY (id),
  INDEX idx_status (status),
  INDEX idx_user_id (user_id),
  INDEX idx_company_id (company_id),
  CONSTRAINT fk_sessions_company
    FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_sessions_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: questions
-- --------------------------------------------------------
CREATE TABLE questions (
  id                VARCHAR(100)  NOT NULL,
  category          VARCHAR(100)  NOT NULL,
  subcategory       VARCHAR(100)  NULL,
  question_text_ar  TEXT          NOT NULL,
  question_text_en  TEXT          NOT NULL,
  question_type     ENUM('single_choice','multiple_choice','scale','number','text') NOT NULL,
  is_required       BOOLEAN       NOT NULL DEFAULT TRUE,
  priority          ENUM('critical','high','medium','low') NOT NULL DEFAULT 'medium',
  display_order     INT           NOT NULL DEFAULT 0,
  help_text_ar      TEXT          NULL,
  help_text_en      TEXT          NULL,
  validation_rules  JSON          NULL,
  options           JSON          NULL,
  metadata          JSON          NULL,
  industry_specific VARCHAR(100)  NULL,
  is_active         BOOLEAN       NOT NULL DEFAULT TRUE,
  created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_category (category),
  INDEX idx_industry (industry_specific),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: answers
-- --------------------------------------------------------
CREATE TABLE answers (
  id                BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  session_id        BINARY(16)    NOT NULL,
  question_id       VARCHAR(100)  NOT NULL,
  answer_value      TEXT          NULL,
  answer_normalized JSON          NULL,
  confidence_score  DECIMAL(3,2)  NOT NULL DEFAULT 1.00,
  source            ENUM('user','inferred','imported') NOT NULL DEFAULT 'user',
  time_spent_seconds INT          NOT NULL DEFAULT 0,
  is_skipped        BOOLEAN       NOT NULL DEFAULT FALSE,
  skip_reason       VARCHAR(255)  NULL,
  created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_session_question (session_id, question_id),
  INDEX idx_session (session_id),
  CONSTRAINT fk_answers_session
    FOREIGN KEY (session_id) REFERENCES assessment_sessions (id) ON DELETE CASCADE,
  CONSTRAINT fk_answers_question
    FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: analysis_results
-- --------------------------------------------------------
CREATE TABLE analysis_results (
  id                BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  session_id        BINARY(16)    NOT NULL,
  expert_id         VARCHAR(100)  NOT NULL,
  analysis_category VARCHAR(100)  NOT NULL,
  scores            JSON          NOT NULL,
  insights          JSON          NULL,
  flags             JSON          NULL,
  swot              JSON          NULL,
  recommendations   JSON          NULL,
  created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_session (session_id),
  INDEX idx_expert (expert_id),
  CONSTRAINT fk_analysis_session
    FOREIGN KEY (session_id) REFERENCES assessment_sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: alerts
-- --------------------------------------------------------
CREATE TABLE alerts (
  id               BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  session_id       BINARY(16)    NOT NULL,
  alert_type       ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
  category         VARCHAR(100)  NULL,
  title            VARCHAR(255)  NOT NULL,
  message          TEXT          NOT NULL,
  severity         INT           NOT NULL DEFAULT 1,
  pattern_id       VARCHAR(100)  NULL,
  assigned_expert  VARCHAR(100)  NULL,
  recommendation   TEXT          NULL,
  is_acknowledged  BOOLEAN       NOT NULL DEFAULT FALSE,
  acknowledged_at  TIMESTAMP     NULL DEFAULT NULL,
  created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_session (session_id),
  INDEX idx_type (alert_type),
  CONSTRAINT fk_alerts_session
    FOREIGN KEY (session_id) REFERENCES assessment_sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: recommendations
-- --------------------------------------------------------
CREATE TABLE recommendations (
  id                    BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  session_id            BINARY(16)    NOT NULL,
  layer                 ENUM('strategic','tactical','operational') NOT NULL,
  category              VARCHAR(100)  NULL,
  title                 VARCHAR(255)  NOT NULL,
  description           TEXT          NOT NULL,
  expected_impact       ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  implementation_effort ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  priority_score        INT           NOT NULL DEFAULT 50,
  timeframe             ENUM('immediate','30_days','90_days','6_months','1_year') NULL,
  estimated_cost        VARCHAR(100)  NULL,
  expected_roi          VARCHAR(100)  NULL,
  action_steps          JSON          NULL,
  created_at            TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_session (session_id),
  INDEX idx_priority (priority_score),
  CONSTRAINT fk_recommendations_session
    FOREIGN KEY (session_id) REFERENCES assessment_sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: reports
-- --------------------------------------------------------
CREATE TABLE reports (
  id                 BINARY(16)    NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  session_id         BINARY(16)    NOT NULL,
  company_id         BINARY(16)    NOT NULL,
  report_type        ENUM('executive','detailed','action_plan') NOT NULL,
  title              VARCHAR(255)  NULL,
  language           ENUM('ar','en') NOT NULL DEFAULT 'ar',
  content_data       JSON          NOT NULL,
  file_path_pdf      VARCHAR(500)  NULL,
  file_path_excel    VARCHAR(500)  NULL,
  generation_status  ENUM('pending','processing','ready','failed') NOT NULL DEFAULT 'pending',
  generated_at       TIMESTAMP     NULL DEFAULT NULL,
  viewed_at          TIMESTAMP     NULL DEFAULT NULL,
  download_count     INT           NOT NULL DEFAULT 0,
  expires_at         TIMESTAMP     NULL DEFAULT NULL,
  created_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_session (session_id),
  INDEX idx_status (generation_status),
  CONSTRAINT fk_reports_session
    FOREIGN KEY (session_id) REFERENCES assessment_sessions (id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_company
    FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: benchmarks
-- --------------------------------------------------------
CREATE TABLE benchmarks (
  id             INT           NOT NULL AUTO_INCREMENT,
  industry       VARCHAR(100)  NOT NULL,
  metric_key     VARCHAR(100)  NOT NULL,
  metric_name_ar VARCHAR(255)  NULL,
  metric_name_en VARCHAR(255)  NULL,
  percentile_25  DECIMAL(15,2) NULL,
  percentile_50  DECIMAL(15,2) NULL,
  percentile_75  DECIMAL(15,2) NULL,
  percentile_90  DECIMAL(15,2) NULL,
  unit           VARCHAR(50)   NULL,
  data_source    VARCHAR(255)  NULL,
  year           INT           NULL,
  updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_industry_metric_year (industry, metric_key, year),
  INDEX idx_industry (industry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: patterns
-- --------------------------------------------------------
CREATE TABLE patterns (
  id              VARCHAR(100)  NOT NULL,
  pattern_name    VARCHAR(255)  NOT NULL,
  description     TEXT          NULL,
  detection_rules JSON          NOT NULL,
  category        VARCHAR(100)  NULL,
  severity        ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
  is_active       BOOLEAN       NOT NULL DEFAULT TRUE,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_category (category),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: activity_logs
-- --------------------------------------------------------
CREATE TABLE activity_logs (
  id          BIGINT        NOT NULL AUTO_INCREMENT,
  user_id     BINARY(16)    NULL,
  action      VARCHAR(100)  NOT NULL,
  entity_type VARCHAR(100)  NULL,
  entity_id   BINARY(16)    NULL,
  description TEXT          NULL,
  ip_address  VARCHAR(45)   NULL,
  user_agent  TEXT          NULL,
  metadata    JSON          NULL,
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_user (user_id),
  INDEX idx_action (action),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. Seed Data
-- ============================================================================

-- --------------------------------------------------------
-- Admin User
-- Password: Admin@123  (bcrypt hash)
-- --------------------------------------------------------
SET @admin_uuid = UUID_TO_BIN('00000000-0000-4000-a000-000000000001');

INSERT INTO users (id, email, password_hash, full_name, phone, role, status, email_verified_at, settings)
VALUES (
  @admin_uuid,
  'admin@marketingai.com',
  '$2a$12$LJ3m4ys3Lgx/vkOiPMmax.W66Pv6joMGW6MZOaGNzw.RqMOu/1OHm',
  'System Administrator',
  '+966500000000',
  'admin',
  'active',
  CURRENT_TIMESTAMP,
  '{}'
);

-- --------------------------------------------------------
-- Free Subscription for Admin
-- --------------------------------------------------------
INSERT INTO subscriptions (id, user_id, plan_type, status, started_at, auto_renew, limits)
VALUES (
  UUID_TO_BIN('00000000-0000-4000-a000-000000000002'),
  @admin_uuid,
  'free',
  'active',
  CURRENT_TIMESTAMP,
  TRUE,
  '{"assessments_per_month": 5, "companies": 2, "reports": 10}'
);

-- --------------------------------------------------------
-- Benchmark Seed Data: Education Sector (10 metrics)
-- --------------------------------------------------------
INSERT INTO benchmarks (industry, metric_key, metric_name_ar, metric_name_en, percentile_25, percentile_50, percentile_75, percentile_90, unit, data_source, year) VALUES
('education', 'student_acquisition_cost',       'تكلفة اكتساب الطالب',             'Student Acquisition Cost',          150.00,   250.00,   400.00,   600.00,   'USD',     'Industry Report 2025', 2025),
('education', 'enrollment_conversion_rate',      'معدل تحويل التسجيل',              'Enrollment Conversion Rate',          5.00,    10.00,    18.00,    25.00,   '%',       'Industry Report 2025', 2025),
('education', 'student_retention_rate',          'معدل الاحتفاظ بالطلاب',           'Student Retention Rate',             60.00,    72.00,    83.00,    91.00,   '%',       'Industry Report 2025', 2025),
('education', 'digital_marketing_spend_ratio',   'نسبة الإنفاق على التسويق الرقمي', 'Digital Marketing Spend Ratio',      10.00,    20.00,    35.00,    50.00,   '%',       'Industry Report 2025', 2025),
('education', 'website_traffic_monthly',         'زيارات الموقع الشهرية',           'Monthly Website Traffic',          5000.00, 15000.00, 50000.00,120000.00, 'visits',  'Industry Report 2025', 2025),
('education', 'social_media_engagement_rate',    'معدل التفاعل على وسائل التواصل',  'Social Media Engagement Rate',        1.50,     3.00,     5.50,     8.00,   '%',       'Industry Report 2025', 2025),
('education', 'email_open_rate',                 'معدل فتح البريد الإلكتروني',      'Email Open Rate',                    15.00,    22.00,    30.00,    40.00,   '%',       'Industry Report 2025', 2025),
('education', 'brand_awareness_score',           'درجة الوعي بالعلامة التجارية',    'Brand Awareness Score',              20.00,    35.00,    55.00,    75.00,   'score',   'Industry Report 2025', 2025),
('education', 'content_production_frequency',    'تكرار إنتاج المحتوى',             'Content Production Frequency',        2.00,     4.00,     8.00,    15.00,   'per_week','Industry Report 2025', 2025),
('education', 'customer_satisfaction_score',     'درجة رضا العملاء',                'Customer Satisfaction Score',         60.00,    72.00,    82.00,    90.00,   'score',   'Industry Report 2025', 2025);

-- --------------------------------------------------------
-- Benchmark Seed Data: Retail Sector (10 metrics)
-- --------------------------------------------------------
INSERT INTO benchmarks (industry, metric_key, metric_name_ar, metric_name_en, percentile_25, percentile_50, percentile_75, percentile_90, unit, data_source, year) VALUES
('retail', 'customer_acquisition_cost',       'تكلفة اكتساب العميل',               'Customer Acquisition Cost',          20.00,    45.00,    80.00,   130.00,   'USD',     'Industry Report 2025', 2025),
('retail', 'purchase_conversion_rate',        'معدل تحويل الشراء',                 'Purchase Conversion Rate',            1.50,     2.80,     4.50,     7.00,   '%',       'Industry Report 2025', 2025),
('retail', 'customer_retention_rate',         'معدل الاحتفاظ بالعملاء',            'Customer Retention Rate',            30.00,    45.00,    60.00,    75.00,   '%',       'Industry Report 2025', 2025),
('retail', 'average_order_value',             'متوسط قيمة الطلب',                  'Average Order Value',                35.00,    65.00,   110.00,   180.00,   'USD',     'Industry Report 2025', 2025),
('retail', 'cart_abandonment_rate',           'معدل التخلي عن السلة',              'Cart Abandonment Rate',              55.00,    68.00,    78.00,    85.00,   '%',       'Industry Report 2025', 2025),
('retail', 'return_on_ad_spend',              'العائد على الإنفاق الإعلاني',       'Return on Ad Spend',                  2.00,     3.50,     5.50,     8.00,   'ratio',   'Industry Report 2025', 2025),
('retail', 'email_revenue_percentage',        'نسبة إيرادات البريد الإلكتروني',    'Email Revenue Percentage',            5.00,    12.00,    20.00,    30.00,   '%',       'Industry Report 2025', 2025),
('retail', 'social_media_conversion_rate',    'معدل التحويل من وسائل التواصل',     'Social Media Conversion Rate',        0.50,     1.20,     2.50,     4.00,   '%',       'Industry Report 2025', 2025),
('retail', 'customer_lifetime_value',         'القيمة الدائمة للعميل',             'Customer Lifetime Value',           100.00,   250.00,   500.00,   900.00,   'USD',     'Industry Report 2025', 2025),
('retail', 'inventory_turnover_rate',         'معدل دوران المخزون',                'Inventory Turnover Rate',             4.00,     6.00,     9.00,    14.00,   'turns',   'Industry Report 2025', 2025);

-- ============================================================================
-- Schema creation complete.
-- ============================================================================
