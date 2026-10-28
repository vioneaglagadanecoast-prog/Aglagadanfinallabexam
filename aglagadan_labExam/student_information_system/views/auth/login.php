<?php $pageTitle = 'Admin Login'; require dirname(__DIR__) . '/layout/header.php'; ?>
<section class="login-wrap">
  <div class="login-card">
    <div class="brand-mark">SIS</div>
    <p class="eyebrow">ADMINISTRATION PORTAL</p>
    <h1>Welcome back</h1>
    <p class="muted">Sign in to manage student information and academic records.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= Security::e($error) ?></div><?php endif; ?>
    <form method="post" action="?r=login" class="stack">
      <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
      <label>Username<input name="username" autocomplete="username" required maxlength="80" autofocus></label>
      <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
      <button class="button button-primary button-wide" type="submit">Sign in securely</button>
    </form>
    <p class="login-foot">Authorized administrators only.</p>
  </div>
</section>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
