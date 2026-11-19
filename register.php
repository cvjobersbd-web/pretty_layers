<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';

$next = safe_next(get('next', post('next')), 'account.php');
if (customer()) redirect(url($next));

$errors = [];
$f = ['name' => '', 'phone' => '', 'email' => ''];
if (is_post()) {
    csrf_check();
    foreach ($f as $k => $v) $f[$k] = post($k);
    $phone = normalize_phone($f['phone']);
    $email = strtolower($f['email']);
    $pass = (string)($_POST['password'] ?? '');
    if (mb_strlen($f['name']) < 2) $errors['name'] = 'Please enter your name.';
    if (!$phone) $errors['phone'] = 'Enter a valid mobile number (e.g. 01712345678).';
    elseif (val('SELECT id FROM customers WHERE phone = ?', [$phone])) $errors['phone'] = 'An account with this number already exists. Please login.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email address is not valid.';
    elseif ($email !== '' && val('SELECT id FROM customers WHERE email = ?', [$email])) $errors['email'] = 'This email is already registered.';
    if (strlen($pass) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    elseif ($pass !== ($_POST['password2'] ?? '')) $errors['password2'] = 'Passwords do not match.';

    if (!$errors) {
        $id = insert('customers', ['name' => $f['name'], 'phone' => $phone, 'email' => $email ?: null,
            'password' => password_hash($pass, PASSWORD_DEFAULT), 'address' => '', 'city' => '', 'created_at' => now()]);
        // link earlier guest orders placed with the same number
        foreach (rows('SELECT id, phone FROM orders WHERE customer_id IS NULL') as $o)
            if (normalize_phone($o['phone']) === $phone) q('UPDATE orders SET customer_id = ? WHERE id = ?', [$id, $o['id']]);
        customer_login(row('SELECT * FROM customers WHERE id = ?', [$id]));
        flash('success', 'Your account has been created. Welcome to ' . setting('site_name') . '! 🌸');
        redirect(url($next));
    }
}
$err = fn($k) => isset($errors[$k]) ? '<small class="field-err">' . e($errors[$k]) . '</small>' : '';
$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<section class="container auth-wrap">
  <div class="auth-art">
    <div class="auth-arch"><img src="<?= url('assets/img/products/kurti-floral-lavender.svg') ?>" alt=""></div>
    <h2>Join the <?= e(setting('site_name')) ?> family</h2>
    <ul>
      <li>Save your delivery address</li>
      <li>See every order &amp; its delivery status</li>
      <li>Members-only offers on festive drops</li>
    </ul>
  </div>
  <form method="post" class="auth-card">
    <span class="eyebrow">New here?</span>
    <h1>Create account</h1>
    <p class="muted">Already have an account? <a href="<?= url('login.php' . ($next !== 'account.php' ? '?next=' . urlencode($next) : '')) ?>" class="link">Login</a></p>
    
    <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
    <label class="fld <?= isset($errors['name']) ? 'has-err' : '' ?>"><span>Full name *</span><input name="name" value="<?= e($f['name']) ?>" required><?= $err('name') ?></label>
    <label class="fld <?= isset($errors['phone']) ? 'has-err' : '' ?>"><span>Mobile number *</span><input name="phone" value="<?= e($f['phone']) ?>" placeholder="01XXXXXXXXX" required><?= $err('phone') ?></label>
    <label class="fld <?= isset($errors['email']) ? 'has-err' : '' ?>"><span>Email (optional)</span><input type="email" name="email" value="<?= e($f['email']) ?>"><?= $err('email') ?></label>
    <div class="form-grid">
      <label class="fld <?= isset($errors['password']) ? 'has-err' : '' ?>"><span>Password *</span><input type="password" name="password" minlength="6" required><?= $err('password') ?></label>
      <label class="fld <?= isset($errors['password2']) ? 'has-err' : '' ?>"><span>Confirm password *</span><input type="password" name="password2" minlength="6" required><?= $err('password2') ?></label>
    </div>
    <button class="btn btn-accent btn-lg btn-block">Create account</button>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
