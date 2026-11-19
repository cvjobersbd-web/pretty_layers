<?php
require __DIR__ . '/includes/init.php';

if (is_post()) {
    csrf_check();
    $vid = (int)post('variant_id');
    $mode = post('mode');
    $qty = max(0, (int)post('qty'));
    $note = post('note');
    $v = row('SELECT v.*, p.name FROM variants v JOIN products p ON p.id = v.product_id WHERE v.id = ?', [$vid]);
    if (!$v) flash('error', 'Variant not found.');
    else {
        $by = admin_name();
        $ok = true;
        if ($mode === 'add' && $qty > 0) $ok = stock_move($vid, $qty, 'restock', $note ?: 'New stock received', null, $by);
        elseif ($mode === 'remove' && $qty > 0) $ok = stock_move($vid, -$qty, 'adjust', $note ?: 'Damaged / lost', null, $by);
        elseif ($mode === 'set') $ok = stock_move($vid, $qty - (int)$v['stock'], 'adjust', $note ?: 'Stock count correction', null, $by);
        else { $ok = false; }
        $after = (int)val('SELECT stock FROM variants WHERE id = ?', [$vid]);
        flash($ok ? 'success' : 'error', $ok ? "Stock updated: {$v['name']} (" . variant_label($v) . ") now has $after." : 'Could not change stock — check the quantity (stock cannot go below zero).');
    }
    redirect('inventory.php?' . http_build_query(array_intersect_key($_GET, array_flip(['q', 'filter', 'cat', 'page']))));
}

$filter = get('filter'); $qtext = get('q'); $cat = (int)get('cat');
$where = ['1=1']; $params = [];
if ($filter === 'low') $where[] = 'v.stock > 0 AND v.stock <= p.low_stock';
if ($filter === 'out') $where[] = 'v.stock <= 0';
if ($filter === 'alert') $where[] = 'v.stock <= p.low_stock';
if ($qtext !== '') { $where[] = '(p.name LIKE ? OR v.sku LIKE ? OR p.sku LIKE ?)'; array_push($params, "%$qtext%", "%$qtext%", "%$qtext%"); }
if ($cat) { $where[] = 'p.category_id = ?'; $params[] = $cat; }
$w = ' WHERE ' . implode(' AND ', $where);
$base = "FROM variants v JOIN products p ON p.id = v.product_id LEFT JOIN categories c ON c.id = p.category_id $w";
$select = "SELECT v.*, p.name, p.image, p.low_stock, p.cost_price, p.price, p.sale_price, p.id AS pid, c.name AS category_name,
           (SELECT COALESCE(SUM(oi.qty),0) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.variant_id = v.id AND o.status <> 'cancelled') AS sold
           $base ORDER BY p.name, v.id";

// CSV export
if (get('export') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inventory-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Product', 'Category', 'Variant', 'SKU', 'Stock', 'Low stock at', 'Status', 'Sold', 'Cost price', 'Selling price', 'Stock value (cost)']);
    foreach (rows($select, $params) as $r) {
        fputcsv($out, [$r['name'], $r['category_name'], variant_label($r), $r['sku'], $r['stock'], $r['low_stock'], stock_status((int)$r['stock'], (int)$r['low_stock']),
            $r['sold'], $r['cost_price'], final_price($r), $r['stock'] * $r['cost_price']]);
    }
    exit;
}

$sum = row('SELECT COALESCE(SUM(v.stock),0) AS units, COALESCE(SUM(v.stock * p.cost_price),0) AS cost,
              COALESCE(SUM(v.stock * CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END),0) AS retail,
              SUM(CASE WHEN v.stock > 0 AND v.stock <= p.low_stock THEN 1 ELSE 0 END) AS low,
              SUM(CASE WHEN v.stock <= 0 THEN 1 ELSE 0 END) AS out_of_stock, COUNT(*) AS variants
            FROM variants v JOIN products p ON p.id = v.product_id');
$page = max(1, (int)get('page', 1)); $per = 25;
$total = (int)val("SELECT COUNT(*) $base", $params);
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$list = rows($select . " LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$cats = product_categories();

$title = 'Inventory'; $active = 'inventory';
$topAction = '<a class="a-btn a-btn-ghost" href="?' . e(http_build_query(array_merge($_GET, ['export' => 'csv']))) . '">⬇ Export CSV</a>';
require __DIR__ . '/includes/top.php';
?>
<div class="stat-grid stat-grid-5">
  <div class="stat mini"><span>Total units</span><b><?= number_format((int)$sum['units']) ?></b><small><?= (int)$sum['variants'] ?> variants</small></div>
  <div class="stat mini"><span>Stock value (cost)</span><b><?= money($sum['cost']) ?></b></div>
  <div class="stat mini"><span>Stock value (selling)</span><b><?= money($sum['retail']) ?></b><small>Potential profit <?= money($sum['retail'] - $sum['cost']) ?></small></div>
  <a class="stat mini warn" href="?filter=low"><span>Low stock</span><b><?= (int)$sum['low'] ?></b><small>variants need restock</small></a>
  <a class="stat mini danger" href="?filter=out"><span>Out of stock</span><b><?= (int)$sum['out_of_stock'] ?></b><small>not available to buy</small></a>
</div>

<div class="pill-tabs">
  <?php foreach (['' => 'All', 'alert' => 'Needs attention', 'low' => 'Low stock', 'out' => 'Out of stock'] as $k => $l): ?>
    <a href="?<?= e(http_build_query(array_filter(['filter' => $k, 'q' => $qtext, 'cat' => $cat ?: '']))) ?>" class="<?= $filter === $k ? 'on' : '' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<form class="a-filters" method="get">
  <?php if ($filter): ?><input type="hidden" name="filter" value="<?= e($filter) ?>"><?php endif; ?>
  <input name="q" value="<?= e($qtext) ?>" placeholder="Search product or SKU…">
  <select name="cat"><option value="">All categories</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <button class="a-btn">Filter</button>
  <span class="a-count"><?= $total ?> variant(s)</span>
</form>

<div class="a-card no-pad">
  <div class="a-table-wrap"><table class="a-table inv-table">
    <thead><tr><th>Product</th><th>Variant</th><th>SKU</th><th>Stock</th><th>Sold</th><th>Value (cost)</th><th style="min-width:340px">Update stock</th></tr></thead>
    <tbody>
    <?php foreach ($list as $r): ?>
      <tr>
        <td><div class="cell-prod"><img class="thumb" src="<?= e(img_url($r['image'])) ?>" alt=""><div><a class="a-link" href="product-edit.php?id=<?= $r['pid'] ?>"><b><?= e($r['name']) ?></b></a><br><small class="muted"><?= e($r['category_name'] ?? '') ?></small></div></div></td>
        <td><?= e(variant_label($r)) ?></td>
        <td><small><?= e($r['sku']) ?></small></td>
        <td><?= stock_badge((int)$r['stock'], (int)$r['low_stock']) ?></td>
        <td><?= (int)$r['sold'] ?></td>
        <td><?= money($r['stock'] * $r['cost_price']) ?></td>
        <td>
          <form method="post" class="stock-form">
            <?= csrf_field() ?><input type="hidden" name="variant_id" value="<?= $r['id'] ?>">
            <select name="mode"><option value="add">+ Add (restock)</option><option value="remove">− Remove</option><option value="set">= Set to</option></select>
            <input type="number" name="qty" min="0" placeholder="Qty" required>
            <input name="note" placeholder="Note (optional)">
            <button class="a-btn a-btn-sm a-btn-primary">Save</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$list): ?><tr><td colspan="7" class="a-empty">Nothing here — great job keeping stock healthy!</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?= pager($page, $pages) ?>
<?php require __DIR__ . '/includes/bottom.php'; ?>
