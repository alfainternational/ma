/**
 * QuestionnaireEngine.js
 * المحرك المسؤول عن إدارة تدفق الأسئلة، حفظ الإجابات، والتحكم في شريط التقدم.
 */

class QuestionnaireEngine {
    constructor(sessionId) {
        this.sessionId = sessionId;
        this.currentQuestion = null;
        this.answers = {};
        this.history = [];
        this.progress = 0;

        // ربط عناصر DOM
        this.questionContainer = document.getElementById('question-card');
        this.progressBar = document.getElementById('progress-bar');
        this.progressText = document.getElementById('progress-text');
        this.nextBtn = document.getElementById('next-btn');
        this.prevBtn = document.getElementById('prev-btn');

        this.init();
    }

    async init() {
        if (window.ServerData && window.ServerData.initialQuestion) {
            // استخدام البيانات الجاهزة من السيرفر
            console.log("Using Server Side Data");
            this.sessionId = window.ServerData.sessionId || this.sessionId;
            this.currentQuestion = window.ServerData.initialQuestion;
            this.renderQuestion();
            this.updateUI();
            this.attachEvents();
        } else {
            // الطريقة القديمة (للاحتياط)
            await this.loadQuestion(null);
            this.attachEvents();
        }
    }

    attachEvents() {
        this.nextBtn.addEventListener('click', () => this.handleNext());
        this.prevBtn.addEventListener('click', () => this.handlePrev());
    }

    async loadQuestion(questionId) {
        try {
            const token = localStorage.getItem('token');
            const url = `${API_BASE}api/questions/get?id=${questionId || ''}`;
            const response = await fetch(url, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const data = await response.json();
            const question = data.id ? data : (data.data && data.data.id ? data.data : null);

            if (question) {
                this.currentQuestion = question;
                this.renderQuestion();
                this.updateUI();
            } else {
                console.error("بنية السؤال غير صحيحة أو السؤال غير موجود:", data);
                this.showError("لم يتم العثور على السؤال. يرجى المحاولة لاحقاً.");
            }
        } catch (error) {
            console.error("خطأ في تحميل السؤال:", error);
            this.showError("حدث خطأ في الاتصال بالخادم. يرجى التحقق من الشبكة.");
        }
    }

    showError(msg) {
        document.getElementById('question-card').innerHTML = `
            <div class="text-center text-danger py-5">
                <h4>⚠️ عذراً</h4>
                <p>${msg}</p>
                <button onclick="location.reload()" class="btn btn-outline-secondary mt-3">إعادة المحاولة</button>
            </div>
        `;
    }

    renderQuestion() {
        const q = this.currentQuestion;
        console.log("Rendering question:", q);

        // إعادة تشغيل الأنيميشن
        this.questionContainer.classList.remove('animate-fade-in');
        void this.questionContainer.offsetWidth; // Trigger reflow
        this.questionContainer.classList.add('animate-fade-in');

        document.getElementById('question-text').innerText = q.question_ar || "";
        document.getElementById('category-badge').innerText = q.category || "";
        document.getElementById('help-text').innerText = q.help_text_ar || "";

        const container = document.getElementById('options-container');
        container.innerHTML = "";

        const qType = q.question_type;

        if (qType === 'single_choice' || qType === 'multi_choice') {
            const options = typeof q.options === 'string' ? JSON.parse(q.options || "[]") : (q.options || []);
            options.forEach(opt => {
                const btn = document.createElement('div');
                btn.className = 'btn-option';
                btn.innerText = opt.label_ar;
                btn.dataset.value = opt.value;
                btn.onclick = () => this.selectOption(btn, qType === 'multi_choice');

                if (Array.isArray(this.answers[q.id])) {
                    if (this.answers[q.id].includes(opt.value)) btn.classList.add('selected');
                } else {
                    if (this.answers[q.id] === opt.value) btn.classList.add('selected');
                }

                container.appendChild(btn);
            });
        } else if (qType === 'numeric_input') {
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'form-control form-control-lg';
            input.value = this.answers[q.id] || "";
            input.placeholder = "أدخل قيمة رقمية...";
            input.oninput = (e) => this.answers[q.id] = e.target.value;
            container.appendChild(input);
        } else if (qType === 'text_input' || qType === 'textarea') {
            const input = qType === 'textarea' ? document.createElement('textarea') : document.createElement('input');
            if (qType === 'text_input') input.type = 'text';
            input.className = 'form-control form-control-lg';
            input.rows = 4;
            input.value = this.answers[q.id] || "";
            input.placeholder = "اكتب إجابتك هنا...";
            input.oninput = (e) => this.answers[q.id] = e.target.value;
            container.appendChild(input);
        }
    }

    selectOption(element, isMulti = false) {
        if (!isMulti) {
            document.querySelectorAll('.btn-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            this.answers[this.currentQuestion.id] = element.dataset.value;
        } else {
            element.classList.toggle('selected');
            if (!this.answers[this.currentQuestion.id]) this.answers[this.currentQuestion.id] = [];
            const val = element.dataset.value;
            if (!Array.isArray(this.answers[this.currentQuestion.id])) this.answers[this.currentQuestion.id] = [];
            const index = this.answers[this.currentQuestion.id].indexOf(val);
            if (index > -1) {
                this.answers[this.currentQuestion.id].splice(index, 1);
            } else {
                this.answers[this.currentQuestion.id].push(val);
            }
        }
    }

    async handleNext() {
        if (!this.answers[this.currentQuestion.id]) {
            alert("يرجى الإجابة على السؤال للمتابعة");
            return;
        }

        // حفظ الإجابة عبر الـ API
        await this.submitAnswer();

        // الانتقال للسؤال التالي (المنطق من الخلفية)
        this.history.push(this.currentQuestion.id);
        const nextId = await this.fetchNextId();

        if (nextId) {
            await this.loadQuestion(nextId);
        } else {
            // انتهاء الاستبيان
            window.location.href = 'analysis/results';
        }
    }

    async handlePrev() {
        if (this.history.length > 0) {
            const prevId = this.history.pop();
            await this.loadQuestion(prevId);
        }
    }

    async fetchNextId() {
        const token = localStorage.getItem('token');
        const response = await fetch(`${API_BASE}api/questions/next`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                sessionId: this.sessionId,
                lastId: this.currentQuestion.id,
                answers: this.answers
            })
        });
        const data = await response.json();
        return data.next_id;
    }

    async submitAnswer() {
        const token = localStorage.getItem('token');
        const q = this.currentQuestion;

        try {
            const response = await fetch(`${API_BASE}api/questions/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    sessionId: this.sessionId,
                    questionId: q.id,
                    value: this.answers[q.id]
                })
            });
            const data = await response.json();
            return data.status === 'success';
        } catch (error) {
            console.error("خطأ في حفظ الإجابة:", error);
            return false;
        }
    }

    updateUI() {
        // تحديث شريط التقدم
        this.progress = Math.min(100, (this.history.length / 50) * 100); // 50 سؤال تقديري للمرحلة
        this.progressBar.style.width = `${this.progress}%`;
        this.progressText.innerText = `${Math.round(this.progress)}%`;

        this.prevBtn.disabled = this.history.length === 0;
    }
}

// تشغيل المحرك عند جاهزية الصفحة
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const sessionId = urlParams.get('session');
    if (sessionId) window.engine = new QuestionnaireEngine(sessionId);
});
