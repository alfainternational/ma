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
    flashMessage('error', 'ليس لديك صلاحية للوصول لهذا التقييم');
    redirect(url('dashboard.php'));
}

$session = $sessionModel->getById($sessionId);
if (!$session || $session['status'] !== 'in_progress') {
    if ($session && $session['status'] === 'completed') {
        redirect(url('assessment/results.php?session_id=' . $sessionId));
    }
    redirect(url('dashboard.php'));
}

$pageTitle = 'التقييم - ' . APP_NAME_AR;
$pageCSS = ['questionnaire.css'];
$pageJS = ['questionnaire.js'];
$bodyClass = 'questionnaire-page';
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="page-wrapper">
    <div class="container">
        <!-- Progress -->
        <div class="progress-info">
            <span class="progress-count"><?= $session['answered_questions'] ?> / <?= $session['total_questions'] ?: '~' ?></span>
            <span class="progress-percent"><?= $session['progress_percent'] ?>%</span>
        </div>
        <div class="assessment-progress">
            <div class="progress-fill" style="width:<?= $session['progress_percent'] ?>%"></div>
        </div>

        <!-- Question Container (rendered by JS) -->
        <div id="questionContainer">
            <div class="text-center py-5">
                <div class="loading-spinner mx-auto mb-3"></div>
                <p class="text-muted">جاري تحميل السؤال...</p>
            </div>
        </div>

        <!-- Navigation -->
        <div class="question-nav">
            <button id="btnPrev" class="btn btn-outline-primary">
                <i class="fas fa-arrow-right me-2"></i>السابق
            </button>
            <span id="btnSkip" class="skip-link">تخطي <i class="fas fa-forward me-1"></i></span>
            <button id="btnNext" class="btn btn-primary">
                التالي<i class="fas fa-arrow-left ms-2"></i>
            </button>
        </div>

        <!-- Auto-save indicator -->
        <div class="auto-save"><i class="fas fa-check me-1"></i>تم الحفظ</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qm = new QuestionnaireManager(<?= $sessionId ?>);
    qm.init();
    window.questionnaireManager = qm;
});
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
