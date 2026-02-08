document.addEventListener('DOMContentLoaded', async () => {
    const sessionId = localStorage.getItem('current_session_id');
    const token = localStorage.getItem('token');

    if (!sessionId) {
        window.location.href = 'dashboard';
        return;
    }

    try {
        const response = await fetch(`api/report/get?sessionId=${sessionId}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const result = await response.json();

        if (result.status === 'success') {
            renderResults(result.data);
        } else {
            alert('فشل تحميل التقرير: ' + result.message);
        }
    } catch (error) {
        console.error('Error fetching report:', error);
    }
});

function renderResults(data) {
    const strategySection = document.getElementById('dynamic-strategy-section');

    // تحديث النتائج (Scores)
    if (data.scores) {
        // Maturity
        const maturity = data.scores.maturity || 0;
        document.getElementById('score-maturity-text').textContent = maturity + '%';
        document.getElementById('score-maturity-bar').style.width = maturity + '%';

        // Digital
        const digital = data.scores.digital || 0;
        document.getElementById('score-digital-text').textContent = digital + '%';
        document.getElementById('score-digital-bar').style.width = digital + '%';

        // Operations
        const operations = data.scores.operations || 0;
        document.getElementById('score-operations-text').textContent = operations + '%';
        document.getElementById('score-operations-bar').style.width = operations + '%';

        // Risk (Assuming 0-10 or 0-100, let's assume 0-10 based on view)
        const risk = data.scores.risk || 0;
        document.getElementById('score-risk-text').textContent = risk + '/10';
        document.getElementById('score-risk-bar').style.width = (risk * 10) + '%';
    }

    // البحث عن قسم خطة 2026
    const strategicPlan = data.sections.find(s => s.type === 'strategic_plan_2026');
    if (strategicPlan) {
        const plan = strategicPlan.content;
        strategySection.innerHTML = `
            <div class="glass-card p-4 mb-5 border-start border-5 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">🚀 خطة الاستجابة السريعة 2026</h4>
                    <span class="badge bg-primary">استراتيجي</span>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-1 small">الهدف الاستراتيجي:</p>
                        <p class="fw-bold">${plan.goal_2026}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1 small">عنق الزجاجة:</p>
                        <p class="text-danger fw-bold">${plan.main_challenge}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1 small">القنوات المختارة:</p>
                        <div class="d-flex flex-wrap gap-1">
                            ${(Array.isArray(plan.channels) ? plan.channels : []).map(c => `<span class="badge bg-outline-primary border border-primary text-primary">${c}</span>`).join('')}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-success">مصفوفة العرض (Offer)</h6>
                        <p class="small mb-1"><span class="text-muted">النتيجة:</span> ${plan.offer.outcome}</p>
                        <p class="small mb-0"><span class="text-muted">الآلية:</span> ${plan.offer.mechanism}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-info">مسار الجذب (Attract)</h6>
                        <p class="small mb-0">${plan.funnel.attract}</p>
                    </div>
                </div>
                <h6 class="fw-bold mb-3">خطة عمل الـ 30 يوماً القادمة:</h6>
                <div class="row g-3">
                    ${Object.entries(plan.action_plan_30_days).map(([week, task]) => `
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center h-100 shadow-sm">
                                <span class="badge bg-secondary mb-2">${week.replace('week_', 'الأسبوع ')}</span>
                                <p class="small mb-0 fw-bold">${task}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // رصيد الخبراء
    const expertsGrid = document.getElementById('expert-insights-container');
    if (!expertsGrid) return;
    expertsGrid.innerHTML = '';

    const expertIcons = {
        'chief_strategist': 'fa-chess-king',
        'financial_analyst': 'fa-chart-line',
        'market_analyst': 'fa-globe',
        'digital_marketing_expert': 'fa-hashtag',
        'brand_strategist': 'fa-bullhorn',
        'consumer_psychologist': 'fa-users',
        'data_scientist': 'fa-microchip',
        'operations_expert': 'fa-gears',
        'risk_manager': 'fa-shield-halved',
        'innovation_scout': 'fa-rocket'
    };

    data.sections.filter(s => s.type === 'expert_insights').forEach(section => {
        section.content.forEach((insight, idx) => {
            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-4';
            const icon = expertIcons[insight.expert_id] || 'fa-user-tie';

            card.innerHTML = `
                <div class="glass-card h-100 p-4 animate-fade-in" style="animation-delay: ${idx * 0.1}s">
                    <div class="d-flex align-items-start mb-3">
                        <div class="expert-icon-small me-3">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">${insight.expert || 'خبير استراتيجي'}</h6>
                            <span class="text-uppercase tiny-text fw-bold text-${insight.severity || 'primary'}">${insight.title}</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-2" style="line-height:1.6;">${insight.content}</p>
                </div>
            `;
            expertsGrid.appendChild(card);
        });
    });
}
