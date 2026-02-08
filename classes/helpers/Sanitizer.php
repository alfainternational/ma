<?php
/**
 * Input Sanitizer
 * Marketing AI System
 */
class Sanitizer {
    public static function clean(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function cleanArray(array $data): array {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = self::clean($value);
            } elseif (is_array($value)) {
                $cleaned[$key] = self::cleanArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }

    public static function email(string $email): string {
        return filter_var(trim(strtolower($email)), FILTER_SANITIZE_EMAIL);
    }

    public static function int($value): int {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function float($value): float {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function slug(string $text): string {
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    public static function filename(string $name): string {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $name);
    }
}
