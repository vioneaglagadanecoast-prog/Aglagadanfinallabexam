<?php
$pageTitle = $pageTitle ?? 'Student Information System';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= Security::e($pageTitle) ?> · Student Information System</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="?r=dashboard">Student Information System</a>
  <?php if (!empty($_SESSION['user_id'])): ?>
  <div class="top-actions"><span><?= Security::e($_SESSION['username'] ?? 'Admin') ?></span>
    <form method="post" action="?r=logout" class="inline-form">
      <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
      <button class="button button-ghost" type="submit">Log out</button>
    </form>
  </div>
  <?php endif; ?>
</header>
<div class="shell">
<?php if (!empty($_SESSION['user_id'])): ?>
<aside class="sidebar">
  <div class="nav-label">WORKSPACE</div>
  <a href="?r=dashboard">▦ &nbsp; Dashboard</a>
  <a href="?r=departments">▤ &nbsp; Departments</a>
  <a href="?r=courses">▣ &nbsp; Courses</a>
  <a href="?r=students">♙ &nbsp; Students</a>
  <a href="?r=academics">▥ &nbsp; Academic records</a>
</aside>
<?php endif; ?>
<main class="main-content">
<?php if ($flash): ?><div class="alert alert-<?= Security::e($flash['type'] ?? 'success') ?>"><?= Security::e($flash['message'] ?? '') ?></div><?php endif; ?>
