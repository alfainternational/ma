<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) {
    redirect(url('login.php'));
}

$userModel = new User();
$companyModel = new Company();
$sessionModel = new Session();
$db = Database::getInstance();

$totalUsers = $userModel->getTotalCount();
$totalCompanies = $companyModel->getTotalCount();
$sessionStats = $sessionModel->getStats();
$recentUsers = $userModel->getRecentUsers(5);
$recentSessions = $sessionModel->getAll(5);
$sectorDist = $companyModel->getSectorDistribution();

$pageTitle = 'لوحة الإدارة - ' . APP_NAME_AR;
$pageCSS = ['admin.css'];
$pageJS = ['admin.js'];
$bodyClass = 'admin-page';
include BASE_PATH . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>

    <main class="admin-content">
        <!-- Mobile Toggle -->
        <button class="btn btn-sm btn-outline-primary d-lg-none mb-3" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="admin-header">
            <h1>لوحة المعلومات</h1>
            <span class="text-muted"><?= date('Y/m/d') ?></span>
        </div>

        <!-- Stats -->
        <div class="admin-stats">
            <div class="admin-stat-card">
                <div class="stat-icon-wrapper blue"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">المستخدمون</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="stat-icon-wrapper green"><i class="fas fa-building"></i></div>
                <div>
                    <div class="stat-value"><?= $totalCompanies ?></div>
                    <div class="stat-label">الشركات</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="stat-icon-wrapper orange"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-value"><?= $sessionStats['total'] ?></div>
                    <div class="stat-label">التقييمات</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="stat-icon-wrapper green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-value"><?= $sessionStats['completion_rate'] ?>%</div>
                    <div class="stat-label">معدل الإكمال</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Users -->
            <div class="col-lg-6">
                <div class="admin-table-wrapper">
                    <div class="admin-table-header">
                        <h5 class="mb-0">أحدث المستخدمين</h5>
                        <a href="<?= url('admin/users.php') ?>" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                    </div>
                    <table class="data-table">
                        <thead><tr><th>الاسم</th><th>الدور</th><th>التاريخ</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                            </td>
                            <td><span class="badge-status <?= $u['role'] === 'admin' ? 'active' : 'in_progress' ?>"><?= $u['role'] ?></span></td>
                            <td><small><?= timeAgo($u['created_at']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Sessions -->
            <div class="col-lg-6">
                <div class="admin-table-wrapper">
                    <div class="admin-table-header">
                        <h5 class="mb-0">أحدث التقييمات</h5>
                        <a href="<?= url('admin/sessions.php') ?>" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                    </div>
                    <table class="data-table">
                        <thead><tr><th>المستخدم</th><th>الشركة</th><th>الحالة</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentSessions as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['user_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($s['company_name']) ?></td>
                            <td><span class="badge-status <?= $s['status'] ?>"><?= $s['status'] === 'completed' ? 'مكتمل' : ($s['status'] === 'in_progress' ? 'جاري' : 'متوقف') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Charts -->
            <div class="col-lg-6">
                <div class="dashboard-card">
                    <div class="card-title">توزيع القطاعات</div>
                    <canvas id="sectorChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="dashboard-card">
                    <div class="card-title">الجلسات</div>
                    <canvas id="sessionsChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    AdminPanel.initDashboardCharts({
        sectors: {
            labels: <?= json_encode(array_map(fn($s) => getSectorLabel($s['sector']), $sectorDist)) ?>,
            values: <?= json_encode(array_column($sectorDist, 'count')) ?>
        }
    });
});
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
