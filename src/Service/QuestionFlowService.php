<?php

namespace App\Service;

/**
 * Class QuestionFlowService
 * محرك الأسئلة التكيفي (Adaptive Question Flow).
 * مسؤول عن تحديد السؤال التالي، منطق التفرع، وتخطي الأسئلة غير ذات الصلة.
 */
class QuestionFlowService {
    
    /**
     * الحصول على السؤال التالي بناءً على السياق والإجابات السابقة.
     * @param string|null $lastQuestionId معرف آخر سؤال تمت الإجابة عليه
     * @param array $currentAnswers الإجابات الحالية في الجلسة
     * @param array $context سياق الجلسة (القطاع، الأهداف، إلخ)
     * @return string|null معرف السؤال التالي أو null في حال الانتهاء
     */
    public function getNextQuestionId(?string $lastQuestionId, array $currentAnswers, array $context): ?string {
        // إذا لم يبدأ الاستبيان بعد، نبدأ بالسؤال الأول في بنك الأسئلة
        if ($lastQuestionId === null) {
            $db = \App\Config\Database::getConnection();
            return $db->query("SELECT id FROM questions WHERE active = 1 ORDER BY display_order ASC LIMIT 1")->fetchColumn() ?: null;
        }

        // تطبيق منطق "التفرع الذكي" و "التخطي" (Intelligent Branching & Skipping)
        
        // مثال 1: تخطي أسئلة الموقع إذا كان لا يوجد موقع
        if ($lastQuestionId === 'Q_DIG_001' && ($currentAnswers['Q_DIG_001'] ?? '') === 'no') {
            // تخطي أسئلة الـ SEO والـ Traffic والذهاب لأسئلة السوشيال ميديا مثلاً
            return 'Q_SOC_001'; 
        }

        // مثال 2: تعميق البحث (Deep Dive) في حال وجود Red Flag (انخفاض الإيرادات)
        if ($lastQuestionId === 'Q_FIN_002' && ($currentAnswers['Q_FIN_002'] ?? '') === 'declining') {
            return 'Q_FIN_DD_001'; // سؤال "منذ متى والانخفاض مستمر؟"
        }

        // مثال 3: استبعاد الأسئلة بناءً على القطاع (Sector Specific Skipping)
        $sector = $context['sector'] ?? 'all';
        if ($sector === 'professional_services') {
            // تخطي أسئلة المخزون وسلاسل الإمداد
            // ... منطق استدعاء مصفوفة الأسئلة المتاحة لهذا القطاع
        }

        // المنطق الافتراضي: الانتقال للسؤال التالي في الترتيب (سيتم جلبه من قاعدة البيانات)
        return $this->getDefaultNextId($lastQuestionId);
    }

    /**
     * جلب الرقم التعريفي التالي افتراضياً (بناءً على display_order).
     */
    private function getDefaultNextId(string $currentId): ?string {
        $db = \App\Config\Database::getConnection();
        
        // جلب ترتيب السؤال الحالي
        $stmt = $db->prepare("SELECT display_order FROM questions WHERE id = ?");
        $stmt->execute([$currentId]);
        $currentOrder = $stmt->fetchColumn();

        // جلب المعرف للسؤال التالي في الترتيب
        $stmt = $db->prepare("SELECT id FROM questions WHERE display_order > ? AND active = 1 ORDER BY display_order ASC LIMIT 1");
        $stmt->execute([$currentOrder]);
        return $stmt->fetchColumn() ?: null;
    }
}
