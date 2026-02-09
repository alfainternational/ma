<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) redirect(url('login.php'));

$sessionModel = new Session();
$page = max(1, Sanitizer::int($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$statusFilter = Sanitizer::clean($_GET['status'] ?? '');

$sessions = $sessionModel->getAll($perPage, $offset, $statusFilter);
$stats = $sessionModel->getStats();
$totalPages = ceil($stats['total'] / $perPage);

$pageTitle = 'إدارة الجلسات - ' . APP_NAME_AR;
$pageCSS = ['admin.css'];
$pageJS = ['admin.js'];
include BASE_PATH . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="admin-content">
        <button class="btn btn-sm btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>

        <div class="admin-header">
            <h1>إدارة التقييمات</h1>
        </div>

        <div class="admin-stats">
            <div class="admin-stat-card"><div class="stat-icon-wrapper blue"><i class="fas fa-clipboard-list"></i></div><div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label">الإجمالي</div></div></div>
            <div class="admin-stat-card"><div class="stat-icon-wrapper green"><i class="fas fa-check"></i></div><div><div class="stat-value"><?= $stats['completed'] ?></div><div class="stat-label">مكتمل</div></div></div>
            <div class="admin-stat-card"><div class="stat-icon-wrapper orange"><i class="fas fa-spinner"></i></div><div><div class="stat-value"><?= $stats['in_progress'] ?></div><div class="stat-label">جاري</div></div></div>
            <div class="admin-stat-card"><div class="stat-icon-wrapper red"><i class="fas fa-times"></i></div><div><div class="stat-value"><?= $stats['abandoned'] ?></div><div class="stat-label">متوقف</div></div></div>
        </div>

        <div class="admin-table-wrapper">
            <div class="admin-table-header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control admin-search" data-table="sessionsTable" placeholder="بحث...">
                </div>
                <select class="form-select form-select-sm w-auto" onchange="location.href='?status='+this.value">
                    <option value="">كل الحالات</option>
                    <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>جاري</option>
                    <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>مكتمل</option>
                    <option value="abandoned" <?= $statusFilter === 'abandoned' ? 'selected' : '' ?>>متوقف</option>
                </select>
            </div>

            <div class="table-responsive">
                <table class="data-table" id="sessionsTable">
                    <thead>
                        <tr><th>المستخدم</th><th>الشركة</th><th>التقدم</th><th>الحالة</th><th>البداية</th><th>الإكمال</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sessions as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['user_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($s['company_name']) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:60px;height:5px;background:var(--gray-200);border-radius:3px">
                                    <div style="width:<?= $s['progress_percent'] ?>%;height:100%;background:var(--primary-500);border-radius:3px"></div>
                                </div>
                                <small><?= $s['progress_percent'] ?>%</small>
                            </div>
                        </td>
                        <td><span class="badge-status <?= $s['status'] ?>"><?= $s['status'] === 'completed' ? 'مكتمل' : ($s['status'] === 'in_progress' ? 'جاري' : 'متوقف') ?></span></td>
                        <td><small><?= formatDate($s['started_at']) ?></small></td>
                        <td><small><?= $s['completed_at'] ? formatDate($s['completed_at']) : '-' ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="admin-pagination">
                <span>صفحة <?= $page ?> من <?= $totalPages ?></span>
                <div>
                    <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>" class="btn btn-sm btn-outline-primary">السابق</a><?php endif; ?>
                    <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>" class="btn btn-sm btn-outline-primary ms-1">التالي</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
