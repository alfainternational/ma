<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) redirect(url('login.php'));

$userModel = new User();
$page = max(1, Sanitizer::int($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$roleFilter = Sanitizer::clean($_GET['role'] ?? '');

$users = $userModel->getAll($perPage, $offset, $roleFilter);
$totalUsers = $userModel->getTotalCount($roleFilter);
$totalPages = ceil($totalUsers / $perPage);

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    $action = $_POST['action'] ?? '';
    $targetId = Sanitizer::int($_POST['user_id'] ?? 0);

    if ($action === 'update_role' && $targetId) {
        $newRole = Sanitizer::clean($_POST['role'] ?? 'user');
        $userModel->update($targetId, ['role' => $newRole]);
        flashMessage('success', 'تم تحديث الدور');
        redirect(url('admin/users.php'));
    }

    if ($action === 'toggle_status' && $targetId) {
        $currentUser = $userModel->getById($targetId);
        $newStatus = ($currentUser['status'] ?? 'active') === 'active' ? 'inactive' : 'active';
        $userModel->update($targetId, ['status' => $newStatus]);
        flashMessage('success', 'تم تحديث الحالة');
        redirect(url('admin/users.php'));
    }
}

$flash = getFlash();
$pageTitle = 'إدارة المستخدمين - ' . APP_NAME_AR;
$pageCSS = ['admin.css'];
$pageJS = ['admin.js'];
include BASE_PATH . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="admin-content">
        <button class="btn btn-sm btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>

        <div class="admin-header">
            <h1>إدارة المستخدمين</h1>
            <span class="text-muted"><?= $totalUsers ?> مستخدم</span>
        </div>

        <?php if ($flash): ?>
        <div class="flash-message <?= $flash['type'] ?>"><i class="fas fa-check-circle"></i><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <div class="admin-table-wrapper">
            <div class="admin-table-header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control admin-search" data-table="usersTable" placeholder="بحث...">
                </div>
                <div>
                    <select class="form-select form-select-sm d-inline-block w-auto" onchange="location.href='?role='+this.value">
                        <option value="">كل الأدوار</option>
                        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>مدير</option>
                        <option value="analyst" <?= $roleFilter === 'analyst' ? 'selected' : '' ?>>محلل</option>
                        <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>مستخدم</option>
                    </select>
                    <button onclick="AdminPanel.exportCSV('usersTable','users')" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-download me-1"></i>تصدير
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table" id="usersTable">
                    <thead>
                        <tr><th>المستخدم</th><th>الهاتف</th><th>الدور</th><th>الحالة</th><th>التسجيل</th><th>إجراءات</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" style="width:100px">
                                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>مستخدم</option>
                                    <option value="analyst" <?= $u['role'] === 'analyst' ? 'selected' : '' ?>>محلل</option>
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>مدير</option>
                                </select>
                            </form>
                        </td>
                        <td><span class="badge-status <?= $u['status'] ?? 'active' ?>"><?= ($u['status'] ?? 'active') === 'active' ? 'نشط' : 'موقف' ?></span></td>
                        <td><small><?= formatDate($u['created_at']) ?></small></td>
                        <td>
                            <form method="post" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="action-btn edit" title="تفعيل/إيقاف"><i class="fas fa-power-off"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="admin-pagination">
                <span>صفحة <?= $page ?> من <?= $totalPages ?></span>
                <div>
                    <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>&role=<?= $roleFilter ?>" class="btn btn-sm btn-outline-primary">السابق</a><?php endif; ?>
                    <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>&role=<?= $roleFilter ?>" class="btn btn-sm btn-outline-primary ms-1">التالي</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
