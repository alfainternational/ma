<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn()) { redirect(url('login.php')); }

$user = Auth::getCurrentUser();
$userId = $user['id'];

$sessionModel = new Session();
$companyModel = new Company();

$sessions = $sessionModel->getByUserId($userId, 5);
$companies = $companyModel->getByUserId($userId);
$stats = $sessionModel->getStats();

// Calculate user-specific stats
$userSessions = $sessionModel->getByUserId($userId, 100);
$totalSessions = count($userSessions);
$completedSessions = count(array_filter($userSessions, fn($s) => $s['status'] === 'completed'));
$inProgressSessions = count(array_filter($userSessions, fn($s) => $s['status'] === 'in_progress'));

$pageTitle = 'لوحة التحكم - ' . APP_NAME_AR;
$flash = getFlash();
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="page-wrapper">
    <div class="container">
        <?php if ($flash): ?>
        <div class="flash-message <?= $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <!-- Welcome -->
        <div class="page-header">
            <h1>مرحباً، <?= htmlspecialchars($user['full_name'] ?? 'المستخدم') ?></h1>
            <p>إليك نظرة عامة على تقييماتك التسويقية</p>
        </div>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card info">
                    <div class="stat-icon" style="background:#e3f2fd;color:#1976d2"><i class="fas fa-clipboard-list"></i></div>
                    <div class="stat-value"><?= $totalSessions ?></div>
                    <div class="stat-label">إجمالي التقييمات</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card success">
                    <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?= $completedSessions ?></div>
                    <div class="stat-label">مكتملة</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card warning">
                    <div class="stat-icon" style="background:#fff3e0;color:#ed6c02"><i class="fas fa-spinner"></i></div>
                    <div class="stat-value"><?= $inProgressSessions ?></div>
                    <div class="stat-label">قيد التنفيذ</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e3f2fd;color:#1976d2"><i class="fas fa-building"></i></div>
                    <div class="stat-value"><?= count($companies) ?></div>
                    <div class="stat-label">شركاتي</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Sessions -->
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="card-title">
                        <i class="fas fa-history text-primary-custom"></i>
                        آخر التقييمات
                    </div>
                    <?php if (empty($sessions)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">لم تقم بأي تقييم بعد</p>
                        <a href="<?= url('assessment/start.php') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>ابدأ أول تقييم
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>الشركة</th><th>القطاع</th><th>التقدم</th><th>الحالة</th><th>التاريخ</th><th></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sessions as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['company_name']) ?></strong></td>
                                    <td><?= getSectorLabel($s['company_sector']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:80px;height:6px;background:var(--gray-200);border-radius:3px">
                                                <div style="width:<?= $s['progress_percent'] ?>%;height:100%;background:var(--primary-500);border-radius:3px"></div>
                                            </div>
                                            <small><?= $s['progress_percent'] ?>%</small>
                                        </div>
                                    </td>
                                    <td><span class="badge-status <?= $s['status'] ?>">
                                        <?= $s['status'] === 'completed' ? 'مكتمل' : ($s['status'] === 'in_progress' ? 'جاري' : 'متوقف') ?>
                                    </span></td>
                                    <td><small class="text-muted"><?= timeAgo($s['started_at']) ?></small></td>
                                    <td>
                                        <?php if ($s['status'] === 'completed'): ?>
                                            <a href="<?= url('assessment/results.php?session_id=' . $s['id']) ?>" class="btn btn-sm btn-outline-primary">النتائج</a>
                                        <?php elseif ($s['status'] === 'in_progress'): ?>
                                            <a href="<?= url('assessment/questionnaire.php?session_id=' . $s['id']) ?>" class="btn btn-sm btn-primary">متابعة</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="dashboard-card mb-4">
                    <div class="card-title"><i class="fas fa-bolt text-warning-custom"></i> إجراءات سريعة</div>
                    <div class="d-grid gap-2">
                        <a href="<?= url('assessment/start.php') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>تقييم جديد
                        </a>
                        <a href="<?= url('company/profile.php') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-building me-2"></i>ملف الشركة
                        </a>
                    </div>
                </div>

                <?php if (!empty($companies)): ?>
                <div class="dashboard-card">
                    <div class="card-title"><i class="fas fa-building text-primary-custom"></i> شركاتي</div>
                    <?php foreach ($companies as $c): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <strong class="d-block"><?= htmlspecialchars($c['name']) ?></strong>
                            <small class="text-muted"><?= $c['sector_name_ar'] ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
