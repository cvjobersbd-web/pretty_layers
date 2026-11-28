<?php
require __DIR__ . '/includes/init.php';

$keepQs = fn() => http_build_query(array_intersect_key($_GET, array_flip(['q', 'filter', 'cat', 'page'])));

if (is_post()) {
    $ajax = post('ajax') === '1';
    if ($ajax && !hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        header('Content-Type: application/json'); http_response_code(419);
        echo json_encode(['ok' => false, 'msg' => 'Session expired — please reload the page.']); exit;
    }
    if (!$ajax) csrf_check();
    $op = post('op');
    $by = admin_name();

    /* ---- one-click stock change (AJAX) ---- */
    if ($op === 'inc' || $op === 'set') {
        $vid = (int)post('variant_id');
        $v = row('SELECT v.*, p.name, p.low_stock FROM variants v JOIN products p ON p.id = v.product_id WHERE v.id = ?', [$vid]);
        $ok = false; $msg = 'Item not found.';
        if ($v) {
            if ($op === 'inc') {
                $d = (int)post('value');
                $d = max(-1000, min(1000, $d));
                $ok = stock_move($vid, $d, $d > 0 ? 'restock' : 'adjust', $d > 0 ? 'Added from inventory' : 'Removed from inventory', null, $by);
            } else {
                $new = max(0, (int)post('value'));
                $ok = stock_move($vid, $new - (int)$v['stock'], $new > (int)$v['stock'] ? 'restock' : 'adjust', 'Stock count set in inventory', null, $by);
            }
            $stock = (int)val('SELECT stock FROM variants WHERE id = ?', [$vid]);
            $msg = $ok ? "{$v['name']} (" . variant_label($v) . ") — now $stock pcs" : 'Stock cannot go below zero.';
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok, 'msg' => $msg, 'stock' => $stock ?? 0, 'badge' => $v ? stock_badge($stock ?? 0, (int)$v['low_stock']) : '',
                          'total' => $v ? (int)val('SELECT COALESCE(SUM(stock),0) FROM variants WHERE product_id = ?', [$v['product_id']]) : 0]);
        exit;
    }

    /* ---- quick add a new product ---- */
    if ($op === 'quick_add') {
        $name = post('name'); $price = (float)post('price'); $sale = (float)post('sale_price');
        $errs = [];
        if ($name === '') $errs[] = 'Write the product name.';
        if ($price <= 0) $errs[] = 'Write the price.';
        if ($sale > 0 && $sale >= $price) $errs[] = 'Sale price must be lower than the price.';
        $imgErr = null;
        $img = $errs ? null : save_upload($_FILES['image'] ?? [], 'products', $imgErr);
        if ($imgErr) $errs[] = 'Picture: ' . $imgErr;
        if ($errs) { flash('error', implode(' ', $errs)); redirect('inventory.php?add=1'); }

        $id = insert('products', [
            'category_id' => (int)post('category_id') ?: null, 'name' => $name, 'slug' => unique_slug('products', $name),
            'sku' => '', 'short_desc' => '', 'description' => post('description'), 'price' => $price, 'sale_price' => $sale > 0 ? $sale : 0,
            'cost_price' => (float)post('cost_price'), 'low_stock' => (int)setting('low_stock_default', 5), 'is_featured' => 0, 'is_active' => 1,
            'image' => $img ?: '', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $sku = 'LV-' . str_pad((string)($id + 100), 4, '0', STR_PAD_LEFT);
        update('products', ['sku' => $sku], 'id = ?', [$id]);
        $split = fn($s) => array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$s)), 'strlen')));
        $sizes = $split(post('sizes')) ?: [''];
        $colors = $split(post('colors')) ?: [''];
        $each = max(0, (int)post('stock_each'));
        foreach ($sizes as $s) foreach ($colors as $c) {
            $suffix = implode('-', array_filter([$s, substr(preg_replace('/\s+/', '', $c), 0, 3)]));
            $vid = insert('variants', ['product_id' => $id, 'size' => $s, 'color' => $c, 'sku' => strtoupper($sku . '-' . ($suffix ?: 'STD')), 'stock' => 0]);
            if ($each > 0) stock_move($vid, $each, 'initial', 'Opening stock', null, $by);
        }
        // extra pictures
        $sort = 0;
        foreach (files_list($_FILES['gallery'] ?? []) as $file) {
            $err = null; $path = save_upload($file, 'products', $err);
            if ($path) insert('product_images', ['product_id' => $id, 'path' => $path, 'sort_order' => ++$sort]);
        }
        flash('success', "“{$name}” added with " . (count($sizes) * count($colors)) . ' size/colour option(s). It is now live on the shop.');
        redirect('inventory.php?q=' . urlencode($name));
    }
    redirect('inventory.php?' . $keepQs());
}

/* ---------------- list ---------------- */
$filter = get('filter'); $qtext = get('q'); $cat = (int)get('cat');
$where = ['1=1']; $params = [];
if ($filter === 'low') $where[] = 'v.stock > 0 AND v.stock <= p.low_stock';
if ($filter === 'out') $where[] = 'v.stock <= 0';
if ($filter === 'alert') $where[] = 'v.stock <= p.low_stock';
if ($qtext !== '') { $where[] = '(p.name LIKE ? OR v.sku LIKE ? OR p.sku LIKE ?)'; array_push($params, "%$qtext%", "%$qtext%", "%$qtext%"); }
if ($cat) { $where[] = 'p.category_id = ?'; $params[] = $cat; }
$w = ' WHERE ' . implode(' AND ', $where);
$base = "FROM variants v JOIN products p ON p.id = v.product_id LEFT JOIN categories c ON c.id = p.category_id $w";

// CSV export (one row per size/colour)
if (get('export') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inventory-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Product', 'Category', 'Variant', 'SKU', 'Stock', 'Low stock at', 'Status', 'Cost price', 'Selling price', 'Stock value (cost)']);
    foreach (rows("SELECT v.*, p.name, p.low_stock, p.cost_price, p.price, p.sale_price, c.name AS category_name $base ORDER BY p.name, v.id", $params) as $r) {
        fputcsv($out, [$r['name'], $r['category_name'], variant_label($r), $r['sku'], $r['stock'], $r['low_stock'], stock_status((int)$r['stock'], (int)$r['low_stock']),
            $r['cost_price'], final_price($r), $r['stock'] * $r['cost_price']]);
    }
    exit;
}

$sum = row('SELECT COALESCE(SUM(v.stock),0) AS units, COALESCE(SUM(v.stock * p.cost_price),0) AS cost,
              SUM(CASE WHEN v.stock > 0 AND v.stock <= p.low_stock THEN 1 ELSE 0 END) AS low,
              SUM(CASE WHEN v.stock <= 0 THEN 1 ELSE 0 END) AS out_of_stock
            FROM variants v JOIN products p ON p.id = v.product_id');
$page = max(1, (int)get('page', 1)); $per = 12;
$total = (int)val("SELECT COUNT(DISTINCT p.id) $base", $params);
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$pids = array_map('intval', array_column(rows("SELECT p.id, p.name $base GROUP BY p.id, p.name ORDER BY p.name LIMIT $per OFFSET " . (($page - 1) * $per), $params), 'id'));
$products = [];
if ($pids) {
    $in = implode(',', $pids);
    foreach (rows("SELECT p.id, p.name, p.image, p.low_stock, p.is_active, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id IN ($in) ORDER BY p.name") as $p)
        $products[$p['id']] = $p + ['vars' => [], 'total' => 0];
    foreach (rows("SELECT v.* $base AND p.id IN ($in) ORDER BY v.id", $params) as $v) $products[$v['product_id']]['vars'][] = $v;
    foreach (rows("SELECT product_id, SUM(stock) AS t FROM variants WHERE product_id IN ($in) GROUP BY product_id") as $t) $products[$t['product_id']]['total'] = (int)$t['t'];
}
$cats = product_categories();
$openAdd = get('add') === '1';

$title = 'Inventory'; $active = 'inventory';
$topAction = '<a class="a-btn a-btn-ghost" href="?' . e(http_build_query(array_merge($_GET, ['export' => 'csv']))) . '">⬇ Excel / CSV</a>';
require __DIR__ . '/includes/top.php';
?>
<div class="inv-actions">
  <button type="button" class="big-act ba-1" id="qaOpen"><i>➕</i><b>New product</b><small>নতুন প্রোডাক্ট যোগ করুন</small></button>
  <a class="big-act ba-2" href="?filter=alert"><i>⚠️</i><b>Needs stock (<?= (int)$sum['low'] + (int)$sum['out_of_stock'] ?>)</b><small>যেগুলো শেষ বা শেষ হয়ে আসছে</small></a>
  <div class="big-act ba-3 static"><i>📦</i><b><?= number_format((int)$sum['units']) ?> pcs in stock</b><small>মোট মাল · মূল্য <?= money($sum['cost']) ?></small></div>
  <a class="big-act ba-4" href="stock-history.php"><i>🕘</i><b>Stock history</b><small>কখন কী বাড়ানো/কমানো হলো</small></a>
</div>

<section class="a-card qa-card" id="qaCard" <?= $openAdd ? '' : 'hidden' ?>>
  <div class="a-card-head"><h3>➕ Add a new product <small class="bn">— নতুন প্রোডাক্ট</small></h3><button type="button" class="icon-x" id="qaClose" title="Close">✕</button></div>
  <form method="post" enctype="multipart/form-data" class="qa-form">
    <?= csrf_field() ?><input type="hidden" name="op" value="quick_add">
    <div class="qa-pic">
      <label class="img-drop" data-preview><img src="<?= e(img_url('')) ?>" alt=""><span>📷 Click to add picture<br><small>ছবি দিতে ক্লিক করুন</small></span><input type="file" name="image" accept="image/*,.avif"></label>
      <label class="a-fld"><span>More pictures <small class="muted">(optional)</small></span><input type="file" name="gallery[]" accept="image/*,.avif" multiple></label>
    </div>
    <div class="qa-fields">
      <div class="step-t"><em>1</em> Name &amp; price <small class="bn">নাম ও দাম</small></div>
      <label class="a-fld"><span>Product name *</span><input name="name" required placeholder="e.g. Lace Push-up Bra"></label>
      <div class="a-row a-row-3">
        <label class="a-fld"><span>Category</span><select name="category_id"><option value="">— Choose —</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
        <label class="a-fld"><span>Price (<?= e(setting('currency_symbol', '৳')) ?>) *</span><input type="number" min="1" step="1" name="price" required placeholder="1200"></label>
        <label class="a-fld"><span>Sale price <small class="muted">(optional)</small></span><input type="number" min="0" step="1" name="sale_price" placeholder="Discount price"></label>
      </div>
      <div class="step-t"><em>2</em> Sizes, colours &amp; stock <small class="bn">সাইজ, কালার ও কতগুলো আছে</small></div>
      <label class="a-fld"><span>Sizes <small class="muted">— tap to add, or type with commas. Empty = one size</small></span><input name="sizes" id="qaSizes" placeholder="e.g. S, M, L"></label>
      <div class="chips" data-chips="qaSizes"><?php foreach (['S', 'M', 'L', 'XL', 'XXL', 'Free Size', '32', '34', '36', '38', '40', '32B', '34B', '36B'] as $ch): ?><button type="button"><?= $ch ?></button><?php endforeach; ?></div>
      <label class="a-fld"><span>Colours <small class="muted">— optional</small></span><input name="colors" id="qaColors" placeholder="e.g. Black, Pink"></label>
      <div class="chips" data-chips="qaColors"><?php foreach (['Black', 'White', 'Pink', 'Red', 'Purple', 'Nude', 'Blue', 'Maroon'] as $ch): ?><button type="button"><?= $ch ?></button><?php endforeach; ?></div>
      <div class="a-row">
        <label class="a-fld"><span>How many of each? *</span><input type="number" min="0" name="stock_each" value="5" required></label>
        <label class="a-fld"><span>Buying cost <small class="muted">(optional, only you see it)</small></span><input type="number" min="0" step="1" name="cost_price" placeholder="For profit report"></label>
      </div>
      <p class="qa-sum" id="qaSum"></p>
      <details class="more"><summary>Description (optional) <small class="bn">— বিবরণ</small></summary>
        <label class="a-fld"><textarea name="description" rows="4" placeholder="Fabric, fit, care…"></textarea></label></details>
      <div class="qa-btns"><button class="a-btn a-btn-primary">✓ Add product</button><a class="a-btn a-btn-ghost" href="product-edit.php">Open full form</a></div>
    </div>
  </form>
</section>

<div class="inv-tip">💡 <b>+</b> চাপলে ১টা বাড়বে, <b>−</b> চাপলে ১টা কমবে — সাথে সাথে সেভ হয়। নতুন মাল একসাথে অনেক এলে বক্সে মোট সংখ্যা লিখে <b>✓</b> চাপুন।</div>

<div class="pill-tabs">
  <?php foreach (['' => 'All', 'alert' => 'Needs attention', 'low' => 'Low stock', 'out' => 'Out of stock'] as $k => $l): ?>
    <a href="?<?= e(http_build_query(array_filter(['filter' => $k, 'q' => $qtext, 'cat' => $cat ?: '']))) ?>" class="<?= $filter === $k ? 'on' : '' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<form class="a-filters" method="get">
  <?php if ($filter): ?><input type="hidden" name="filter" value="<?= e($filter) ?>"><?php endif; ?>
  <input name="q" value="<?= e($qtext) ?>" placeholder="🔍 Search product name or code…">
  <select name="cat" onchange="this.form.submit()"><option value="">All categories</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <button class="a-btn">Search</button>
  <?php if ($qtext !== '' || $cat): ?><a class="a-btn a-btn-ghost" href="?<?= e(http_build_query(array_filter(['filter' => $filter]))) ?>">Clear</a><?php endif; ?>
  <span class="a-count"><?= $total ?> product(s)</span>
</form>

<div class="inv-list" data-csrf="<?= e(csrf_token()) ?>">
  <?php foreach ($products as $p): ?>
    <article class="inv-card">
      <div class="inv-head">
        <img src="<?= e(img_url($p['image'])) ?>" alt="">
        <div class="inv-title">
          <a href="product-edit.php?id=<?= $p['id'] ?>"><b><?= e($p['name']) ?></b></a>
          <small><?= e($p['category_name'] ?? 'No category') ?><?= $p['is_active'] ? '' : ' · <span class="muted">hidden from shop</span>' ?></small>
          <span class="inv-total">Total: <b data-total><?= $p['total'] ?></b> pcs</span>
        </div>
        <a class="a-btn a-btn-sm a-btn-ghost" href="product-edit.php?id=<?= $p['id'] ?>" title="Edit name, price, pictures, sizes">✏️ Edit</a>
      </div>
      <div class="inv-vars">
        <?php foreach ($p['vars'] as $v): ?>
          <div class="inv-var" data-id="<?= $v['id'] ?>">
            <span class="iv-name"><?= e(variant_label($v)) ?></span>
            <span class="iv-badge"><?= stock_badge((int)$v['stock'], (int)$p['low_stock']) ?></span>
            <div class="stepper">
              <button type="button" data-d="-1" aria-label="Remove one">−</button>
              <input type="number" min="0" value="<?= (int)$v['stock'] ?>" data-was="<?= (int)$v['stock'] ?>" aria-label="Stock">
              <button type="button" data-d="1" aria-label="Add one">+</button>
            </div>
            <button type="button" class="iv-save" hidden title="Save this number">✓</button>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$products): ?><div class="a-card a-empty">Nothing found here. <?= $filter ? 'Great — all stock is healthy! 🎉' : '' ?></div><?php endif; ?>
</div>
<?= pager($page, $pages) ?>
<div class="a-toast" id="aToast"></div>
<?php require __DIR__ . '/includes/bottom.php'; ?>
