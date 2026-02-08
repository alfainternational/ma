/**
 * Marketing AI System - Questionnaire Manager
 */
'use strict';

class QuestionnaireManager {
    constructor(sessionId) {
        this.sessionId = sessionId;
        this.currentQuestion = null;
        this.currentIndex = 0;
        this.totalQuestions = 0;
        this.answers = {};
        this.answeredCount = 0;
        this.autoSaveTimer = null;
    }

    async init() {
        this.bindEvents();
        await this.loadNextQuestion();
    }

    bindEvents() {
        document.getElementById('btnNext')?.addEventListener('click', () => this.nextQuestion());
        document.getElementById('btnPrev')?.addEventListener('click', () => this.prevQuestion());
        document.getElementById('btnSkip')?.addEventListener('click', () => this.skipQuestion());
        document.getElementById('btnSubmit')?.addEventListener('click', () => this.submitAssessment());

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey && this.currentQuestion?.type !== 'text_input') {
                e.preventDefault();
                this.nextQuestion();
            }
        });
    }

    async loadNextQuestion() {
        try {
            const result = await MAI.apiRequest(
                `api/questions.php?action=next&session_id=${this.sessionId}`
            );

            if (result.success && result.data) {
                if (result.data.completed) {
                    window.location.href = `assessment/review.php?session_id=${this.sessionId}`;
                    return;
                }
                this.currentQuestion = result.data.question;
                this.totalQuestions = result.data.total || 0;
                this.answeredCount = result.data.answered || 0;
                this.renderQuestion(this.currentQuestion);
                this.updateProgress();
            }
        } catch (error) {
            MAI.showToast('حدث خطأ في تحميل السؤال', 'error');
        }
    }

    renderQuestion(question) {
        const container = document.getElementById('questionContainer');
        if (!container) return;

        const helpHtml = question.help_text_ar
            ? `<div class="question-help"><i class="fas fa-lightbulb me-2"></i>${question.help_text_ar}</div>`
            : '';

        let answerHtml = '';
        switch (question.type) {
            case 'single_choice':
                answerHtml = this.renderSingleChoice(question.options);
                break;
            case 'multiple_choice':
                answerHtml = this.renderMultipleChoice(question.options);
                break;
            case 'scale_rating':
                answerHtml = this.renderScaleRating(question.validation);
                break;
            case 'numeric_input':
                answerHtml = this.renderNumericInput(question.validation);
                break;
            case 'text_input':
                answerHtml = this.renderTextInput(question.validation);
                break;
        }

        container.innerHTML = `
            <div class="question-card slide-right">
                <span class="question-category">${question.category_label || question.category}</span>
                <h2 class="question-text">${question.text_ar}</h2>
                ${helpHtml}
                <div class="answer-area">${answerHtml}</div>
            </div>
        `;

        // Restore previous answer if exists
        const savedAnswer = this.answers[question.id];
        if (savedAnswer) this.restoreAnswer(question.type, savedAnswer);
    }

    renderSingleChoice(options) {
        if (!options || !Array.isArray(options)) return '<p>لا توجد خيارات</p>';
        return `<div class="answer-options">
            ${options.map((opt, i) => `
                <label class="answer-option" data-value="${opt.value}">
                    <input type="radio" name="answer" value="${opt.value}">
                    <span class="option-radio"></span>
                    <span class="option-text">${opt.label_ar}</span>
                </label>
            `).join('')}
        </div>`;
    }

    renderMultipleChoice(options) {
        if (!options || !Array.isArray(options)) return '<p>لا توجد خيارات</p>';
        return `<div class="answer-options">
            ${options.map((opt, i) => `
                <label class="answer-option" data-value="${opt.value}">
                    <input type="checkbox" name="answer[]" value="${opt.value}">
                    <span class="option-check"><i class="fas fa-check" style="font-size:12px"></i></span>
                    <span class="option-text">${opt.label_ar}</span>
                </label>
            `).join('')}
        </div>`;
    }

    renderScaleRating(validation) {
        const min = validation?.min || 1;
        const max = validation?.max || 10;
        const mid = Math.ceil((max - min) / 2) + min;
        return `
            <div class="scale-rating">
                <div class="scale-value-display" id="scaleValue">${mid}</div>
                <input type="range" class="scale-slider" id="scaleSlider"
                    min="${min}" max="${max}" value="${mid}" step="1">
                <div class="scale-labels">
                    <span>${validation?.labels?.min || 'ضعيف جداً'}</span>
                    <span>${validation?.labels?.mid || 'متوسط'}</span>
                    <span>${validation?.labels?.max || 'ممتاز'}</span>
                </div>
            </div>
        `;
    }

    renderNumericInput(validation) {
        const unit = validation?.unit || '';
        const placeholder = validation?.placeholder || 'أدخل الرقم';
        return `
            <div class="numeric-input-wrapper">
                <input type="number" class="numeric-input form-control" id="numericAnswer"
                    min="${validation?.min || 0}" max="${validation?.max || ''}"
                    placeholder="${placeholder}" step="${validation?.step || 1}">
                ${unit ? `<span class="numeric-unit">${unit}</span>` : ''}
            </div>
        `;
    }

    renderTextInput(validation) {
        const maxLen = validation?.max_length || 1000;
        return `
            <div>
                <textarea class="text-answer" id="textAnswer"
                    maxlength="${maxLen}"
                    placeholder="${validation?.placeholder || 'اكتب إجابتك هنا...'}"
                    rows="4"></textarea>
                <div class="char-count"><span id="charCount">0</span>/${maxLen}</div>
            </div>
        `;
    }

    getAnswer() {
        if (!this.currentQuestion) return null;

        switch (this.currentQuestion.type) {
            case 'single_choice': {
                const selected = document.querySelector('input[name="answer"]:checked');
                return selected ? selected.value : null;
            }
            case 'multiple_choice': {
                const checked = document.querySelectorAll('input[name="answer[]"]:checked');
                return checked.length > 0 ? Array.from(checked).map(c => c.value) : null;
            }
            case 'scale_rating':
                return document.getElementById('scaleSlider')?.value || null;
            case 'numeric_input': {
                const val = document.getElementById('numericAnswer')?.value;
                return val !== '' ? val : null;
            }
            case 'text_input': {
                const text = document.getElementById('textAnswer')?.value?.trim();
                return text || null;
            }
        }
        return null;
    }

    restoreAnswer(type, answer) {
        switch (type) {
            case 'single_choice':
                document.querySelectorAll('.answer-option').forEach(opt => {
                    if (opt.dataset.value === answer) {
                        opt.querySelector('input').checked = true;
                        opt.classList.add('selected');
                    }
                });
                break;
            case 'scale_rating':
                const slider = document.getElementById('scaleSlider');
                if (slider) { slider.value = answer; }
                const display = document.getElementById('scaleValue');
                if (display) { display.textContent = answer; }
                break;
            case 'numeric_input':
                const numInput = document.getElementById('numericAnswer');
                if (numInput) numInput.value = answer;
                break;
            case 'text_input':
                const textInput = document.getElementById('textAnswer');
                if (textInput) textInput.value = answer;
                break;
        }
    }

    async saveAnswer(questionId, answer) {
        try {
            const result = await MAI.apiRequest('api/questions.php', 'POST', {
                action: 'answer',
                session_id: this.sessionId,
                question_id: questionId,
                answer: answer
            });

            if (result.success) {
                this.answers[questionId] = answer;
                this.answeredCount++;
                this.showAutoSave();
            }
            return result;
        } catch (error) {
            MAI.showToast('فشل حفظ الإجابة', 'error');
            return null;
        }
    }

    async nextQuestion() {
        const answer = this.getAnswer();
        if (answer === null && this.currentQuestion?.priority === 'critical') {
            MAI.showToast('هذا السؤال مطلوب. يرجى الإجابة.', 'warning');
            return;
        }

        if (answer !== null) {
            await this.saveAnswer(this.currentQuestion.id, answer);
        }

        await this.loadNextQuestion();
    }

    async prevQuestion() {
        // Save current answer first
        const answer = this.getAnswer();
        if (answer !== null) {
            await this.saveAnswer(this.currentQuestion.id, answer);
        }
        // Go to previous (would need server support for prev question)
        MAI.showToast('سيتم دعم الرجوع قريباً', 'info');
    }

    async skipQuestion() {
        try {
            await MAI.apiRequest('api/questions.php', 'POST', {
                action: 'skip',
                session_id: this.sessionId,
                question_id: this.currentQuestion.id
            });
            await this.loadNextQuestion();
        } catch (error) {
            MAI.showToast('حدث خطأ', 'error');
        }
    }

    updateProgress() {
        const percent = this.totalQuestions > 0
            ? Math.round((this.answeredCount / this.totalQuestions) * 100)
            : 0;

        const fill = document.querySelector('.progress-fill');
        if (fill) fill.style.width = percent + '%';

        const percentEl = document.querySelector('.progress-percent');
        if (percentEl) percentEl.textContent = percent + '%';

        const countEl = document.querySelector('.progress-count');
        if (countEl) countEl.textContent = `${this.answeredCount} / ${this.totalQuestions}`;
    }

    showAutoSave() {
        const indicator = document.querySelector('.auto-save');
        if (indicator) {
            indicator.textContent = 'تم الحفظ تلقائياً';
            indicator.classList.add('show');
            clearTimeout(this.autoSaveTimer);
            this.autoSaveTimer = setTimeout(() => indicator.classList.remove('show'), 2000);
        }
    }

    async submitAssessment() {
        const confirmed = await MAI.confirmAction('هل أنت متأكد من إرسال التقييم؟ لن تتمكن من تعديل الإجابات بعد ذلك.');
        if (!confirmed) return;

        MAI.showLoading();
        try {
            const result = await MAI.apiRequest('api/analysis.php', 'POST', {
                action: 'run',
                session_id: this.sessionId
            });

            if (result.success) {
                window.location.href = `assessment/results.php?session_id=${this.sessionId}`;
            } else {
                MAI.showToast(result.message || 'حدث خطأ', 'error');
            }
        } catch (error) {
            MAI.showToast('حدث خطأ في إرسال التقييم', 'error');
        } finally {
            MAI.hideLoading();
        }
    }
}

// Event delegation for answer option clicks
document.addEventListener('click', (e) => {
    const option = e.target.closest('.answer-option');
    if (!option) return;

    const input = option.querySelector('input');
    if (!input) return;

    if (input.type === 'radio') {
        document.querySelectorAll('.answer-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        input.checked = true;
    } else if (input.type === 'checkbox') {
        input.checked = !input.checked;
        option.classList.toggle('selected', input.checked);
    }
});

// Scale slider event
document.addEventListener('input', (e) => {
    if (e.target.id === 'scaleSlider') {
        const display = document.getElementById('scaleValue');
        if (display) display.textContent = e.target.value;
    }
});

// Text counter event
document.addEventListener('input', (e) => {
    if (e.target.id === 'textAnswer') {
        const counter = document.getElementById('charCount');
        if (counter) counter.textContent = e.target.value.length;
    }
});
