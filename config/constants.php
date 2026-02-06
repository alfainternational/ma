<?php
/**
 * Application Constants
 * Marketing AI System
 */

// Sectors (8 sectors from specification)
define('SECTORS', [
    'education' => ['ar' => 'التعليم الخاص', 'en' => 'Private Education'],
    'healthcare' => ['ar' => 'الخدمات الصحية والتجميلية', 'en' => 'Healthcare & Beauty'],
    'fnb' => ['ar' => 'الأغذية والمشروبات', 'en' => 'Food & Beverage'],
    'retail' => ['ar' => 'التجزئة المتخصصة', 'en' => 'Specialty Retail'],
    'professional_services' => ['ar' => 'الخدمات المهنية', 'en' => 'Professional Services'],
    'real_estate' => ['ar' => 'العقارات', 'en' => 'Real Estate'],
    'fitness' => ['ar' => 'اللياقة والخدمات الشخصية', 'en' => 'Fitness & Personal Services'],
    'crafts' => ['ar' => 'الحرف والصناعات اليدوية', 'en' => 'Crafts & Handmade'],
]);

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_CLIENT', 'client');
define('ROLE_ANALYST', 'analyst');

// Session Statuses
define('SESSION_IN_PROGRESS', 'in_progress');
define('SESSION_COMPLETED', 'completed');
define('SESSION_ABANDONED', 'abandoned');

// Question Types
define('QUESTION_TYPES', [
    'single_choice' => 'اختيار واحد',
    'multiple_choice' => 'اختيار متعدد',
    'numeric_input' => 'إدخال رقمي',
    'scale_rating' => 'تقييم بمقياس',
    'text_input' => 'إدخال نصي',
]);

// Question Priorities
define('PRIORITY_CRITICAL', 'critical');
define('PRIORITY_HIGH', 'high');
define('PRIORITY_MEDIUM', 'medium');
define('PRIORITY_LOW', 'low');

// Question Categories (20 categories)
define('QUESTION_CATEGORIES', [
    'basic_info' => 'معلومات أساسية',
    'financial' => 'معلومات مالية',
    'business_size' => 'حجم العمل',
    'current_marketing' => 'التسويق الحالي',
    'digital_presence' => 'الحضور الرقمي',
    'social_media' => 'وسائل التواصل الاجتماعي',
    'website' => 'الموقع الإلكتروني',
    'customers' => 'العملاء',
    'competition' => 'المنافسة',
    'goals' => 'الأهداف',
    'budget' => 'الميزانية',
    'team' => 'الفريق',
    'products_services' => 'المنتجات والخدمات',
    'sales_process' => 'عملية البيع',
    'customer_service' => 'خدمة العملاء',
    'data_analytics' => 'البيانات والتحليلات',
    'challenges' => 'التحديات',
    'opportunities' => 'الفرص',
    'technology' => 'التكنولوجيا',
    'growth_readiness' => 'الجاهزية للنمو',
]);

// Alert Severity Levels
define('ALERT_CRITICAL', 'critical');
define('ALERT_HIGH', 'high');
define('ALERT_WARNING', 'warning');
define('ALERT_INFO', 'info');
define('ALERT_OPPORTUNITY', 'opportunity');

// Report Types
define('REPORT_TYPES', [
    'executive_summary' => 'الملخص التنفيذي',
    'detailed_analysis' => 'التحليل التفصيلي',
    'action_plan' => 'خطة العمل',
    'monthly_performance' => 'تقرير الأداء الشهري',
    'competitive_intelligence' => 'الاستخبارات التنافسية',
]);

// Plan Types
define('PLAN_TYPES', [
    'emergency_plan' => ['ar' => 'خطة طوارئ', 'duration' => '30 يوم'],
    'treatment_plan' => ['ar' => 'خطة معالجة', 'duration' => '3 أشهر'],
    'growth_plan' => ['ar' => 'خطة نمو', 'duration' => '6 أشهر'],
    'transformation_plan' => ['ar' => 'خطة تحول', 'duration' => '12 شهر'],
]);

// Expert IDs (10 experts)
define('EXPERTS', [
    'chief_strategist' => 'خبير الاستراتيجية الرئيسي',
    'financial_analyst' => 'خبير التحليل المالي',
    'market_analyst' => 'خبير تحليل السوق',
    'consumer_psychologist' => 'خبير سلوك المستهلك',
    'digital_marketing_expert' => 'خبير التسويق الرقمي',
    'brand_strategist' => 'خبير العلامة التجارية والمحتوى',
    'data_scientist' => 'عالم البيانات',
    'operations_expert' => 'خبير التنفيذ والعمليات',
    'risk_manager' => 'خبير إدارة المخاطر',
    'innovation_scout' => 'خبير الفرص والابتكار',
]);

// Maturity Levels
define('MATURITY_LEVELS', [
    'beginner' => ['ar' => 'مبتدئ', 'range' => [0, 25]],
    'developing' => ['ar' => 'نامي', 'range' => [26, 50]],
    'advanced' => ['ar' => 'متقدم', 'range' => [51, 75]],
    'expert' => ['ar' => 'خبير', 'range' => [76, 100]],
]);

// Budget Tiers
define('BUDGET_TIERS', [
    'micro' => ['range' => [0, 5000], 'ar' => 'ميزانية صغيرة جداً'],
    'small' => ['range' => [5001, 20000], 'ar' => 'ميزانية صغيرة'],
    'medium' => ['range' => [20001, 100000], 'ar' => 'ميزانية متوسطة'],
    'large' => ['range' => [100001, PHP_INT_MAX], 'ar' => 'ميزانية كبيرة'],
]);

// Business Stages
define('BUSINESS_STAGES', [
    'startup' => ['range' => [0, 2], 'ar' => 'ناشئ'],
    'early_growth' => ['range' => [2, 5], 'ar' => 'نمو مبكر'],
    'growth' => ['range' => [5, 10], 'ar' => 'نمو'],
    'mature' => ['range' => [10, 30], 'ar' => 'ناضج'],
    'legacy' => ['range' => [30, 100], 'ar' => 'راسخ'],
]);
