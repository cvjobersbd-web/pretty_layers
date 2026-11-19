<?php
require __DIR__ . '/includes/init.php';

$o = row('SELECT * FROM orders WHERE id = ?', [(int)get('id')]);
if (!$o) { flash('error', 'Order not found.'); redirect('orders.php'); }
$items = rows('SELECT oi.*, v.stock AS current_stock FROM order_items oi LEFT JOIN variants v ON v.id = oi.variant_id WHERE oi.order_id = ?', [$o['id']]);

if (is_post() && post('action') === 'delete') {
    csrf_check();
    if (delete_order((int)$o['id'], (bool)post('restock'))) { flash('success', 'Order #' . $o['order_no'] . ' deleted permanently.'); redirect('orders.php'); }
    flash('error', 'Could not delete the order.');
    redirect('order-view.php?id=' . $o['id']);
}
if (is_post()) {
    csrf_check();
    $new = post('status');
    if (isset(order_statuses()[$new]) && $new !== $o['status']) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $by = admin_name();
            // Inventory: cancelled -> put items back into stock
            if ($new === 'cancelled' && !$o['stock_returned']) {
                foreach ($items as $it) if ($it['current_stock'] !== null) stock_move((int)$it['variant_id'], (int)$it['qty'], 'cancel', 'Order cancelled', $o['order_no'], $by);
                $o['stock_returned'] = 1;
            }
            // Inventory: re-opening a cancelled order -> take stock again
            if ($o['status'] === 'cancelled' && $new !== 'cancelled' && $o['stock_returned']) {
                foreach ($items as $it) {
                    if ($it['current_stock'] === null) continue;
                    if (!stock_move((int)$it['variant_id'], -(int)$it['qty'], 'order', 'Order re-opened', $o['order_no'], $by))
                        throw new RuntimeException('Not enough stock for “' . $it['name'] . ' (' . variant_label($it) . ')” to re-open this order.');
                }
                $o['stock_returned'] = 0;
            }
            update('orders', ['status' => $new, 'stock_returned' => $o['stock_returned'], 'updated_at' => now()], 'id = ?', [$o['id']]);
            $pdo->commit();
            flash('success', 'Order status changed to ' . order_statuses()[$new] . ($new === 'cancelled' ? ' — items returned to stock.' : '.'));
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', $ex instanceof RuntimeException ? $ex->getMessage() : 'Could not update the order.');
        }
    }
    redirect('order-view.php?id=' . $o['id']);
}

$pm = ['cod' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad'];
$title = 'Order #' . $o['order_no']; $active = 'orders';
$topAction = '<button class="a-btn a-btn-ghost" onclick="window.print()">🖨 Print invoice</button>';
require __DIR__ . '/includes/top.php';
?>
<div class="a-grid-2 wide-left">
  <section class="a-card invoice">
    <div class="inv-head">
      <div class="a-brand dark"><?= logo_html('logo') ?></div>
      <div class="inv-meta"><h3>INVOICE</h3><p>#<?= e($o['order_no']) ?><br><?= e(date('d M Y, h:i A', strtotime($o['created_at']))) ?></p></div>
    </div>
    <div class="inv-parties">
      <div><small class="muted">Bill to</small><p><b><?= e($o['customer_name']) ?></b><br><?= e($o['phone']) ?><?= $o['email'] ? '<br>' . e($o['email']) : '' ?><br><?= e($o['address']) ?>, <?= e($o['city']) ?></p></div>
      <div><small class="muted">From</small><p><b><?= e(setting('site_name')) ?></b><br><?= e(setting('phone')) ?><br><?= e(setting('address')) ?></p></div>
    </div>
    <div class="a-table-wrap"><table class="a-table">
      <thead><tr><th>Item</th><th>Variant</th><th>Price</th><th>Qty</th><th class="r">Total</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr><td><div class="cell-prod"><img class="thumb" src="<?= e(img_url($it['image'])) ?>" alt=""><div><b><?= e($it['name']) ?></b><br><small class="muted"><?= e($it['sku']) ?></small></div></div></td>
          <td><?= e(variant_label($it)) ?></td><td><?= money($it['price']) ?></td><td><?= (int)$it['qty'] ?></td><td class="r"><?= money($it['price'] * $it['qty']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="4" class="r">Subtotal</td><td class="r"><?= money($o['subtotal']) ?></td></tr>
        <tr><td colspan="4" class="r">Delivery (<?= $o['zone'] === 'inside' ? 'Inside Dhaka' : 'Outside Dhaka' ?>)</td><td class="r"><?= money($o['shipping']) ?></td></tr>
        <tr class="grand"><td colspan="4" class="r">Total</td><td class="r"><?= money($o['total']) ?></td></tr>
      </tfoot>
    </table></div>
    <p class="inv-note">Payment: <b><?= e($pm[$o['payment_method']] ?? $o['payment_method']) ?></b><?= $o['trx_id'] ? ' · TrxID: <b>' . e($o['trx_id']) . '</b>' : '' ?><?= $o['note'] ? '<br>Note: ' . e($o['note']) : '' ?></p>
  </section>

  <aside class="no-print">
    <section class="a-card">
      <h3>Order status</h3>
      <p>Current: <?= status_badge($o['status']) ?></p>
      <form method="post">
        <?= csrf_field() ?>
        <label class="a-fld"><span>Change to</span>
          <select name="status"><?php foreach (order_statuses() as $k => $l): ?><option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
        <button class="a-btn a-btn-primary a-btn-block">Update status</button>
      </form>
      <p class="a-help">📦 Stock was taken automatically when this order was placed. Choosing <b>Cancelled</b> puts the items back into stock; re-opening a cancelled order takes them out again.</p>
    </section>
    <section class="a-card">
      <h3>Customer</h3>
      <p><b><?= e($o['customer_name']) ?></b><br><a class="a-link" href="tel:<?= e($o['phone']) ?>"><?= e($o['phone']) ?></a><br><?= e($o['address']) ?>, <?= e($o['city']) ?></p>
      <p><?= !empty($o['customer_id']) ? '<span class="sb sb-in">Registered customer</span>' : '<span class="sb sb-low">Guest checkout</span>' ?></p>
      <p class="muted">Last updated: <?= e(date('d M Y, h:i A', strtotime($o['updated_at']))) ?></p>
    </section>
    <section class="a-card danger-card">
      <h3>Delete order</h3>
      <p class="a-help">Removes this order and its items from the database permanently.</p>
      <form method="post" onsubmit="return confirm('Delete order #<?= e($o['order_no']) ?> permanently? This cannot be undone.')">
        <?= csrf_field() ?><input type="hidden" name="action" value="delete">
        <?php if (!in_array($o['status'], ['delivered', 'cancelled'], true)): ?>
          <label class="a-check small"><input type="checkbox" name="restock" value="1" checked> Put the items back into stock</label>
        <?php endif; ?>
        <button class="a-btn a-btn-danger a-btn-block">🗑 Delete this order</button>
      </form>
    </section>
    <a href="orders.php" class="a-muted-link">← All orders</a>
  </aside>
</div>
<?php require __DIR__ . '/includes/bottom.php'; ?>
