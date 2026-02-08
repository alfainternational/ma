# Marketing AI Assessment System

نظام تقييم تسويقي ذكي شامل للمنشآت الصغيرة والمتوسطة العربية.

## Overview

A comprehensive AI-powered marketing assessment platform designed for Arab SMEs. The system features 10 virtual AI experts, 4 analysis engines, 250 assessment questions across 20 categories, 3-layer recommendations, 4-level alerts, and 5 report types.

## Tech Stack

- **Backend:** PHP 8+, MySQL 8+
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5 RTL
- **Design:** Arabic-first RTL, Tajawal font
- **Charts:** Chart.js
- **Security:** bcrypt, CSRF tokens, PDO prepared statements, rate limiting

## Features

- 10 Virtual AI Experts (Chief Strategist, Financial Analyst, Market Analyst, etc.)
- 4 AI Engines (Context, Relationship, Scoring, Inference)
- 250 Arabic assessment questions across 20 categories
- Adaptive questioning with skip logic and deep-dive triggers
- 5-dimension maturity scoring (Digital, Marketing, Organizational, Risk, Opportunity)
- 3-layer recommendations (Strategic, Tactical, Execution)
- 4-level alerts + opportunity detection
- 5 report types (Executive Summary, Detailed Analysis, Action Plan, Monthly Performance, Competitive Intelligence)
- 8 industry sectors supported
- Full Arabic RTL interface

## Project Structure

```
ma/
├── config/           # Configuration (database, app settings, constants)
├── classes/          # PHP classes
│   ├── ai-engine/    # AI engine classes
│   │   └── experts/  # 10 virtual expert classes
│   └── helpers/      # Validator, Sanitizer, utility functions
├── data/             # JSON data files (questions, benchmarks, patterns)
├── database/         # SQL schema
├── api/              # REST API endpoints
├── admin/            # Admin panel pages
├── assessment/       # Assessment flow pages
├── company/          # Company management
├── includes/         # Shared templates (header, footer, navbar, sidebar)
├── assets/
│   ├── css/          # Stylesheets
│   └── js/           # JavaScript modules
└── index.php         # Landing page
```

## Setup

1. Clone the repository
2. Copy `.env.example` to `.env` and configure database credentials
3. Import `database/schema.sql` into MySQL
4. Run `composer install`
5. Ensure PHP 8+ and MySQL 8+ are available
6. Access the application via web server

## Default Admin

- Email: `admin@marketingai.com`
- Password: `Admin@123`

## Sectors Supported

1. التعليم الخاص (Private Education)
2. الخدمات الصحية والتجميلية (Healthcare & Beauty)
3. الأغذية والمشروبات (Food & Beverage)
4. التجزئة المتخصصة (Specialty Retail)
5. الخدمات المهنية (Professional Services)
6. العقارات (Real Estate)
7. اللياقة والخدمات الشخصية (Fitness & Personal Services)
8. الحرف والصناعات اليدوية (Crafts & Handmade)
