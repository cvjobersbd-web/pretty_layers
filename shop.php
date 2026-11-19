<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/partials.php';

// the separate "Sale" page is no longer used — old links go to the SALE category (or the shop)
if (get('sale') === '1') {
    $saleCat = val("SELECT slug FROM categories WHERE kind = 'sale' AND is_active = 1");
    redirect(url('shop.php' . ($saleCat ? '?cat=' . urlencode($saleCat) : '')));
}
$cats = categories_with_counts();
$catSlug = get('cat');
$cat = $catSlug ? row('SELECT * FROM categories WHERE slug = ?', [$catSlug]) : null;
$qtext = get('q');
$min = get('min') !== '' ? (float)get('min') : null;
$max = get('max') !== '' ? (float)get('max') : null;
$onSale = get('sale') === '1';
$inStock = get('stock') === '1';
$sort = get('sort', 'new');
$page = max(1, (int)get('page', 1));
$per = 12;

$newOnly = get('new') === '1' || ($cat && ($cat['kind'] ?? '') === 'new');
if ($cat && ($cat['kind'] ?? '') === 'sale') $onSale = true;
$where = ['p.is_active = 1']; $params = [];
if ($cat && ($cat['kind'] ?? 'normal') === 'normal') { $where[] = 'p.category_id = ?'; $params[] = $cat['id']; }
if ($newOnly) { $where[] = 'p.created_at >= ?'; $params[] = date('Y-m-d H:i:s', time() - new_days() * 86400); }
if ($qtext !== '') { $where[] = '(p.name LIKE ? OR p.short_desc LIKE ? OR p.sku LIKE ?)'; array_push($params, "%$qtext%", "%$qtext%", "%$qtext%"); }
$priceExpr = '(CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END)';
if ($min !== null) { $where[] = "$priceExpr >= ?"; $params[] = $min; }
if ($max !== null) { $where[] = "$priceExpr <= ?"; $params[] = $max; }
if ($onSale) $where[] = 'p.sale_price > 0 AND p.sale_price < p.price';
if ($inStock) $where[] = '(SELECT COALESCE(SUM(v.stock),0) FROM variants v WHERE v.product_id = p.id) > 0';
$w = ' WHERE ' . implode(' AND ', $where);

$orders = [
    'new' => 'p.created_at DESC, p.id DESC', 'price_asc' => "$priceExpr ASC", 'price_desc' => "$priceExpr DESC",
    'popular' => 'sold DESC, p.id DESC', 'name' => 'p.name ASC',
];
$orderBy = $orders[$sort] ?? $orders['new'];
$total = (int)val("SELECT COUNT(*) FROM products p $w", $params);
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$list = rows(product_sql() . " $w ORDER BY $orderBy LIMIT $per OFFSET " . (($page - 1) * $per), $params);

function qs(array $change): string
{
    $q = array_merge($_GET, $change);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null);
    return url('shop.php' . ($q ? '?' . http_build_query($q) : ''));
}

$title = $cat ? $cat['name'] : ($newOnly ? 'New Arrivals' : ($onSale ? 'Sale' : ($qtext !== '' ? 'Search: ' . $qtext : 'Shop All')));
$pageTitle = $title;
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <nav class="crumbs"><a href="<?= url('index.php') ?>">Home</a><span>/</span><a href="<?= url('shop.php') ?>">Shop</a><?php if ($cat || $onSale || $newOnly || $qtext): ?><span>/</span><b><?= e($title) ?></b><?php endif; ?></nav>
    <h1><?= e($title) ?></h1>
    <p><?= $total ?> <?= $total === 1 ? 'product' : 'products' ?> found</p>
  </div>
</section>

<section class="container shop-layout">
  <aside class="filters" id="filters">
    <div class="filters-head"><b>Filters</b><button class="icon-btn" data-close-filters aria-label="Close">✕</button></div>
    <form method="get" action="<?= url('shop.php') ?>">
      <?php if ($qtext !== ''): ?><input type="hidden" name="q" value="<?= e($qtext) ?>"><?php endif; ?>
      <input type="hidden" name="sort" value="<?= e($sort) ?>">
      <div class="f-block">
        <h4>Categories</h4>
        <label class="f-radio"><input type="radio" name="cat" value="" <?= !$cat ? 'checked' : '' ?> onchange="this.form.submit()"><span>All products</span></label>
        <?php foreach ($cats as $c): ?>
          <label class="f-radio"><input type="radio" name="cat" value="<?= e($c['slug']) ?>" <?= $cat && $cat['id'] == $c['id'] ? 'checked' : '' ?> onchange="this.form.submit()"><span><?= e($c['name']) ?></span><em><?= (int)$c['cnt'] ?></em></label>
        <?php endforeach; ?>
      </div>
      <div class="f-block">
        <h4>Price (<?= e(setting('currency_symbol', '৳')) ?>)</h4>
        <div class="f-price"><input type="number" name="min" placeholder="Min" value="<?= e(get('min')) ?>" min="0"><span>—</span><input type="number" name="max" placeholder="Max" value="<?= e(get('max')) ?>" min="0"></div>
      </div>
      <div class="f-block">
        <h4>Availability</h4>
        <label class="f-check"><input type="checkbox" name="stock" value="1" <?= $inStock ? 'checked' : '' ?>><span>In stock only</span></label>
      </div>
      <button class="btn btn-accent btn-block">Apply filters</button>
      <a class="btn-link" href="<?= url('shop.php') ?>">Clear all</a>
    </form>
  </aside>

  <div class="shop-main">
    <div class="shop-bar">
      <button class="btn btn-outline btn-sm filter-toggle" data-open-filters>☰ Filters</button>
      <span class="muted">Showing <?= $total ? (($page - 1) * $per + 1) . '–' . min($page * $per, $total) : 0 ?> of <?= $total ?></span>
      <form method="get" class="sort-form">
        <?php foreach ($_GET as $k => $v) if ($k !== 'sort' && $k !== 'page' && is_string($v)): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endif; ?>
        <label>Sort by
          <select name="sort" onchange="this.form.submit()">
            <?php foreach (['new' => 'Newest', 'popular' => 'Most popular', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'name' => 'Name A–Z'] as $k => $l): ?>
              <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </form>
    </div>

    <?php if ($list): ?>
      <div class="p-grid p-grid-3"><?php foreach ($list as $p) echo product_card($p); ?></div>
    <?php else: ?>
      <div class="empty-state">
        <img src="<?= url('assets/img/products/hijab-chiffon-rose.svg') ?>" alt="">
        <h3>No products match your search</h3><p>Try a different keyword or clear the filters.</p>
        <a class="btn btn-accent" href="<?= url('shop.php') ?>">See all products</a>
      </div>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
      <nav class="pager">
        <?php if ($page > 1): ?><a href="<?= qs(['page' => $page - 1]) ?>">‹</a><?php endif; ?>
        <?php for ($i = 1; $i <= $pages; $i++): ?><a href="<?= qs(['page' => $i]) ?>" class="<?= $i === $page ? 'on' : '' ?>"><?= $i ?></a><?php endfor; ?>
        <?php if ($page < $pages): ?><a href="<?= qs(['page' => $page + 1]) ?>">›</a><?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
<div class="filter-overlay" data-close-filters></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
