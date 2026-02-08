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
    $questionModel = new Question();
    $answerModel = new Answer();

    switch ($action) {
        case 'next':
            $sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
            if (!$sessionId || !$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'جلسة غير صالحة'], 403);
            }

            $session = $sessionModel->getById($sessionId);
            $existingAnswers = $answerModel->getBySession($sessionId);
            $answeredIds = array_column($existingAnswers, 'question_id');

            // Load all questions
            $questionsFile = BASE_PATH . '/data/questions.json';
            $questions = [];
            if (file_exists($questionsFile)) {
                $allQuestions = json_decode(file_get_contents($questionsFile), true);
                $questions = $allQuestions['questions'] ?? $allQuestions ?? [];
            }

            if (empty($questions)) {
                // Fallback to database
                $questions = $questionModel->getAll();
            }

            // Filter applicable questions for the sector
            $sector = $session['company_sector'] ?? 'all';
            $questions = array_filter($questions, function($q) use ($sector) {
                $applicable = $q['applicable_sectors'] ?? ['all'];
                return in_array('all', $applicable) || in_array($sector, $applicable);
            });
            $questions = array_values($questions);

            // Use QuestionFlow if available
            if (class_exists('QuestionFlow')) {
                $flow = new QuestionFlow();
                $context = $session['context'] ?? [];
                $answersMap = [];
                foreach ($existingAnswers as $a) {
                    $answersMap[$a['question_id']] = $a['answer_value'];
                }
                $nextQ = $flow->getNextQuestion($sessionId, $answeredIds, $context);
                if ($nextQ) {
                    jsonResponse([
                        'success' => true,
                        'data' => [
                            'question' => $nextQ,
                            'total' => count($questions),
                            'answered' => count($answeredIds),
                        ]
                    ]);
                }
            }

            // Simple sequential fallback
            $nextQuestion = null;
            foreach ($questions as $q) {
                $qId = $q['id'] ?? '';
                if (!in_array($qId, $answeredIds)) {
                    $nextQuestion = $q;
                    break;
                }
            }

            if (!$nextQuestion) {
                jsonResponse(['success' => true, 'data' => ['completed' => true, 'total' => count($questions), 'answered' => count($answeredIds)]]);
            }

            // Update session progress
            $sessionModel->updateProgress($sessionId, count($answeredIds), count($questions), $nextQuestion['id'] ?? null);

            jsonResponse([
                'success' => true,
                'data' => [
                    'question' => $nextQuestion,
                    'total' => count($questions),
                    'answered' => count($answeredIds),
                ]
            ]);
            break;

        case 'answer':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

            $sessionId = Sanitizer::int($input['session_id'] ?? 0);
            $questionId = Sanitizer::clean($input['question_id'] ?? '');
            $answer = $input['answer'] ?? null;

            if (!$sessionId || !$questionId || $answer === null) {
                jsonResponse(['success' => false, 'message' => 'بيانات ناقصة'], 422);
            }

            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $answerModel->save([
                'session_id' => $sessionId,
                'question_id' => $questionId,
                'answer_value' => is_array($answer) ? json_encode($answer) : $answer,
            ]);

            // Update context if needed
            if (class_exists('ContextEngine')) {
                try {
                    $contextEngine = new ContextEngine();
                    $allAnswers = $answerModel->getBySession($sessionId);
                    $session = $sessionModel->getById($sessionId);
                    // Run lightweight context update
                    $context = $contextEngine->getFullContext($sessionId);
                    $sessionModel->updateContext($sessionId, $context);
                } catch (\Throwable $e) {
                    error_log("Context update error: " . $e->getMessage());
                }
            }

            jsonResponse(['success' => true, 'message' => 'تم حفظ الإجابة']);
            break;

        case 'skip':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

            $sessionId = Sanitizer::int($input['session_id'] ?? 0);
            $questionId = Sanitizer::clean($input['question_id'] ?? '');

            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            // Save as skipped
            $answerModel->save([
                'session_id' => $sessionId,
                'question_id' => $questionId,
                'answer_value' => '__skipped__',
            ]);

            jsonResponse(['success' => true, 'message' => 'تم تخطي السؤال']);
            break;

        case 'progress':
            $sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
            if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
                jsonResponse(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $session = $sessionModel->getById($sessionId);
            jsonResponse([
                'success' => true,
                'data' => [
                    'progress_percent' => $session['progress_percent'],
                    'answered' => $session['answered_questions'],
                    'total' => $session['total_questions'],
                ]
            ]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'الإجراء غير معروف'], 400);
    }
} catch (\Throwable $e) {
    error_log("API Questions Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'حدث خطأ في الخادم'], 500);
}
