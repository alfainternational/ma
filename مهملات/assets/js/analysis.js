/**
 * Marketing AI System - Analysis & Results Charts
 */
'use strict';

const AnalysisCharts = {
    colors: {
        primary: '#1976d2',
        success: '#2e7d32',
        warning: '#ed6c02',
        error: '#d32f2f',
        info: '#0288d1',
        purple: '#7b1fa2',
    },

    init(data) {
        if (!data) return;
        if (data.scores) this.renderMaturityRadar(data.scores);
        if (data.risk) this.renderRiskDoughnut(data.risk);
        if (data.benchmarks) this.renderBenchmarkBar(data.benchmarks);
        if (data.projections) this.renderProjectionLine(data.projections);
        this.animateScores();
    },

    renderMaturityRadar(scores) {
        const ctx = document.getElementById('maturityRadar');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['النضج الرقمي', 'النضج التسويقي', 'الجاهزية التنظيمية', 'تقييم المخاطر', 'درجة الفرص'],
                datasets: [{
                    label: 'درجاتك',
                    data: [
                        scores.digital_maturity || 0,
                        scores.marketing_maturity || 0,
                        scores.organizational_readiness || 0,
                        100 - (scores.risk_score || 0),
                        scores.opportunity_score || 0
                    ],
                    backgroundColor: 'rgba(25, 118, 210, 0.15)',
                    borderColor: this.colors.primary,
                    borderWidth: 2,
                    pointBackgroundColor: this.colors.primary,
                    pointRadius: 5,
                }, {
                    label: 'معيار القطاع',
                    data: [
                        scores.benchmark_digital || 50,
                        scores.benchmark_marketing || 50,
                        scores.benchmark_org || 50,
                        scores.benchmark_risk || 50,
                        scores.benchmark_opportunity || 50
                    ],
                    backgroundColor: 'rgba(158, 158, 158, 0.1)',
                    borderColor: '#9e9e9e',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { stepSize: 25, font: { family: 'Tajawal' } },
                        pointLabels: { font: { family: 'Tajawal', size: 12 } }
                    }
                },
                plugins: {
                    legend: { labels: { font: { family: 'Tajawal' } } }
                }
            }
        });
    },

    renderRiskDoughnut(risk) {
        const ctx = document.getElementById('riskDoughnut');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['مخاطر مالية', 'مخاطر تنافسية', 'مخاطر تنفيذ', 'مخاطر سوقية'],
                datasets: [{
                    data: [
                        risk.financial || 0,
                        risk.competitive || 0,
                        risk.execution || 0,
                        risk.market || 0
                    ],
                    backgroundColor: [
                        this.colors.error,
                        this.colors.warning,
                        this.colors.info,
                        this.colors.purple
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Tajawal' }, padding: 16 }
                    }
                }
            }
        });
    },

    renderBenchmarkBar(benchmarks) {
        const ctx = document.getElementById('benchmarkChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: benchmarks.labels || ['النضج الرقمي', 'التسويق', 'الجاهزية'],
                datasets: [{
                    label: 'درجتك',
                    data: benchmarks.your_scores || [],
                    backgroundColor: this.colors.primary,
                    borderRadius: 8,
                }, {
                    label: 'متوسط القطاع',
                    data: benchmarks.sector_avg || [],
                    backgroundColor: '#bdbdbd',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { font: { family: 'Tajawal' } } },
                    x: { ticks: { font: { family: 'Tajawal' } } }
                },
                plugins: {
                    legend: { labels: { font: { family: 'Tajawal' } } }
                }
            }
        });
    },

    renderProjectionLine(projections) {
        const ctx = document.getElementById('projectionChart');
        if (!ctx) return;

        const months = ['الشهر 1', 'الشهر 3', 'الشهر 6', 'الشهر 9', 'الشهر 12'];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'سيناريو متفائل',
                    data: projections.aggressive || [],
                    borderColor: this.colors.success,
                    backgroundColor: 'rgba(46, 125, 50, 0.05)',
                    fill: true,
                    tension: 0.4,
                }, {
                    label: 'سيناريو متوسط',
                    data: projections.moderate || [],
                    borderColor: this.colors.primary,
                    backgroundColor: 'rgba(25, 118, 210, 0.05)',
                    fill: true,
                    tension: 0.4,
                }, {
                    label: 'سيناريو متحفظ',
                    data: projections.conservative || [],
                    borderColor: this.colors.warning,
                    backgroundColor: 'rgba(237, 108, 2, 0.05)',
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { ticks: { font: { family: 'Tajawal' } } },
                    x: { ticks: { font: { family: 'Tajawal' } } }
                },
                plugins: {
                    legend: { labels: { font: { family: 'Tajawal' } } }
                }
            }
        });
    },

    animateScores() {
        document.querySelectorAll('[data-target-score]').forEach(el => {
            const target = parseInt(el.dataset.targetScore) || 0;
            this.animateScore(el, target, 1500);
        });
    },

    animateScore(element, target, duration) {
        let start = 0;
        const startTime = performance.now();

        const step = (timestamp) => {
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(eased * target);

            element.textContent = current;

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    }
};
