<?php if (!defined('BASE_PATH')) exit;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$parentDir = basename(dirname($_SERVER['PHP_SELF']));
function sidebarActive(string $page, string $current): string {
    return $page === $current ? 'active' : '';
}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="<?= url('admin/') ?>" class="sidebar-brand">
            <i class="fas fa-chart-line"></i>
            <span>لوحة الإدارة</span>
        </a>
        <button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= sidebarActive('index', $currentPage) ?>" href="<?= url('admin/index.php') ?>">
                    <i class="fas fa-chart-line"></i><span>لوحة المعلومات</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= sidebarActive('users', $currentPage) ?>" href="<?= url('admin/users.php') ?>">
                    <i class="fas fa-users"></i><span>المستخدمون</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= sidebarActive('sessions', $currentPage) ?>" href="<?= url('admin/sessions.php') ?>">
                    <i class="fas fa-clipboard-list"></i><span>الجلسات</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= sidebarActive('companies', $currentPage) ?>" href="<?= url('admin/companies.php') ?>">
                    <i class="fas fa-building"></i><span>الشركات</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= sidebarActive('questions', $currentPage) ?>" href="<?= url('admin/questions.php') ?>">
                    <i class="fas fa-question-circle"></i><span>الأسئلة</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= sidebarActive('reports', $currentPage) ?>" href="<?= url('admin/reports.php') ?>">
                    <i class="fas fa-file-alt"></i><span>التقارير</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= sidebarActive('settings', $currentPage) ?>" href="<?= url('admin/settings.php') ?>">
                    <i class="fas fa-cog"></i><span>الإعدادات</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= url('dashboard.php') ?>"><i class="fas fa-arrow-right me-1"></i> العودة للموقع</a>
    </div>
</aside>
