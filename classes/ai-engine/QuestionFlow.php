<?php
/**
 * Question Flow - محرك تدفق الأسئلة
 * Dynamic question routing and adaptive questioning
 */
class QuestionFlow {
    private array $questions = [];
    private array $skipRules = [];

    public function __construct() {
        $this->loadQuestions();
    }

    public function getNextQuestion(int $sessionId, array $answeredIds, array $context): ?array {
        $sector = $context['sector'] ?? $context['company_sector'] ?? 'all';
        $answersMap = $context['answers_map'] ?? [];

        // Filter and sort questions
        $available = array_filter($this->questions, function ($q) use ($answeredIds, $sector, $answersMap, $context) {
            $qId = $q['id'] ?? '';
            if (in_array($qId, $answeredIds)) return false;
            if ($this->shouldSkip($q, $answersMap, $context)) return false;

            // Check sector applicability
            $applicable = $q['applicable_sectors'] ?? ['all'];
            if (!in_array('all', $applicable) && !in_array($sector, $applicable)) return false;

            return true;
        });

        // Sort by priority and order
        usort($available, function ($a, $b) {
            $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
            $pA = $priorityOrder[$a['priority'] ?? 'medium'] ?? 2;
            $pB = $priorityOrder[$b['priority'] ?? 'medium'] ?? 2;
            if ($pA !== $pB) return $pA - $pB;
            return ($a['order'] ?? 999) - ($b['order'] ?? 999);
        });

        $next = reset($available);

        // Check if we need deep dive questions
        $deepDive = $this->shouldDeepDive($answersMap, $context);
        if (!empty($deepDive)) {
            foreach ($deepDive as $ddQ) {
                if (!in_array($ddQ['id'] ?? '', $answeredIds)) {
                    return $ddQ;
                }
            }
        }

        return $next ?: null;
    }

    public function shouldSkip(array $question, array $answers, array $context): bool {
        $skipIf = $question['skip_if'] ?? null;
        if (!$skipIf) return false;

        // Check conditions
        if (isset($skipIf['field']) && isset($skipIf['value'])) {
            $actual = $answers[$skipIf['field']] ?? $context[$skipIf['field']] ?? null;
            $operator = $skipIf['operator'] ?? '==';
            $expected = $skipIf['value'];

            return $this->evaluateCondition($actual, $operator, $expected);
        }

        // Check multiple conditions (all_of)
        if (isset($skipIf['all_of'])) {
            foreach ($skipIf['all_of'] as $condition) {
                $actual = $answers[$condition['field'] ?? ''] ?? null;
                if (!$this->evaluateCondition($actual, $condition['operator'] ?? '==', $condition['value'] ?? null)) {
                    return false;
                }
            }
            return true;
        }

        // Built-in skip rules
        $category = $question['category'] ?? '';

        // If no website, skip website-related questions
        if (in_array($category, ['website']) && !$this->hasWebsite($answers)) return true;

        // If no social media, skip social questions
        if ($category === 'social_media' && !$this->hasSocialMedia($answers)) return true;

        // If startup, skip historical questions
        $years = (float)($answers['years_in_business'] ?? $context['years_in_business'] ?? 5);
        if ($years < 1 && ($question['requires_history'] ?? false)) return true;

        return false;
    }

    public function shouldDeepDive(array $answers, array $context): array {
        $deepDives = [];

        // Revenue declining deep dive
        if (($answers['revenue_trend'] ?? '') === 'declining') {
            $deepDives[] = [
                'id' => 'DD_REV_001',
                'category' => 'deep_dive',
                'text_ar' => 'منذ متى والإيرادات في انخفاض؟',
                'type' => 'single_choice',
                'priority' => 'critical',
                'options' => [
                    ['value' => '1_month', 'label_ar' => 'شهر واحد'],
                    ['value' => '3_months', 'label_ar' => '3 أشهر'],
                    ['value' => '6_months', 'label_ar' => '6 أشهر'],
                    ['value' => 'over_year', 'label_ar' => 'أكثر من سنة'],
                ],
                'applicable_sectors' => ['all'],
            ];
        }

        // High churn deep dive
        if ((float)($answers['churn_rate'] ?? 0) > 20) {
            $deepDives[] = [
                'id' => 'DD_CHURN_001',
                'category' => 'deep_dive',
                'text_ar' => 'ما الأسباب الرئيسية لمغادرة العملاء؟',
                'type' => 'multiple_choice',
                'priority' => 'high',
                'options' => [
                    ['value' => 'price', 'label_ar' => 'السعر مرتفع'],
                    ['value' => 'quality', 'label_ar' => 'جودة المنتج/الخدمة'],
                    ['value' => 'service', 'label_ar' => 'خدمة العملاء'],
                    ['value' => 'competition', 'label_ar' => 'عروض المنافسين'],
                    ['value' => 'unknown', 'label_ar' => 'لا نعرف السبب'],
                ],
                'applicable_sectors' => ['all'],
            ];
        }

        return $deepDives;
    }

    public function calculateProgress(int $sessionId): array {
        $db = Database::getInstance();
        $answered = $db->count('answers', 'session_id = :sid', ['sid' => $sessionId]);
        $total = count($this->questions);

        return [
            'answered' => $answered,
            'total' => $total,
            'percent' => $total > 0 ? round(($answered / $total) * 100) : 0,
        ];
    }

    private function hasWebsite(array $answers): bool {
        return in_array($answers['has_website'] ?? '', ['yes', 'نعم', '1', true], true);
    }

    private function hasSocialMedia(array $answers): bool {
        $platforms = $answers['active_platforms_count'] ?? $answers['social_platforms'] ?? 0;
        return (int)$platforms > 0 || in_array($answers['has_social_media'] ?? '', ['yes', 'نعم', '1', true], true);
    }

    private function evaluateCondition($actual, string $operator, $expected): bool {
        if ($actual === null) return false;
        return match ($operator) {
            '==' => $actual == $expected,
            '!=' => $actual != $expected,
            '>' => (float)$actual > (float)$expected,
            '<' => (float)$actual < (float)$expected,
            default => false,
        };
    }

    private function loadQuestions(): void {
        $file = BASE_PATH . '/data/questions.json';
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            $this->questions = $data['questions'] ?? $data ?? [];
        }
    }
}
