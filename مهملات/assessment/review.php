<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn()) redirect(url('login.php'));

$sessionId = Sanitizer::int($_GET['session_id'] ?? 0);
if (!$sessionId) redirect(url('dashboard.php'));

$sessionModel = new Session();
$user = Auth::getCurrentUser();

if (!$sessionModel->belongsToUser($sessionId, $user['id'])) {
    redirect(url('dashboard.php'));
}

$session = $sessionModel->getById($sessionId);
if (!$session || $session['status'] !== 'in_progress') {
    redirect(url('dashboard.php'));
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        // Mark session as completed
        $sessionModel->complete($sessionId);

        // Run analysis if engines are available
        if (class_exists('ContextEngine') && class_exists('ScoringEngine')) {
            try {
                $answerModel = new Answer();
                $answers = $answerModel->getBySession($sessionId);

                $contextEngine = new ContextEngine();
                $context = $contextEngine->getFullContext($sessionId);

                $scoringEngine = new ScoringEngine();
                $allScores = $scoringEngine->calculateAllScores($answers, $context);
                $scoringEngine->saveScores($sessionId, $allScores);
            } catch (\Throwable $e) {
                error_log("Analysis error: " . $e->getMessage());
            }
        }

        redirect(url('assessment/results.php?session_id=' . $sessionId));
    }
}

// Load answers
$answerModel = new Answer();
$answers = $answerModel->getBySession($sessionId);

// Group answers by category
$grouped = [];
foreach ($answers as $a) {
    $cat = $a['category'] ?? 'عام';
    $grouped[$cat][] = $a;
}

$pageTitle = 'مراجعة الإجابات - ' . APP_NAME_AR;
$pageCSS = ['questionnaire.css'];
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="page-header text-center">
                    <h1><i class="fas fa-clipboard-check text-primary-custom me-2"></i>مراجعة الإجابات</h1>
                    <p class="text-muted">راجع إجاباتك قبل إرسال التقييم النهائي</p>
                    <p><strong><?= htmlspecialchars($session['company_name']) ?></strong> - <?= count($answers) ?> إجابة</p>
                </div>

                <?php if (empty($answers)): ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-3">لم تقم بالإجابة على أي سؤال بعد</p>
                    <a href="<?= url('assessment/questionnaire.php?session_id=' . $sessionId) ?>" class="btn btn-primary">
                        العودة للأسئلة
                    </a>
                </div>
                <?php else: ?>

                <?php foreach ($grouped as $category => $catAnswers): ?>
                <div class="review-section">
                    <div class="review-section-header" data-bs-toggle="collapse"
                         data-bs-target="#cat-<?= md5($category) ?>">
                        <span><i class="fas fa-folder me-2"></i><?= htmlspecialchars($category) ?> (<?= count($catAnswers) ?>)</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="collapse show" id="cat-<?= md5($category) ?>">
                        <div class="review-section-body">
                            <?php foreach ($catAnswers as $a): ?>
                            <div class="review-answer">
                                <span class="question-label"><?= htmlspecialchars($a['question_text'] ?? 'سؤال #' . $a['question_id']) ?></span>
                                <span class="answer-value"><?= htmlspecialchars(is_array($a['answer_value']) ? implode(', ', $a['answer_value']) : $a['answer_value']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between mt-4 mb-4">
                    <a href="<?= url('assessment/questionnaire.php?session_id=' . $sessionId) ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للتعديل
                    </a>
                    <form method="post" action="">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>إرسال التقييم
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
