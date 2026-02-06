<?php $title = ($question ? 'تعديل سؤال' : 'إضافة سؤال جديد') . ' | Marketing AI'; ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card p-4">
            <h4 class="fw-bold mb-4"><?= $title ?></h4>
            
            <form id="question-form">
                <input type="hidden" id="id" value="<?= $question['id'] ?? '' ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-primary">السؤال الأساسي (عربي)</label>
                        <textarea class="form-control" id="question_ar" rows="2" required><?= htmlspecialchars($question['question_ar'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">نص المساعدة / توضيح للسؤال (عربي)</label>
                        <textarea class="form-control" id="help_text_ar" rows="2"><?= htmlspecialchars($question['help_text_ar'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="card mb-4 border-info bg-light">
                    <div class="card-header bg-info text-white fw-bold">تحليلات المستشار الخبير (Educational Insights)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-dark">لماذا هذا مهم؟ (Why it matters)</label>
                                <textarea class="form-control" id="why_it_matters_ar" rows="3"><?= htmlspecialchars($question['why_it_matters_ar'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-dark">مخاطر الإهمال (Risks of neglect)</label>
                                <textarea class="form-control" id="risks_of_neglect_ar" rows="3"><?= htmlspecialchars($question['risks_of_neglect_ar'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-dark">نصيحة الخبير (Expert Tip)</label>
                                <textarea class="form-control" id="educational_tips_ar" rows="3"><?= htmlspecialchars($question['educational_tips_ar'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">التصنيف (Category)</label>
                        <select class="form-select" id="category" required>
                            <option value="">اختر...</option>
                            <option value="STRATEGY" <?= ($question['category']??'')=='STRATEGY'?'selected':'' ?>>Strategy</option>
                            <option value="MARKETING" <?= ($question['category']??'')=='MARKETING'?'selected':'' ?>>Marketing</option>
                            <option value="OPERATIONS" <?= ($question['category']??'')=='OPERATIONS'?'selected':'' ?>>Operations</option>
                            <option value="FINANCE" <?= ($question['category']??'')=='FINANCE'?'selected':'' ?>>Finance</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">نوع السؤال</label>
                        <select class="form-select" id="question_type" onchange="toggleOptions()" required>
                            <option value="multiple_choice" <?= ($question['question_type']??'')=='multiple_choice'?'selected':'' ?>>اختيار من متعدد</option>
                            <option value="scale_rating" <?= ($question['question_type']??'')=='scale_rating'?'selected':'' ?>>مقياس (1-10)</option>
                            <option value="text_input" <?= ($question['question_type']??'')=='text_input'?'selected':'' ?>>نص حر</option>
                            <option value="numeric_input" <?= ($question['question_type']??'')=='numeric_input'?'selected':'' ?>>رقم</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">الترتيب</label>
                        <input type="number" class="form-control" id="display_order" value="<?= $question['display_order'] ?? 0 ?>">
                    </div>
                </div>

                <!-- قسم الخيارات (يظهر فقط إذا كان اختيار من متعدد) -->
                <div id="options-section" class="mb-4 p-3 bg-light rounded" style="display:none;">
                    <label class="form-label fw-bold">الخيارات المتاحة</label>
                    <div id="options-list">
                        <!-- Options injected via JS -->
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addOption()">+ إضافة خيار</button>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="active" <?= ($question['active']??1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="active">نشط</label>
                </div>

                <div class="text-end">
                    <a href="admin/questions" class="btn btn-link text-muted">إلغاء</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">حفظ السؤال</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load existing options if any
let currentOptions = <?= isset($question['options']) ? $question['options'] : '[]' ?>;

function renderOptions() {
    const list = document.getElementById('options-list');
    list.innerHTML = '';
    currentOptions.forEach((opt, index) => {
        list.innerHTML += `
            <div class="input-group mb-2">
                <span class="input-group-text">قيمة</span>
                <input type="text" class="form-control" placeholder="Value (e.g., 5)" value="${opt.value}" onchange="updateOption(${index}, 'value', this.value)">
                <span class="input-group-text">نص</span>
                <input type="text" class="form-control w-50" placeholder="Label text" value="${opt.label_ar || opt.value}" onchange="updateOption(${index}, 'label_ar', this.value)">
                <button type="button" class="btn btn-outline-danger" onclick="removeOption(${index})">x</button>
            </div>
        `;
    });
}

function updateOption(index, key, val) {
    currentOptions[index][key] = val;
}

function addOption() {
    currentOptions.push({ value: '', label_ar: '', label_en: '' });
    renderOptions();
}

function removeOption(index) {
    currentOptions.splice(index, 1);
    renderOptions();
}

function toggleOptions() {
    const type = document.getElementById('question_type').value;
    const section = document.getElementById('options-section');
    if (type === 'multiple_choice') {
        section.style.display = 'block';
        if (currentOptions.length === 0) addOption();
        else renderOptions();
    } else {
        section.style.display = 'none';
        // Don't clear options, just hide, in case they switch back
    }
}

// Init
toggleOptions();

document.getElementById('question-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const token = localStorage.getItem('token');
    
    // Collect Data
    const data = {
        id: document.getElementById('id').value,
        question_ar: document.getElementById('question_ar').value,
        help_text_ar: document.getElementById('help_text_ar').value,
        why_it_matters_ar: document.getElementById('why_it_matters_ar').value,
        risks_of_neglect_ar: document.getElementById('risks_of_neglect_ar').value,
        educational_tips_ar: document.getElementById('educational_tips_ar').value,
        category: document.getElementById('category').value,
        question_type: document.getElementById('question_type').value,
        display_order: document.getElementById('display_order').value,
        active: document.getElementById('active').checked ? 1 : 0,
        options: currentOptions
    };

    try {
        const res = await fetch('api/admin/questions/save', {
            method: 'POST',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        
        if (result.status === 'success') {
            window.location.href = 'admin/questions';
        } else {
            alert(result.message);
        }
    } catch (err) {
        console.error(err);
        alert('Error saving question');
    }
});
</script>
