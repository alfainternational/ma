<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) redirect(url('login.php'));

// Load questions from JSON
$questionsFile = BASE_PATH . '/data/questions.json';
$allQuestions = [];
$categories = [];

if (file_exists($questionsFile)) {
    $data = json_decode(file_get_contents($questionsFile), true);
    $allQuestions = $data['questions'] ?? $data ?? [];
    foreach ($allQuestions as $q) {
        $cat = $q['category'] ?? 'unknown';
        if (!isset($categories[$cat])) $categories[$cat] = 0;
        $categories[$cat]++;
    }
}

$categoryFilter = $_GET['category'] ?? '';
if ($categoryFilter) {
    $allQuestions = array_filter($allQuestions, fn($q) => ($q['category'] ?? '') === $categoryFilter);
    $allQuestions = array_values($allQuestions);
}

$totalQuestions = count($allQuestions);

$pageTitle = 'بنك الأسئلة - ' . APP_NAME_AR;
$pageCSS = ['admin.css'];
include BASE_PATH . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="admin-content">
        <button class="btn btn-sm btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="admin-header"><h1>بنك الأسئلة</h1><span class="text-muted"><?= $totalQuestions ?> سؤال</span></div>

        <!-- Category Summary -->
        <div class="row g-3 mb-4">
            <?php foreach ($categories as $cat => $count): ?>
            <div class="col-auto">
                <a href="?category=<?= urlencode($cat) ?>" class="btn btn-sm <?= $categoryFilter === $cat ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <?= htmlspecialchars($cat) ?> (<?= $count ?>)
                </a>
            </div>
            <?php endforeach; ?>
            <?php if ($categoryFilter): ?>
            <div class="col-auto"><a href="?" class="btn btn-sm btn-outline-secondary">كل الفئات</a></div>
            <?php endif; ?>
        </div>

        <div class="admin-table-wrapper">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>الرمز</th><th>السؤال</th><th>النوع</th><th>الفئة</th><th>الأولوية</th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($allQuestions, 0, 50) as $q): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($q['id'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars(mb_substr($q['text_ar'] ?? '-', 0, 80)) ?><?= mb_strlen($q['text_ar'] ?? '') > 80 ? '...' : '' ?></td>
                        <td><small><?= $q['type'] ?? '-' ?></small></td>
                        <td><small><?= $q['category'] ?? '-' ?></small></td>
                        <td><span class="badge-status <?= ($q['priority'] ?? '') === 'critical' ? 'abandoned' : (($q['priority'] ?? '') === 'high' ? 'in_progress' : 'completed') ?>"><?= $q['priority'] ?? '-' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($allQuestions) > 50): ?>
            <div class="admin-pagination"><span>عرض 50 من <?= count($allQuestions) ?></span></div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
