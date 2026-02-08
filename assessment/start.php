<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn()) redirect(url('login.php'));

$user = Auth::getCurrentUser();
$companyModel = new Company();
$companies = $companyModel->getByUserId($user['id']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'رمز الحماية غير صالح';
    } else {
        $companyId = Sanitizer::int($_POST['company_id'] ?? 0);

        if (!$companyId || !$companyModel->belongsToUser($companyId, $user['id'])) {
            $error = 'يرجى اختيار شركة صالحة';
        } else {
            $sessionModel = new Session();
            $result = $sessionModel->create($user['id'], $companyId);
            redirect(url('assessment/questionnaire.php?session_id=' . $result['id']));
        }
    }
}

$pageTitle = 'بدء تقييم جديد - ' . APP_NAME_AR;
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="page-header text-center">
                    <h1><i class="fas fa-clipboard-check text-primary-custom me-2"></i>بدء تقييم جديد</h1>
                    <p>اختر الشركة وابدأ تقييمك التسويقي الشامل</p>
                </div>

                <?php if ($error): ?>
                <div class="flash-message error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card p-4">
                    <!-- What to Expect -->
                    <div class="mb-4 p-3 rounded-lg" style="background:var(--gray-50)">
                        <h5 class="mb-3"><i class="fas fa-info-circle text-info me-2"></i>ماذا تتوقع؟</h5>
                        <ul class="mb-0" style="padding-right:1.25rem">
                            <li class="mb-2">أسئلة ذكية تتكيف مع إجاباتك</li>
                            <li class="mb-2">الوقت المتوقع: <strong>15-20 دقيقة</strong></li>
                            <li class="mb-2">يمكنك الحفظ والمتابعة لاحقاً</li>
                            <li class="mb-2">تحليل فوري من 10 خبراء افتراضيين</li>
                            <li>تقرير شامل مع خطة عمل مخصصة</li>
                        </ul>
                    </div>

                    <form method="post" action="">
                        <?= csrfField() ?>
                        <div class="form-group">
                            <label class="form-label">اختر الشركة</label>
                            <?php if (empty($companies)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted">لم تضف أي شركة بعد</p>
                                <a href="<?= url('company/profile.php') ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>إضافة شركة
                                </a>
                            </div>
                            <?php else: ?>
                            <select name="company_id" class="form-select" required>
                                <option value="">اختر شركتك</option>
                                <?php foreach ($companies as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> - <?= $c['sector_name_ar'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($companies)): ?>
                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                            <i class="fas fa-play me-2"></i>ابدأ التقييم
                        </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
