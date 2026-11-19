<?php
/** Customer-facing order summary with status timeline */
function render_order(array $o): void
{
    $items = rows('SELECT * FROM order_items WHERE order_id = ?', [$o['id']]);
    $steps = ['pending' => 'Order placed', 'confirmed' => 'Confirmed', 'processing' => 'Packed', 'shipped' => 'On the way', 'delivered' => 'Delivered'];
    $keys = array_keys($steps);
    $idx = array_search($o['status'], $keys, true);
    $pm = ['cod' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad'];
    ?>
  <div class="order-card">
    <div class="oc-head">
      <div><span class="muted">Order</span><b>#<?= e($o['order_no']) ?></b></div>
      <div><span class="muted">Date</span><b><?= e(date('d M Y, h:i A', strtotime($o['created_at']))) ?></b></div>
      <div><span class="muted">Payment</span><b><?= e($pm[$o['payment_method']] ?? $o['payment_method']) ?></b></div>
      <div><span class="muted">Total</span><b><?= money($o['total']) ?></b></div>
    </div>
    <?php if ($o['status'] === 'cancelled'): ?>
      <div class="oc-cancel">This order has been cancelled. Please contact us if you have any questions.</div>
    <?php else: ?>
      <ol class="timeline">
        <?php foreach ($steps as $k => $label): $i = array_search($k, $keys, true); ?>
          <li class="<?= $i < $idx ? 'done' : ($i === $idx ? 'current' : '') ?>"><span></span><?= e($label) ?></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
    <div class="oc-items">
      <?php foreach ($items as $it): ?>
        <div class="sum-item"><span class="si-img"><img src="<?= e(img_url($it['image'])) ?>" alt=""><i><?= (int)$it['qty'] ?></i></span><div><b><?= e($it['name']) ?></b><small><?= e(variant_label($it)) ?> · <?= money($it['price']) ?></small></div><span><?= money($it['price'] * $it['qty']) ?></span></div>
      <?php endforeach; ?>
    </div>
    <div class="oc-foot">
      <div><span class="muted">Deliver to</span><p><?= e($o['customer_name']) ?><br><?= e($o['address']) ?>, <?= e($o['city']) ?></p></div>
      <div class="oc-totals">
        <div class="sum-row"><span>Subtotal</span><b><?= money($o['subtotal']) ?></b></div>
        <div class="sum-row"><span>Delivery</span><b><?= money($o['shipping']) ?></b></div>
        <div class="sum-row sum-total"><span>Total</span><b><?= money($o['total']) ?></b></div>
      </div>
    </div>
  </div>
<?php }
