<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) redirect(url('login.php'));

$db = Database::getInstance();
$reports = $db->fetchAll(
    "SELECT r.*, u.full_name as user_name, s.company_id
     FROM reports r
     JOIN users u ON r.user_id = u.id
     JOIN assessment_sessions s ON r.session_id = s.id
     ORDER BY r.created_at DESC LIMIT 50"
);

$pageTitle = 'التقارير - ' . APP_NAME_AR;
$pageCSS = ['admin.css'];
include BASE_PATH . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="admin-content">
        <button class="btn btn-sm btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="admin-header"><h1>التقارير</h1></div>

        <div class="admin-table-wrapper">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>#</th><th>المستخدم</th><th>النوع</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    <?php if (empty($reports)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">لا توجد تقارير بعد</td></tr>
                    <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['user_name']) ?></td>
                        <td><?= REPORT_TYPES[$r['report_type']]['ar'] ?? $r['report_type'] ?></td>
                        <td><span class="badge-status <?= $r['status'] ?>"><?= $r['status'] === 'completed' ? 'مكتمل' : 'معلق' ?></span></td>
                        <td><small><?= formatDate($r['created_at']) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
