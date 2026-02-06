<?php $title = 'إدارة بنك الأسئلة | Marketing AI'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">بنك الأسئلة</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="admin/questions/edit" class="btn btn-primary fw-bold">+ سؤال جديد</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>المعرف</th>
                        <th>السؤال (عربي)</th>
                        <th>التصنيف</th>
                        <th>النوع</th>
                        <th>الترتيب</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($questions)): ?>
                        <?php foreach ($questions as $q): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= $q['id'] ?></span></td>
                            <td style="max-width: 300px;"><?= htmlspecialchars($q['question_ar']) ?></td>
                            <td><?= $q['category'] ?> <small class="text-muted"><?= $q['subcategory'] ?></small></td>
                            <td><?= $q['question_type'] ?></td>
                            <td><?= $q['display_order'] ?></td>
                            <td>
                                <?php if ($q['active']): ?>
                                    <span class="badge bg-success">نشط</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">معطل</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin/questions/edit?id=<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary">تعديل</a>
                                <button onclick="deleteQuestion('<?= $q['id'] ?>')" class="btn btn-sm btn-outline-danger">حذف</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">لا توجد أسئلة مضافة.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function deleteQuestion(id) {
    if(!confirm('هل أنت متأكد من حذف هذا السؤال؟')) return;
    
    const token = localStorage.getItem('token');
    try {
        const res = await fetch('api/admin/questions/delete', {
            method: 'POST',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if(data.status === 'success') location.reload();
        else alert(data.message);
    } catch(e) {
        console.error(e);
        alert('حدث خطأ');
    }
}
</script>
