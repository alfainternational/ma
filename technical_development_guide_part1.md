# دليل التطوير التقني الشامل
## نظام الذكاء الاصطناعي التسويقي

**الإصدار:** 1.0  
**التاريخ:** 2026-02-05  
**المطور:** Khaled - Marketing AI System

---

# الجزء الأول: البنية التقنية (Architecture)

## 1. نظرة عامة على البنية

### 1.1 معمارية النظام (System Architecture)

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend Layer                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Web App    │  │  Mobile App  │  │   Admin      │     │
│  │   (React)    │  │  (React      │  │   Dashboard  │     │
│  │              │  │   Native)    │  │              │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      API Gateway                            │
│              (Kong / AWS API Gateway)                       │
│    - Authentication   - Rate Limiting   - Routing          │
└─────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Application Layer                         │
│  ┌────────────────────────────────────────────────────┐    │
│  │           Core API Service (Node.js/Python)         │    │
│  │                                                      │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │    │
│  │  │ Question     │  │  Session     │  │  User    │ │    │
│  │  │ Service      │  │  Manager     │  │  Service │ │    │
│  │  └──────────────┘  └──────────────┘  └──────────┘ │    │
│  │                                                      │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │    │
│  │  │ Analysis     │  │  Expert      │  │ Report   │ │    │
│  │  │ Engine       │  │  Engine      │  │ Generator│ │    │
│  │  └──────────────┘  └──────────────┘  └──────────┘ │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                  AI/ML Processing Layer                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  Inference   │  │  Pattern     │  │  Prediction  │     │
│  │  Engine      │  │  Recognition │  │  Models      │     │
│  │ (Python ML)  │  │  (ML/AI)     │  │  (TensorFlow)│     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  PostgreSQL  │  │   MongoDB    │  │    Redis     │     │
│  │ (Relational) │  │  (NoSQL)     │  │   (Cache)    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│                                                             │
│  ┌──────────────┐  ┌──────────────┐                       │
│  │  ElasticSearch│ │   S3/Blob    │                       │
│  │   (Search)    │ │   (Files)    │                       │
│  └──────────────┘  └──────────────┘                       │
└─────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────┐
│               External Services & Integrations              │
│  - Email Service (SendGrid)                                 │
│  - SMS Service (Twilio)                                     │
│  - Payment Gateway (Stripe/PayPal)                          │
│  - Analytics (Google Analytics)                             │
│  - Monitoring (DataDog/Sentry)                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. التقنيات المستخدمة (Tech Stack)

### 2.1 Frontend

#### **Web Application**
```javascript
{
  "framework": "React 18+",
  "state_management": "Redux Toolkit / Zustand",
  "ui_library": "Material-UI (MUI) / Ant Design",
  "forms": "React Hook Form + Yup",
  "routing": "React Router v6",
  "api_client": "Axios / React Query",
  "charts": "Recharts / Chart.js",
  "styling": "Styled Components / Tailwind CSS",
  "build": "Vite / Webpack",
  "typescript": true,
  "pwa": true
}
```

#### **Mobile Application**
```javascript
{
  "framework": "React Native",
  "navigation": "React Navigation",
  "state": "Redux Toolkit",
  "ui": "React Native Paper / Native Base"
}
```

### 2.2 Backend

#### **API Service**
```javascript
// Option 1: Node.js Stack
{
  "runtime": "Node.js 20+",
  "framework": "Express.js / Fastify / NestJS",
  "language": "TypeScript",
  "validation": "Joi / Zod",
  "orm": "Prisma / TypeORM",
  "authentication": "JWT + Passport.js",
  "documentation": "Swagger / OpenAPI"
}

// Option 2: Python Stack
{
  "runtime": "Python 3.11+",
  "framework": "FastAPI / Django REST",
  "validation": "Pydantic",
  "orm": "SQLAlchemy / Django ORM",
  "authentication": "JWT + OAuth2",
  "documentation": "FastAPI Auto Docs"
}
```

#### **AI/ML Services**
```python
{
  "language": "Python 3.11+",
  "ml_frameworks": [
    "TensorFlow 2.x",
    "PyTorch",
    "scikit-learn"
  ],
  "nlp": [
    "spaCy",
    "Transformers (Hugging Face)",
    "NLTK"
  ],
  "data_processing": [
    "Pandas",
    "NumPy",
    "SciPy"
  ]
}
```

### 2.3 Databases

#### **PostgreSQL (Primary Database)**
```sql
-- للبيانات المهيكلة:
- Users
- Companies
- Sessions
- Questions & Answers
- Reports
- Subscriptions
```

#### **MongoDB (NoSQL)**
```javascript
// للبيانات غير المهيكلة:
{
  "collections": [
    "analysis_results",
    "expert_insights",
    "inference_patterns",
    "recommendation_history",
    "raw_ml_data"
  ]
}
```

#### **Redis (Cache & Sessions)**
```
- Session storage
- API response caching
- Real-time data
- Queue management
```

#### **ElasticSearch (Search Engine)**
```
- Full-text search
- Question search
- Report search
- Analytics
```

### 2.4 Infrastructure

```yaml
hosting:
  provider: "AWS / Google Cloud / Azure"
  compute: "ECS / Kubernetes"
  
cdn:
  provider: "CloudFront / Cloudflare"
  
storage:
  files: "S3 / Google Cloud Storage"
  backups: "Automated daily"
  
monitoring:
  application: "DataDog / New Relic"
  errors: "Sentry"
  logs: "ELK Stack / CloudWatch"
  
ci_cd:
  pipeline: "GitHub Actions / GitLab CI"
  deployment: "Blue-Green / Canary"
```

---

## 3. قاعدة البيانات (Database Schema)

### 3.1 PostgreSQL Schema

#### **Users Table**
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    role VARCHAR(50) DEFAULT 'client', -- client, admin, analyst
    status VARCHAR(50) DEFAULT 'active', -- active, suspended, deleted
    email_verified BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    last_login TIMESTAMP,
    settings JSONB DEFAULT '{}',
    metadata JSONB DEFAULT '{}'
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
```

#### **Companies Table**
```sql
CREATE TABLE companies (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    sector VARCHAR(100) NOT NULL, -- education, healthcare, etc.
    legal_name VARCHAR(255),
    registration_number VARCHAR(100),
    founded_year INTEGER,
    employee_count INTEGER,
    location JSONB, -- {country, city, address}
    contact_info JSONB, -- {phone, email, website}
    logo_url VARCHAR(500),
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_companies_user ON companies(user_id);
CREATE INDEX idx_companies_sector ON companies(sector);
```

#### **Sessions Table**
```sql
CREATE TABLE assessment_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id),
    session_type VARCHAR(50) DEFAULT 'full_assessment',
    status VARCHAR(50) DEFAULT 'in_progress', -- in_progress, completed, abandoned
    started_at TIMESTAMP DEFAULT NOW(),
    completed_at TIMESTAMP,
    progress_percent INTEGER DEFAULT 0,
    current_question_id VARCHAR(100),
    context JSONB DEFAULT '{}', -- business_stage, urgency, etc.
    metadata JSONB DEFAULT '{}'
);

CREATE INDEX idx_sessions_company ON assessment_sessions(company_id);
CREATE INDEX idx_sessions_status ON assessment_sessions(status);
```

#### **Questions Table**
```sql
CREATE TABLE questions (
    id VARCHAR(100) PRIMARY KEY, -- Q_FIN_001
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    question_ar TEXT NOT NULL,
    question_en TEXT NOT NULL,
    question_type VARCHAR(50) NOT NULL, -- single_choice, numeric, text, etc.
    required BOOLEAN DEFAULT true,
    priority VARCHAR(50), -- critical, high, medium, low
    display_order INTEGER,
    help_text_ar TEXT,
    help_text_en TEXT,
    validation_rules JSONB,
    options JSONB, -- [{value, label_ar, label_en}]
    metadata JSONB, -- weight, affects_questions, etc.
    sector_specific VARCHAR(100), -- null means all sectors
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_questions_category ON questions(category);
CREATE INDEX idx_questions_sector ON questions(sector_specific);
CREATE INDEX idx_questions_order ON questions(display_order);
```

#### **Answers Table**
```sql
CREATE TABLE answers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    session_id UUID REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    question_id VARCHAR(100) REFERENCES questions(id),
    answer_value TEXT, -- stores the actual answer
    answer_normalized JSONB, -- normalized for processing
    confidence_score DECIMAL(3,2), -- 0.00 to 1.00
    source VARCHAR(50) DEFAULT 'user_input', -- user_input, inferred, default
    answered_at TIMESTAMP DEFAULT NOW(),
    time_taken_seconds INTEGER,
    metadata JSONB DEFAULT '{}',
    UNIQUE(session_id, question_id)
);

CREATE INDEX idx_answers_session ON answers(session_id);
CREATE INDEX idx_answers_question ON answers(question_id);
```

#### **Analysis Results Table**
```sql
CREATE TABLE analysis_results (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    session_id UUID REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    analysis_type VARCHAR(100) NOT NULL, -- financial, market, swot, etc.
    expert_id VARCHAR(100), -- which expert analyzed
    scores JSONB, -- {digital_maturity: 45, risk_score: 6, etc}
    insights JSONB, -- [{type, message, severity}]
    flags JSONB, -- [{type: red_flag, pattern, message}]
    recommendations JSONB, -- [{priority, action, rationale}]
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_analysis_session ON analysis_results(session_id);
CREATE INDEX idx_analysis_type ON analysis_results(analysis_type);
```

#### **Reports Table**
```sql
CREATE TABLE reports (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    session_id UUID REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    company_id UUID REFERENCES companies(id),
    report_type VARCHAR(100) NOT NULL, -- executive, detailed, action_plan
    title VARCHAR(255),
    content JSONB, -- full report structure
    pdf_url VARCHAR(500),
    status VARCHAR(50) DEFAULT 'generating', -- generating, ready, failed
    generated_at TIMESTAMP,
    viewed_at TIMESTAMP,
    downloaded_at TIMESTAMP,
    shared_with JSONB, -- array of emails
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_reports_session ON reports(session_id);
CREATE INDEX idx_reports_company ON reports(company_id);
CREATE INDEX idx_reports_type ON reports(report_type);
```

#### **Alerts Table**
```sql
CREATE TABLE alerts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    session_id UUID REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    alert_type VARCHAR(100) NOT NULL, -- critical, high, warning, info
    category VARCHAR(100), -- financial_risk, opportunity, contradiction
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    severity INTEGER, -- 1-10
    pattern_id VARCHAR(100), -- ALERT_CRIT_001
    assigned_expert VARCHAR(100),
    recommendation TEXT,
    acknowledged BOOLEAN DEFAULT false,
    acknowledged_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_alerts_session ON alerts(session_id);
CREATE INDEX idx_alerts_severity ON alerts(severity DESC);
CREATE INDEX idx_alerts_acknowledged ON alerts(acknowledged);
```

#### **Subscriptions Table**
```sql
CREATE TABLE subscriptions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    plan_type VARCHAR(50) NOT NULL, -- free, basic, professional, enterprise
    status VARCHAR(50) DEFAULT 'active', -- active, cancelled, expired
    started_at TIMESTAMP DEFAULT NOW(),
    expires_at TIMESTAMP,
    auto_renew BOOLEAN DEFAULT true,
    payment_method JSONB,
    billing_info JSONB,
    limits JSONB, -- {max_sessions: 5, max_reports: 10}
    features JSONB, -- {advanced_analytics: true}
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
```

### 3.2 MongoDB Collections

#### **Expert Insights Collection**
```javascript
{
  "_id": ObjectId("..."),
  "session_id": "uuid",
  "expert_id": "financial_analyst",
  "expert_name": "خبير التحليل المالي",
  "analysis_timestamp": ISODate("2026-02-05T10:00:00Z"),
  
  "input_data": {
    "revenue": 500000,
    "costs": 400000,
    "marketing_budget": 30000,
    // ... other relevant data
  },
  
  "analysis": {
    "financial_health_score": 7.5,
    "profit_margin": 20,
    "budget_efficiency": "adequate",
    "key_concerns": [
      "ميزانية تسويقية أقل من المعيار",
      "هوامش ربح جيدة لكن يمكن تحسينها"
    ],
    "opportunities": [
      "زيادة الاستثمار التسويقي لتسريع النمو"
    ]
  },
  
  "recommendations": [
    {
      "priority": "high",
      "action": "زيادة الميزانية التسويقية إلى 50,000 شهرياً",
      "rationale": "الأعمال مربحة والسوق يسمح بالنمو",
      "expected_impact": "زيادة 30-40% في الإيرادات خلال 6 أشهر",
      "risk_level": "low"
    }
  ],
  
  "metadata": {
    "confidence_score": 0.85,
    "data_completeness": 0.92,
    "processing_time_ms": 1250
  }
}
```

#### **Inference Patterns Collection**
```javascript
{
  "_id": ObjectId("..."),
  "pattern_id": "INF_001",
  "pattern_name": "struggling_business",
  "detected_count": 1523, // كم مرة تم اكتشاف هذا النمط
  "success_rate": 0.78, // نسبة نجاح التوصيات
  
  "conditions": [
    {
      "field": "revenue_trend",
      "operator": "==",
      "value": "declining",
      "matched_count": 1523
    },
    {
      "field": "digital_maturity_score",
      "operator": "<",
      "value": 30,
      "matched_count": 1489
    }
  ],
  
  "historical_outcomes": [
    {
      "session_id": "uuid",
      "recommendation_followed": true,
      "outcome": "improved",
      "revenue_change_percent": 15.5,
      "months_to_improvement": 3
    }
  ],
  
  "learning_data": {
    "last_updated": ISODate("2026-02-01T00:00:00Z"),
    "total_cases": 1523,
    "successful_cases": 1188,
    "failed_cases": 335
  }
}
```

---

## 4. بنية الملفات (File Structure)

### 4.1 Backend Structure (Node.js)

```
marketing-ai-backend/
├── src/
│   ├── config/
│   │   ├── database.ts
│   │   ├── redis.ts
│   │   ├── elasticsearch.ts
│   │   └── app.config.ts
│   │
│   ├── models/
│   │   ├── User.ts
│   │   ├── Company.ts
│   │   ├── Session.ts
│   │   ├── Question.ts
│   │   ├── Answer.ts
│   │   └── Report.ts
│   │
│   ├── controllers/
│   │   ├── auth.controller.ts
│   │   ├── session.controller.ts
│   │   ├── question.controller.ts
│   │   ├── analysis.controller.ts
│   │   └── report.controller.ts
│   │
│   ├── services/
│   │   ├── auth/
│   │   │   ├── auth.service.ts
│   │   │   └── jwt.service.ts
│   │   ├── session/
│   │   │   ├── session.service.ts
│   │   │   └── context.service.ts
│   │   ├── question/
│   │   │   ├── question.service.ts
│   │   │   ├── adaptive-flow.service.ts
│   │   │   └── validation.service.ts
│   │   ├── analysis/
│   │   │   ├── scoring.service.ts
│   │   │   ├── inference.service.ts
│   │   │   └── relationship.service.ts
│   │   ├── expert/
│   │   │   ├── expert-manager.service.ts
│   │   │   ├── financial-analyst.service.ts
│   │   │   ├── market-analyst.service.ts
│   │   │   └── ... (other experts)
│   │   ├── recommendation/
│   │   │   ├── recommendation.service.ts
│   │   │   └── personalization.service.ts
│   │   ├── alert/
│   │   │   ├── alert.service.ts
│   │   │   └── pattern-detection.service.ts
│   │   └── report/
│   │       ├── report-generator.service.ts
│   │       ├── pdf-generator.service.ts
│   │       └── visualization.service.ts
│   │
│   ├── ai-engine/
│   │   ├── inference-engine.ts
│   │   ├── pattern-recognition.ts
│   │   ├── prediction-models.ts
│   │   └── ml-pipeline.ts
│   │
│   ├── middleware/
│   │   ├── auth.middleware.ts
│   │   ├── validation.middleware.ts
│   │   ├── error.middleware.ts
│   │   └── rate-limit.middleware.ts
│   │
│   ├── routes/
│   │   ├── auth.routes.ts
│   │   ├── session.routes.ts
│   │   ├── question.routes.ts
│   │   ├── analysis.routes.ts
│   │   └── report.routes.ts
│   │
│   ├── utils/
│   │   ├── logger.ts
│   │   ├── validators.ts
│   │   ├── helpers.ts
│   │   └── constants.ts
│   │
│   ├── types/
│   │   ├── express.d.ts
│   │   ├── models.d.ts
│   │   └── ai-engine.d.ts
│   │
│   └── app.ts
│
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
│
├── scripts/
│   ├── seed-questions.ts
│   ├── migrate.ts
│   └── backup.ts
│
├── .env.example
├── .gitignore
├── package.json
├── tsconfig.json
├── docker-compose.yml
└── README.md
```

### 4.2 Frontend Structure (React)

```
marketing-ai-frontend/
├── public/
│   ├── index.html
│   └── assets/
│
├── src/
│   ├── components/
│   │   ├── common/
│   │   │   ├── Button/
│   │   │   ├── Input/
│   │   │   ├── Card/
│   │   │   ├── Modal/
│   │   │   └── Loading/
│   │   ├── layout/
│   │   │   ├── Header/
│   │   │   ├── Sidebar/
│   │   │   ├── Footer/
│   │   │   └── DashboardLayout/
│   │   ├── auth/
│   │   │   ├── LoginForm/
│   │   │   ├── RegisterForm/
│   │   │   └── ForgotPassword/
│   │   ├── questionnaire/
│   │   │   ├── QuestionCard/
│   │   │   ├── ProgressBar/
│   │   │   ├── QuestionTypes/
│   │   │   │   ├── MultipleChoice/
│   │   │   │   ├── NumericInput/
│   │   │   │   ├── ScaleRating/
│   │   │   │   └── TextInput/
│   │   │   └── NavigationButtons/
│   │   ├── analysis/
│   │   │   ├── ScoreGauge/
│   │   │   ├── MaturityChart/
│   │   │   ├── RiskAssessment/
│   │   │   └── OpportunityMap/
│   │   ├── alerts/
│   │   │   ├── AlertCard/
│   │   │   └── AlertList/
│   │   ├── reports/
│   │   │   ├── ReportViewer/
│   │   │   ├── ReportDownload/
│   │   │   └── ReportShare/
│   │   └── dashboard/
│   │       ├── MetricCard/
│   │       ├── ChartWidget/
│   │       └── ActivityFeed/
│   │
│   ├── pages/
│   │   ├── Home/
│   │   ├── Login/
│   │   ├── Register/
│   │   ├── Dashboard/
│   │   ├── Questionnaire/
│   │   ├── Analysis/
│   │   ├── Reports/
│   │   ├── Settings/
│   │   └── NotFound/
│   │
│   ├── hooks/
│   │   ├── useAuth.ts
│   │   ├── useSession.ts
│   │   ├── useQuestions.ts
│   │   ├── useAnalysis.ts
│   │   └── useReports.ts
│   │
│   ├── services/
│   │   ├── api.ts
│   │   ├── auth.service.ts
│   │   ├── session.service.ts
│   │   ├── question.service.ts
│   │   └── report.service.ts
│   │
│   ├── store/
│   │   ├── store.ts
│   │   ├── slices/
│   │   │   ├── authSlice.ts
│   │   │   ├── sessionSlice.ts
│   │   │   ├── questionSlice.ts
│   │   │   └── analysisSlice.ts
│   │   └── middleware/
│   │
│   ├── utils/
│   │   ├── validators.ts
│   │   ├── formatters.ts
│   │   ├── constants.ts
│   │   └── helpers.ts
│   │
│   ├── types/
│   │   ├── api.types.ts
│   │   ├── question.types.ts
│   │   └── analysis.types.ts
│   │
│   ├── styles/
│   │   ├── theme.ts
│   │   ├── global.css
│   │   └── variables.css
│   │
│   ├── App.tsx
│   ├── index.tsx
│   └── routes.tsx
│
├── .env.example
├── .gitignore
├── package.json
├── tsconfig.json
└── README.md
```

---

## 5. Environment Variables

### 5.1 Backend (.env)

```bash
# Application
NODE_ENV=development
PORT=3000
API_VERSION=v1
APP_NAME=Marketing AI System
FRONTEND_URL=http://localhost:3001

# Database - PostgreSQL
DATABASE_HOST=localhost
DATABASE_PORT=5432
DATABASE_NAME=marketing_ai
DATABASE_USER=postgres
DATABASE_PASSWORD=your_password
DATABASE_SSL=false
DATABASE_POOL_MIN=2
DATABASE_POOL_MAX=10

# Database - MongoDB
MONGODB_URI=mongodb://localhost:27017/marketing_ai
MONGODB_DB_NAME=marketing_ai

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

# Elasticsearch
ELASTICSEARCH_NODE=http://localhost:9200
ELASTICSEARCH_USERNAME=
ELASTICSEARCH_PASSWORD=

# JWT
JWT_SECRET=your_super_secret_key_change_this_in_production
JWT_EXPIRES_IN=7d
JWT_REFRESH_SECRET=your_refresh_secret
JWT_REFRESH_EXPIRES_IN=30d

# Email
EMAIL_SERVICE=sendgrid
EMAIL_FROM=noreply@marketingai.com
SENDGRID_API_KEY=your_sendgrid_key

# SMS
SMS_SERVICE=twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_PHONE_NUMBER=+1234567890

# File Storage
STORAGE_TYPE=s3
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_REGION=us-east-1
AWS_S3_BUCKET=marketing-ai-files

# AI/ML Services
OPENAI_API_KEY=your_openai_key
HUGGING_FACE_TOKEN=your_hf_token

# Monitoring
SENTRY_DSN=your_sentry_dsn
DATADOG_API_KEY=your_datadog_key

# Rate Limiting
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100

# CORS
CORS_ORIGIN=http://localhost:3001
```

### 5.2 Frontend (.env)

```bash
# API
REACT_APP_API_URL=http://localhost:3000/api/v1
REACT_APP_WS_URL=ws://localhost:3000

# Environment
REACT_APP_ENV=development
REACT_APP_NAME=Marketing AI

# Features
REACT_APP_ENABLE_ANALYTICS=true
REACT_APP_ENABLE_PWA=true

# Analytics
REACT_APP_GA_TRACKING_ID=UA-XXXXXXXXX-X
REACT_APP_HOTJAR_ID=your_hotjar_id

# Payments
REACT_APP_STRIPE_PUBLIC_KEY=pk_test_xxx

# Maps (if needed)
REACT_APP_GOOGLE_MAPS_KEY=your_google_maps_key
```

---

**يتبع في الجزء الثاني: API Endpoints والخوارزميات...**
