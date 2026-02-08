<?php 
$title = 'جلسة التقييم | Marketing AI'; 
// البيانات القادمة من الكونترولر: $sessionId, $initialQuestion
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'التقييم المستمر' ?></title>
    
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Google Fonts: Tajawal & Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Premium Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/assessment_focus.css?v=<?= time() ?>">
    
    <script>
        const API_BASE = "<?php echo rtrim(BASE_URL, '/') . '/'; ?>";
    </script>
</head>
<body class="bg-premium">

    <div class="assessment-wrapper">
        <!-- شريط التقدم العلوي النحيف -->
        <div class="progress-container">
            <div id="progress-fill" class="progress-bar-premium"></div>
        </div>

        <div class="container-fluid h-100">
            <div class="row h-100 g-0">
                <!-- الجزء الأيمن: منطقة السؤال (Focus Area) -->
                <div class="col-lg-7 d-flex align-items-center justify-content-center p-4 p-md-5">
                    <div id="focus-container" class="w-100" style="max-width: 700px;">
                        <div class="text-center text-muted">
                            <div class="spinner-premium mb-3"></div>
                            <p class="animate-pulse">جارٍ تحضير جلستك الاستشارية...</p>
                        </div>
                    </div>
                </div>

                <!-- الجزء الأيسر: لوحة الخبير (Advisor Panel) -->
                <div class="col-lg-5 advisor-panel p-4 p-md-5 d-none d-lg-block">
                    <div id="advisor-content" class="advisor-inner glass-morphism">
                        <div class="advisor-header mb-4">
                            <div class="advisor-icon">
                                <i class="fa-solid fa-lightbulb"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">المستشار الذكي</h5>
                                <small class="text-white-50">توجيهات استراتيجية فورية</small>
                            </div>
                        </div>

                        <div id="advisor-body" class="advisor-body scrollbar-hidden">
                            <div class="empty-state text-center py-5 opacity-50">
                                <i class="fa-solid fa-headset fa-3x mb-3"></i>
                                <p>ابدأ الإجابة لتظهر لك التحليلات العميقة والفوائد الاستشارية هنا.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- فذائف الموبايل للمعلومات (Bottom Sheet for Mobile) -->
    <div class="mobile-advisor-trigger d-lg-none" onclick="engine.toggleMobileAdvisor()">
         <i class="fa-solid fa-circle-info"></i>
    </div>

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/assessment_focus.js?v=<?= time() ?>"></script>
    <script>
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '<?= BASE_URL ?>/login?redirect=assessment/start';
        }

        window.ServerData = {
            sessionId: "<?= $sessionId ?? '' ?>",
            initialQuestion: <?= isset($initialQuestion) ? json_encode($initialQuestion) : 'null' ?>
        };

        // Persist session ID for results and dashboard
        if (window.ServerData.sessionId) {
            localStorage.setItem('current_session_id', window.ServerData.sessionId);
        }

        document.addEventListener('DOMContentLoaded', () => {
             window.engine = new FocusEngine({
                sessionId: window.ServerData.sessionId,
                initialQuestion: window.ServerData.initialQuestion,
                apiBase: API_BASE
             });
        });
    </script>
</body>
</html>
