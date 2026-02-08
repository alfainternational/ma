/**
 * AssessmentFocus.js
 * محرك تجربة المستخدم الجديدة (Premium Advisor Mode)
 */

class FocusEngine {
    constructor(config) {
        this.sessionId = config.sessionId || null;
        this.currentQuestion = config.initialQuestion || null;
        this.apiBase = config.apiBase || '/';

        this.answers = {};
        this.history = [];
        this.isAnimating = false;

        // Elements
        this.container = document.getElementById('focus-container');
        this.advisorBody = document.getElementById('advisor-body');
        this.progressBar = document.getElementById('progress-fill');
        this.realtimeTimeout = null;

        this.init();
    }

    init() {
        console.log("Premium Focus Engine Initialized");
        if (this.currentQuestion) {
            this.renderQuestion(this.currentQuestion);
        } else {
            this.container.innerHTML = `<div class="text-center"><p class="text-danger">فشل في تحميل بيانات الاستشارة.</p></div>`;
        }

        document.addEventListener('keydown', (e) => this.handleKey(e));
    }

    renderQuestion(q) {
        // 1. Render Main Question
        const html = `
            <div id="q-wrapper" class="slide-in-up">
                <div class="question-number">
                    <span class="key-hint" style="background:var(--accent-blue); color:white;">#${this.history.length + 1}</span>
                    <span class="text-uppercase small fw-bold" style="letter-spacing:2px;">${q.category || 'Strategic Analysis'}</span>
                </div>
                
                <h1 class="question-text">${q.question_ar}</h1>
                
                ${q.help_text_ar ? `<p class="question-help">${q.help_text_ar}</p>` : ''}

                <div class="options-container mb-5">
                    ${this.renderInputType(q)}
                </div>

                <div class="controls-area d-flex align-items-center gap-3">
                    ${(q.question_type !== 'single_choice') ?
                `<button id="btn-continue" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold" onclick="engine.submitCurrent()">متابعة <i class="fa-solid fa-arrow-left ms-2"></i></button>`
                : ''}
                    ${this.history.length > 0 ?
                `<button class="btn btn-link text-white-50 text-decoration-none" onclick="engine.goBack()">السابق</button>`
                : ''}
                </div>
            </div>
        `;

        this.container.innerHTML = html;
        this.updateProgress();
        this.renderAdvisor(q);

        // Auto-focus
        const input = document.querySelector('.focus-input');
        if (input) input.focus();
    }
    renderAdvisor(q) {
        if (!this.advisorBody) return;

        let advisorHtml = `
            <div id="advisor-live-feedback"></div>
            <div class="advisor-animate slide-in-up">
                <h6><i class="fa-solid fa-bullseye"></i> الأهمية الاستراتيجية</h6>
                <div class="insight-card strength">
                    ${q.why_it_matters_ar || 'يقوم المحرك الاستراتيجي بتحليل هذا السؤال لتقديم أفضل نصيحة مخصصة لقطاعك.'}
                </div>

                <h6><i class="fa-solid fa-triangle-exclamation"></i> مخاطر الإهمال</h6>
                <div class="insight-card risk">
                    ${q.risks_of_neglect_ar || 'تجاهل هذه النقطة قد يؤثر على دقة النتائج النهائية في ميزان المخاطر.'}
                </div>

                ${q.educational_tips_ar ? `
                <h6><i class="fa-solid fa-graduation-cap"></i> نصيحة الخبراء</h6>
                <div class="insight-card tip">${q.educational_tips_ar}</div>
                ` : ''}
            </div>
        `;

        this.advisorBody.innerHTML = advisorHtml;
    }

    renderInputType(q) {
        let html = '';
        const options = typeof q.options === 'string' ? JSON.parse(q.options || "[]") : (q.options || []);

        switch (q.question_type) {
            case 'single_choice':
                html = `<div class="options-list">`;
                options.forEach((opt, idx) => {
                    html += `
                        <div class="option-item" onclick="engine.selectOption('${opt.value}')">
                            <span class="key-hint">${idx + 1}</span>
                            <div class="flex-grow-1">
                                <div class="fw-bold">${opt.label_ar}</div>
                                ${opt.description_ar ? `<small class="text-white-50">${opt.description_ar}</small>` : ''}
                            </div>
                            <i class="fa-solid fa-chevron-left opacity-0"></i>
                        </div>
                    `;
                });
                html += `</div>`;
                break;

            case 'multi_choice':
                html = `<div class="options-list">`;
                options.forEach((opt, idx) => {
                    html += `
                        <div class="option-item" onclick="engine.toggleOption(this, '${opt.value}')">
                            <span class="key-hint">${idx + 1}</span>
                            <div class="flex-grow-1">
                                <div class="fw-bold">${opt.label_ar}</div>
                            </div>
                            <div class="check-box-circle"></div>
                        </div>
                    `;
                });
                html += `</div>`;
                break;

            case 'numeric_input':
                // FIX: Support 0 value by checking if input is empty string instead of falsy
                html = `<input type="number" class="focus-input" placeholder="0" oninput="engine.handleRealtimeInput(this.value)" onkeydown="if(event.key==='Enter') engine.submitCurrent()">`;
                break;

            case 'text_input':
                html = `<input type="text" class="focus-input" placeholder="اكتب هنا..." oninput="engine.handleRealtimeInput(this.value)" onkeydown="if(event.key==='Enter') engine.submitCurrent()">`;
                break;

            case 'textarea':
                html = `<textarea class="focus-input mt-3" rows="3" placeholder="اكتب تفاصيل إضافية هنا..." oninput="engine.handleRealtimeInput(this.value)" style="font-size:1.2rem;"></textarea>`;
                break;
        }
        return html;
    }

    selectOption(value) {
        if (this.isAnimating) return;
        this.answers[this.currentQuestion.id] = value;

        const el = document.querySelector(`.option-item[onclick*="${value}"]`);
        if (el) el.classList.add('selected');

        setTimeout(() => this.submitAnswer(), 400);
    }

    toggleOption(el, value) {
        el.classList.toggle('selected');
        let current = this.answers[this.currentQuestion.id] || [];
        if (!Array.isArray(current)) current = [];

        if (current.includes(value)) {
            current = current.filter(v => v !== value);
        } else {
            current.push(value);
        }
        this.answers[this.currentQuestion.id] = current;
    }

    submitCurrent() {
        const q = this.currentQuestion;

        if (['numeric_input', 'text_input', 'textarea'].includes(q.question_type)) {
            const val = document.querySelector('.focus-input').value;

            // FIX: Correctly handle 0 for numeric inputs
            if (val.trim() === "") {
                return alert("يرجى إدخال قيمة صحيحة");
            }
            this.answers[q.id] = val;
        }

        if (!this.answers[q.id] || (Array.isArray(this.answers[q.id]) && this.answers[q.id].length === 0)) {
            return alert("يرجى اختيار إجابة للمتابعة");
        }

        this.submitAnswer();
    }

    async submitAnswer() {
        if (this.isAnimating) return;
        this.isAnimating = true;

        try {
            const token = localStorage.getItem('token');
            const payload = {
                sessionId: this.sessionId,
                questionId: this.currentQuestion.id,
                value: this.answers[this.currentQuestion.id]
            };

            // 1. Submit
            const subResp = await fetch(this.apiBase + 'api/questions/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(payload)
            });
            const subData = await subResp.json();

            // 2. Transition or Finish
            if (subData.status === 'success') {
                if (subData.next_id) {
                    const qResp = await fetch(this.apiBase + 'api/questions/get?id=' + subData.next_id, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    });
                    const qData = await qResp.json();
                    this.transitionTo(qData);
                } else {
                    this.finishAssessment();
                }
            } else {
                throw new Error(subData.message || "فشل حفظ الإجابة");
            }

        } catch (e) {
            console.error(e);
            alert("حدث خطأ أثناء حفظ الإجابة، يرجى المحاولة مرة أخرى.");
            this.isAnimating = false;
        }
    }

    transitionTo(nextQ) {
        const wrapper = document.getElementById('q-wrapper');
        const advisor = document.querySelector('.advisor-inner');

        wrapper.classList.remove('slide-in-up');
        wrapper.classList.add('fade-out-up');
        if (advisor) advisor.style.opacity = '0.5';

        setTimeout(() => {
            this.history.push(this.currentQuestion.id);
            this.currentQuestion = nextQ;
            this.isAnimating = false;
            if (advisor) advisor.style.opacity = '1';
            this.renderQuestion(nextQ);
        }, 400);
    }

    goBack() {
        if (this.history.length === 0) return;
        const prevId = this.history.pop();
        this.fetchAndGo(prevId);
    }

    async fetchAndGo(id) {
        const token = localStorage.getItem('token');
        const qResp = await fetch(this.apiBase + 'api/questions/get?id=' + id, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const qData = await qResp.json();
        this.renderQuestion(qData);
    }

    finishAssessment() {
        this.container.innerHTML = `
            <div class="text-center slide-in-up">
                <div class="spinner-premium mb-4"></div>
                <h2 class="fw-bold mb-3">تم الانتهاء من جمع البيانات!</h2>
                <p class="text-white-50">يقوم الخبراء الـ 10 الآن بتحليل إجاباتك لبناء تقريرك الاستراتيجي...</p>
            </div>
        `;
        setTimeout(() => {
            window.location.href = this.apiBase + 'analysis/results';
        }, 2000);
    }

    toggleMobileAdvisor() {
        const panel = document.querySelector('.advisor-panel');
        panel.classList.toggle('d-none');
        panel.style.position = 'fixed';
        panel.style.zIndex = '2000';
        panel.style.top = '0';
        panel.style.width = '100%';
    }

    updateProgress() {
        // Simplified progress purely based on answered count in this session
        const totalEstimate = 15;
        const percent = Math.min((this.history.length / totalEstimate) * 100, 100);
        this.progressBar.style.width = percent + '%';
    }

    handleRealtimeInput(val) {
        if (this.realtimeTimeout) clearTimeout(this.realtimeTimeout);

        // التحليل اللحظي بعد توقف بسيط عن الكتابة (Debounce)
        this.realtimeTimeout = setTimeout(() => {
            const insightData = this.getLiveInsight(this.currentQuestion.id, val);
            if (insightData) {
                this.updateAdvisorLive(insightData);
            }
        }, 600);
    }

    getLiveInsight(qId, val) {
        if (!val || val.length < 2) return null;

        const rules = {
            'Q_2026_BASIC_001': (v) => {
                const parts = v.trim().split(' ');
                if (parts.length < 2) {
                    return {
                        analysis: `الاسم " ${v} " يبدو غير مكتمل من الناحية المهنية.`,
                        action: `نوصي بإضافة الاسم الثنائي أو العائلي لزيادة المصداقية في تقاريرك الاستشارية.`
                    };
                }
                return {
                    analysis: `تشرفنا بك يا " ${parts[0]} ". هذا الوضوح يعزز من هوية التقرير.`,
                    action: `استخدم دائماً هذا الاسم في مراسلاتك لعام 2026 لبناء سمعة شخصية (Personal Brand) قوية.`
                };
            },
            'Q_2026_BASIC_002': (v) => {
                if (v.length < 4) return null;
                return {
                    analysis: `مشروع " ${v} " يقع في نطاق الذكاء الاستراتيجي الخاص بنا.`,
                    action: `تأكد من حجز النطاق (Domain) وحسابات التواصل الاجتماعي بهذا الاسم " ${v} " فوراً لضمان السيادة الرقمية.`
                };
            },
            'Q_2026_BASIC_003': (v) => {
                const hasNumbers = v.match(/\d+/);
                const isLong = v.length > 20;

                if (!hasNumbers) {
                    return {
                        analysis: `الهدف " ${v} " عاطفي وجيد، لكنه يفتقر إلى الدقة الرقمية المطلوبة في خطة 2026.`,
                        action: `حوّل الهدف إلى رقم ملموس؛ مثلاً بدلاً من "زيادة المبيعات"، اجعلها "زيادة مبيعات ${v} بنسبة 30%".`
                    };
                }
                return {
                    analysis: `رائع! ذكرك للرقم " ${hasNumbers[0]} " يرفع من دقة التحليل الاستراتيجي بنسبة 40%.`,
                    action: `سنقوم في الخطوات القادمة بتقسيم رقم " ${hasNumbers[0]} " إلى مستهدفات شهرية في خريطة الطريق.`
                };
            },
            'Q_2026_BASIC_005': (v) => {
                const keywords = {
                    'سيولة': 'تحدي مالي يتطلب إعادة هيكلة التدفقات النقدية.',
                    'عملاء': 'تحدي تسويقي يتطلب تفعيل قنوات جذب جديدة.',
                    'موظفين': 'تحدي تشغيلي يتطلب تحسين آليات الإدارة.',
                    'مبيعات': 'تحدي نمو يتطلب تحسين معدلات التحويل (Conversion).',
                    'تنفيذ': 'تحدي عملياتي يتطلب أتمتة أو تبسيط الإجراءات.'
                };

                for (let [k, desc] of Object.entries(keywords)) {
                    if (v.includes(k)) {
                        return {
                            analysis: `تحدي " ${k} " هو العائق الرئيسي أمام نموك حالياً: ${desc}`,
                            action: `لا تستثمر في "التسويق" فقط لحل مشكلة " ${k} "؛ ركز أولاً على سد الفجوة في " ${k} " لضمان العائد.`
                        };
                    }
                }
                return {
                    analysis: `لقد رصدنا " ${v} " كمشكلة محورية في نموذج عملك الحالي.`,
                    action: `في نهاية الاستشارة، سنعطيك "توصية مضادة" (Counter-strategy) للتعامل مع " ${v} ".`
                };
            },
            'Q_2026_OFFER_001': (v) => {
                if (v.length < 15) {
                    return {
                        analysis: `وصف المنتج " ${v} " يركز على الخصائص وليس على النفع النهائي (Benefit).`,
                        action: `أعد صياغة " ${v} " بحيث تجيب على سؤال العميل: "ماذا سأربح أنا؟".`
                    };
                }
                return {
                    analysis: `تحليل القيمة للمنتج " ${v} " يظهر جاذبية عالية في السوق الحالي.`,
                    action: `اجعل هذا الوصف " ${v} " هو العنوان الرئيسي في صفحة الهبوط لتوليد عملاء محتملين فوراً.`
                };
            }
        };

        return rules[qId] ? rules[qId](val) : null;
    }

    updateAdvisorLive(insight) {
        const liveSection = document.getElementById('advisor-live-feedback');
        if (!liveSection) return;

        liveSection.innerHTML = `
            <div class="advisor-animate slide-in-up mb-4">
                <!-- قسم التحليل -->
                <div class="analysis-box p-3 rounded-4 mb-3" style="background: rgba(0, 132, 255, 0.08); border-right: 4px solid var(--accent-blue);">
                    <h6 class="text-accent-blue small fw-bold mb-2"><i class="fa-solid fa-microchip pulse"></i> التحليل التشخيصي اللحظي</h6>
                    <div class="text-white-50 small" style="line-height:1.6;">${insight.analysis}</div>
                </div>

                <!-- قسم التوصية -->
                <div class="action-box p-3 rounded-4" style="background: rgba(0, 217, 126, 0.08); border-right: 4px solid #00d97e;">
                    <h6 class="small fw-bold mb-2" style="color:#00d97e;"><i class="fa-solid fa-bolt"></i> خطة عمل فورية (قيد التنفيذ)</h6>
                    <div class="text-white small" style="line-height:1.6;">${insight.action}</div>
                </div>
            </div>
        `;
    }

    handleKey(e) {
        if (this.isAnimating) return;
        if (e.key === 'Enter' && this.currentQuestion.question_type !== 'textarea') {
            this.submitCurrent();
        }
        if (['1', '2', '3', '4', '5', '6', '7', '8', '9'].includes(e.key)) {
            const idx = parseInt(e.key) - 1;
            const options = document.querySelectorAll('.option-item');
            if (options[idx]) options[idx].click();
        }
    }
}
