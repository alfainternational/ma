<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// تحميل متغيرات البيئة
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// إعداد التوجيه الديناميكي
$scriptName = $_SERVER['SCRIPT_NAME']; // مثال: /project/public/index.php
$basePath = str_replace('\\', '/', dirname($scriptName));
if ($basePath === '/') $basePath = '';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = '/' . ltrim(substr($requestUri, strlen($basePath)), '/');
$route = rtrim($route, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// تعريف الثوابت للمسارات
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . "://" . $host . $basePath);
define('APP_ROUTE', $route);

// --- توجيه الواجهات (Frontend Views) ---
if ($method === 'GET') {
    $content = "";
    switch ($route) {
        case '/':
        case '/login':
            ob_start(); include __DIR__ . '/../src/Views/auth/login.php'; $content = ob_get_clean();
            include __DIR__ . '/../src/Views/layout.php';
            exit;
        case '/register':
            ob_start(); include __DIR__ . '/../src/Views/auth/register.php'; $content = ob_get_clean();
            include __DIR__ . '/../src/Views/layout.php';
            exit;
        case '/setup-company':
            ob_start(); include __DIR__ . '/../src/Views/auth/setup-company.php'; $content = ob_get_clean();
            include __DIR__ . '/../src/Views/layout.php';
            exit;
        case '/dashboard':
            ob_start(); include __DIR__ . '/../src/Views/dashboard/router.php'; $content = ob_get_clean();
            include __DIR__ . '/../src/Views/layout.php';
            exit;
        case '/admin/questions':
            // Verify admin via simple session check or rely on API to block data
            // Best practice: Middleware check before rendering, but for now we rely on API protection in the view or layout
            $controller = new \App\API\AdminController();
            $questions = $controller->getAllQuestions();
            ob_start(); include __DIR__ . '/../src/Views/admin/questions/index.php'; $content = ob_get_clean();
            include __DIR__ . '/../src/Views/layout.php';
            exit;
        case '/admin/questions/edit':
            $id = $_GET['id'] ?? null;
            $question = null;
            if ($id) {
                $controller = new \App\API\AdminController();
                $question = $controller->getQuestion($id);
            }
            ob_start(); include __DIR__ . '/../src/Views/admin/questions/edit.php'; $content = ob_get_clean();
            include __DIR__ . '/../src/Views/layout.php';
            exit;
        case '/assessment/start':
            $controller = new \App\API\QuestionController();
            $data = $controller->handleAssessmentStart($_GET['session'] ?? null);
            extract($data); // $sessionId, $initialQuestion
            
            // This view is standalone (Full Screen Focus Mode)
            include __DIR__ . '/../src/Views/questionnaire/active.php';
            exit;
        case '/analysis/results':
            ob_start(); include __DIR__ . '/../src/Views/analysis/results.php'; $content = ob_get_clean();
            include __DIR__ . '/../src/Views/layout.php';
            exit;
    }
}

// --- توجيه الـ API ---
header('Content-Type: application/json; charset=utf-8');
if (strpos($route, '/api/') === 0) {
    // --- Public Routes ---
    
    // Auth Login
    if ($route === '/api/auth/login' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $auth = new \App\API\AuthController();
        echo json_encode($auth->login($data['email'] ?? '', $data['password'] ?? ''));
        exit;
    }

    // Auth Register
    if ($route === '/api/auth/register' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $auth = new \App\API\AuthController();
        echo json_encode($auth->register(
            $data['fullName'] ?? '', 
            $data['email'] ?? '', 
            $data['password'] ?? '',
            $data['phone'] ?? ''
        ));
        exit;
    }

    // --- Protected Routes (Auth Required) ---
    $middleware = new \App\Middleware\AuthMiddleware();
    $userContext = $middleware->handle(); // Throws 401 if invalid

    // Auth Check (Get Current User)
    if ($route === '/api/auth/me' && $method === 'GET') {
        echo json_encode(['status' => 'success', 'user' => $userContext]);
        exit;
    }
    
    // --- Assessment APIs (Now Protected) ---

    // Question Get
    if ($route === '/api/questions/get' && $method === 'GET') {
        $id = $_GET['id'] ?? null;
        $controller = new \App\API\QuestionController();
        echo json_encode($controller->getQuestion($id));
        exit;
    }
    
    // Questions Submit
    if ($route === '/api/questions/submit' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $controller = new \App\API\QuestionController();
        echo json_encode($controller->submitAnswer(
            $data['sessionId'] ?? '', 
            $data['questionId'] ?? '', 
            $data['value'] ?? '',
            $userContext['userId'] ?? null
        ));
        exit;
    }

    // Questions Next
    if ($route === '/api/questions/next' && $method === 'POST') {
        $rawInput = file_get_contents('php://input');
        
        $data = json_decode($rawInput, true);
        $flow = new \App\Service\QuestionFlowService();
        $nextId = $flow->getNextQuestionId($data['lastId'] ?? null, $data['answers'] ?? [], []);
        
        echo json_encode(['status' => 'success', 'next_id' => $nextId]);
        exit;
    }
    
    // Report Get
    if ($route === '/api/report/get' && $method === 'GET') {
        $sessionId = $_GET['sessionId'] ?? '';
        $controller = new \App\API\ReportController();
        echo json_encode($controller->generateReport($sessionId));
        exit;
    }

    // --- View Rendering APIs (Serving HTML Fragments) ---
    
    // Admin Dashboard View
    if ($route === '/api/admin/dashboard-view' && $method === 'GET') {
        if ($userContext['role'] !== 'admin') {
             http_response_code(403);
             echo "Access Denied"; 
             exit;
        }
        $controller = new \App\API\AdminController();
        $data = $controller->getDashboardStats();
        extract($data); // $stats, $users
        header('Content-Type: text/html; charset=utf-8');
        include __DIR__ . '/../src/Views/admin/dashboard.php';
        exit;
    }

    // Admin Question Management
    if ($route === '/api/admin/questions/save' && $method === 'POST') {
        if ($userContext['role'] !== 'admin') { http_response_code(403); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $controller = new \App\API\AdminController();
        echo json_encode($controller->saveQuestion($data));
        exit;
    }
    
    if ($route === '/api/admin/questions/delete' && $method === 'POST') {
        if ($userContext['role'] !== 'admin') { http_response_code(403); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $controller = new \App\API\AdminController();
        echo json_encode($controller->deleteQuestion($data['id']));
        exit;
    }

    // Client Dashboard View
    if ($route === '/api/client/dashboard-view' && $method === 'GET') {
        $controller = new \App\API\ClientController();
        // User context now uses 'userId'
        $data = $controller->getDashboardData($userContext['userId']);
        extract($data); // $reports
        header('Content-Type: text/html; charset=utf-8');
        include __DIR__ . '/../src/Views/dashboard/main.php';
        exit;
    }

    // Create Company
    if ($route === '/api/company/create' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $controller = new \App\API\ClientController();
        echo json_encode($controller->createCompany($userContext['userId'], $data));
        exit;
    }

    // Start Session
    if ($route === '/api/sessions/start' && $method === 'POST') {
        $clientCtrl = new \App\API\ClientController();
        $companyId = $clientCtrl->getCompanyId($userContext['userId']);
        
        if (!$companyId) {
            echo json_encode(['status' => 'error', 'message' => 'يرجى إكمال إعدادات الشركة أولاً']);
            exit;
        }

        $sessionCtrl = new \App\API\SessionController();
        echo json_encode($sessionCtrl->startSession($companyId, $userContext['userId']));
        exit;
    }

    // End of API Routes
}

echo json_encode(['message' => 'Route not found', 'route' => $route]);
