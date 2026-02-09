<?php if (!defined('BASE_PATH')) exit; ?>
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= url() ?>">
            <i class="fas fa-chart-line me-2"></i>
            <?= APP_NAME_AR ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (Auth::isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('dashboard.php') ?>">
                        <i class="fas fa-home me-1"></i> لوحة التحكم
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('assessment/start.php') ?>">
                        <i class="fas fa-clipboard-check me-1"></i> تقييم جديد
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('company/profile.php') ?>">
                        <i class="fas fa-building me-1"></i> شركتي
                    </a>
                </li>
                <?php if (Auth::isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="<?= url('admin/') ?>">
                        <i class="fas fa-shield-alt me-1"></i> لوحة الإدارة
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (Auth::isLoggedIn()): ?>
                    <?php $user = Auth::getCurrentUser(); ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($user['full_name'] ?? 'المستخدم') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('dashboard.php') ?>"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</a></li>
                        <li><a class="dropdown-item" href="<?= url('company/profile.php') ?>"><i class="fas fa-building me-2"></i>ملف الشركة</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="<?= url('login.php') ?>">
                                <input type="hidden" name="action" value="logout">
                                <?= csrfField() ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</button>
                            </form>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('login.php') ?>">تسجيل الدخول</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ms-2" href="<?= url('register.php') ?>">إنشاء حساب</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
