<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/layout/header.php';
$cards = [
    ['departments','Departments','Manage academic units','▤'],
    ['courses','Courses','Programs offered','▣'],
    ['students','Students','Student profiles','♙'],
    ['academics','Academic records','Enrollment history','▥'],
];
?>
<div class="page-heading">
  <div><p class="eyebrow">OVERVIEW</p><h1>Dashboard</h1><p class="muted">A quick summary of your academic records.</p></div>
  <span class="date-chip"><?= date('M d, Y') ?></span>
</div>
<div class="stat-grid">
<?php foreach ($cards as [$key,$label,$desc,$icon]): ?>
<a class="stat-card" href="?r=<?= Security::e($key) ?>">
  <div class="stat-top"><span class="stat-icon"><?= $icon ?></span><span class="stat-arrow">↗</span></div>
  <div class="stat-number"><?= (int)$counts[$key] ?></div>
  <div class="stat-title"><?= Security::e($label) ?></div>
  <div class="stat-description"><?= Security::e($desc) ?></div>
</a>
<?php endforeach; ?>
</div>
<section class="welcome-panel">
  <div><p class="eyebrow">GET STARTED</p><h2>Keep records organized</h2><p class="muted">Manage departments and courses first, then register students and maintain their academic history.</p></div>
  <div class="quick-actions">
    <a class="button button-primary" href="?r=students/form">+ Add student</a>
    <a class="button button-secondary" href="?r=academics/form">+ Add academic record</a>
  </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
