<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';

$next = safe_next(get('next', post('next')), 'account.php');
if (customer()) redirect(url($next));

$error = ''; $login = '';
if (is_post()) {
    csrf_check();
    $login = post('login');
    $_SESSION['c_tries'] = ($_SESSION['c_tries'] ?? 0) + 1;
    if ($_SESSION['c_tries'] > 6) sleep(2);
    $phone = normalize_phone($login);
    $c = $phone ? row('SELECT * FROM customers WHERE phone = ?', [$phone])
                : row('SELECT * FROM customers WHERE email = ?', [strtolower($login)]);
    if ($c && password_verify((string)($_POST['password'] ?? ''), $c['password'])) {
        $_SESSION['c_tries'] = 0;
        customer_login($c);
        flash('success', 'Welcome back, ' . explode(' ', $c['name'])[0] . '! 💜');
        redirect(url($next));
    }
    $error = 'Mobile number / email or password is incorrect.';
}
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<section class="container auth-wrap">
  <div class="auth-art">
    <div class="auth-arch"><img src="<?= url('assets/img/products/gown-tiered-lavender.svg') ?>" alt=""></div>
    <h2>Welcome back, beautiful!</h2>
    <ul>
      <li>Track all your orders in one place</li>
      <li>Faster checkout — your address is saved</li>
      <li>Early access to new arrivals &amp; offers</li>
    </ul>
  </div>
  <form method="post" class="auth-card">
    <span class="eyebrow">My account</span>
    <h1>Login</h1>
    <p class="muted">Don't have an account? <a href="<?= url('register.php' . ($next !== 'account.php' ? '?next=' . urlencode($next) : '')) ?>" class="link">Create one</a></p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
    <label class="fld"><span>Mobile number or email</span><input name="login" value="<?= e($login) ?>" placeholder="01XXXXXXXXX" required autofocus></label>
    <label class="fld"><span>Password</span><span class="pw"><input type="password" name="password" required><button type="button" class="pw-toggle" aria-label="Show password">👁</button></span></label>
    <button class="btn btn-accent btn-lg btn-block">Login</button>
    <p class="auth-note">Forgot your password? Call us at <b><?= e(setting('phone')) ?></b> and we'll reset it for you.</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
