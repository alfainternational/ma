<?php
/**
 * Input Validator
 * Marketing AI System
 */
class Validator {
    private array $errors = [];

    public function validate(array $data, array $rules): bool {
        $this->errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            foreach ($ruleList as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule, $data);
            }
        }
        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): ?string {
        return $this->errors[0] ?? null;
    }

    private function applyRule(string $field, $value, string $rule, array $data): void {
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;
        $label = $this->getFieldLabel($field);

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[] = "{$label} مطلوب";
                }
                break;
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "{$label} غير صالح";
                }
                break;
            case 'min':
                if ($value && strlen($value) < (int)$param) {
                    $this->errors[] = "{$label} يجب أن يكون {$param} أحرف على الأقل";
                }
                break;
            case 'max':
                if ($value && strlen($value) > (int)$param) {
                    $this->errors[] = "{$label} يجب ألا يتجاوز {$param} حرف";
                }
                break;
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->errors[] = "{$label} يجب أن يكون رقماً";
                }
                break;
            case 'min_value':
                if ($value !== null && is_numeric($value) && (float)$value < (float)$param) {
                    $this->errors[] = "{$label} يجب أن يكون {$param} على الأقل";
                }
                break;
            case 'max_value':
                if ($value !== null && is_numeric($value) && (float)$value > (float)$param) {
                    $this->errors[] = "{$label} يجب ألا يتجاوز {$param}";
                }
                break;
            case 'in':
                $options = explode(',', $param);
                if ($value && !in_array($value, $options)) {
                    $this->errors[] = "{$label} قيمة غير صالحة";
                }
                break;
            case 'confirmed':
                if ($value !== ($data[$field . '_confirm'] ?? null)) {
                    $this->errors[] = "{$label} غير متطابق مع التأكيد";
                }
                break;
            case 'phone':
                if ($value && !preg_match('/^[\+]?[0-9\s\-]{8,15}$/', $value)) {
                    $this->errors[] = "{$label} غير صالح";
                }
                break;
        }
    }

    private function getFieldLabel(string $field): string {
        $labels = [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'full_name' => 'الاسم الكامل',
            'phone' => 'رقم الهاتف',
            'company_name' => 'اسم الشركة',
            'name' => 'الاسم',
            'sector' => 'القطاع',
            'answer' => 'الإجابة',
        ];
        return $labels[$field] ?? $field;
    }
}
