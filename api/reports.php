<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'غير مصرح'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $action ?: ($input['action'] ?? '');
$user = Auth::getCurrentUser();

try {
    $sessionModel = new Session();
    $db = Database::getInstance();

    switch ($action) {
        case 'generate':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

            $sessionId = Sanitizer::int($input['session_id'] ?? 0);
            $type = Sanitizer::clean($input['type'] ?? 'executive_summary');

            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $validTypes = array_keys(REPORT_TYPES);
            if (!in_array($type, $validTypes)) {
                jsonResponse(['success' => false, 'message' => 'نوع تقرير غير صالح'], 422);
            }

            // Use ReportGenerator if available
            if (class_exists('ReportGenerator')) {
                $generator = new ReportGenerator();
                $report = $generator->generate($sessionId, $type);
            } else {
                // Basic report generation
                $analysis = $db->fetch("SELECT * FROM analysis_results WHERE session_id = :sid ORDER BY created_at DESC LIMIT 1", ['sid' => $sessionId]);
                $session = $sessionModel->getById($sessionId);

                $report = [
                    'type' => $type,
                    'title' => REPORT_TYPES[$type]['ar'] ?? $type,
                    'session_id' => $sessionId,
                    'company' => $session['company_name'],
                    'generated_at' => date('Y-m-d H:i:s'),
                    'scores' => json_decode($analysis['scores'] ?? '{}', true),
                    'insights' => json_decode($analysis['insights'] ?? '[]', true),
                ];
            }

            // Save report
            $reportId = $db->insert('reports', [
                'session_id' => $sessionId,
                'user_id' => $user['id'],
                'report_type' => $type,
                'content' => json_encode($report),
                'status' => 'completed',
            ]);

            jsonResponse(['success' => true, 'data' => ['report_id' => $reportId, 'report' => $report]]);
            break;

        case 'get':
            $reportId = Sanitizer::int($_GET['id'] ?? 0);
            $report = $db->fetch("SELECT r.*, s.company_id FROM reports r JOIN assessment_sessions s ON r.session_id = s.id WHERE r.id = :id AND r.user_id = :uid", ['id' => $reportId, 'uid' => $user['id']]);

            if (!$report) {
                jsonResponse(['success' => false, 'message' => 'التقرير غير موجود'], 404);
            }

            $report['content'] = json_decode($report['content'], true);
            jsonResponse(['success' => true, 'data' => $report]);
            break;

        case 'list':
            $sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
            if ($sessionId && !$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $sql = "SELECT id, session_id, report_type, status, created_at FROM reports WHERE user_id = :uid";
            $params = ['uid' => $user['id']];

            if ($sessionId) {
                $sql .= " AND session_id = :sid";
                $params['sid'] = $sessionId;
            }

            $sql .= " ORDER BY created_at DESC";
            $reports = $db->fetchAll($sql, $params);

            jsonResponse(['success' => true, 'data' => $reports]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'الإجراء غير معروف'], 400);
    }
} catch (\Throwable $e) {
    error_log("API Reports Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'حدث خطأ في الخادم'], 500);
}
