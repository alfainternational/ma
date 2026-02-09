<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) redirect(url('login.php'));

$db = Database::getInstance();
$settings = $db->fetchAll("SELECT * FROM settings ORDER BY setting_key");

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    foreach ($_POST['settings'] ?? [] as $key => $value) {
        $cleanKey = Sanitizer::clean($key);
        $cleanValue = Sanitizer::clean($value);
        $db->query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = :v2",
            ['k' => $cleanKey, 'v' => $cleanValue, 'v2' => $cleanValue]
        );
    }
    flashMessage('success', 'تم حفظ الإعدادات');
    redirect(url('admin/settings.php'));
}

$flash = getFlash();
$pageTitle = 'الإعدادات - ' . APP_NAME_AR;
$pageCSS = ['admin.css'];
include BASE_PATH . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="admin-content">
        <button class="btn btn-sm btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="admin-header"><h1>الإعدادات</h1></div>

        <?php if ($flash): ?>
        <div class="flash-message <?= $flash['type'] ?>"><i class="fas fa-check-circle"></i><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="post">
                <?= csrfField() ?>

                <h5 class="mb-3">إعدادات النظام</h5>

                <?php foreach ($settings as $s): ?>
                <div class="form-group">
                    <label class="form-label"><?= htmlspecialchars($s['setting_key']) ?></label>
                    <?php if (strlen($s['setting_value'] ?? '') > 100): ?>
                    <textarea name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" class="form-control" rows="3"><?= htmlspecialchars($s['setting_value']) ?></textarea>
                    <?php else: ?>
                    <input type="text" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]"
                           class="form-control" value="<?= htmlspecialchars($s['setting_value'] ?? '') ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-save me-2"></i>حفظ الإعدادات
                </button>
            </form>
        </div>

        <div class="card p-4 mt-4">
            <h5 class="mb-3">معلومات النظام</h5>
            <table class="data-table">
                <tbody>
                    <tr><td>إصدار النظام</td><td><?= APP_VERSION ?></td></tr>
                    <tr><td>PHP</td><td><?= phpversion() ?></td></tr>
                    <tr><td>قاعدة البيانات</td><td><?= DB_NAME ?></td></tr>
                    <tr><td>المنطقة الزمنية</td><td><?= date_default_timezone_get() ?></td></tr>
                    <tr><td>البيئة</td><td><?= APP_ENV ?></td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
