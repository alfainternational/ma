/**
 * Marketing AI System - Main JavaScript
 */
'use strict';

const MAI = {
    csrfToken: null,

    init() {
        this.csrfToken = document.querySelector('input[name="_csrf_token"]')?.value;
        this.initFlashMessages();
        this.initTooltips();
        this.initSessionTimeout();
    },

    // AJAX Helper
    async apiRequest(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        };

        if (data && method !== 'GET') {
            if (data instanceof FormData) {
                if (this.csrfToken) data.append('_csrf_token', this.csrfToken);
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                if (this.csrfToken) data._csrf_token = this.csrfToken;
                options.body = JSON.stringify(data);
            }
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'حدث خطأ في الطلب');
            }
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // Toast Notification
    showToast(message, type = 'info', duration = 4000) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `toast-message ${type}`;
        toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-1rem)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    // Loading Overlay
    showLoading() {
        if (document.querySelector('.spinner-overlay')) return;
        const overlay = document.createElement('div');
        overlay.className = 'spinner-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
    },

    hideLoading() {
        document.querySelector('.spinner-overlay')?.remove();
    },

    // Confirm Dialog
    confirmAction(message) {
        return new Promise(resolve => {
            resolve(confirm(message));
        });
    },

    // Flash Messages
    initFlashMessages() {
        document.querySelectorAll('.flash-message').forEach(el => {
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-0.5rem)';
                setTimeout(() => el.remove(), 300);
            }, 5000);
        });
    },

    // Bootstrap Tooltips
    initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        }
    },

    // Session Timeout Warning
    initSessionTimeout() {
        const lifetime = 7200; // 2 hours
        const warningBefore = 300; // 5 minutes

        setTimeout(() => {
            this.showToast('ستنتهي الجلسة خلال 5 دقائق. يرجى حفظ عملك.', 'warning', 10000);
        }, (lifetime - warningBefore) * 1000);
    },

    // Format Number (Arabic)
    formatNumber(num, decimals = 0) {
        return new Intl.NumberFormat('ar-SA', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    },

    // Format Currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('ar-SA', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount) + ' ريال';
    },

    // Form Validation
    validateForm(formEl) {
        let isValid = true;
        formEl.querySelectorAll('[required]').forEach(field => {
            const feedback = field.nextElementSibling;
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                if (feedback?.classList.contains('invalid-feedback')) {
                    feedback.textContent = 'هذا الحقل مطلوب';
                }
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Email validation
        formEl.querySelectorAll('input[type="email"]').forEach(field => {
            if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        return isValid;
    },

    // Print Report
    printReport() {
        window.print();
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => MAI.init());
