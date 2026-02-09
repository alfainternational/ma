<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

if (Auth::isLoggedIn()) redirect(url('dashboard.php'));

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'رمز الحماية غير صالح';
    } else {
        $data = Sanitizer::cleanArray($_POST);

        $validator = new Validator();
        $validator->validate($data, [
            'full_name' => 'required|min:3|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'phone',
            'company_name' => 'required|min:2|max:100',
            'sector' => 'required|in:' . implode(',', array_keys(SECTORS)),
        ]);

        if ($validator->getErrors()) {
            $error = $validator->getFirstError();
        } else {
            $result = Auth::register(
                $data['email'],
                $_POST['password'],
                $data['full_name'],
                $data['phone'] ?? '',
                $data['company_name']
            );

            if ($result['success']) {
                // Create company
                $company = new Company();
                $company->create([
                    'user_id' => $result['user_id'],
                    'name' => $data['company_name'],
                    'sector' => $data['sector'],
                ]);

                Auth::login($data['email'], $_POST['password']);
                flashMessage('success', 'تم إنشاء حسابك بنجاح! مرحباً بك');
                redirect(url('dashboard.php'));
            } else {
                $error = $result['error'];
            }
        }
    }
    $_SESSION['_old_input'] = $_POST;
}

$pageTitle = 'إنشاء حساب - ' . APP_NAME_AR;
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4 mt-4">
                    <div class="text-center mb-4">
                        <h3><i class="fas fa-user-plus text-primary-custom me-2"></i>إنشاء حساب جديد</h3>
                        <p class="text-muted">أنشئ حسابك المجاني وابدأ تقييم تسويقك</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="flash-message error">
                        <i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label">الاسم الكامل *</label>
                                <input type="text" name="full_name" class="form-control" required
                                       value="<?= old('full_name') ?>" placeholder="الاسم الكامل">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="tel" name="phone" class="form-control" dir="ltr"
                                       value="<?= old('phone') ?>" placeholder="+966 5XX XXX XXX">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني *</label>
                            <input type="email" name="email" class="form-control" required dir="ltr"
                                   value="<?= old('email') ?>" placeholder="example@email.com">
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label">كلمة المرور *</label>
                                <input type="password" name="password" class="form-control" required dir="ltr"
                                       minlength="8" placeholder="8 أحرف على الأقل">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">تأكيد كلمة المرور *</label>
                                <input type="password" name="password_confirm" class="form-control" required dir="ltr"
                                       placeholder="أعد كتابة كلمة المرور">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">اسم الشركة *</label>
                            <input type="text" name="company_name" class="form-control" required
                                   value="<?= old('company_name') ?>" placeholder="اسم شركتك">
                        </div>
                        <div class="form-group">
                            <label class="form-label">القطاع *</label>
                            <select name="sector" class="form-select" required>
                                <option value="">اختر القطاع</option>
                                <?php foreach (SECTORS as $key => $sector): ?>
                                <option value="<?= $key ?>" <?= old('sector') === $key ? 'selected' : '' ?>>
                                    <?= $sector['ar'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                            <label class="form-check-label" for="terms">أوافق على <a href="#">الشروط والأحكام</a></label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                            <i class="fas fa-user-plus me-2"></i>إنشاء الحساب
                        </button>
                    </form>
                    <div class="text-center">
                        <p class="text-muted">لديك حساب بالفعل؟ <a href="<?= url('login.php') ?>">تسجيل الدخول</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
