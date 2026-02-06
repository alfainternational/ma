<?php $title = 'تسجيل الدخول | Marketing AI'; ?>

<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5">
        <div class="card p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Marketing AI</h2>
                <p class="text-muted">الاستشاري الذكي لنمو منشأتكم</p>
            </div>
            
            <form id="login-form">
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" class="form-control" id="email" placeholder="name@company.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">دخول للنظام</button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="small text-muted">ليس لديك حساب؟ <a href="register">أنشئ حساباً جديداً لنشاطك التجاري</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch(API_BASE + 'api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            localStorage.setItem('token', data.token);
            window.location.href = 'dashboard';
        } else {
            alert(data.message);
        }
    } catch (err) {
        console.error("Login failed", err);
    }
});
</script>
