<?php
require __DIR__ . '/includes/init.php';

$type = get('type'); $qtext = get('q'); $pid = (int)get('product');
$where = ['1=1']; $params = [];
if ($type) { $where[] = 'm.type = ?'; $params[] = $type; }
if ($pid) { $where[] = 'm.product_id = ?'; $params[] = $pid; }
if ($qtext !== '') { $where[] = '(p.name LIKE ? OR v.sku LIKE ? OR m.ref LIKE ?)'; array_push($params, "%$qtext%", "%$qtext%", "%$qtext%"); }
$w = ' WHERE ' . implode(' AND ', $where);
$base = "FROM stock_movements m LEFT JOIN variants v ON v.id = m.variant_id LEFT JOIN products p ON p.id = m.product_id $w";
$page = max(1, (int)get('page', 1)); $per = 30;
$total = (int)val("SELECT COUNT(*) $base", $params);
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$list = rows("SELECT m.*, v.size, v.color, v.sku, p.name, p.image $base ORDER BY m.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$types = ['initial', 'restock', 'adjust', 'edit', 'order', 'cancel'];

$title = 'Stock History'; $active = 'history';
require __DIR__ . '/includes/top.php';
?>
<p class="a-help">Every change in stock is recorded here — opening stock, restocks, manual adjustments, items sold through orders and items returned from cancelled orders.</p>
<form class="a-filters" method="get">
  <input name="q" value="<?= e($qtext) ?>" placeholder="Search product, SKU or order no…">
  <select name="type"><option value="">All types</option><?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= movement_label($t) ?></option><?php endforeach; ?></select>
  <button class="a-btn">Filter</button>
  <?php if ($type || $qtext || $pid): ?><a href="stock-history.php" class="a-muted-link">Clear</a><?php endif; ?>
  <span class="a-count"><?= $total ?> record(s)</span>
</form>
<div class="a-card no-pad">
  <div class="a-table-wrap"><table class="a-table">
    <thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Change</th><th>Balance</th><th>Reference / note</th><th>By</th></tr></thead>
    <tbody>
    <?php foreach ($list as $m): ?>
      <tr>
        <td><?= e(date('d M Y', strtotime($m['created_at']))) ?><br><small class="muted"><?= e(date('h:i A', strtotime($m['created_at']))) ?></small></td>
        <td><div class="cell-prod"><img class="thumb sm" src="<?= e(img_url($m['image'])) ?>" alt=""><div><b><?= e($m['name'] ?? 'Deleted product') ?></b><br><small class="muted"><?= e($m['size'] !== null ? variant_label($m) : 'Removed variant') ?></small></div></div></td>
        <td><span class="mv mv-<?= e($m['type']) ?>"><?= e(movement_label($m['type'])) ?></span></td>
        <td><b class="<?= $m['qty_change'] > 0 ? 'pos' : 'neg' ?>"><?= $m['qty_change'] > 0 ? '+' : '' ?><?= (int)$m['qty_change'] ?></b></td>
        <td><?= (int)$m['balance_after'] ?></td>
        <td><?php if ($m['ref']): ?><a class="a-link" href="orders.php?q=<?= urlencode($m['ref']) ?>">#<?= e($m['ref']) ?></a><br><?php endif; ?><small class="muted"><?= e($m['note']) ?></small></td>
        <td><small><?= e($m['created_by']) ?></small></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$list): ?><tr><td colspan="7" class="a-empty">No stock movements yet.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?= pager($page, $pages) ?>
<?php require __DIR__ . '/includes/bottom.php'; ?>
