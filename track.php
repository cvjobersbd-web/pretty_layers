<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/order_view.php';

$no = strtoupper(trim(get('no')));
$phone = trim(get('phone'));
$order = null; $searched = $no !== '';
if ($searched) {
    $o = row('SELECT * FROM orders WHERE order_no = ?', [ltrim($no, '#')]);
    $norm = fn($s) => substr(preg_replace('/\D/', '', $s), -10);
    if ($o && $norm($o['phone']) === $norm($phone)) $order = $o;
}
$pageTitle = 'Track Order';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-sm"><div class="container"><nav class="crumbs"><a href="<?= url('index.php') ?>">Home</a><span>/</span><b>Track order</b></nav><h1>Track Your Order</h1><p>Enter your order number and the mobile number used at checkout.</p></div></section>
<section class="container section-sm">
  <form class="track-form" method="get">
    <input name="no" value="<?= e($no) ?>" placeholder="Order number e.g. LV2611161234" required>
    <input name="phone" value="<?= e($phone) ?>" placeholder="Mobile number" required>
    <button class="btn btn-accent">Track</button>
  </form>
  <?php if ($searched && !$order): ?><p class="alert alert-error">We couldn't find an order with these details. Please check and try again.</p><?php endif; ?>
  <?php if ($order) render_order($order); ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
