<?php if (!defined('BASE_PATH')) exit; ?>
    <!-- Footer -->
    <footer class="site-footer mt-auto py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">&copy; <?= date('Y') ?> <?= APP_NAME_AR ?>. جميع الحقوق محفوظة.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?= url('privacy') ?>" class="text-muted ms-3">سياسة الخصوصية</a>
                    <a href="<?= url('terms') ?>" class="text-muted">الشروط والأحكام</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- Main JS -->
    <script src="<?= asset('js/main.js') ?>"></script>
    <?php if (!empty($pageJS)): ?>
        <?php foreach ($pageJS as $js): ?>
        <script src="<?= asset('js/' . $js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
