<?php $title = 'لوحة التحكم | Marketing AI'; ?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2 class="fw-bold">مرحباً بك في نظام التسويق بالذكاء الاصطناعي</h2>
        <p class="text-muted">احصل على استشارات احترافية مدعومة بـ 10 خبراء افتراضيين.</p>
    </div>
</div>

<div class="row g-4">
    <!-- بطاقة بدء تقييم جديد -->
    <div class="col-md-4">
        <div class="card h-100 p-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    <img src="https://img.icons8.com/isometric-line/100/null/brain-storming.png" alt="AI Brain">
                </div>
                <h4 class="card-title fw-bold">تقييم استراتيجي شامل</h4>
                <p class="card-text">قم بالإجابة على بنك الأسئلة المتطور للحصول على خطة نمو متكاملة.</p>
                <button onclick="startNewAssessment()" class="btn btn-primary w-100 py-3 mt-3">ابدأ التقييم الآن</button>
            </div>
        </div>
    </div>

    <!-- بطاقة التقارير السابقة -->
    <div class="col-md-8">
        <div class="card h-100 p-4">
            <h4 class="fw-bold mb-4">التقارير الأخيرة</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>حالة التقييم</th>
                            <th>النتيجة العامة</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reports)): ?>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($report['started_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $report['status_class'] ?>">
                                        <?= $report['status_label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($report['status'] === 'completed'): ?>
                                        <span class="text-success fw-bold"><?= $report['score'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($report['status'] === 'completed'): ?>
                                        <a href="javascript:void(0)" onclick="viewReport('<?= $report['id'] ?>')" class="btn btn-sm btn-outline-primary">عرض التقرير</a>
                                    <?php else: ?>
                                        <a href="assessment/start?session=<?= $report['id'] ?>" class="btn btn-sm btn-outline-warning">استكمال</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <p class="text-muted mb-0">لم تقم بأي تقييم بعد.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    
                    <script>
                    async function startNewAssessment() {
                        const token = localStorage.getItem('token');
                        try {
                            const res = await fetch(API_BASE + 'api/sessions/start', {
                                method: 'POST',
                                headers: { 'Authorization': `Bearer ${token}` }
                            });
                            const data = await res.json();
                            if (data.status === 'success') {
                                window.location.href = 'assessment/start?session=' + data.session_id;
                            } else {
                                alert(data.message);
                            }
                        } catch (e) {
                            console.error(e);
                            alert("فشل في بدء التقييم");
                        }
                    }

                    function viewReport(sessionId) {
                        // Store session ID and go to results
                        localStorage.setItem('current_session_id', sessionId);
                        window.location.href = 'analysis/results';
                    }
                    </script>
                </table>
            </div>
        </div>
    </div>
</div>
