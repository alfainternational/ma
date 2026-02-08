/**
 * Marketing AI System - Admin Panel JavaScript
 */
'use strict';

const AdminPanel = {
    init() {
        this.initSidebar();
        this.initSearch();
        this.initDeleteConfirm();
        this.initStatusUpdates();
    },

    // Sidebar Toggle
    initSidebar() {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        if (toggle && sidebar) {
            toggle.addEventListener('click', () => sidebar.classList.toggle('show'));
        }

        // Close sidebar on outside click (mobile)
        document.addEventListener('click', (e) => {
            if (sidebar?.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                e.target !== toggle) {
                sidebar.classList.remove('show');
            }
        });
    },

    // Table Search
    initSearch() {
        document.querySelectorAll('.admin-search').forEach(input => {
            input.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const tableId = e.target.dataset.table;
                const table = document.getElementById(tableId);
                if (!table) return;

                table.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    },

    // Delete Confirmation
    initDeleteConfirm() {
        document.querySelectorAll('[data-delete]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const name = btn.dataset.name || 'هذا العنصر';
                const confirmed = await MAI.confirmAction(`هل أنت متأكد من حذف ${name}؟`);
                if (confirmed) {
                    const url = btn.dataset.delete;
                    try {
                        MAI.showLoading();
                        const result = await MAI.apiRequest(url, 'POST', { action: 'delete' });
                        if (result.success) {
                            btn.closest('tr')?.remove();
                            MAI.showToast('تم الحذف بنجاح', 'success');
                        }
                    } catch (error) {
                        MAI.showToast('فشل الحذف', 'error');
                    } finally {
                        MAI.hideLoading();
                    }
                }
            });
        });
    },

    // Status Updates
    initStatusUpdates() {
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', async (e) => {
                const url = e.target.dataset.url;
                const newStatus = e.target.value;
                try {
                    const result = await MAI.apiRequest(url, 'POST', {
                        action: 'update_status',
                        status: newStatus
                    });
                    if (result.success) {
                        MAI.showToast('تم التحديث', 'success');
                    }
                } catch (error) {
                    MAI.showToast('فشل التحديث', 'error');
                }
            });
        });
    },

    // Export to CSV
    exportCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = [];
        table.querySelectorAll('tr').forEach(row => {
            const cols = [];
            row.querySelectorAll('th, td').forEach(cell => {
                cols.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
            });
            rows.push(cols.join(','));
        });

        const csv = '\uFEFF' + rows.join('\n'); // BOM for Arabic
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = (filename || 'export') + '.csv';
        link.click();
    },

    // Dashboard Charts
    initDashboardCharts(data) {
        if (!data) return;

        // Sessions over time
        const sessionsCtx = document.getElementById('sessionsChart');
        if (sessionsCtx && data.sessions_timeline) {
            new Chart(sessionsCtx, {
                type: 'line',
                data: {
                    labels: data.sessions_timeline.labels,
                    datasets: [{
                        label: 'الجلسات',
                        data: data.sessions_timeline.values,
                        borderColor: '#1976d2',
                        backgroundColor: 'rgba(25,118,210,0.1)',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { family: 'Tajawal' } } },
                        x: { ticks: { font: { family: 'Tajawal' } } }
                    }
                }
            });
        }

        // Sector distribution
        const sectorCtx = document.getElementById('sectorChart');
        if (sectorCtx && data.sectors) {
            new Chart(sectorCtx, {
                type: 'doughnut',
                data: {
                    labels: data.sectors.labels,
                    datasets: [{
                        data: data.sectors.values,
                        backgroundColor: [
                            '#1976d2', '#2e7d32', '#ed6c02', '#d32f2f',
                            '#7b1fa2', '#0288d1', '#f57c00', '#455a64'
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { family: 'Tajawal' }, padding: 12 }
                        }
                    }
                }
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => AdminPanel.init());

function toggleSidebar() {
    document.getElementById('adminSidebar')?.classList.toggle('show');
}
