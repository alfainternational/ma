# دليل التطوير التقني الشامل
## الجزء الثالث: Frontend Development - React Examples

---

# 8. Frontend Code Examples

## 8.1 React Components

### **QuestionCard Component**

```typescript
// src/components/questionnaire/QuestionCard/QuestionCard.tsx

import React, { useState } from 'react';
import { Question, Answer } from '../../../types/question.types';
import { 
  Card, 
  CardContent, 
  Typography, 
  Button,
  Tooltip,
  IconButton 
} from '@mui/material';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';
import MultipleChoice from './QuestionTypes/MultipleChoice';
import NumericInput from './QuestionTypes/NumericInput';
import ScaleRating from './QuestionTypes/ScaleRating';
import TextInput from './QuestionTypes/TextInput';

interface QuestionCardProps {
  question: Question;
  onAnswer: (answer: Answer) => void;
  onSkip?: () => void;
  canSkip?: boolean;
  initialValue?: any;
}

export const QuestionCard: React.FC<QuestionCardProps> = ({
  question,
  onAnswer,
  onSkip,
  canSkip = false,
  initialValue
}) => {
  const [answer, setAnswer] = useState<any>(initialValue || null);
  const [error, setError] = useState<string>('');
  const [startTime] = useState(Date.now());

  const handleSubmit = () => {
    // Validation
    if (question.required && !answer) {
      setError('هذا السؤال مطلوب');
      return;
    }

    if (question.validation_rules) {
      const validationError = validateAnswer(answer, question.validation_rules);
      if (validationError) {
        setError(validationError);
        return;
      }
    }

    // Calculate time taken
    const timeTaken = Math.floor((Date.now() - startTime) / 1000);

    // Submit answer
    onAnswer({
      question_id: question.id,
      answer_value: answer,
      time_taken_seconds: timeTaken
    });
  };

  const renderQuestionInput = () => {
    switch (question.type) {
      case 'single_choice':
      case 'multiple_choice':
        return (
          <MultipleChoice
            options={question.options || []}
            value={answer}
            onChange={setAnswer}
            multiple={question.type === 'multiple_choice'}
          />
        );
      
      case 'numeric_input':
        return (
          <NumericInput
            value={answer}
            onChange={setAnswer}
            config={question.input_config}
            error={error}
          />
        );
      
      case 'scale_rating':
        return (
          <ScaleRating
            value={answer}
            onChange={setAnswer}
            min={question.scale_config?.min || 1}
            max={question.scale_config?.max || 10}
            labels={question.scale_config?.labels}
          />
        );
      
      case 'text_input':
        return (
          <TextInput
            value={answer}
            onChange={setAnswer}
            multiline={question.text_config?.multiline}
            placeholder={question.text_config?.placeholder}
          />
        );
      
      default:
        return <div>نوع سؤال غير مدعوم</div>;
    }
  };

  return (
    <Card 
      className="question-card"
      sx={{ 
        maxWidth: 800, 
        margin: '0 auto',
        boxShadow: 3,
        borderRadius: 2
      }}
    >
      <CardContent sx={{ p: 4 }}>
        {/* Question Header */}
        <div style={{ display: 'flex', alignItems: 'flex-start', mb: 3 }}>
          <Typography 
            variant="h5" 
            component="h2"
            sx={{ flex: 1, fontWeight: 600, color: '#1a1a1a' }}
          >
            {question.question}
            {question.required && (
              <span style={{ color: '#d32f2f', marginRight: 8 }}>*</span>
            )}
          </Typography>
          
          {question.help_text && (
            <Tooltip title={question.help_text} arrow placement="top">
              <IconButton size="small" sx={{ ml: 1 }}>
                <HelpOutlineIcon />
              </IconButton>
            </Tooltip>
          )}
        </div>

        {/* Question Category Badge */}
        {question.category && (
          <Typography 
            variant="caption" 
            sx={{ 
              display: 'inline-block',
              bgcolor: '#e3f2fd',
              color: '#1976d2',
              px: 2,
              py: 0.5,
              borderRadius: 1,
              mb: 3
            }}
          >
            {getCategoryLabel(question.category)}
          </Typography>
        )}

        {/* Question Input */}
        <div style={{ marginTop: 24, marginBottom: 24 }}>
          {renderQuestionInput()}
        </div>

        {/* Error Message */}
        {error && (
          <Typography 
            variant="body2" 
            color="error"
            sx={{ mt: 2, mb: 2 }}
          >
            {error}
          </Typography>
        )}

        {/* Action Buttons */}
        <div style={{ 
          display: 'flex', 
          justifyContent: 'space-between',
          marginTop: 32
        }}>
          {canSkip && onSkip && (
            <Button
              variant="outlined"
              onClick={onSkip}
              sx={{ minWidth: 120 }}
            >
              تخطي
            </Button>
          )}
          
          <div style={{ marginRight: 'auto' }} />
          
          <Button
            variant="contained"
            onClick={handleSubmit}
            disabled={question.required && !answer}
            sx={{ 
              minWidth: 150,
              bgcolor: '#1976d2',
              '&:hover': { bgcolor: '#1565c0' }
            }}
          >
            التالي
          </Button>
        </div>
      </CardContent>
    </Card>
  );
};

// Helper function
function validateAnswer(answer: any, rules: ValidationRules): string | null {
  if (rules.min !== undefined && answer < rules.min) {
    return `القيمة يجب أن تكون ${rules.min} على الأقل`;
  }
  
  if (rules.max !== undefined && answer > rules.max) {
    return `القيمة يجب أن لا تتجاوز ${rules.max}`;
  }
  
  if (rules.pattern && !new RegExp(rules.pattern).test(answer)) {
    return 'الصيغة غير صحيحة';
  }
  
  return null;
}

function getCategoryLabel(category: string): string {
  const labels: Record<string, string> = {
    'basic_info': 'معلومات أساسية',
    'financial': 'مالية',
    'marketing': 'تسويق',
    'digital': 'رقمية',
    'customers': 'عملاء',
    'competition': 'منافسة'
  };
  
  return labels[category] || category;
}
```

### **Progress Bar Component**

```typescript
// src/components/questionnaire/ProgressBar/ProgressBar.tsx

import React from 'react';
import { 
  LinearProgress, 
  Box, 
  Typography,
  Paper 
} from '@mui/material';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';

interface ProgressBarProps {
  current: number;
  total: number;
  percent: number;
}

export const ProgressBar: React.FC<ProgressBarProps> = ({
  current,
  total,
  percent
}) => {
  return (
    <Paper 
      elevation={0} 
      sx={{ 
        p: 3, 
        mb: 3,
        bgcolor: '#f5f5f5',
        borderRadius: 2
      }}
    >
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
        <Typography variant="body1" sx={{ fontWeight: 600, flex: 1 }}>
          التقدم في الاستبيان
        </Typography>
        <Typography variant="h6" sx={{ fontWeight: 700, color: '#1976d2' }}>
          {percent}%
        </Typography>
      </Box>
      
      <LinearProgress 
        variant="determinate" 
        value={percent}
        sx={{
          height: 10,
          borderRadius: 5,
          bgcolor: '#e0e0e0',
          '& .MuiLinearProgress-bar': {
            bgcolor: '#1976d2',
            borderRadius: 5
          }
        }}
      />
      
      <Box sx={{ 
        display: 'flex', 
        justifyContent: 'space-between',
        mt: 1
      }}>
        <Typography variant="caption" color="text.secondary">
          <CheckCircleIcon 
            sx={{ fontSize: 16, verticalAlign: 'middle', mr: 0.5 }} 
          />
          {current} من {total} سؤال
        </Typography>
        
        <Typography variant="caption" color="text.secondary">
          متبقي: {total - current}
        </Typography>
      </Box>
    </Paper>
  );
};
```

### **Analysis Dashboard Component**

```typescript
// src/components/analysis/AnalysisDashboard/AnalysisDashboard.tsx

import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { 
  Container, 
  Grid, 
  Paper,
  Typography,
  Box,
  CircularProgress,
  Alert
} from '@mui/material';
import { useAnalysis } from '../../../hooks/useAnalysis';
import ScoreGauge from './ScoreGauge';
import MaturityChart from './MaturityChart';
import RiskAssessment from './RiskAssessment';
import ExpertInsights from './ExpertInsights';
import Recommendations from './Recommendations';
import AlertsList from './AlertsList';

export const AnalysisDashboard: React.FC = () => {
  const { sessionId } = useParams<{ sessionId: string }>();
  const { 
    analysis, 
    loading, 
    error, 
    fetchAnalysis 
  } = useAnalysis();

  useEffect(() => {
    if (sessionId) {
      fetchAnalysis(sessionId);
    }
  }, [sessionId]);

  if (loading) {
    return (
      <Box 
        display="flex" 
        justifyContent="center" 
        alignItems="center" 
        minHeight="60vh"
      >
        <CircularProgress size={60} />
      </Box>
    );
  }

  if (error) {
    return (
      <Container maxWidth="md" sx={{ mt: 4 }}>
        <Alert severity="error">{error}</Alert>
      </Container>
    );
  }

  if (!analysis) return null;

  return (
    <Container maxWidth="xl" sx={{ py: 4 }}>
      {/* Header */}
      <Typography variant="h4" sx={{ mb: 4, fontWeight: 700 }}>
        نتائج التحليل الشامل
      </Typography>

      {/* Scores Overview */}
      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} md={3}>
          <ScoreGauge
            title="النضج الرقمي"
            score={analysis.scores.digital_maturity.score}
            level={analysis.scores.digital_maturity.level}
            color="#1976d2"
          />
        </Grid>
        
        <Grid item xs={12} md={3}>
          <ScoreGauge
            title="النضج التسويقي"
            score={analysis.scores.marketing_maturity.score}
            level={analysis.scores.marketing_maturity.level}
            color="#2e7d32"
          />
        </Grid>
        
        <Grid item xs={12} md={3}>
          <ScoreGauge
            title="الجاهزية التنظيمية"
            score={analysis.scores.organizational_readiness.score}
            level={analysis.scores.organizational_readiness.level}
            color="#ed6c02"
          />
        </Grid>
        
        <Grid item xs={12} md={3}>
          <Paper sx={{ p: 3, height: '100%' }}>
            <Typography variant="h6" sx={{ mb: 2 }}>
              مستوى المخاطر
            </Typography>
            <Typography 
              variant="h2" 
              sx={{ 
                color: getRiskColor(analysis.scores.risk_assessment.overall_risk),
                fontWeight: 700
              }}
            >
              {analysis.scores.risk_assessment.overall_risk.toFixed(1)}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              من 10
            </Typography>
          </Paper>
        </Grid>
      </Grid>

      {/* Alerts */}
      {analysis.alerts && analysis.alerts.length > 0 && (
        <AlertsList alerts={analysis.alerts} sx={{ mb: 4 }} />
      )}

      {/* Digital Maturity Breakdown */}
      <Paper sx={{ p: 4, mb: 4 }}>
        <Typography variant="h5" sx={{ mb: 3, fontWeight: 600 }}>
          تفصيل النضج الرقمي
        </Typography>
        <MaturityChart 
          components={analysis.scores.digital_maturity.components}
        />
      </Paper>

      {/* Expert Insights */}
      <Typography variant="h5" sx={{ mb: 3, fontWeight: 600 }}>
        رؤى الخبراء
      </Typography>
      <Grid container spacing={3} sx={{ mb: 4 }}>
        {analysis.expert_insights.map((insight, index) => (
          <Grid item xs={12} md={6} key={index}>
            <ExpertInsights insight={insight} />
          </Grid>
        ))}
      </Grid>

      {/* Strategic Recommendation */}
      <Paper sx={{ p: 4, mb: 4, bgcolor: '#e3f2fd' }}>
        <Typography variant="h5" sx={{ mb: 2, fontWeight: 600 }}>
          التوصية الاستراتيجية
        </Typography>
        <Typography variant="h6" sx={{ mb: 2, color: '#1976d2' }}>
          {analysis.strategic_recommendation.recommended_plan}
        </Typography>
        <Typography variant="body1" paragraph>
          {analysis.strategic_recommendation.rationale}
        </Typography>
        
        <Grid container spacing={2}>
          <Grid item xs={12} md={4}>
            <Typography variant="subtitle2" color="text.secondary">
              الاستثمار المتوقع
            </Typography>
            <Typography variant="h6">
              {analysis.strategic_recommendation.expected_investment}
            </Typography>
          </Grid>
          <Grid item xs={12} md={4}>
            <Typography variant="subtitle2" color="text.secondary">
              العائد المتوقع
            </Typography>
            <Typography variant="h6">
              {analysis.strategic_recommendation.expected_return}
            </Typography>
          </Grid>
          <Grid item xs={12} md={4}>
            <Typography variant="subtitle2" color="text.secondary">
              الإطار الزمني
            </Typography>
            <Typography variant="h6">
              6 أشهر
            </Typography>
          </Grid>
        </Grid>
      </Paper>

      {/* Recommendations */}
      <Recommendations 
        recommendations={analysis.strategic_recommendation.key_priorities}
      />
    </Container>
  );
};

function getRiskColor(risk: number): string {
  if (risk < 3) return '#2e7d32'; // green
  if (risk < 6) return '#ed6c02'; // orange
  return '#d32f2f'; // red
}
```

## 8.2 Custom Hooks

### **useSession Hook**

```typescript
// src/hooks/useSession.ts

import { useState, useCallback } from 'react';
import { sessionService } from '../services/session.service';
import { Session, SessionDetails } from '../types/session.types';

export const useSession = () => {
  const [session, setSession] = useState<SessionDetails | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const createSession = useCallback(async (data: CreateSessionData) => {
    setLoading(true);
    setError(null);
    
    try {
      const newSession = await sessionService.create(data);
      setSession(newSession);
      return newSession;
    } catch (err: any) {
      setError(err.message || 'فشل في إنشاء الجلسة');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchSession = useCallback(async (sessionId: string) => {
    setLoading(true);
    setError(null);
    
    try {
      const sessionData = await sessionService.get(sessionId);
      setSession(sessionData);
      return sessionData;
    } catch (err: any) {
      setError(err.message || 'فشل في جلب بيانات الجلسة');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  const completeSession = useCallback(async (sessionId: string) => {
    setLoading(true);
    setError(null);
    
    try {
      await sessionService.complete(sessionId);
      // Update session status
      if (session) {
        setSession({
          ...session,
          status: 'completed',
          progress_percent: 100
        });
      }
    } catch (err: any) {
      setError(err.message || 'فشل في إكمال الجلسة');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [session]);

  return {
    session,
    loading,
    error,
    createSession,
    fetchSession,
    completeSession
  };
};
```

### **useQuestions Hook**

```typescript
// src/hooks/useQuestions.ts

import { useState, useCallback } from 'react';
import { questionService } from '../services/question.service';
import { Question, Answer } from '../types/question.types';

export const useQuestions = (sessionId: string) => {
  const [currentQuestion, setCurrentQuestion] = useState<Question | null>(null);
  const [progress, setProgress] = useState({ current: 0, total: 0, percent: 0 });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchNextQuestion = useCallback(async () => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await questionService.getNext(sessionId);
      setCurrentQuestion(response.question);
      setProgress(response.progress);
      return response;
    } catch (err: any) {
      setError(err.message || 'فشل في جلب السؤال');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [sessionId]);

  const submitAnswer = useCallback(async (answer: Answer) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await questionService.submitAnswer(answer);
      
      // Update progress
      if (response.progress_updated) {
        setProgress({
          current: response.progress_updated.answered,
          total: response.progress_updated.answered + response.progress_updated.remaining,
          percent: response.progress_updated.percent
        });
      }
      
      // Fetch next question
      if (response.next_question_id) {
        await fetchNextQuestion();
      }
      
      return response;
    } catch (err: any) {
      setError(err.message || 'فشل في حفظ الإجابة');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [sessionId, fetchNextQuestion]);

  const skipQuestion = useCallback(async () => {
    // Similar to submitAnswer but without answer
    await fetchNextQuestion();
  }, [fetchNextQuestion]);

  return {
    currentQuestion,
    progress,
    loading,
    error,
    fetchNextQuestion,
    submitAnswer,
    skipQuestion
  };
};
```

## 8.3 Services

### **API Service Base**

```typescript
// src/services/api.ts

import axios, { AxiosInstance, AxiosRequestConfig } from 'axios';

class ApiService {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: process.env.REACT_APP_API_URL,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json'
      }
    });

    // Request interceptor
    this.client.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('access_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor
    this.client.interceptors.response.use(
      (response) => response.data,
      async (error) => {
        if (error.response?.status === 401) {
          // Token expired, try refresh
          const refreshed = await this.refreshToken();
          if (refreshed) {
            // Retry original request
            return this.client.request(error.config);
          } else {
            // Logout user
            localStorage.clear();
            window.location.href = '/login';
          }
        }
        return Promise.reject(error);
      }
    );
  }

  private async refreshToken(): Promise<boolean> {
    try {
      const refreshToken = localStorage.getItem('refresh_token');
      if (!refreshToken) return false;

      const response = await axios.post(
        `${process.env.REACT_APP_API_URL}/auth/refresh`,
        { refresh_token: refreshToken }
      );

      localStorage.setItem('access_token', response.data.access_token);
      return true;
    } catch {
      return false;
    }
  }

  get<T = any>(url: string, config?: AxiosRequestConfig) {
    return this.client.get<T, T>(url, config);
  }

  post<T = any>(url: string, data?: any, config?: AxiosRequestConfig) {
    return this.client.post<T, T>(url, data, config);
  }

  put<T = any>(url: string, data?: any, config?: AxiosRequestConfig) {
    return this.client.put<T, T>(url, data, config);
  }

  delete<T = any>(url: string, config?: AxiosRequestConfig) {
    return this.client.delete<T, T>(url, config);
  }
}

export const api = new ApiService();
```

### **Question Service**

```typescript
// src/services/question.service.ts

import { api } from './api';
import { Question, Answer, QuestionResponse } from '../types/question.types';

class QuestionService {
  async getNext(sessionId: string): Promise<QuestionResponse> {
    return api.get(`/questions/next?session_id=${sessionId}`);
  }

  async submitAnswer(answer: Answer): Promise<any> {
    return api.post('/questions/answer', answer);
  }

  async bulkSubmit(sessionId: string, answers: Answer[]): Promise<any> {
    return api.post('/questions/bulk-answer', {
      session_id: sessionId,
      answers
    });
  }

  async getPrevious(sessionId: string): Promise<Question> {
    return api.get(`/questions/previous?session_id=${sessionId}`);
  }
}

export const questionService = new QuestionService();
```

## 8.4 State Management (Redux)

### **Session Slice**

```typescript
// src/store/slices/sessionSlice.ts

import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { sessionService } from '../../services/session.service';
import { SessionDetails } from '../../types/session.types';

interface SessionState {
  current: SessionDetails | null;
  loading: boolean;
  error: string | null;
}

const initialState: SessionState = {
  current: null,
  loading: false,
  error: null
};

export const createSession = createAsyncThunk(
  'session/create',
  async (data: CreateSessionData) => {
    const response = await sessionService.create(data);
    return response;
  }
);

export const fetchSession = createAsyncThunk(
  'session/fetch',
  async (sessionId: string) => {
    const response = await sessionService.get(sessionId);
    return response;
  }
);

const sessionSlice = createSlice({
  name: 'session',
  initialState,
  reducers: {
    updateProgress: (state, action: PayloadAction<number>) => {
      if (state.current) {
        state.current.progress_percent = action.payload;
      }
    },
    clearSession: (state) => {
      state.current = null;
      state.error = null;
    }
  },
  extraReducers: (builder) => {
    builder
      // Create Session
      .addCase(createSession.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(createSession.fulfilled, (state, action) => {
        state.loading = false;
        state.current = action.payload;
      })
      .addCase(createSession.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'فشل في إنشاء الجلسة';
      })
      
      // Fetch Session
      .addCase(fetchSession.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchSession.fulfilled, (state, action) => {
        state.loading = false;
        state.current = action.payload;
      })
      .addCase(fetchSession.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'فشل في جلب الجلسة';
      });
  }
});

export const { updateProgress, clearSession } = sessionSlice.actions;
export default sessionSlice.reducer;
```

---

**يتبع في الملف التالي: UI/UX Design وتصميمات الواجهات الكاملة...**
