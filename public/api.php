<?php

declare(strict_types=1);

/**
 * Marketing AI Assessment System - API Entry Point
 * RESTful API v1 Router
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            putenv(trim($line));
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Error handling
set_exception_handler(function (\Throwable $e) {
    $code = match (true) {
        $e instanceof \App\Shared\Exceptions\ValidationException => 422,
        $e instanceof \App\Shared\Exceptions\AuthenticationException => 401,
        $e instanceof \App\Shared\Exceptions\NotFoundException => 404,
        default => 500,
    };

    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        'success' => false,
        'data' => null,
        'message' => $e->getMessage(),
    ];

    if ($e instanceof \App\Shared\Exceptions\ValidationException) {
        $response['errors'] = $e->getErrors();
    }

    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        $response['debug'] = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
});

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Services initialization
use App\Infrastructure\Security\JwtManager;
use App\Infrastructure\Security\PasswordHasher;
use App\Infrastructure\Security\RateLimiter;
use App\Infrastructure\Persistence\MySQL\UserRepository;
use App\Infrastructure\Persistence\MySQL\SessionRepository;
use App\Infrastructure\Persistence\MySQL\QuestionRepository;
use App\Application\Services\AuthService;
use App\Application\Services\AssessmentService;
use App\Application\Services\AnalysisService;
use App\Application\Services\ReportService;
use App\API\Controllers\AuthController;
use App\API\Controllers\AssessmentController;
use App\API\Controllers\QuestionController;
use App\API\Controllers\ReportController;
use App\API\Controllers\AdminController;
use App\API\Middleware\AuthMiddleware;
use App\API\Middleware\RateLimitMiddleware;
use App\API\Middleware\CorsMiddleware;

// CORS
$cors = new CorsMiddleware();
if (!$cors->handle()) {
    exit;
}

// Rate limiting
$rateLimiter = new RateLimitMiddleware(new RateLimiter(), 120, 60);
if (!$rateLimiter->handle()) {
    exit;
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove /api/v1 prefix
$path = preg_replace('#^/api/v1#', '', $path);
$path = rtrim($path, '/') ?: '/';

// Initialize services
$jwt = new JwtManager();
$passwordHasher = new PasswordHasher();
$userRepo = new UserRepository();
$sessionRepo = new SessionRepository();
$questionRepo = new QuestionRepository();

$authService = new AuthService($userRepo, $jwt, $passwordHasher);
$assessmentService = new AssessmentService($sessionRepo, $questionRepo);
$analysisService = new AnalysisService();
$reportService = new ReportService();

$authController = new AuthController($authService);
$assessmentController = new AssessmentController($assessmentService);
$questionController = new QuestionController($questionRepo);
$reportController = new ReportController($reportService);
$adminController = new AdminController();

$authMiddleware = new AuthMiddleware($jwt);

// Route matching helper
function matchRoute(string $pattern, string $path, array &$params = []): bool
{
    $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
    $regex = "#^{$regex}$#";
    if (preg_match($regex, $path, $matches)) {
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        return true;
    }
    return false;
}

$params = [];

// Public routes (no auth required)
if ($method === 'POST' && $path === '/auth/login') {
    $authController->login();
    exit;
}

if ($method === 'POST' && $path === '/auth/register') {
    $authController->register();
    exit;
}

if ($method === 'POST' && $path === '/auth/refresh') {
    $authController->refreshToken();
    exit;
}

// Health check
if ($method === 'GET' && $path === '/health') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'data' => [
            'status' => 'healthy',
            'version' => '2.0.0',
            'timestamp' => date('c'),
        ],
        'message' => 'النظام يعمل بشكل طبيعي',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Protected routes (auth required)
if (!$authMiddleware->handle()) {
    exit;
}

// Auth routes
if ($method === 'GET' && $path === '/auth/me') {
    $authController->me();
    exit;
}

if ($method === 'POST' && $path === '/auth/logout') {
    $authController->logout();
    exit;
}

// Assessment routes
if ($method === 'POST' && $path === '/assessments/sessions') {
    $assessmentController->createSession();
    exit;
}

if ($method === 'GET' && matchRoute('/assessments/sessions/{id}', $path, $params)) {
    $_REQUEST['session_id'] = $params['id'];
    $assessmentController->getSession();
    exit;
}

if ($method === 'POST' && matchRoute('/assessments/sessions/{id}/answers', $path, $params)) {
    $_REQUEST['session_id'] = $params['id'];
    $assessmentController->submitAnswer();
    exit;
}

if ($method === 'GET' && matchRoute('/assessments/sessions/{id}/progress', $path, $params)) {
    $_REQUEST['session_id'] = $params['id'];
    $assessmentController->getProgress();
    exit;
}

if ($method === 'POST' && matchRoute('/assessments/sessions/{id}/complete', $path, $params)) {
    $_REQUEST['session_id'] = $params['id'];
    $assessmentController->completeSession();
    exit;
}

if ($method === 'GET' && matchRoute('/assessments/sessions/{id}/next-question', $path, $params)) {
    $_REQUEST['session_id'] = $params['id'];
    $questionController->getNext();
    exit;
}

if ($method === 'GET' && $path === '/assessments/sessions') {
    $assessmentController->listSessions();
    exit;
}

// Question routes
if ($method === 'GET' && $path === '/questions') {
    $questionController->list();
    exit;
}

if ($method === 'GET' && matchRoute('/questions/category/{category}', $path, $params)) {
    $_REQUEST['category'] = $params['category'];
    $questionController->getByCategory();
    exit;
}

// Report routes
if ($method === 'POST' && $path === '/reports') {
    $reportController->generate();
    exit;
}

if ($method === 'GET' && matchRoute('/reports/{id}', $path, $params)) {
    $_REQUEST['report_id'] = $params['id'];
    $reportController->get();
    exit;
}

if ($method === 'GET' && matchRoute('/reports/{id}/export', $path, $params)) {
    $_REQUEST['report_id'] = $params['id'];
    $reportController->export();
    exit;
}

if ($method === 'GET' && $path === '/reports') {
    $reportController->list();
    exit;
}

// Admin routes
if ($path === '/admin/dashboard' && $method === 'GET') {
    if (!$authMiddleware->requireRole('admin')) exit;
    $adminController->dashboard();
    exit;
}

if ($path === '/admin/users' && $method === 'GET') {
    if (!$authMiddleware->requireRole('admin')) exit;
    $adminController->listUsers();
    exit;
}

if ($path === '/admin/sessions' && $method === 'GET') {
    if (!$authMiddleware->requireRole('admin')) exit;
    $adminController->listSessions();
    exit;
}

if ($path === '/admin/stats' && $method === 'GET') {
    if (!$authMiddleware->requireRole('admin')) exit;
    $adminController->getSystemStats();
    exit;
}

// 404 - Route not found
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => false,
    'data' => null,
    'message' => 'المسار غير موجود',
], JSON_UNESCAPED_UNICODE);
