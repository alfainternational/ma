<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

// Redirect if already logged in
if (Auth::isLoggedIn()) redirect(url('dashboard.php'));

$error = '';

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '');
    Auth::logout();
    flashMessage('success', 'تم تسجيل الخروج بنجاح');
    redirect(url('login.php'));
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'logout') {
    if (!Auth::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'رمز الحماية غير صالح. يرجى المحاولة مرة أخرى.';
    } else {
        $email = Sanitizer::email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $validator = new Validator();
        $validator->validate(['email' => $email, 'password' => $password], [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->getErrors()) {
            $error = $validator->getFirstError();
        } else {
            $result = Auth::login($email, $password);
            if ($result['success']) {
                flashMessage('success', 'تم تسجيل الدخول بنجاح');
                redirect(url('dashboard.php'));
            } else {
                $error = $result['error'];
            }
        }
    }
}

$pageTitle = 'تسجيل الدخول - ' . APP_NAME_AR;
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
$flash = getFlash();
?>

<div class="page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 mt-4">
                    <div class="text-center mb-4">
                        <h3><i class="fas fa-sign-in-alt text-primary-custom me-2"></i>تسجيل الدخول</h3>
                        <p class="text-muted">أدخل بياناتك للوصول لحسابك</p>
                    </div>

                    <?php if ($flash): ?>
                    <div class="flash-message <?= $flash['type'] ?>">
                        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                    <div class="flash-message error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <?= csrfField() ?>
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= old('email') ?>" placeholder="example@email.com" dir="ltr">
                        </div>
                        <div class="form-group">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required
                                   placeholder="••••••••" dir="ltr" minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                        </button>
                    </form>
                    <div class="text-center">
                        <p class="text-muted">ليس لديك حساب؟ <a href="<?= url('register.php') ?>">إنشاء حساب جديد</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
