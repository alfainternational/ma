<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) redirect(url('login.php'));

$companyModel = new Company();
$page = max(1, Sanitizer::int($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$companies = $companyModel->getAll($perPage, $offset);
$totalCompanies = $companyModel->getTotalCount();
$totalPages = ceil($totalCompanies / $perPage);
$sectorDist = $companyModel->getSectorDistribution();

$pageTitle = 'إدارة الشركات - ' . APP_NAME_AR;
$pageCSS = ['admin.css'];
$pageJS = ['admin.js'];
include BASE_PATH . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="admin-content">
        <button class="btn btn-sm btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="admin-header"><h1>إدارة الشركات</h1><span class="text-muted"><?= $totalCompanies ?> شركة</span></div>

        <div class="admin-table-wrapper">
            <div class="admin-table-header">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" class="form-control admin-search" data-table="companiesTable" placeholder="بحث..."></div>
                <button onclick="AdminPanel.exportCSV('companiesTable','companies')" class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i>تصدير</button>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="companiesTable">
                    <thead><tr><th>الشركة</th><th>المالك</th><th>القطاع</th><th>الموظفين</th><th>الإيرادات</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    <?php foreach ($companies as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                        <td><?= htmlspecialchars($c['owner_name'] ?? '-') ?></td>
                        <td><?= getSectorLabel($c['sector']) ?></td>
                        <td><?= $c['employee_count'] ?? '-' ?></td>
                        <td><?= $c['annual_revenue'] ? formatCurrency($c['annual_revenue']) : '-' ?></td>
                        <td><small><?= formatDate($c['created_at']) ?></small></td>
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
