<?php $title = 'إنشاء حساب جديد | Marketing AI'; ?>

<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5">
        <div class="card p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Marketing AI</h2>
                <p class="text-muted">انضم إلينا لتبدأ رحلة نمو مؤسستك</p>
            </div>
            
            <form id="register-form">
                <div class="mb-3">
                    <label class="form-label">الاسم الكامل</label>
                    <input type="text" class="form-control" id="fullName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" class="form-control" id="email" placeholder="name@company.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="tel" class="form-control" id="phone" placeholder="05xxxxxxxx" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">تسجيل حساب جديد</button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="small text-muted">لديك حساب بالفعل؟ <a href="login">سجل دخولك هنا</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fullName = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch(API_BASE + 'api/auth/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fullName, email, phone, password })
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            localStorage.setItem('token', data.token);
            // توجيه المستخدم لصفحة إعداد الشركة
            window.location.href = 'setup-company';
        } else {
            alert(data.message);
        }
    } catch (err) {
        console.error("Registration failed", err);
        alert("حدث خطأ في الاتصال");
    }
});
</script>
