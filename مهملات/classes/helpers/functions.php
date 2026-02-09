<?php
/**
 * Global Helper Functions
 * Marketing AI System
 */

function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

function back(): void {
    $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
    redirect($referer);
}

function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function asset(string $path): string {
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

function old(string $field, string $default = ''): string {
    return htmlspecialchars($_SESSION['_old_input'][$field] ?? $default, ENT_QUOTES, 'UTF-8');
}

function csrfField(): string {
    $token = Auth::generateCSRFToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
}

function flashMessage(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function formatNumber($number, int $decimals = 0): string {
    return number_format((float)$number, $decimals, '.', ',');
}

function formatCurrency($amount): string {
    return number_format((float)$amount, 0, '.', ',') . ' ريال';
}

function formatDate(?string $date, string $format = 'Y/m/d'): string {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function formatDateAr(?string $date): string {
    if (!$date) return '-';
    return date('Y/m/d H:i', strtotime($date));
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'الآن';
    if ($diff < 3600) return floor($diff / 60) . ' دقيقة';
    if ($diff < 86400) return floor($diff / 3600) . ' ساعة';
    if ($diff < 2592000) return floor($diff / 86400) . ' يوم';
    return formatDate($datetime);
}

function truncate(string $text, int $length = 100): string {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

function getScoreColor(int $score): string {
    if ($score >= 75) return '#2e7d32';
    if ($score >= 50) return '#ed6c02';
    if ($score >= 25) return '#f57c00';
    return '#d32f2f';
}

function getAlertColor(string $type): string {
    return match($type) {
        'critical' => '#d32f2f',
        'high' => '#ed6c02',
        'warning' => '#fbc02d',
        'info' => '#0288d1',
        'opportunity' => '#2e7d32',
        default => '#9e9e9e',
    };
}

function getSeverityLabel(string $type): string {
    return match($type) {
        'critical' => 'حرج',
        'high' => 'عالي',
        'warning' => 'تحذير',
        'info' => 'معلومة',
        'opportunity' => 'فرصة',
        default => $type,
    };
}

function getMaturityLabel(string $level): string {
    return MATURITY_LEVELS[$level]['ar'] ?? $level;
}

function getSectorLabel(string $sector): string {
    return SECTORS[$sector]['ar'] ?? $sector;
}

function getPlanLabel(string $plan): string {
    return PLAN_TYPES[$plan]['ar'] ?? $plan;
}

function getExpertName(string $expertId): string {
    return EXPERTS[$expertId] ?? $expertId;
}
