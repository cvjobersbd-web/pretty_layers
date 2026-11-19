<?php
require __DIR__ . '/includes/init.php';

if (is_post()) {
    csrf_check();
    $id = (int)post('id');
    $act = post('action');
    if ($act === 'toggle') {
        q('UPDATE products SET is_active = 1 - is_active, updated_at = ? WHERE id = ?', [now(), $id]);
        flash('success', 'Product visibility updated.');
    } elseif ($act === 'feature') {
        q('UPDATE products SET is_featured = 1 - is_featured, updated_at = ? WHERE id = ?', [now(), $id]);
        flash('success', 'Featured status updated.');
    } elseif ($act === 'delete') {
        $p = row('SELECT * FROM products WHERE id = ?', [$id]);
        if ($p) {
            foreach (rows('SELECT path FROM product_images WHERE product_id = ?', [$id]) as $im) delete_upload($im['path']);
            delete_upload($p['image']);
            q('DELETE FROM product_images WHERE product_id = ?', [$id]);
            q('DELETE FROM variants WHERE product_id = ?', [$id]);
            q('DELETE FROM products WHERE id = ?', [$id]);
            flash('success', '“' . $p['name'] . '” deleted. Past orders are kept.');
        }
    }
    redirect('products.php?' . http_build_query($_GET));
}

$qtext = get('q'); $cat = (int)get('cat'); $status = get('status');
$where = ['1=1']; $params = [];
if ($qtext !== '') { $where[] = '(p.name LIKE ? OR p.sku LIKE ?)'; array_push($params, "%$qtext%", "%$qtext%"); }
if ($cat) { $where[] = 'p.category_id = ?'; $params[] = $cat; }
if ($status === 'active') $where[] = 'p.is_active = 1';
if ($status === 'hidden') $where[] = 'p.is_active = 0';
if ($status === 'featured') $where[] = 'p.is_featured = 1';
if ($status === 'low') $where[] = '(SELECT COALESCE(SUM(v.stock),0) FROM variants v WHERE v.product_id = p.id) BETWEEN 1 AND p.low_stock';
if ($status === 'out') $where[] = '(SELECT COALESCE(SUM(v.stock),0) FROM variants v WHERE v.product_id = p.id) = 0';
$w = ' WHERE ' . implode(' AND ', $where);
$page = max(1, (int)get('page', 1)); $per = 15;
$total = (int)val("SELECT COUNT(*) FROM products p $w", $params);
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$list = rows(product_sql() . " $w ORDER BY p.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$cats = product_categories();

$title = 'Products'; $active = 'products';
$topAction = '<a href="product-edit.php" class="a-btn a-btn-primary">+ Add product</a>';
require __DIR__ . '/includes/top.php';
?>
<form class="a-filters" method="get">
  <input name="q" value="<?= e($qtext) ?>" placeholder="Search name or SKU…">
  <select name="cat"><option value="">All categories</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <select name="status">
    <?php foreach (['' => 'All products', 'active' => 'Visible', 'hidden' => 'Hidden', 'featured' => 'Featured', 'low' => 'Low stock', 'out' => 'Out of stock'] as $k => $l): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
  </select>
  <button class="a-btn">Filter</button>
  <?php if ($qtext || $cat || $status): ?><a href="products.php" class="a-muted-link">Clear</a><?php endif; ?>
  <span class="a-count"><?= $total ?> product(s)</span>
</form>

<div class="a-card no-pad">
  <div class="a-table-wrap"><table class="a-table">
    <thead><tr><th></th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Sold</th><th>Visible</th><th>Featured</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $p): ?>
      <tr>
        <td><img class="thumb" src="<?= e(img_url($p['image'])) ?>" alt=""></td>
        <td><a href="product-edit.php?id=<?= $p['id'] ?>" class="a-link"><b><?= e($p['name']) ?></b></a><br><small class="muted">SKU: <?= e($p['sku']) ?> · <?= (int)$p['variant_count'] ?> variant(s)</small></td>
        <td><?= e($p['category_name'] ?? '—') ?></td>
        <td><b><?= money(final_price($p)) ?></b><?php if (discount_pct($p)): ?><br><del class="muted"><?= money($p['price']) ?></del><?php endif; ?></td>
        <td><?= stock_badge((int)$p['total_stock'], (int)$p['low_stock']) ?></td>
        <td><?= (int)$p['sold'] ?></td>
        <td><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="switch <?= $p['is_active'] ? 'on' : '' ?>" name="action" value="toggle" title="Show / hide"></button></form></td>
        <td><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="star <?= $p['is_featured'] ? 'on' : '' ?>" name="action" value="feature" title="Featured">★</button></form></td>
        <td class="row-actions">
          <a class="a-btn a-btn-sm" href="product-edit.php?id=<?= $p['id'] ?>">Edit</a>
          <a class="a-btn a-btn-sm a-btn-ghost" href="../product.php?slug=<?= urlencode($p['slug']) ?>" target="_blank">View</a>
          <form method="post" onsubmit="return confirm('Delete this product permanently?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="a-btn a-btn-sm a-btn-danger" name="action" value="delete">Delete</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$list): ?><tr><td colspan="9" class="a-empty">No products found. <a href="product-edit.php">Add your first product →</a></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?= pager($page, $pages) ?>
<?php require __DIR__ . '/includes/bottom.php'; ?>
