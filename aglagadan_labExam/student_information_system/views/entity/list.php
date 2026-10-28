<?php
$pageTitle = $title;
require dirname(__DIR__) . '/layout/header.php';
$columns = match ($entity) {
 'departments' => ['department_code'=>'Code','department_name'=>'Department','description'=>'Description','course_count'=>'Courses'],
 'courses' => ['course_code'=>'Code','course_name'=>'Course','department_name'=>'Department','duration_years'=>'Duration (years)'],
 'students' => ['student_number'=>'Student No.','last_name'=>'Last name','first_name'=>'First name','course_name'=>'Course','status'=>'Status'],
 'academics' => ['student_number'=>'Student No.','student_name'=>'Student','school_year'=>'School year','semester'=>'Semester','year_level'=>'Year level','academic_status'=>'Academic status'],
};
?>
<div class="page-heading">
  <div><p class="eyebrow">RECORD MANAGEMENT</p><h1><?= Security::e($title) ?></h1><p class="muted">View and maintain <?= strtolower(Security::e($title)) ?> records.</p></div>
  <a class="button button-primary" href="?r=<?= Security::e($entity) ?>/form">+ Add new</a>
</div>
<div class="panel">
  <div class="table-toolbar"><div><strong><?= count($rows) ?> records</strong><span class="muted"> · Current listing</span></div><input class="table-search" type="search" placeholder="Filter visible rows…" oninput="filterTable(this.value)" aria-label="Filter table"></div>
  <div class="table-scroll">
  <table id="records-table">
    <thead><tr><?php foreach ($columns as $label): ?><th><?= Security::e($label) ?></th><?php endforeach; ?><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
      <?php foreach ($columns as $field=>$label): ?>
        <td><?php if ($field === 'status' || $field === 'academic_status'): ?><span class="status-pill"><?= Security::e($row[$field] ?? '') ?></span><?php else: ?><?= Security::e($row[$field] ?? '') ?><?php endif; ?></td>
      <?php endforeach; ?>
      <td><div class="actions">
        <?php if ($entity === 'students'): ?><a class="action-link" href="?r=students/view&id=<?= (int)$row['student_id'] ?>">View</a><?php endif; ?>
        <a class="action-link" href="?r=<?= Security::e($entity) ?>/form&id=<?= (int)$row[match($entity){'departments'=>'department_id','courses'=>'course_id','students'=>'student_id','academics'=>'academic_id'}] ?>">Edit</a>
        <form method="post" action="?r=<?= Security::e($entity) ?>/delete" onsubmit="return confirm('Delete this record? Related records may prevent deletion.')" class="inline-form">
          <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
          <input type="hidden" name="id" value="<?= (int)$row[match($entity){'departments'=>'department_id','courses'=>'course_id','students'=>'student_id','academics'=>'academic_id'}] ?>">
          <button class="action-link danger-link" type="submit">Delete</button>
        </form>
      </div></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="<?= count($columns)+1 ?>" class="empty-state">No records yet. Select “Add new” to create the first record.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<script>
function filterTable(query) {
  const q = query.toLowerCase();
  document.querySelectorAll('#records-table tbody tr').forEach(row => {
    row.hidden = !row.innerText.toLowerCase().includes(q);
  });
}
</script>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
