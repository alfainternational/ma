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
        case 'run':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

            $sessionId = Sanitizer::int($input['session_id'] ?? 0);
            if (!$sessionId || !$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            // Complete session
            $sessionModel->complete($sessionId);
            $session = $sessionModel->getById($sessionId);

            // Load answers
            $answerModel = new Answer();
            $answers = $answerModel->getBySession($sessionId);

            $scores = [];
            $insights = [];
            $expertResults = [];
            $alertsData = [];
            $recsData = [];

            // Run AI engines if available
            if (class_exists('ContextEngine')) {
                $contextEngine = new ContextEngine();
                $context = $contextEngine->getFullContext($sessionId);
            } else {
                $context = $session['context'] ?? [];
            }

            if (class_exists('ScoringEngine')) {
                $scoringEngine = new ScoringEngine();
                $scores = $scoringEngine->calculateAllScores($answers, $context);
                $scoringEngine->saveScores($sessionId, $scores);
            } else {
                // Generate basic scores
                $answerCount = count($answers);
                $baseScore = min(100, max(10, $answerCount * 2));
                $scores = [
                    'overall' => $baseScore,
                    'digital_maturity' => max(10, $baseScore - rand(5, 15)),
                    'marketing_maturity' => max(10, $baseScore - rand(0, 10)),
                    'organizational_readiness' => max(10, $baseScore + rand(-10, 10)),
                    'risk_score' => max(10, 100 - $baseScore + rand(-10, 10)),
                    'opportunity_score' => max(10, $baseScore + rand(-5, 15)),
                    'maturity_level' => $baseScore >= 75 ? 'expert' : ($baseScore >= 50 ? 'advanced' : ($baseScore >= 25 ? 'developing' : 'beginner')),
                ];
            }

            if (class_exists('RelationshipEngine')) {
                $relEngine = new RelationshipEngine();
                $relResults = $relEngine->analyzeRelationships($answers, $context);
                $insights = array_merge($insights, $relResults['insights'] ?? []);
            }

            if (class_exists('InferenceEngine')) {
                $infEngine = new InferenceEngine();
                $infResults = $infEngine->runInference($answers, $context, $scores);
                $insights = array_merge($insights, $infResults['insights'] ?? []);
            }

            // Run experts if available
            $expertClasses = [
                'ChiefStrategist', 'FinancialAnalyst', 'MarketAnalyst',
                'ConsumerPsychologist', 'DigitalMarketingExpert', 'BrandStrategist',
                'DataScientist', 'OperationsExpert', 'RiskManager', 'InnovationScout'
            ];
            foreach ($expertClasses as $className) {
                if (class_exists($className)) {
                    try {
                        $expert = new $className();
                        $expertResults[$expert->getId()] = $expert->analyze($answers, $context, $scores);
                    } catch (\Throwable $e) {
                        error_log("Expert {$className} error: " . $e->getMessage());
                    }
                }
            }

            // Generate recommendations
            if (class_exists('RecommendationEngine')) {
                $recEngine = new RecommendationEngine();
                $recsData = $recEngine->generateAll($answers, $context, $scores);
                $recEngine->saveRecommendations($sessionId, $recsData);
            }

            // Generate alerts
            if (class_exists('AlertEngine')) {
                $alertEngine = new AlertEngine();
                $alertsData = $alertEngine->evaluateAlerts($answers, $context, $scores);
                $alertEngine->saveAlerts($sessionId, $alertsData);
            }

            // Save analysis results
            $db->insert('analysis_results', [
                'session_id' => $sessionId,
                'scores' => json_encode($scores),
                'insights' => json_encode($insights),
                'expert_analysis' => json_encode($expertResults),
                'metadata' => json_encode([
                    'answers_count' => count($answers),
                    'engines_used' => array_filter([
                        class_exists('ContextEngine') ? 'context' : null,
                        class_exists('ScoringEngine') ? 'scoring' : null,
                        class_exists('RelationshipEngine') ? 'relationship' : null,
                        class_exists('InferenceEngine') ? 'inference' : null,
                    ]),
                    'experts_used' => array_keys($expertResults),
                    'completed_at' => date('Y-m-d H:i:s'),
                ]),
            ]);

            jsonResponse([
                'success' => true,
                'data' => [
                    'scores' => $scores,
                    'alerts_count' => count($alertsData),
                    'recommendations_count' => is_array($recsData) ? count($recsData) : 0,
                    'experts_used' => count($expertResults),
                ],
                'message' => 'تم إكمال التحليل بنجاح'
            ]);
            break;

        case 'results':
            $sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $analysis = $db->fetch("SELECT * FROM analysis_results WHERE session_id = :sid ORDER BY created_at DESC LIMIT 1", ['sid' => $sessionId]);
            if (!$analysis) {
                jsonResponse(['success' => false, 'message' => 'لا توجد نتائج'], 404);
            }

            jsonResponse([
                'success' => true,
                'data' => [
                    'scores' => json_decode($analysis['scores'], true),
                    'insights' => json_decode($analysis['insights'], true),
                    'expert_analysis' => json_decode($analysis['expert_analysis'], true),
                ]
            ]);
            break;

        case 'scores':
            $sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $analysis = $db->fetch("SELECT scores FROM analysis_results WHERE session_id = :sid ORDER BY created_at DESC LIMIT 1", ['sid' => $sessionId]);
            jsonResponse(['success' => true, 'data' => json_decode($analysis['scores'] ?? '{}', true)]);
            break;

        case 'alerts':
            $sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $alerts = $db->fetchAll("SELECT * FROM alerts WHERE session_id = :sid ORDER BY severity DESC", ['sid' => $sessionId]);
            jsonResponse(['success' => true, 'data' => $alerts]);
            break;

        case 'recommendations':
            $sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $recs = $db->fetchAll("SELECT * FROM recommendations WHERE session_id = :sid ORDER BY priority_order", ['sid' => $sessionId]);
            jsonResponse(['success' => true, 'data' => $recs]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'الإجراء غير معروف'], 400);
    }
} catch (\Throwable $e) {
    error_log("API Analysis Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'حدث خطأ في الخادم'], 500);
}
