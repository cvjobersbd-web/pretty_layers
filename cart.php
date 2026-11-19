<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';

$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

if (is_post()) {
    csrf_check();
    $action = post('action');
    $msg = ''; $ok = true;
    if ($action === 'add') {
        $vid = (int)post('variant_id');
        $qty = max(1, (int)post('qty', 1));
        $v = $vid ? row('SELECT v.*, p.name, p.is_active FROM variants v JOIN products p ON p.id = v.product_id WHERE v.id = ?', [$vid]) : null;
        if (!$v || !$v['is_active']) { $ok = false; $msg = 'Please choose a size / colour first.'; }
        else {
            $have = cart()[$vid] ?? 0;
            $want = $have + $qty;
            if ((int)$v['stock'] <= 0) { $ok = false; $msg = 'Sorry, this item is out of stock.'; }
            elseif ($want > (int)$v['stock']) { cart_set($vid, (int)$v['stock']); $msg = 'Only ' . $v['stock'] . ' available — your bag has been updated.'; }
            else { cart_set($vid, $want); $msg = '“' . $v['name'] . '” added to your bag.'; }
        }
        if ($ok && post('buy_now')) redirect(url('checkout.php'));
    } elseif ($action === 'update') {
        foreach ((array)post('qty', []) as $vid => $q) cart_set((int)$vid, (int)$q);
        cart_lines(); // clamps to stock
        $msg = 'Your bag has been updated.';
    } elseif ($action === 'remove') {
        cart_set((int)post('variant_id'), 0);
        $msg = 'Item removed.';
    }
    if ($ajax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok, 'message' => $msg, 'count' => cart_count()]); exit; }
    flash($ok ? 'success' : 'error', $msg);
    redirect($action === 'add' && !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url('cart.php'));
}

$lines = cart_lines();
$subtotal = array_sum(array_column($lines, 'line_total'));
$free = (float)setting('free_shipping_over', 0);
$pageTitle = 'Shopping Bag';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-sm"><div class="container"><nav class="crumbs"><a href="<?= url('index.php') ?>">Home</a><span>/</span><b>Shopping bag</b></nav><h1>Your Shopping Bag</h1></div></section>

<section class="container section-sm">
<?php if (!$lines): ?>
  <div class="empty-state">
    <img src="<?= url('assets/img/products/bag-tote-cherry.svg') ?>" alt="">
    <h3>Your bag is empty</h3><p>Looks like you haven't added anything yet.</p>
    <a class="btn btn-accent" href="<?= url('shop.php') ?>">Start shopping</a>
  </div>
<?php else: ?>
  <div class="cart-grid">
    <form method="post" class="cart-list">
      <?= csrf_field() ?><input type="hidden" name="action" value="update">
      <?php foreach ($lines as $l): ?>
        <div class="cart-item">
          <a href="<?= url('product.php?slug=' . urlencode($l['slug'])) ?>" class="ci-img"><img src="<?= e(img_url($l['image'])) ?>" alt=""></a>
          <div class="ci-info">
            <a href="<?= url('product.php?slug=' . urlencode($l['slug'])) ?>" class="ci-name"><?= e($l['name']) ?></a>
            <span class="muted"><?= e(variant_label($l)) ?></span>
            <span class="ci-unit"><?= money($l['unit']) ?></span>
            <?php if ((int)$l['stock'] <= (int)$l['low_stock']): ?><span class="ci-warn">Only <?= (int)$l['stock'] ?> left</span><?php endif; ?>
          </div>
          <div class="qty qty-sm"><button type="button" data-qty="-1">−</button><input type="number" name="qty[<?= (int)$l['variant_id'] ?>]" value="<?= (int)$l['qty'] ?>" min="0" max="<?= (int)$l['stock'] ?>" data-autosubmit><button type="button" data-qty="1">+</button></div>
          <b class="ci-total"><?= money($l['line_total']) ?></b>
          <button class="ci-remove" name="qty[<?= (int)$l['variant_id'] ?>]" value="0" title="Remove" aria-label="Remove">✕</button>
        </div>
      <?php endforeach; ?>
      <div class="cart-actions"><a href="<?= url('shop.php') ?>" class="btn-link">← Continue shopping</a><button class="btn btn-outline btn-sm">Update bag</button></div>
    </form>

    <aside class="summary">
      <h3>Order summary</h3>
      <div class="sum-row"><span>Subtotal</span><b><?= money($subtotal) ?></b></div>
      <div class="sum-row"><span>Delivery</span><span class="muted">Calculated at checkout</span></div>
      <?php if ($free > 0): $left = $free - $subtotal; ?>
        <div class="free-ship">
          <?php if ($left > 0): ?>Add <b><?= money($left) ?></b> more for <b>free delivery</b><?php else: ?>🎉 You've unlocked <b>free delivery</b>!<?php endif; ?>
          <div class="fs-bar"><i style="width:<?= min(100, round($subtotal / $free * 100)) ?>%"></i></div>
        </div>
      <?php endif; ?>
      <a href="<?= url('checkout.php') ?>" class="btn btn-accent btn-lg btn-block">Proceed to checkout</a>
      <p class="secure">🔒 Safe &amp; secure checkout</p>
    </aside>
  </div>
<?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
