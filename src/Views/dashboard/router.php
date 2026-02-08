<?php $title = 'جاري التحميل... | Marketing AI'; ?>

<div id="dashboard-loader" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-3 text-muted">جاري التحقق من الصلاحيات...</p>
</div>

<div id="dashboard-content" style="display:none;"></div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');
    
    if (!token) {
        window.location.href = 'login';
        return;
    }

    try {
        const authResp = await fetch(API_BASE + 'api/auth/me', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (!authResp.ok) throw new Error('Auth failed');
        
        const authData = await authResp.json();
        const user = authData.user;

        let dashboardUrl = '';
        if (user.role === 'admin') {
            dashboardUrl = API_BASE + 'api/admin/dashboard-view';
        } else {
            dashboardUrl = API_BASE + 'api/client/dashboard-view';
        }

        const viewResp = await fetch(dashboardUrl, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (!viewResp.ok) {
            const errorText = await viewResp.text();
            throw new Error(`View load failed (${viewResp.status}): ${errorText.substring(0, 100)}`);
        }

        const html = await viewResp.text();
        
        document.getElementById('dashboard-loader').remove();
        const container = document.getElementById('dashboard-content');
        container.innerHTML = html;
        container.style.display = 'block';
        
        // Update Page Title
        document.title = (user.role === 'admin' ? 'لوحة الإدارة' : 'لوحة التحكم') + ' | Marketing AI';

    } catch (e) {
        console.error('Dashboard Router Error:', e);
        localStorage.removeItem('token'); 
        window.location.href = API_BASE + 'login';
    }
});
</script>
