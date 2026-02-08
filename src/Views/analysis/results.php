<?php $title = 'نتائج التحليل | Marketing AI'; ?>

<div class="analysis-results py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">نتائج التحليل الاستراتيجي</h2>
        <button class="btn btn-outline-primary" onclick="window.print()">تحميل PDF</button>
    </div>

    <!-- نظرة عامة على المقاييس (Gauges) -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">نضج الاستراتيجية</h6>
                <h2 id="score-maturity-text" class="fw-bold text-primary">-</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div id="score-maturity-bar" class="progress-bar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">النضج الرقمي</h6>
                <h2 id="score-digital-text" class="fw-bold text-success">-</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div id="score-digital-bar" class="progress-bar bg-success" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">كفاءة العمليات</h6>
                <h2 id="score-operations-text" class="fw-bold text-warning">-</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div id="score-operations-bar" class="progress-bar bg-warning" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">مؤشر المخاطر</h6>
                <h2 id="score-risk-text" class="fw-bold text-danger">-</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div id="score-risk-bar" class="progress-bar bg-danger" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- خطط العمل المخصصة -->
    <div id="dynamic-strategy-section"></div>

    <!-- رؤى الخبراء الـ 10 -->
    <h4 class="fw-bold mb-4">تقييم الخبراء الافتراضيين</h4>
    <div id="expert-insights-container" class="row g-4 mb-5">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">يقوم الخبراء الـ 10 بمراجعة بياناتك حالياً...</p>
        </div>
    </div>
</div>

<script src="assets/js/results.js"></script>
