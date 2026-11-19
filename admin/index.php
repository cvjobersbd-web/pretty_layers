<?php
require __DIR__ . '/includes/init.php';

$today = date('Y-m-d');
$valid = "status <> 'cancelled'";
$todaySales = (float)val("SELECT COALESCE(SUM(total),0) FROM orders WHERE $valid AND created_at >= ?", [$today . ' 00:00:00']);
$todayOrders = (int)val("SELECT COUNT(*) FROM orders WHERE created_at >= ?", [$today . ' 00:00:00']);
$monthSales = (float)val("SELECT COALESCE(SUM(total),0) FROM orders WHERE $valid AND created_at >= ?", [date('Y-m-01 00:00:00')]);
$totalSales = (float)val("SELECT COALESCE(SUM(total),0) FROM orders WHERE $valid");
$pending = (int)val("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$productsCount = (int)val('SELECT COUNT(*) FROM products');
$inv = row('SELECT COALESCE(SUM(v.stock),0) AS units, COALESCE(SUM(v.stock * p.cost_price),0) AS cost,
                   COALESCE(SUM(v.stock * CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END),0) AS retail
            FROM variants v JOIN products p ON p.id = v.product_id');
$profit = (float)val("SELECT COALESCE(SUM((oi.price - oi.cost_price) * oi.qty),0) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.$valid");

// last 14 days chart
$from = date('Y-m-d', strtotime('-13 days'));
$days = [];
for ($i = 13; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i days"))] = 0;
foreach (rows("SELECT created_at, total FROM orders WHERE $valid AND created_at >= ?", [$from . ' 00:00:00']) as $o) {
    $d = substr($o['created_at'], 0, 10);
    if (isset($days[$d])) $days[$d] += (float)$o['total'];
}
$maxDay = max(1, max($days));

$recent = rows('SELECT * FROM orders ORDER BY created_at DESC, id DESC LIMIT 6');
$lowList = rows('SELECT v.*, p.name, p.image, p.low_stock, p.id AS pid FROM variants v JOIN products p ON p.id = v.product_id
                 WHERE p.is_active = 1 AND v.stock <= p.low_stock ORDER BY v.stock ASC, p.name LIMIT 8');
$top = rows("SELECT oi.product_id, oi.name, oi.image, SUM(oi.qty) AS qty, SUM(oi.qty * oi.price) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.$valid GROUP BY oi.product_id, oi.name, oi.image ORDER BY qty DESC LIMIT 5");

$title = 'Dashboard'; $active = 'dashboard';
$topAction = '<a href="product-edit.php" class="a-btn a-btn-primary">+ Add product</a>';
require __DIR__ . '/includes/top.php';
?>
<p class="a-hello">Hello, <b><?= e(admin_name()) ?></b> 👋 Here's what's happening in your shop today.</p>

<div class="stat-grid">
  <div class="stat s1"><span>Today's sales</span><b><?= money($todaySales) ?></b><small><?= $todayOrders ?> order(s) today</small></div>
  <div class="stat s2"><span>This month</span><b><?= money($monthSales) ?></b><small>Total: <?= money($totalSales) ?></small></div>
  <div class="stat s3"><span>Pending orders</span><b><?= $pending ?></b><small><a href="orders.php?status=pending">Review now →</a></small></div>
  <div class="stat s4"><span>Gross profit</span><b><?= money($profit) ?></b><small>From all valid orders</small></div>
</div>

<div class="stat-grid">
  <div class="stat mini"><span>Products</span><b><?= $productsCount ?></b></div>
  <div class="stat mini"><span>Units in stock</span><b><?= number_format((int)$inv['units']) ?></b></div>
  <div class="stat mini"><span>Stock value (cost)</span><b><?= money($inv['cost']) ?></b></div>
  <div class="stat mini"><span>Stock value (retail)</span><b><?= money($inv['retail']) ?></b></div>
</div>

<div class="a-grid-2">
  <section class="a-card">
    <div class="a-card-head"><h3>Sales — last 14 days</h3></div>
    <div class="bar-chart">
      <?php foreach ($days as $d => $v): ?>
        <div class="bc-col" title="<?= e(date('d M', strtotime($d)) . ': ' . money($v)) ?>">
          <span class="bc-val"><?= $v ? number_format($v / 1000, 1) . 'k' : '' ?></span>
          <i style="height:<?= max(2, round($v / $maxDay * 100)) ?>%" class="<?= $d === $today ? 'today' : '' ?>"></i>
          <small><?= date('d', strtotime($d)) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="a-card">
    <div class="a-card-head"><h3>⚠ Low stock alerts</h3><a href="inventory.php?filter=low">View all</a></div>
    <?php if (!$lowList): ?><p class="a-empty">All products are well stocked 🎉</p><?php endif; ?>
    <ul class="mini-list">
      <?php foreach ($lowList as $l): ?>
        <li><img src="<?= e(img_url($l['image'])) ?>" alt=""><div><b><?= e($l['name']) ?></b><small><?= e(variant_label($l)) ?></small></div>
          <?= stock_badge((int)$l['stock'], (int)$l['low_stock']) ?><a class="a-btn a-btn-sm" href="inventory.php?q=<?= urlencode($l['sku'] ?: $l['name']) ?>">Restock</a></li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>

<div class="a-grid-2 wide-left">
  <section class="a-card">
    <div class="a-card-head"><h3>Recent orders</h3><a href="orders.php">All orders</a></div>
    <div class="a-table-wrap"><table class="a-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $o): ?>
        <tr onclick="location='order-view.php?id=<?= (int)$o['id'] ?>'" class="clickable"><td><b>#<?= e($o['order_no']) ?></b></td><td><?= e($o['customer_name']) ?><br><small class="muted"><?= e($o['phone']) ?></small></td><td class="nw"><?= money($o['total']) ?></td><td><?= status_badge($o['status']) ?></td><td class="nw"><?= e(date('d M', strtotime($o['created_at']))) ?><br><small class="muted"><?= e(date('h:i A', strtotime($o['created_at']))) ?></small></td></tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="5" class="a-empty">No orders yet.</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </section>
  <section class="a-card">
    <div class="a-card-head"><h3>Best sellers</h3></div>
    <ul class="mini-list">
      <?php foreach ($top as $i => $t): ?>
        <li><span class="rank"><?= $i + 1 ?></span><img src="<?= e(img_url($t['image'])) ?>" alt=""><div><b><?= e($t['name']) ?></b><small><?= (int)$t['qty'] ?> sold · <?= money($t['revenue']) ?></small></div></li>
      <?php endforeach; ?>
      <?php if (!$top): ?><li class="a-empty">No sales yet.</li><?php endif; ?>
    </ul>
  </section>
</div>
<?php require __DIR__ . '/includes/bottom.php'; ?>
