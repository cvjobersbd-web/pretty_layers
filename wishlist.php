<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/partials.php';

if (is_post()) {
    csrf_check();
    $pid = (int)post('product_id');
    $ok = $pid && val('SELECT id FROM products WHERE id = ?', [$pid]);
    $on = $ok ? wishlist_toggle($pid) : false;
    $count = count((function () { $c = $_SESSION['customer_id'] ?? 0;
        return $c ? rows('SELECT product_id FROM wishlist WHERE customer_id = ?', [$c]) : ($_SESSION['wish'] ?? []); })());
    $msg = !$ok ? 'Product not found.' : ($on ? 'Added to your wishlist 💜' : 'Removed from your wishlist.');
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$ok, 'on' => $on, 'count' => $count, 'message' => $msg]);
        exit;
    }
    flash($ok ? 'success' : 'error', $msg);
    redirect($_SERVER['HTTP_REFERER'] ?? url('wishlist.php'));
}

$ids = wishlist_ids();
$list = [];
if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $byId = array_column(rows(product_sql() . " WHERE p.is_active = 1 AND p.id IN ($in)", $ids), null, 'id');
    foreach ($ids as $id) if (isset($byId[$id])) $list[] = $byId[$id];
}
$pageTitle = 'My Wishlist';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-sm"><div class="container"><nav class="crumbs"><a href="<?= url('index.php') ?>">Home</a><span>/</span><b>Wishlist</b></nav><h1>My Wishlist</h1>
  <p><?= count($list) ?> saved <?= count($list) === 1 ? 'item' : 'items' ?></p></div></section>
<section class="container section-sm">
  <?php if (!$list): ?>
    <div class="empty-state">
      <div class="empty-heart">♡</div>
      <h3>Your wishlist is empty</h3>
      <p>Tap the heart on any product to save it here for later.</p>
      <a class="btn btn-accent" href="<?= url('shop.php') ?>">Explore products</a>
    </div>
  <?php else: ?>
    <?php if (!customer()): ?><div class="co-login">💡 <a href="<?= url('login.php?next=wishlist.php') ?>">Login</a> to keep your wishlist saved on every device.</div><?php endif; ?>
    <div class="p-grid" data-wish-page><?php foreach ($list as $p) echo product_card($p); ?></div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
