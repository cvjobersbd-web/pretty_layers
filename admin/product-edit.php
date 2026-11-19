<?php
require __DIR__ . '/includes/init.php';

$id = (int)get('id');
$p = $id ? row('SELECT * FROM products WHERE id = ?', [$id]) : null;
if ($id && !$p) { flash('error', 'Product not found.'); redirect('products.php'); }
$isNew = !$p;
$cats = product_categories();
$variants = $p ? rows('SELECT * FROM variants WHERE product_id = ? ORDER BY id', [$id]) : [];
$gallery = $p ? rows('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$id]) : [];
$errors = [];

$f = $p ?: ['name' => '', 'category_id' => $cats[0]['id'] ?? null, 'sku' => '', 'short_desc' => '', 'description' => '', 'price' => '', 'sale_price' => '',
    'cost_price' => '', 'low_stock' => setting('low_stock_default', 5), 'is_featured' => 0, 'is_active' => 1, 'image' => ''];

if (is_post()) {
    csrf_check();
    foreach (['name', 'sku', 'short_desc', 'description', 'price', 'sale_price', 'cost_price', 'low_stock'] as $k) $f[$k] = post($k);
    $f['category_id'] = (int)post('category_id') ?: null;
    $f['is_featured'] = post('is_featured') ? 1 : 0;
    $f['is_active'] = post('is_active') ? 1 : 0;
    if ($f['name'] === '') $errors[] = 'Product name is required.';
    if (!is_numeric($f['price']) || (float)$f['price'] <= 0) $errors[] = 'Regular price must be greater than 0.';
    if ($f['sale_price'] !== '' && (float)$f['sale_price'] >= (float)$f['price']) $errors[] = 'Sale price must be lower than the regular price (leave empty for no sale).';

    // collect variant rows
    $rowsIn = [];
    foreach ((array)post('v_size', []) as $i => $size) {
        $color = trim($_POST['v_color'][$i] ?? '');
        $size = trim($size);
        $rowsIn[] = ['id' => (int)($_POST['v_id'][$i] ?? 0), 'size' => $size, 'color' => $color, 'sku' => trim($_POST['v_sku'][$i] ?? ''), 'stock' => max(0, (int)($_POST['v_stock'][$i] ?? 0))];
    }
    if (!$rowsIn) $rowsIn[] = ['id' => 0, 'size' => '', 'color' => '', 'sku' => '', 'stock' => 0];
    $combo = [];
    foreach ($rowsIn as $r) {
        $k = strtolower($r['size'] . '|' . $r['color']);
        if (isset($combo[$k])) { $errors[] = 'Duplicate variant: ' . (variant_label($r)) . '. Each size/colour combination must be unique.'; break; }
        $combo[$k] = 1;
    }

    $imgErr = null;
    $newMain = save_upload($_FILES['image'] ?? [], 'products', $imgErr);
    if ($imgErr) $errors[] = 'Main image: ' . $imgErr;

    if (!$errors) {
        $data = [
            'category_id' => $f['category_id'], 'name' => $f['name'], 'short_desc' => $f['short_desc'], 'description' => $f['description'],
            'price' => (float)$f['price'], 'sale_price' => $f['sale_price'] === '' ? 0 : (float)$f['sale_price'], 'cost_price' => (float)$f['cost_price'],
            'low_stock' => max(0, (int)$f['low_stock']), 'is_featured' => $f['is_featured'], 'is_active' => $f['is_active'], 'updated_at' => now(),
        ];
        if ($newMain) { if ($p) delete_upload($p['image']); $data['image'] = $newMain; }
        elseif (post('remove_main') && $p) { delete_upload($p['image']); $data['image'] = ''; }

        if ($isNew) {
            $data['slug'] = unique_slug('products', $f['name']);
            $data['created_at'] = now();
            $data['sku'] = $f['sku'];
            $id = insert('products', $data);
            if ($f['sku'] === '') { $f['sku'] = 'LV-' . str_pad((string)($id + 100), 4, '0', STR_PAD_LEFT); update('products', ['sku' => $f['sku']], 'id = ?', [$id]); }
        } else {
            if ($f['name'] !== $p['name']) $data['slug'] = unique_slug('products', $f['name'], $id);
            $data['sku'] = $f['sku'] ?: $p['sku'];
            update('products', $data, 'id = ?', [$id]);
        }
        $sku = $f['sku'] ?: ($p['sku'] ?? '');

        // ---- variants & inventory ----
        $keep = [];
        $by = admin_name();
        foreach ($rowsIn as $r) {
            $suffix = implode('-', array_filter([$r['size'], substr(preg_replace('/\s+/', '', $r['color']), 0, 3)]));
            $vsku = $r['sku'] ?: strtoupper($sku . '-' . ($suffix ?: 'STD'));
            $old = $r['id'] ? row('SELECT * FROM variants WHERE id = ? AND product_id = ?', [$r['id'], $id]) : null;
            if ($old) {
                update('variants', ['size' => $r['size'], 'color' => $r['color'], 'sku' => $vsku], 'id = ?', [$old['id']]);
                $diff = $r['stock'] - (int)$old['stock'];
                if ($diff) stock_move((int)$old['id'], $diff, 'edit', 'Changed in product form', null, $by);
                $keep[] = (int)$old['id'];
            } else {
                $vid = insert('variants', ['product_id' => $id, 'size' => $r['size'], 'color' => $r['color'], 'sku' => $vsku, 'stock' => 0]);
                if ($r['stock'] > 0) stock_move($vid, $r['stock'], 'initial', 'Opening stock', null, $by);
                $keep[] = $vid;
            }
        }
        foreach ($variants as $v) if (!in_array((int)$v['id'], $keep, true)) {
            if ((int)$v['stock'] > 0) stock_move((int)$v['id'], -(int)$v['stock'], 'adjust', 'Variant removed', null, $by);
            q('DELETE FROM variants WHERE id = ?', [$v['id']]);
        }

        // ---- gallery ----
        foreach ((array)post('rm_img', []) as $gid) {
            $g = row('SELECT * FROM product_images WHERE id = ? AND product_id = ?', [(int)$gid, $id]);
            if ($g) { delete_upload($g['path']); q('DELETE FROM product_images WHERE id = ?', [$g['id']]); }
        }
        $sort = (int)val('SELECT COALESCE(MAX(sort_order),0) FROM product_images WHERE product_id = ?', [$id]);
        $gErrs = [];
        foreach (files_list($_FILES['gallery'] ?? []) as $file) {
            $err = null; $path = save_upload($file, 'products', $err);
            if ($path) insert('product_images', ['product_id' => $id, 'path' => $path, 'sort_order' => ++$sort]);
            elseif ($err) $gErrs[] = $file['name'] . ': ' . $err;
        }
        flash('success', $isNew ? 'Product added successfully!' : 'Product saved.');
        if ($gErrs) flash('error', 'Some gallery images were skipped — ' . implode('; ', $gErrs));
        redirect('product-edit.php?id=' . $id);
    }
    // re-show posted variants on error
    $variants = $rowsIn;
}

if (!$variants) $variants = [['id' => 0, 'size' => '', 'color' => '', 'sku' => '', 'stock' => 0]];
$title = $isNew ? 'Add product' : 'Edit product';
$active = 'products';
$topAction = $isNew ? '' : '<a href="../product.php?slug=' . urlencode($p['slug']) . '" target="_blank" class="a-btn a-btn-ghost">View on shop ↗</a>';
require __DIR__ . '/includes/top.php';
?>
<?php if ($errors): ?><div class="a-alert a-alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="pe-grid">
  <?= csrf_field() ?>
  <div class="pe-main">
    <section class="a-card">
      <h3>Basic information</h3>
      <label class="a-fld"><span>Product name *</span><input name="name" value="<?= e($f['name']) ?>" required placeholder="e.g. Floral Embroidered Cotton Kurti"></label>
      <div class="a-row">
        <label class="a-fld"><span>Category</span><select name="category_id"><option value="">— None —</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $f['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?><?= $c['is_active'] ? '' : ' (hidden)' ?></option><?php endforeach; ?></select></label>
        <label class="a-fld"><span>SKU / Product code</span><input name="sku" value="<?= e($f['sku']) ?>" placeholder="Auto if empty"></label>
      </div>
      <label class="a-fld"><span>Short description</span><input name="short_desc" value="<?= e($f['short_desc']) ?>" maxlength="250" placeholder="One line shown under the price"></label>
      <label class="a-fld"><span>Full description</span><textarea name="description" rows="7"><?= e($f['description']) ?></textarea></label>
    </section>

    <section class="a-card">
      <h3>Pricing</h3>
      <div class="a-row a-row-3">
        <label class="a-fld"><span>Regular price (<?= e(setting('currency_symbol', '৳')) ?>) *</span><input type="number" step="0.01" min="0" name="price" value="<?= e($f['price']) ?>" required></label>
        <label class="a-fld"><span>Sale price</span><input type="number" step="0.01" min="0" name="sale_price" value="<?= (float)$f['sale_price'] > 0 ? e($f['sale_price']) : '' ?>" placeholder="Optional"></label>
        <label class="a-fld"><span>Cost price <i class="tip" title="Your buying cost. Used for profit and stock value reports. Not shown to customers.">?</i></span><input type="number" step="0.01" min="0" name="cost_price" value="<?= e($f['cost_price']) ?>"></label>
      </div>
    </section>

    <section class="a-card">
      <div class="a-card-head"><h3>Variants &amp; stock (Inventory)</h3></div>
      <p class="a-help">Each size / colour combination has its own stock. Leave size and colour empty for a product with a single option (e.g. a bag). Changing stock here is saved in <a href="stock-history.php">Stock History</a>.</p>
      <div class="gen-box">
        <input id="genSizes" placeholder="Sizes e.g. S, M, L, XL">
        <input id="genColors" placeholder="Colours e.g. Red, Blue">
        <input id="genStock" type="number" min="0" placeholder="Stock each" style="max-width:120px">
        <button type="button" class="a-btn a-btn-sm" id="genBtn">Generate variants</button>
      </div>
      <div class="a-table-wrap"><table class="a-table v-table" id="vTable">
        <thead><tr><th>Size</th><th>Colour</th><th>Variant SKU</th><th>Stock</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($variants as $v): ?>
            <tr>
              <td><input type="hidden" name="v_id[]" value="<?= (int)$v['id'] ?>"><input name="v_size[]" value="<?= e($v['size']) ?>" placeholder="e.g. M"></td>
              <td><input name="v_color[]" value="<?= e($v['color']) ?>" placeholder="e.g. Lavender"></td>
              <td><input name="v_sku[]" value="<?= e($v['sku']) ?>" placeholder="Auto"></td>
              <td><input type="number" min="0" name="v_stock[]" value="<?= (int)$v['stock'] ?>" class="v-stock"></td>
              <td><button type="button" class="icon-x" data-rm-row title="Remove">✕</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="3"><button type="button" class="a-btn a-btn-sm a-btn-ghost" id="addRow">+ Add variant</button></td><td colspan="2"><b>Total: <span id="vTotal">0</span></b></td></tr></tfoot>
      </table></div>
      <label class="a-fld" style="max-width:260px;margin-top:14px"><span>Low stock alert at <i class="tip" title="When any variant's stock falls to this number or below, you'll see an alert on the dashboard and inventory page.">?</i></span><input type="number" min="0" name="low_stock" value="<?= e($f['low_stock']) ?>"></label>
    </section>
  </div>

  <aside class="pe-side">
    <section class="a-card">
      <h3>Publish</h3>
      <label class="a-check"><input type="checkbox" name="is_active" value="1" <?= $f['is_active'] ? 'checked' : '' ?>> Visible on shop</label>
      <label class="a-check"><input type="checkbox" name="is_featured" value="1" <?= $f['is_featured'] ? 'checked' : '' ?>> Featured product ★</label>
      <button class="a-btn a-btn-primary a-btn-block"><?= $isNew ? 'Add product' : 'Save changes' ?></button>
      <a href="products.php" class="a-muted-link">← Back to products</a>
    </section>
    <section class="a-card">
      <h3>Main image</h3>
      <label class="img-drop" data-preview>
        <img src="<?= e(img_url($f['image'])) ?>" alt="">
        <span>Click to upload (JPG, PNG, WEBP, AVIF — max 5 MB). Best size 800×1000.</span>
        <input type="file" name="image" accept="image/*,.avif">
      </label>
      <?php if (!$isNew && $f['image']): ?><label class="a-check small"><input type="checkbox" name="remove_main" value="1"> Remove current image</label><?php endif; ?>
    </section>
    <section class="a-card">
      <h3>Gallery images</h3>
      <div class="gal-grid">
        <?php foreach ($gallery as $g): ?>
          <label class="gal-item"><img src="<?= e(img_url($g['path'])) ?>" alt=""><span><input type="checkbox" name="rm_img[]" value="<?= $g['id'] ?>"> remove</span></label>
        <?php endforeach; ?>
      </div>
      <label class="a-fld"><span>Add more images</span><input type="file" name="gallery[]" accept="image/*,.avif" multiple></label>
    </section>
  </aside>
</form>

<template id="vRowTpl"><tr>
  <td><input type="hidden" name="v_id[]" value="0"><input name="v_size[]" placeholder="e.g. M"></td>
  <td><input name="v_color[]" placeholder="e.g. Lavender"></td>
  <td><input name="v_sku[]" placeholder="Auto"></td>
  <td><input type="number" min="0" name="v_stock[]" value="0" class="v-stock"></td>
  <td><button type="button" class="icon-x" data-rm-row title="Remove">✕</button></td>
</tr></template>
<?php require __DIR__ . '/includes/bottom.php'; ?>
