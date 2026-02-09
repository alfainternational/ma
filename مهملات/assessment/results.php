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
if (!$session || $session['status'] !== 'completed') {
    redirect(url('dashboard.php'));
}

// Load analysis results
$db = Database::getInstance();
$analysis = $db->fetch("SELECT * FROM analysis_results WHERE session_id = :sid ORDER BY created_at DESC LIMIT 1", ['sid' => $sessionId]);
$alerts = $db->fetchAll("SELECT * FROM alerts WHERE session_id = :sid ORDER BY severity DESC", ['sid' => $sessionId]);
$recommendations = $db->fetchAll("SELECT * FROM recommendations WHERE session_id = :sid ORDER BY priority_order ASC", ['sid' => $sessionId]);

$scores = $analysis ? json_decode($analysis['scores'] ?? '{}', true) : [];
$insights = $analysis ? json_decode($analysis['insights'] ?? '[]', true) : [];
$expertAnalysis = $analysis ? json_decode($analysis['expert_analysis'] ?? '{}', true) : [];

$pageTitle = 'نتائج التقييم - ' . APP_NAME_AR;
$pageCSS = ['questionnaire.css'];
$pageJS = ['analysis.js'];
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="page-wrapper">
    <div class="container">
        <!-- Results Header -->
        <div class="results-header">
            <h1>نتائج التقييم التسويقي</h1>
            <p class="text-muted"><?= htmlspecialchars($session['company_name']) ?> - <?= getSectorLabel($session['company_sector']) ?></p>

            <!-- Overall Score -->
            <div class="overall-score" style="border:6px solid <?= getScoreColor($scores['overall'] ?? 0) ?>">
                <span data-target-score="<?= $scores['overall'] ?? 0 ?>">0</span>
            </div>
            <h4><?= getMaturityLabel($scores['maturity_level'] ?? 'beginner') ?></h4>
        </div>

        <!-- Score Cards -->
        <div class="scores-grid">
            <?php
            $scoreLabels = [
                'digital_maturity' => ['النضج الرقمي', 'fa-laptop'],
                'marketing_maturity' => ['النضج التسويقي', 'fa-bullhorn'],
                'organizational_readiness' => ['الجاهزية التنظيمية', 'fa-sitemap'],
                'risk_score' => ['تقييم المخاطر', 'fa-shield-alt'],
                'opportunity_score' => ['درجة الفرص', 'fa-lightbulb'],
            ];
            foreach ($scoreLabels as $key => [$label, $icon]):
                $score = $scores[$key] ?? 0;
            ?>
            <div class="score-card card">
                <div class="score-gauge" style="border:4px solid <?= getScoreColor($score) ?>">
                    <span class="score-value" data-target-score="<?= $score ?>">0</span>
                </div>
                <div class="score-label"><i class="fas <?= $icon ?> me-1"></i><?= $label ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4 mt-3">
            <!-- Charts -->
            <div class="col-lg-6">
                <div class="dashboard-card">
                    <div class="card-title"><i class="fas fa-chart-radar me-2"></i>مقارنة الأبعاد</div>
                    <canvas id="maturityRadar"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="dashboard-card">
                    <div class="card-title"><i class="fas fa-chart-pie me-2"></i>توزيع المخاطر</div>
                    <canvas id="riskDoughnut"></canvas>
                </div>
            </div>

            <!-- Alerts -->
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-title"><i class="fas fa-bell text-warning-custom me-2"></i>التنبيهات والتحذيرات</div>
                    <?php if (empty($alerts)): ?>
                    <p class="text-muted text-center py-3">لا توجد تنبيهات</p>
                    <?php else: ?>
                    <?php foreach ($alerts as $alert): ?>
                    <div class="alert-card <?= $alert['alert_type'] ?>">
                        <i class="alert-icon fas <?= $alert['alert_type'] === 'critical' ? 'fa-exclamation-triangle' : 'fa-info-circle' ?>"></i>
                        <div>
                            <strong><?= htmlspecialchars($alert['title']) ?></strong>
                            <p class="mb-1"><?= htmlspecialchars($alert['message']) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($alert['recommendation']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-title"><i class="fas fa-tasks text-success-custom me-2"></i>التوصيات</div>
                    <?php if (empty($recommendations)): ?>
                    <p class="text-muted text-center py-3">لا توجد توصيات بعد</p>
                    <?php else: ?>
                    <?php foreach ($recommendations as $rec): ?>
                    <div class="recommendation-card <?= $rec['layer'] ?? 'strategic' ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-0"><?= htmlspecialchars($rec['title']) ?></h5>
                            <span class="rec-priority <?= $rec['priority'] ?? 'medium' ?>"><?= $rec['priority'] ?? 'متوسط' ?></span>
                        </div>
                        <p class="text-muted mb-1"><?= htmlspecialchars($rec['description']) ?></p>
                        <?php if ($rec['actions']): ?>
                        <small class="text-muted"><?= htmlspecialchars($rec['actions']) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Projected Growth -->
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-title"><i class="fas fa-chart-line text-primary-custom me-2"></i>التوقعات</div>
                    <canvas id="projectionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="text-center mt-4 mb-4 no-print">
            <button onclick="MAI.printReport()" class="btn btn-outline-primary me-2">
                <i class="fas fa-print me-2"></i>طباعة التقرير
            </button>
            <a href="<?= url('dashboard.php') ?>" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>العودة للوحة التحكم
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    AnalysisCharts.init({
        scores: <?= json_encode($scores) ?>,
        risk: <?= json_encode($scores['risk_breakdown'] ?? []) ?>,
        benchmarks: <?= json_encode($scores['benchmarks'] ?? []) ?>,
        projections: <?= json_encode($scores['projections'] ?? []) ?>
    });
});
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
