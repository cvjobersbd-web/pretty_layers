<?php
define('ADMIN_PUBLIC', true);
require __DIR__ . '/includes/init.php';
if (admin()) redirect(admin_home());

$error = '';
if (is_post()) {
    csrf_check();
    $_SESSION['login_tries'] = ($_SESSION['login_tries'] ?? 0) + 1;
    if ($_SESSION['login_tries'] > 5) sleep(2); // slow down guessing
    $a = row('SELECT * FROM admins WHERE username = ?', [post('username')]);
    if ($a && isset($a['is_active']) && !$a['is_active']) $a = null;
    if ($a && password_verify((string)($_POST['password'] ?? ''), $a['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $a['id'], 'username' => $a['username']];
        $_SESSION['login_tries'] = 0;
        admin(true);
        redirect(admin_home());
    }
    $error = 'Wrong username or password.';
}
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login · <?= e(setting('site_name', 'Lavanya')) ?></title>
<?= favicon_tag() ?>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600&family=Playfair+Display:wght@600&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>"><style><?= theme_css() ?></style></head>
<body class="login-page login-simple">
  <form method="post" class="login-card">
    <div class="a-brand dark"><?= logo_html('logo') ?></div>
    <h1>Admin sign in</h1>
    <?php if ($error): ?><div class="a-alert a-alert-error"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label class="a-fld"><span>Username</span><input name="username" value="<?= e(post('username')) ?>" required autofocus></label>
    <label class="a-fld"><span>Password</span><input type="password" name="password" required></label>
    <button class="a-btn a-btn-primary a-btn-block">Sign in</button>
    <a href="../index.php" class="a-muted-link">← Back to shop</a>
  </form>
<script src="<?= asset('assets/js/admin.js') ?>"></script>
</body></html>
