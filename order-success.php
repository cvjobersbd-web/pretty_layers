<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/order_view.php';

$no = get('no');
$order = ($no && ($_SESSION['last_order'] ?? '') === $no) ? row('SELECT * FROM orders WHERE order_no = ?', [$no]) : null;
if (!$order) redirect(url('track.php'));
$pageTitle = 'Thank you!';
require __DIR__ . '/includes/header.php';
?>
<section class="container section-sm">
  <div class="success-box">
    <div class="success-ico"><svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12.5l4.5 4.5L19 7.5" class="draw"/></svg></div>
    <h1>Thank you, <?= e(explode(' ', $order['customer_name'])[0]) ?>!</h1>
    <p>Your order <b>#<?= e($order['order_no']) ?></b> has been placed successfully. We'll call you on <b><?= e($order['phone']) ?></b> to confirm it shortly.</p>
    <p class="muted">Save your order number to track your delivery.</p>
  </div>
  <?php render_order($order); ?>
  <div class="center"><a class="btn btn-accent" href="<?= url('shop.php') ?>">Continue shopping</a> <a class="btn btn-outline" href="<?= url('track.php?no=' . urlencode($order['order_no']) . '&phone=' . urlencode($order['phone'])) ?>">Track this order</a></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
