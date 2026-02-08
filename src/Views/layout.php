<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_URL ?>/">
    <title><?php echo $title ?? 'نظام التسويق بالذكاء الاصطناعي'; ?></title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Google Fonts: Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom Premium Styles -->
    <link rel="stylesheet" href="assets/css/main.css">
    <script>
        const API_BASE = "<?php echo rtrim(BASE_URL, '/') . '/'; ?>";
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Marketing AI</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard">لوحة التحكم</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="assessment/start">بدء تقييم</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container">
    <?php echo $content; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
