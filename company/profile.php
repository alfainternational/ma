<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (!Auth::isLoggedIn()) redirect(url('login.php'));

$user = Auth::getCurrentUser();
$companyModel = new Company();
$companies = $companyModel->getByUserId($user['id']);
$company = $companies[0] ?? null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'رمز الحماية غير صالح';
    } else {
        $data = Sanitizer::cleanArray($_POST);
        $action = $data['action'] ?? 'update';

        if ($action === 'create') {
            $validator = new Validator();
            $validator->validate($data, [
                'name' => 'required|min:2|max:100',
                'sector' => 'required|in:' . implode(',', array_keys(SECTORS)),
            ]);
            if ($validator->getErrors()) {
                $error = $validator->getFirstError();
            } else {
                $companyModel->create([
                    'user_id' => $user['id'],
                    'name' => $data['name'],
                    'sector' => $data['sector'],
                    'years_in_business' => $data['years_in_business'] ?? null,
                    'employee_count' => $data['employee_count'] ?? null,
                    'annual_revenue' => $data['annual_revenue'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);
                flashMessage('success', 'تم إضافة الشركة بنجاح');
                redirect(url('company/profile.php'));
            }
        } elseif ($action === 'update' && $company) {
            $companyModel->update($company['id'], [
                'name' => $data['name'] ?? $company['name'],
                'sector' => $data['sector'] ?? $company['sector'],
                'years_in_business' => $data['years_in_business'] ?? null,
                'employee_count' => $data['employee_count'] ?? null,
                'annual_revenue' => $data['annual_revenue'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
            flashMessage('success', 'تم تحديث بيانات الشركة');
            redirect(url('company/profile.php'));
        }
    }
}

$pageTitle = 'ملف الشركة - ' . APP_NAME_AR;
$flash = getFlash();
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="page-header">
                    <h1><i class="fas fa-building text-primary-custom me-2"></i>ملف الشركة</h1>
                    <p>معلومات شركتك الأساسية</p>
                </div>

                <?php if ($flash): ?>
                <div class="flash-message <?= $flash['type'] ?>">
                    <i class="fas fa-check-circle"></i><?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="flash-message error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card p-4">
                    <form method="post" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="<?= $company ? 'update' : 'create' ?>">

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label">اسم الشركة *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= htmlspecialchars($company['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">القطاع *</label>
                                <select name="sector" class="form-select" required>
                                    <option value="">اختر القطاع</option>
                                    <?php foreach (SECTORS as $key => $sector): ?>
                                    <option value="<?= $key ?>" <?= ($company['sector'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= $sector['ar'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="form-label">سنوات العمل</label>
                                <input type="number" name="years_in_business" class="form-control" min="0" max="100"
                                       value="<?= $company['years_in_business'] ?? '' ?>">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">عدد الموظفين</label>
                                <input type="number" name="employee_count" class="form-control" min="0"
                                       value="<?= $company['employee_count'] ?? '' ?>">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">الإيرادات السنوية (ريال)</label>
                                <input type="number" name="annual_revenue" class="form-control" min="0" dir="ltr"
                                       value="<?= $company['annual_revenue'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">وصف الشركة</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?= $company ? 'حفظ التغييرات' : 'إضافة الشركة' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
