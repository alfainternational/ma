<?php $title = 'إعداد ملف الشركة | Marketing AI'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <div class="card p-4">
            <h3 class="fw-bold mb-3">بيانات المنشأة</h3>
            <p class="text-muted mb-4">أخبرنا المزيد عن شركتك لنتمكن من تخصيص التحليل لك.</p>

            <form id="company-setup-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">اسم الشركة</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">القطاع</label>
                        <select class="form-select" id="sector" required>
                            <option value="">اختر القطاع...</option>
                            <option value="retail">التجزئة (Retail)</option>
                            <option value="services">الخدمات (Services)</option>
                            <option value="technology">التقنية (Technology)</option>
                            <option value="healthcare">الرعاية الصحية (Healthcare)</option>
                            <option value="education">التعليم (Education)</option>
                            <option value="fnb">المطاعم والكافيهات (F&B)</option>
                            <option value="real_estate">العطارات (Real Estate)</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">سنة التأسيس</label>
                        <input type="number" class="form-control" id="founded_year" min="1900" max="2026">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">عدد الموظفين</label>
                        <select class="form-select" id="employee_count">
                            <option value="1-10">1 - 10</option>
                            <option value="11-50">11 - 50</option>
                            <option value="51-200">51 - 200</option>
                            <option value="200+">أكثر من 200</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">الموقع الإلكتروني (اختياري)</label>
                    <input type="url" class="form-control" id="website" placeholder="https://">
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-5 fw-bold">حفظ ومتابعة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('company-setup-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'login';
        return;
    }

    const data = {
        name: document.getElementById('name').value,
        sector: document.getElementById('sector').value,
        founded_year: document.getElementById('founded_year').value,
        employee_count: document.getElementById('employee_count').value,
        website: document.getElementById('website').value
    };

    try {
        const response = await fetch(API_BASE + 'api/company/create', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.status === 'success') {
            window.location.href = 'dashboard';
        } else {
            alert('خطأ: ' + (result.message || 'حدث خطأ غير معروف'));
        }
    } catch (err) {
        console.error("Company setup failed", err);
        alert("حدث خطأ في معالجة طلبكم. يرجى مراجعة سجلات النظام.");
    }
});
</script>
