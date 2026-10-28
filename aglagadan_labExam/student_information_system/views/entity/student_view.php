<?php
$pageTitle = 'Student details';
require dirname(__DIR__) . '/layout/header.php';
?>
<div class="page-heading">
 <div><p class="eyebrow">STUDENT PROFILE</p><h1><?= Security::e($student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '')) ?></h1><p class="muted"><?= Security::e($student['student_number']) ?></p></div>
 <div class="quick-actions"><a class="button button-primary" href="?r=students/form&id=<?= (int)$student['student_id'] ?>">Edit student</a><a class="button button-secondary" href="?r=students">Back to students</a></div>
</div>
<div class="detail-grid">
 <?php foreach (['Course'=>'course_name','Status'=>'status','First name'=>'first_name','Middle name'=>'middle_name','Last name'=>'last_name','Sex'=>'sex','Birth date'=>'birth_date','Email'=>'email','Phone'=>'phone','Address'=>'address','Enrollment date'=>'enrollment_date'] as $label=>$field): ?>
 <div class="detail-item"><span><?= Security::e($label) ?></span><strong><?= Security::e($student[$field] ?? '—') ?></strong></div>
 <?php endforeach; ?>
</div>
<div class="page-heading subheading"><div><h2>Academic history</h2><p class="muted">Semester-by-semester records for this student.</p></div><a class="button button-primary" href="?r=academics/form">+ Add academic record</a></div>
<div class="panel table-scroll"><table><thead><tr><th>School year</th><th>Semester</th><th>Year level</th><th>Academic status</th><th>Remarks</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($history as $h): ?><tr><td><?= Security::e($h['school_year']) ?></td><td><?= Security::e($h['semester']) ?></td><td><?= (int)$h['year_level'] ?></td><td><?= Security::e($h['academic_status']) ?></td><td><?= Security::e($h['remarks']) ?></td><td><a class="action-link" href="?r=academics/form&id=<?= (int)$h['academic_id'] ?>">Edit</a></td></tr><?php endforeach; ?>
<?php if (!$history): ?><tr><td colspan="6" class="empty-state">No academic history recorded yet.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
