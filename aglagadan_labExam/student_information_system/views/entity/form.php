<?php
$isEdit = (bool)$record;
$pageTitle = ($isEdit ? 'Edit ' : 'Add ') . rtrim($title, 's');
require dirname(__DIR__) . '/layout/header.php';
$idField = ['departments'=>'department_id','courses'=>'course_id','students'=>'student_id','academics'=>'academic_id'][$entity];
$fields = [
'departments' => [
 ['department_code','Department code','text',true], ['department_name','Department name','text',true], ['description','Description','textarea',false],
],
'courses' => [
 ['department_id','Department','select_departments',true], ['course_code','Course code','text',true], ['course_name','Course name','text',true], ['duration_years','Duration in years','number',true], ['description','Description','textarea',false],
],
'students' => [
 ['student_number','Student number','text',true], ['first_name','First name','text',true], ['middle_name','Middle name','text',false], ['last_name','Last name','text',true], ['sex','Sex','select_sex',true], ['birth_date','Birth date','date',false], ['email','Email address','email',false], ['phone','Phone number','text',false], ['address','Address','textarea',false], ['course_id','Course','select_courses',true], ['enrollment_date','Enrollment date','date',true], ['status','Student status','select_student_status',true],
],
'academics' => [
 ['student_id','Student','select_students',true], ['school_year','School year (e.g. 2026-2027)','text',true], ['semester','Semester','select_semester',true], ['year_level','Year level','number',true], ['academic_status','Academic status','select_academic_status',true], ['remarks','Remarks','textarea',false],
],
];
$selectOptions = [
 'select_sex'=>['Male'=>'Male','Female'=>'Female','Prefer not to say'=>'Prefer not to say'],
 'select_student_status'=>['Active'=>'Active','Inactive'=>'Inactive','Graduated'=>'Graduated','Dropped'=>'Dropped'],
 'select_semester'=>['1st'=>'1st semester','2nd'=>'2nd semester','Summer'=>'Summer'],
 'select_academic_status'=>['Enrolled'=>'Enrolled','Passed'=>'Passed','Failed'=>'Failed','Incomplete'=>'Incomplete','On Leave'=>'On Leave'],
];
?>
<div class="page-heading">
 <div><p class="eyebrow">RECORD MANAGEMENT</p><h1><?= $isEdit ? 'Edit record' : 'Add new record' ?></h1><p class="muted"><?= Security::e($title) ?></p></div>
 <a class="button button-secondary" href="?r=<?= Security::e($entity) ?>">← Back to list</a>
</div>
<div class="panel form-panel">
<form method="post" action="?r=<?= Security::e($entity) ?>" class="form-grid">
 <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
 <input type="hidden" name="id" value="<?= (int)($record[$idField] ?? 0) ?>">
 <?php foreach ($fields[$entity] as [$field,$label,$type,$required]):
   $value = $record[$field] ?? '';
 ?>
 <label class="<?= $type === 'textarea' ? 'full-width' : '' ?>"><?= Security::e($label) ?><?= $required ? ' *' : '' ?>
 <?php if ($type === 'textarea'): ?>
   <textarea name="<?= Security::e($field) ?>" rows="3" <?= $required?'required':'' ?>><?= Security::e($value) ?></textarea>
 <?php elseif (str_starts_with($type,'select_')): ?>
   <select name="<?= Security::e($field) ?>" <?= $required?'required':'' ?>>
     <option value="">Select…</option>
     <?php
       $opts = match($type) {
         'select_departments'=>$options['departments'],
         'select_courses'=>$options['courses'],
         'select_students'=>$options['students'],
         default=>array_map(fn($k,$v)=>['id'=>$k,'name'=>$v], array_keys($selectOptions[$type]), array_values($selectOptions[$type])),
       };
     ?>
     <?php foreach ($opts as $opt): ?><option value="<?= Security::e($opt['id']) ?>" <?= (string)$value === (string)$opt['id']?'selected':'' ?>><?= Security::e($opt['name']) ?></option><?php endforeach; ?>
   </select>
 <?php else: ?>
   <input type="<?= Security::e($type) ?>" name="<?= Security::e($field) ?>" value="<?= Security::e($value) ?>" <?= $required?'required':'' ?> <?= $type==='number'?'min="1" max="10"':'' ?> <?= $type==='email'?'maxlength="190"':'' ?>>
 <?php endif; ?>
 </label>
 <?php endforeach; ?>
 <div class="form-actions full-width"><button class="button button-primary" type="submit"><?= $isEdit ? 'Save changes' : 'Create record' ?></button><a class="button button-secondary" href="?r=<?= Security::e($entity) ?>">Cancel</a></div>
</form>
</div>
<?php if ($entity === 'students' && $isEdit): ?>
<div class="page-heading subheading"><div><h2>Academic history</h2><p class="muted">Academic records associated with this student.</p></div><a class="button button-secondary" href="?r=academics/form">+ Add academic record</a></div>
<div class="panel table-scroll"><table><thead><tr><th>School year</th><th>Semester</th><th>Year level</th><th>Status</th><th>Remarks</th></tr></thead><tbody>
<?php foreach ($history as $h): ?><tr><td><?= Security::e($h['school_year']) ?></td><td><?= Security::e($h['semester']) ?></td><td><?= (int)$h['year_level'] ?></td><td><?= Security::e($h['academic_status']) ?></td><td><?= Security::e($h['remarks']) ?></td></tr><?php endforeach; ?>
<?php if (!$history): ?><tr><td colspan="5" class="empty-state">No academic history has been recorded.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
