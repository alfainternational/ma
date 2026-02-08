<?php $title = 'لوحة الإدارة | Marketing AI'; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="fw-bold">لوحة تحكم النظام</h2>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card p-4 text-center">
            <h6 class="text-muted">إجمالي المستخدمين</h6>
            <h2 class="fw-bold text-primary"><?= $stats['total_users'] ?></h2>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 text-center">
            <h6 class="text-muted">التقييمات المكتملة</h6>
            <h2 class="fw-bold text-success"><?= $stats['total_sessions'] ?></h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">المستخدمين المسجلين</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الدور</th>
                    <th>عدد التقييمات</th>
                    <th>تاريخ التسجيل</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'info' ?>">
                                <?= $user['role'] ?>
                            </span>
                        </td>
                        <td><?= $user['session_count'] ?></td>
                        <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">لا يوجد مستخدمين بعد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
