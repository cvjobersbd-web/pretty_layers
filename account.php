<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/order_view.php';

$cust = customer();
if (!$cust) redirect(url('login.php?next=' . urlencode('account.php')));
$tab = get('tab', 'orders');

if (is_post()) {
    csrf_check();
    $sec = post('section');
    if ($sec === 'profile') {
        $email = strtolower(post('email'));
        if (mb_strlen(post('name')) < 2) flash('error', 'Please enter your name.');
        elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) flash('error', 'Email address is not valid.');
        elseif ($email !== '' && val('SELECT id FROM customers WHERE email = ? AND id <> ?', [$email, $cust['id']])) flash('error', 'This email is used by another account.');
        elseif (!has_real_phone($cust) && !normalize_phone(post('phone'))) flash('error', 'Enter a valid mobile number (e.g. 01712345678).');
        elseif (!has_real_phone($cust) && val('SELECT id FROM customers WHERE phone = ? AND id <> ?', [normalize_phone(post('phone')), $cust['id']])) flash('error', 'This mobile number is already used by another account.');
        else {
            $upd = ['name' => post('name'), 'email' => $email ?: null, 'address' => post('address'), 'city' => post('city')];
            if (!has_real_phone($cust)) $upd['phone'] = normalize_phone(post('phone'));
            update('customers', $upd, 'id = ?', [$cust['id']]);
            flash('success', 'Your profile has been saved.');
        }
    } elseif ($sec === 'password') {
        $new = (string)($_POST['new_password'] ?? '');
        if (!password_verify((string)($_POST['current_password'] ?? ''), $cust['password'])) flash('error', 'Current password is wrong.');
        elseif (strlen($new) < 6) flash('error', 'New password must be at least 6 characters.');
        elseif ($new !== ($_POST['confirm_password'] ?? '')) flash('error', 'New passwords do not match.');
        else { update('customers', ['password' => password_hash($new, PASSWORD_DEFAULT)], 'id = ?', [$cust['id']]); flash('success', 'Password changed.'); }
    }
    redirect(url('account.php?tab=' . urlencode($sec)));
}

$orders = rows('SELECT o.*, (SELECT SUM(qty) FROM order_items oi WHERE oi.order_id = o.id) AS items FROM orders o WHERE customer_id = ? ORDER BY created_at DESC, id DESC', [$cust['id']]);
$viewing = null;
if (get('order')) $viewing = row('SELECT * FROM orders WHERE order_no = ? AND customer_id = ?', [get('order'), $cust['id']]);
$spent = array_sum(array_map(fn($o) => $o['status'] === 'cancelled' ? 0 : $o['total'], $orders));
$active = count(array_filter($orders, fn($o) => !in_array($o['status'], ['delivered', 'cancelled'], true)));
$statusClass = ['pending' => 'warn', 'confirmed' => 'info', 'processing' => 'info', 'shipped' => 'info', 'delivered' => 'ok', 'cancelled' => 'bad'];

$pageTitle = 'My account';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-sm"><div class="container"><nav class="crumbs"><a href="<?= url('index.php') ?>">Home</a><span>/</span><b>My account</b></nav><h1>Hello, <?= e(explode(' ', $cust['name'])[0]) ?> 🌸</h1></div></section>

<section class="container section-sm acc-layout">
  <aside class="acc-side">
    <div class="acc-user"><span class="avatar"><?= e(mb_strtoupper(mb_substr($cust['name'], 0, 1))) ?></span><div><b><?= e($cust['name']) ?></b><small><?= e(shown_phone($cust) ?: ($cust['email'] ?? '')) ?></small></div></div>
    <div class="acc-stats"><div><b><?= count($orders) ?></b><small>Orders</small></div><div><b><?= $active ?></b><small>Active</small></div><div><b><?= money($spent) ?></b><small>Spent</small></div></div>
    <nav class="acc-nav">
      <a href="?tab=orders" class="<?= $tab === 'orders' ? 'on' : '' ?>">📦 My orders</a>
      <a href="?tab=profile" class="<?= $tab === 'profile' ? 'on' : '' ?>">👤 Profile &amp; address</a>
      <a href="?tab=password" class="<?= $tab === 'password' ? 'on' : '' ?>">🔒 Change password</a>
      <a href="<?= url('logout.php') ?>">↪ Logout</a>
    </nav>
  </aside>

  <div class="acc-main">
    <?php if ($tab === 'orders'): ?>
      <?php if ($viewing): ?>
        <a href="?tab=orders" class="btn-link" style="margin:0 0 14px">← All orders</a>
        <?php render_order($viewing); ?>
      <?php elseif (!$orders): ?>
        <div class="co-card empty-state">
          <img src="<?= url('assets/img/products/bag-tote-cherry.svg') ?>" alt="">
          <h3>No orders yet</h3><p>When you place an order, it will appear here.</p>
          <a class="btn btn-accent" href="<?= url('shop.php') ?>">Start shopping</a>
        </div>
      <?php else: ?>
        <div class="co-card">
          <h3>My orders</h3>
          <div class="acc-orders">
            <?php foreach ($orders as $o): ?>
              <a class="acc-order" href="?tab=orders&order=<?= urlencode($o['order_no']) ?>">
                <div><b>#<?= e($o['order_no']) ?></b><small><?= e(date('d M Y', strtotime($o['created_at']))) ?> · <?= (int)$o['items'] ?> item(s)</small></div>
                <span class="o-st o-<?= $statusClass[$o['status']] ?? 'info' ?>"><?= e(order_statuses()[$o['status']] ?? $o['status']) ?></span>
                <b class="o-total"><?= money($o['total']) ?></b>
                <span class="o-go">›</span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    <?php elseif ($tab === 'profile'): ?>
      <form method="post" class="co-card">
        <?= csrf_field() ?><input type="hidden" name="section" value="profile">
        <h3>Profile &amp; delivery address</h3>
        <p class="muted">Your saved address is filled in automatically at checkout.</p>
        <div class="form-grid">
          <label class="fld"><span>Full name</span><input name="name" value="<?= e($cust['name']) ?>" required></label>
          <?php if (has_real_phone($cust)): ?>
            <label class="fld"><span>Mobile number</span><input value="<?= e($cust['phone']) ?>" disabled></label>
          <?php else: ?>
            <label class="fld <?= get('need_phone') ? 'has-err' : '' ?>"><span>Mobile number *</span><input name="phone" placeholder="01XXXXXXXXX" required><?php if (get('need_phone')): ?><small class="field-err">Please add your mobile number so we can deliver your orders.</small><?php endif; ?></label>
          <?php endif; ?>
          <label class="fld"><span>Email</span><input type="email" name="email" value="<?= e($cust['email']) ?>"></label>
          <label class="fld"><span>City / District</span><input name="city" value="<?= e($cust['city']) ?>"></label>
          <label class="fld fld-full"><span>Full address</span><textarea name="address" rows="2" placeholder="House, road, area"><?= e($cust['address']) ?></textarea></label>
        </div>
        <button class="btn btn-accent" style="margin-top:18px">Save profile</button>
      </form>

    <?php else: ?>
      <form method="post" class="co-card" style="max-width:520px">
        <?= csrf_field() ?><input type="hidden" name="section" value="password">
        <h3>Change password</h3>
        <label class="fld" style="margin-bottom:14px"><span>Current password</span><input type="password" name="current_password" required></label>
        <label class="fld" style="margin-bottom:14px"><span>New password</span><input type="password" name="new_password" minlength="6" required></label>
        <label class="fld" style="margin-bottom:14px"><span>Confirm new password</span><input type="password" name="confirm_password" minlength="6" required></label>
        <button class="btn btn-accent">Update password</button>
      </form>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
