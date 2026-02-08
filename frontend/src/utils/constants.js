export const SESSION_TYPES = {
  full: 'تقييم شامل',
  quick: 'تقييم سريع',
  focused: 'تقييم مركز',
}

export const SESSION_STATUS = {
  draft: 'مسودة',
  in_progress: 'جاري',
  completed: 'مكتمل',
  abandoned: 'متروك',
}

export const MATURITY_LEVELS = {
  beginner: { label: 'مبتدئ', color: 'red', min: 0, max: 25 },
  developing: { label: 'نامي', color: 'orange', min: 25, max: 50 },
  intermediate: { label: 'متوسط', color: 'yellow', min: 50, max: 75 },
  advanced: { label: 'متقدم', color: 'green', min: 75, max: 90 },
  expert: { label: 'خبير', color: 'blue', min: 90, max: 100 },
}

export const SECTORS = [
  { id: 'education', label: 'التعليم الخاص' },
  { id: 'healthcare', label: 'الخدمات الصحية والتجميلية' },
  { id: 'food', label: 'الأغذية والمشروبات' },
  { id: 'retail', label: 'التجزئة المتخصصة' },
  { id: 'professional', label: 'الخدمات المهنية' },
  { id: 'realestate', label: 'العقارات' },
  { id: 'fitness', label: 'اللياقة والخدمات الشخصية' },
  { id: 'crafts', label: 'الحرف والصناعات اليدوية' },
]

export const QUESTION_TYPES = {
  single_choice: 'اختيار واحد',
  multiple_choice: 'اختيار متعدد',
  scale_rating: 'تقييم مقياس',
  numeric_input: 'إدخال رقمي',
  text_input: 'إدخال نصي',
}

export const SCORING_DIMENSIONS = [
  { id: 'digital', label: 'النضج الرقمي', color: '#3b82f6' },
  { id: 'marketing', label: 'فعالية التسويق', color: '#22c55e' },
  { id: 'organizational', label: 'الجاهزية المؤسسية', color: '#f59e0b' },
  { id: 'risk', label: 'إدارة المخاطر', color: '#ef4444' },
  { id: 'opportunity', label: 'استغلال الفرص', color: '#8b5cf6' },
]
