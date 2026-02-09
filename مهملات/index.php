<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

$pageTitle = APP_NAME_AR . ' - حوّل تسويقك بالذكاء الاصطناعي';
$bodyClass = 'landing-page';
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="fade-in">حوّل تسويقك بالذكاء الاصطناعي</h1>
        <p class="lead mb-4 fade-in">نظام تقييم تسويقي شامل مدعوم بـ 10 خبراء افتراضيين و4 محركات ذكاء اصطناعي</p>
        <p class="mb-4 fade-in" style="opacity:0.85">احصل على تحليل شامل لتسويقك وتوصيات مخصصة لقطاعك خلال دقائق</p>
        <?php if (Auth::isLoggedIn()): ?>
            <a href="<?= url('assessment/start.php') ?>" class="btn btn-lg btn-light text-primary fw-bold px-5">
                <i class="fas fa-rocket me-2"></i>ابدأ تقييم جديد
            </a>
        <?php else: ?>
            <a href="<?= url('register.php') ?>" class="btn btn-lg btn-light text-primary fw-bold px-5 me-3">
                <i class="fas fa-user-plus me-2"></i>ابدأ مجاناً
            </a>
            <a href="<?= url('login.php') ?>" class="btn btn-lg btn-outline-light px-4">تسجيل الدخول</a>
        <?php endif; ?>
    </div>
</section>

<!-- Features -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title">لماذا <?= APP_NAME_AR ?>؟</h2>
        <p class="section-subtitle">أدوات ذكية لتحليل وتطوير تسويقك</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-brain"></i></div>
                    <h4>التحليل الشامل</h4>
                    <p>تقييم متعدد الأبعاد يغطي كل جوانب التسويق والعمليات والمالية لشركتك</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:linear-gradient(135deg,#2e7d32,#1b5e20)"><i class="fas fa-users-cog"></i></div>
                    <h4>فريق خبراء افتراضي</h4>
                    <p>10 خبراء متخصصين يحللون بياناتك من زوايا مختلفة لتقديم رؤية شاملة</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:linear-gradient(135deg,#ed6c02,#e65100)"><i class="fas fa-bullseye"></i></div>
                    <h4>توصيات مخصصة</h4>
                    <p>خطة عمل تفصيلية مصممة لقطاعك وميزانيتك وأهدافك المحددة</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5" style="background:var(--gray-50)">
    <div class="container">
        <h2 class="section-title">كيف يعمل النظام؟</h2>
        <p class="section-subtitle">4 خطوات بسيطة للحصول على خطتك التسويقية</p>
        <div class="row g-4">
            <?php
            $steps = [
                ['icon' => 'fa-user-plus', 'title' => 'سجّل حسابك', 'desc' => 'أنشئ حسابك المجاني وأضف بيانات شركتك الأساسية'],
                ['icon' => 'fa-clipboard-list', 'title' => 'أجب على الأسئلة', 'desc' => 'أجب على أسئلة ذكية مصممة لفهم وضعك التسويقي'],
                ['icon' => 'fa-chart-pie', 'title' => 'احصل على التحليل', 'desc' => 'تحليل شامل من 10 خبراء افتراضيين و4 محركات ذكاء اصطناعي'],
                ['icon' => 'fa-tasks', 'title' => 'نفذ الخطة', 'desc' => 'خطة عمل تفصيلية مع أولويات واضحة وجداول زمنية'],
            ];
            foreach ($steps as $i => $step): ?>
            <div class="col-md-3">
                <div class="step-card">
                    <div class="step-number"><?= $i + 1 ?></div>
                    <h5><?= $step['title'] ?></h5>
                    <p class="text-muted"><?= $step['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Sectors -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title">القطاعات المدعومة</h2>
        <p class="section-subtitle">تحليل متخصص لـ 8 قطاعات رئيسية في السوق العربي</p>
        <div class="row g-3">
            <?php
            $sectorIcons = [
                'education' => 'fa-graduation-cap', 'healthcare' => 'fa-heartbeat',
                'fnb' => 'fa-utensils', 'retail' => 'fa-shopping-bag',
                'professional_services' => 'fa-briefcase', 'real_estate' => 'fa-home',
                'fitness' => 'fa-dumbbell', 'crafts' => 'fa-palette',
            ];
            foreach (SECTORS as $key => $sector): ?>
            <div class="col-md-3 col-6">
                <div class="sector-card">
                    <div class="sector-icon"><i class="fas <?= $sectorIcons[$key] ?? 'fa-industry' ?>"></i></div>
                    <h6><?= $sector['ar'] ?></h6>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 bg-gradient-primary text-white text-center">
    <div class="container">
        <h2 class="text-white mb-3">جاهز لتطوير تسويقك؟</h2>
        <p class="mb-4 opacity-75">ابدأ تقييمك المجاني الآن واحصل على خطة عمل مخصصة</p>
        <a href="<?= url(Auth::isLoggedIn() ? 'assessment/start.php' : 'register.php') ?>"
           class="btn btn-lg btn-light text-primary fw-bold px-5">ابدأ الآن مجاناً</a>
    </div>
</section>

<?php include BASE_PATH . '/includes/footer.php'; ?>
