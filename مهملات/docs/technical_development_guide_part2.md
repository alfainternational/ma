# دليل التطوير التقني الشامل
## الجزء الثاني: API Endpoints والخوارزميات

---

# 6. API Endpoints Documentation

## 6.1 Authentication APIs

### **POST /api/v1/auth/register**
تسجيل مستخدم جديد

**Request:**
```javascript
{
  "email": "user@example.com",
  "password": "SecurePass123!",
  "full_name": "خالد محمد",
  "phone": "+966501234567",
  "company_name": "شركة النجاح"
}
```

**Response (201):**
```javascript
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "full_name": "خالد محمد",
      "role": "client"
    },
    "tokens": {
      "access_token": "jwt_token",
      "refresh_token": "refresh_token",
      "expires_in": 604800
    }
  }
}
```

### **POST /api/v1/auth/login**
تسجيل الدخول

**Request:**
```javascript
{
  "email": "user@example.com",
  "password": "SecurePass123!"
}
```

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "full_name": "خالد محمد",
      "role": "client",
      "subscription": {
        "plan": "professional",
        "status": "active",
        "expires_at": "2026-03-05T00:00:00Z"
      }
    },
    "tokens": {
      "access_token": "jwt_token",
      "refresh_token": "refresh_token"
    }
  }
}
```

---

## 6.2 Session Management APIs

### **POST /api/v1/sessions/create**
إنشاء جلسة تقييم جديدة

**Request:**
```javascript
{
  "company_id": "uuid",
  "session_type": "full_assessment",
  "initial_context": {
    "sector": "education",
    "urgency": "high"
  }
}
```

**Response (201):**
```javascript
{
  "success": true,
  "data": {
    "session_id": "uuid",
    "status": "in_progress",
    "progress_percent": 0,
    "estimated_duration_minutes": 30,
    "first_question": {
      "id": "Q_BASIC_001",
      "question": "ما هو القطاع الذي تعمل فيه منشأتكم؟",
      "type": "single_choice",
      "options": [/* ... */],
      "required": true
    }
  }
}
```

### **GET /api/v1/sessions/:sessionId**
الحصول على تفاصيل الجلسة

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "session_id": "uuid",
    "status": "in_progress",
    "progress_percent": 45,
    "started_at": "2026-02-05T10:00:00Z",
    "questions_answered": 23,
    "questions_total": 51,
    "current_question_id": "Q_FIN_005",
    "context": {
      "business_stage": "growth",
      "urgency_level": "high",
      "budget_tier": "medium"
    },
    "flags_raised": [
      {
        "type": "warning",
        "message": "ميزانية منخفضة مقارنة بالقطاع"
      }
    ]
  }
}
```

---

## 6.3 Question Flow APIs

### **GET /api/v1/questions/next**
الحصول على السؤال التالي

**Query Parameters:**
```
session_id=uuid
```

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "question": {
      "id": "Q_FIN_001",
      "category": "financial",
      "question": "ما هو حجم إيراداتكم السنوية الحالية؟",
      "type": "numeric_input",
      "required": true,
      "priority": "critical",
      "help_text": "أدخل إجمالي الإيرادات السنوية...",
      "validation": {
        "min": 0,
        "max": 1000000000,
        "unit": "SAR"
      },
      "metadata": {
        "weight": 2.0,
        "affects_analysis": ["financial_health", "budget_capacity"]
      }
    },
    "progress": {
      "current": 24,
      "total": 51,
      "percent": 47
    },
    "can_skip": false,
    "can_go_back": true
  }
}
```

### **POST /api/v1/questions/answer**
إرسال إجابة على سؤال

**Request:**
```javascript
{
  "session_id": "uuid",
  "question_id": "Q_FIN_001",
  "answer": "500000",
  "time_taken_seconds": 45
}
```

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "answer_id": "uuid",
    "validation": {
      "is_valid": true,
      "normalized_value": 500000
    },
    "immediate_insights": [
      {
        "type": "benchmark",
        "message": "إيراداتك ضمن المعدل الطبيعي للقطاع التعليمي"
      }
    ],
    "triggered_alerts": [],
    "next_question_id": "Q_FIN_002",
    "questions_unlocked": ["Q_FIN_005", "Q_GOALS_001"],
    "questions_skipped": [],
    "progress_updated": {
      "percent": 49,
      "answered": 25,
      "remaining": 26
    }
  }
}
```

### **POST /api/v1/questions/bulk-answer**
إرسال عدة إجابات دفعة واحدة

**Request:**
```javascript
{
  "session_id": "uuid",
  "answers": [
    {"question_id": "Q_BASIC_001", "answer": "education"},
    {"question_id": "Q_BASIC_002", "answer": "3"},
    {"question_id": "Q_FIN_001", "answer": "800000"}
  ]
}
```

---

## 6.4 Analysis APIs

### **POST /api/v1/analysis/run**
تشغيل التحليل الشامل

**Request:**
```javascript
{
  "session_id": "uuid",
  "analysis_types": ["all"], // or specific: ["financial", "market"]
  "include_predictions": true
}
```

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "session_id": "uuid",
    "analysis_status": "completed",
    "completed_at": "2026-02-05T11:30:00Z",
    
    "scores": {
      "digital_maturity": {
        "score": 45,
        "level": "developing",
        "components": {
          "website_quality": 50,
          "social_media": 40,
          "digital_advertising": 35,
          "email_marketing": 45,
          "analytics": 60
        }
      },
      "marketing_maturity": {
        "score": 52,
        "level": "developing"
      },
      "organizational_readiness": {
        "score": 68,
        "level": "medium-high"
      },
      "risk_assessment": {
        "overall_risk": 6.2,
        "level": "medium",
        "breakdown": {
          "financial_risk": 5.5,
          "competitive_risk": 7.0,
          "execution_risk": 6.0,
          "market_risk": 6.5
        }
      },
      "opportunity_score": {
        "overall": 7.5,
        "level": "high",
        "breakdown": {
          "growth_potential": 8.0,
          "market_opportunity": 7.5,
          "competitive_advantage": 7.0
        }
      }
    },
    
    "expert_insights": [
      {
        "expert_id": "financial_analyst",
        "expert_name": "خبير التحليل المالي",
        "assessment": "صحة مالية جيدة مع هوامش ربح 25%",
        "key_findings": [
          "الإيرادات في نمو 15% سنوياً",
          "الميزانية التسويقية 6% من الإيرادات - أقل من المعيار"
        ],
        "recommendations": [
          {
            "priority": "high",
            "action": "زيادة الميزانية التسويقية إلى 10-12%",
            "expected_roi": "4-6x"
          }
        ]
      },
      {
        "expert_id": "digital_marketing_expert",
        "expert_name": "خبير التسويق الرقمي",
        "assessment": "حضور رقمي ضعيف يحد من النمو",
        "recommendations": [/* ... */]
      }
      // ... other experts
    ],
    
    "patterns_detected": [
      {
        "pattern_id": "INF_002",
        "pattern_name": "growth_opportunity_untapped",
        "confidence": 0.88,
        "evidence": [
          "revenue_growing",
          "low_marketing_spend",
          "high_customer_satisfaction"
        ],
        "implication": "فرصة كبيرة للنمو من خلال زيادة الاستثمار التسويقي"
      }
    ],
    
    "predictions": {
      "revenue_forecast_12_months": {
        "conservative": 920000,
        "moderate": 1100000,
        "aggressive": 1300000,
        "most_likely": 1100000,
        "confidence_interval": "±15%"
      },
      "success_probability": {
        "with_recommended_plan": 0.78,
        "without_action": 0.45
      }
    },
    
    "strategic_recommendation": {
      "recommended_plan": "growth_plan_6_months",
      "rationale": "الأعمال مستقرة مالياً مع فرصة نمو واضحة",
      "expected_investment": "150000 SAR over 6 months",
      "expected_return": "600000 - 900000 SAR additional revenue",
      "key_priorities": [
        "بناء الحضور الرقمي",
        "زيادة الميزانية التسويقية",
        "تحسين معدلات التحويل"
      ]
    }
  }
}
```

### **GET /api/v1/analysis/:sessionId/alerts**
الحصول على التنبيهات

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "alerts": [
      {
        "id": "uuid",
        "type": "warning",
        "severity": 7,
        "category": "budget",
        "title": "ميزانية تسويقية منخفضة",
        "message": "ميزانيتك التسويقية 6% من الإيرادات - المعيار 10-15%",
        "recommendation": "زيادة الميزانية تدريجياً للاستفادة من فرص النمو",
        "expert": "financial_analyst",
        "created_at": "2026-02-05T11:25:00Z"
      },
      {
        "id": "uuid",
        "type": "opportunity",
        "severity": 4,
        "category": "digital",
        "title": "فرصة: تحسين الحضور الرقمي",
        "message": "منافسوك متقدمون رقمياً - يمكنك اللحاق والتفوق",
        "recommendation": "الاستثمار في بناء موقع وحضور قوي على Instagram",
        "expert": "digital_marketing_expert"
      }
    ],
    "summary": {
      "total": 8,
      "critical": 0,
      "high": 2,
      "warning": 4,
      "info": 2
    }
  }
}
```

---

## 6.5 Report APIs

### **POST /api/v1/reports/generate**
توليد تقرير

**Request:**
```javascript
{
  "session_id": "uuid",
  "report_type": "executive_summary", // or detailed_analysis, action_plan
  "language": "ar",
  "include_charts": true,
  "format": "pdf" // or json, html
}
```

**Response (202 Accepted):**
```javascript
{
  "success": true,
  "data": {
    "report_id": "uuid",
    "status": "generating",
    "estimated_time_seconds": 30,
    "status_url": "/api/v1/reports/uuid/status"
  }
}
```

### **GET /api/v1/reports/:reportId**
الحصول على التقرير

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "report_id": "uuid",
    "session_id": "uuid",
    "type": "executive_summary",
    "status": "ready",
    "generated_at": "2026-02-05T11:35:00Z",
    
    "content": {
      "title": "تقرير التقييم التسويقي - شركة النجاح التعليمية",
      "executive_summary": {
        "overview": "منشأة تعليمية في مرحلة نمو مع فرص كبيرة...",
        "key_findings": [/* ... */],
        "strategic_recommendation": "خطة نمو 6 أشهر"
      },
      "assessment": {/* scores and insights */},
      "recommendations": [/* detailed recommendations */],
      "priorities": [/* prioritized actions */],
      "next_steps": [/* immediate actions */]
    },
    
    "files": {
      "pdf_url": "https://cdn.example.com/reports/uuid.pdf",
      "json_url": "https://api.example.com/reports/uuid.json"
    },
    
    "metadata": {
      "pages": 12,
      "word_count": 4500,
      "charts": 8
    }
  }
}
```

### **GET /api/v1/reports/list**
قائمة التقارير للمستخدم

**Query Parameters:**
```
company_id=uuid
page=1
limit=10
type=executive_summary
```

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "reports": [
      {
        "id": "uuid",
        "type": "executive_summary",
        "title": "تقرير التقييم - فبراير 2026",
        "created_at": "2026-02-05T11:35:00Z",
        "status": "ready",
        "preview_url": "/reports/uuid/preview"
      }
      // ... more reports
    ],
    "pagination": {
      "page": 1,
      "limit": 10,
      "total": 15,
      "pages": 2
    }
  }
}
```

---

## 6.6 Dashboard & Analytics APIs

### **GET /api/v1/dashboard/metrics**
مؤشرات Dashboard

**Response (200):**
```javascript
{
  "success": true,
  "data": {
    "overview": {
      "sessions_completed": 12,
      "reports_generated": 8,
      "alerts_unread": 3,
      "recommendations_implemented": 5
    },
    "recent_activity": [
      {
        "type": "session_completed",
        "message": "اكتمل تقييم شركة النجاح",
        "timestamp": "2026-02-05T11:30:00Z"
      }
    ],
    "performance_trends": {
      "digital_maturity": {
        "current": 45,
        "previous": 35,
        "change_percent": 28.6
      }
    }
  }
}
```

---

# 7. أكواد نموذجية (Sample Code)

## 7.1 Backend - Session Service

```typescript
// src/services/session/session.service.ts

import { v4 as uuidv4 } from 'uuid';
import { db } from '../../config/database';
import { ContextService } from './context.service';
import { QuestionFlowService } from '../question/adaptive-flow.service';

export class SessionService {
  private contextService: ContextService;
  private questionFlow: QuestionFlowService;

  constructor() {
    this.contextService = new ContextService();
    this.questionFlow = new QuestionFlowService();
  }

  async createSession(data: CreateSessionDTO): Promise<Session> {
    const sessionId = uuidv4();
    
    // Create session record
    const session = await db.assessment_sessions.create({
      id: sessionId,
      company_id: data.company_id,
      user_id: data.user_id,
      session_type: data.session_type || 'full_assessment',
      status: 'in_progress',
      context: data.initial_context || {},
      started_at: new Date()
    });

    // Initialize context
    await this.contextService.initializeContext(sessionId, {
      sector: data.initial_context?.sector,
      urgency: data.initial_context?.urgency
    });

    // Get first question
    const firstQuestion = await this.questionFlow.getNextQuestion(
      sessionId,
      null // no previous question
    );

    return {
      ...session,
      first_question: firstQuestion
    };
  }

  async getSession(sessionId: string): Promise<SessionDetails> {
    const session = await db.assessment_sessions.findByPk(sessionId);
    
    if (!session) {
      throw new Error('Session not found');
    }

    // Get progress
    const answersCount = await db.answers.count({
      where: { session_id: sessionId }
    });

    // Get total questions for this session
    const totalQuestions = await this.questionFlow
      .getTotalQuestionsCount(sessionId);

    // Get raised flags/alerts
    const alerts = await db.alerts.findAll({
      where: { 
        session_id: sessionId,
        acknowledged: false
      },
      limit: 5,
      order: [['severity', 'DESC']]
    });

    return {
      ...session.toJSON(),
      progress_percent: Math.round((answersCount / totalQuestions) * 100),
      questions_answered: answersCount,
      questions_total: totalQuestions,
      flags_raised: alerts.map(a => ({
        type: a.alert_type,
        message: a.message,
        severity: a.severity
      }))
    };
  }

  async completeSession(sessionId: string): Promise<void> {
    await db.assessment_sessions.update(
      {
        status: 'completed',
        completed_at: new Date(),
        progress_percent: 100
      },
      { where: { id: sessionId } }
    );

    // Trigger analysis
    await this.triggerAnalysis(sessionId);
  }

  private async triggerAnalysis(sessionId: string): Promise<void> {
    // Queue analysis job
    await analysisQueue.add('comprehensive-analysis', {
      session_id: sessionId
    });
  }
}
```

## 7.2 Backend - Inference Engine

```typescript
// src/ai-engine/inference-engine.ts

import { PatternMatcher } from './pattern-recognition';
import { PredictionModel } from './prediction-models';
import { db } from '../config/database';

export class InferenceEngine {
  private patternMatcher: PatternMatcher;
  private predictionModel: PredictionModel;

  constructor() {
    this.patternMatcher = new PatternMatcher();
    this.predictionModel = new PredictionModel();
  }

  async runInference(sessionId: string): Promise<InferenceResult> {
    // Get all answers
    const answers = await this.getSessionAnswers(sessionId);
    
    // Get context
    const context = await this.getSessionContext(sessionId);

    // Detect patterns
    const patterns = await this.patternMatcher.detectPatterns(
      answers,
      context
    );

    // Run predictions
    const predictions = await this.predictionModel.predict(
      answers,
      patterns
    );

    // Generate insights
    const insights = await this.generateInsights(
      patterns,
      predictions,
      context
    );

    // Store results
    await this.storeInferenceResults(sessionId, {
      patterns,
      predictions,
      insights
    });

    return {
      patterns,
      predictions,
      insights
    };
  }

  private async detectPatterns(
    answers: Answer[],
    context: Context
  ): Promise<DetectedPattern[]> {
    const detectedPatterns: DetectedPattern[] = [];

    // Load pattern definitions from JSON config
    const patternDefs = await this.loadPatternDefinitions();

    for (const patternDef of patternDefs) {
      const match = await this.evaluatePattern(
        patternDef,
        answers,
        context
      );

      if (match.isMatch) {
        detectedPatterns.push({
          pattern_id: patternDef.id,
          pattern_name: patternDef.name,
          confidence: match.confidence,
          evidence: match.evidence,
          inference: patternDef.inference,
          recommended_action: patternDef.recommended_action
        });
      }
    }

    return detectedPatterns;
  }

  private async evaluatePattern(
    pattern: PatternDefinition,
    answers: Answer[],
    context: Context
  ): Promise<PatternMatch> {
    let matchCount = 0;
    const evidence: string[] = [];

    for (const condition of pattern.conditions) {
      const answer = answers.find(a => a.question_id === condition.field);
      
      if (!answer) continue;

      const isConditionMet = this.evaluateCondition(
        answer.answer_normalized,
        condition.operator,
        condition.value
      );

      if (isConditionMet) {
        matchCount++;
        evidence.push(condition.field);
      }
    }

    const matchRatio = matchCount / pattern.conditions.length;
    const isMatch = matchRatio >= (pattern.min_match_ratio || 1.0);

    return {
      isMatch,
      confidence: matchRatio,
      evidence
    };
  }

  private evaluateCondition(
    value: any,
    operator: string,
    compareValue: any
  ): boolean {
    switch (operator) {
      case '==':
        return value === compareValue;
      case '!=':
        return value !== compareValue;
      case '>':
        return value > compareValue;
      case '<':
        return value < compareValue;
      case '>=':
        return value >= compareValue;
      case '<=':
        return value <= compareValue;
      case 'contains':
        return String(value).includes(compareValue);
      default:
        return false;
    }
  }

  private async generateInsights(
    patterns: DetectedPattern[],
    predictions: Prediction[],
    context: Context
  ): Promise<Insight[]> {
    const insights: Insight[] = [];

    // Generate insights from detected patterns
    for (const pattern of patterns) {
      insights.push({
        type: 'pattern_based',
        source: 'inference_engine',
        category: this.categorizePattern(pattern),
        title: pattern.pattern_name,
        message: pattern.inference,
        confidence: pattern.confidence,
        actionable: true,
        recommendation: pattern.recommended_action
      });
    }

    // Generate insights from predictions
    // ... similar logic

    return insights;
  }
}
```

## 7.3 Backend - Expert Service Example

```typescript
// src/services/expert/financial-analyst.service.ts

import { ExpertBase } from './expert-base';
import { FinancialBenchmarks } from '../../config/benchmarks';

export class FinancialAnalystService extends ExpertBase {
  expertId = 'financial_analyst';
  expertName = 'خبير التحليل المالي';

  async analyze(sessionData: SessionData): Promise<ExpertAnalysis> {
    const financialData = this.extractFinancialData(sessionData);
    
    // Calculate key metrics
    const metrics = await this.calculateMetrics(financialData);
    
    // Compare with benchmarks
    const benchmarkComparison = await this.compareToBenchmarks(
      metrics,
      sessionData.context.sector
    );

    // Assess financial health
    const healthScore = this.assessFinancialHealth(metrics);

    // Identify concerns
    const concerns = await this.identifyConcerns(
      metrics,
      benchmarkComparison
    );

    // Generate recommendations
    const recommendations = await this.generateRecommendations(
      metrics,
      concerns,
      sessionData.context
    );

    return {
      expert_id: this.expertId,
      expert_name: this.expertName,
      analysis_timestamp: new Date(),
      
      metrics,
      health_score: healthScore,
      benchmark_comparison: benchmarkComparison,
      concerns,
      opportunities: await this.identifyOpportunities(metrics),
      recommendations,
      
      confidence_score: this.calculateConfidence(sessionData)
    };
  }

  private calculateMetrics(data: FinancialData): FinancialMetrics {
    const {
      annual_revenue,
      monthly_costs,
      marketing_budget
    } = data;

    const annual_costs = monthly_costs * 12;
    const gross_profit = annual_revenue - annual_costs;
    const profit_margin = (gross_profit / annual_revenue) * 100;
    
    const marketing_budget_annual = marketing_budget * 12;
    const marketing_percent = (marketing_budget_annual / annual_revenue) * 100;

    return {
      annual_revenue,
      annual_costs,
      gross_profit,
      profit_margin,
      marketing_budget_annual,
      marketing_percent,
      monthly_burn_rate: monthly_costs,
      break_even_revenue: annual_costs / 0.75 // assuming 25% margin
    };
  }

  private async compareToBenchmarks(
    metrics: FinancialMetrics,
    sector: string
  ): Promise<BenchmarkComparison> {
    const benchmark = FinancialBenchmarks[sector];

    return {
      profit_margin: {
        value: metrics.profit_margin,
        benchmark: benchmark.profit_margin.ideal,
        variance: metrics.profit_margin - benchmark.profit_margin.ideal,
        status: this.getBenchmarkStatus(
          metrics.profit_margin,
          benchmark.profit_margin
        )
      },
      marketing_spend: {
        value: metrics.marketing_percent,
        benchmark: benchmark.marketing_budget_percent.ideal,
        variance: metrics.marketing_percent - benchmark.marketing_budget_percent.ideal,
        status: this.getBenchmarkStatus(
          metrics.marketing_percent,
          benchmark.marketing_budget_percent
        )
      }
    };
  }

  private async generateRecommendations(
    metrics: FinancialMetrics,
    concerns: Concern[],
    context: Context
  ): Promise<Recommendation[]> {
    const recommendations: Recommendation[] = [];

    // Low marketing budget
    if (metrics.marketing_percent < 5) {
      recommendations.push({
        priority: 'high',
        category: 'budget',
        action: 'زيادة الميزانية التسويقية',
        rationale: `ميزانيتك ${metrics.marketing_percent.toFixed(1)}% فقط - أقل بكثير من المعيار`,
        specific_steps: [
          `زيادة الميزانية تدريجياً إلى ${this.calculateIdealBudget(metrics)} ريال شهرياً`,
          'تخصيص 40% للإعلانات الرقمية',
          'تخصيص 30% للمحتوى والتصميم'
        ],
        expected_impact: 'زيادة متوقعة 30-50% في العملاء الجدد',
        timeline: '3-6 أشهر',
        investment_required: this.calculateIdealBudget(metrics) * 6,
        expected_roi: '3-5x'
      });
    }

    return recommendations;
  }
}
```

---

**يتبع في الملف التالي: Frontend Code Examples وتصميم واجهات المستخدم...**
