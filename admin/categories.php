<?php
require __DIR__ . '/includes/init.php';

$edit = get('edit') ? row('SELECT * FROM categories WHERE id = ?', [(int)get('edit')]) : null;

if (is_post()) {
    csrf_check();
    $act = post('action');
    if ($act === 'style') {
        set_setting('cat_style', in_array(post('cat_style'), ['landscape', 'square', 'arch'], true) ? post('cat_style') : 'arch');
        set_setting('cat_fit', post('cat_fit') === 'cover' ? 'cover' : 'contain');
        set_setting('cat_autoslide', post('cat_autoslide') ? '1' : '0');
        set_setting('cat_slide_seconds', (string)min(10, max(2, (int)post('cat_slide_seconds', 3))));
        set_setting('cat_subtitle', post('cat_subtitle'));
        flash('success', 'Homepage category style saved.');
        redirect('categories.php');
    }
    if ($act === 'save') {
        $id = (int)post('id');
        $name = post('name');
        if ($name === '') { flash('error', 'Category name is required.'); redirect('categories.php' . ($id ? '?edit=' . $id : '')); }
        $err = null;
        $img = save_upload($_FILES['image'] ?? [], 'products', $err);
        if ($err) flash('error', 'Image: ' . $err);
        $kind = isset(category_kinds()[post('kind')]) ? post('kind') : 'normal';
        $data = ['name' => $name, 'description' => post('description'), 'sort_order' => (int)post('sort_order'), 'is_active' => post('is_active') ? 1 : 0, 'kind' => $kind];
        if ($img) $data['image'] = $img;
        elseif (post('remove_image') && $id) { delete_upload((string)val('SELECT image FROM categories WHERE id = ?', [$id])); $data['image'] = ''; }
        if ($id) {
            $old = row('SELECT * FROM categories WHERE id = ?', [$id]);
            if ($img && $old) delete_upload($old['image']);
            if ($old && $old['name'] !== $name) $data['slug'] = unique_slug('categories', $name, $id);
            update('categories', $data, 'id = ?', [$id]);
            flash('success', 'Category updated.');
        } else {
            $data['slug'] = unique_slug('categories', $name);
            insert('categories', $data);
            flash('success', 'Category added.');
        }
    } elseif ($act === 'delete') {
        $id = (int)post('id');
        $c = row('SELECT * FROM categories WHERE id = ?', [$id]);
        if ($c) {
            q('UPDATE products SET category_id = NULL WHERE category_id = ?', [$id]);
            delete_upload($c['image']);
            q('DELETE FROM categories WHERE id = ?', [$id]);
            flash('success', 'Category deleted. Its products are now uncategorised.');
        }
    }
    redirect('categories.php');
}

$cats = rows('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS cnt FROM categories c ORDER BY c.sort_order, c.name');
$c = $edit ?: ['id' => 0, 'name' => '', 'description' => '', 'sort_order' => count($cats), 'is_active' => 1, 'image' => '', 'kind' => 'normal'];
$kindLabel = ['new' => 'Auto: new arrivals', 'sale' => 'Auto: sale items'];
$title = 'Categories'; $active = 'categories';
require __DIR__ . '/includes/top.php';
?>
<form method="post" class="a-card style-card">
  <?= csrf_field() ?><input type="hidden" name="action" value="style">
  <h3>How categories look on the homepage</h3>
  <div class="style-opts">
    <?php $cs = setting('cat_style', 'arch'); foreach ([
      'landscape' => ['Landscape cards', '3 per row · best for wide pictures (e.g. 1536 × 1024)'],
      'square'    => ['Square tiles', 'one row of rounded squares'],
      'arch'      => ['Arches', 'one row, like the original design'],
    ] as $k => [$l, $h]): ?>
      <label class="style-opt"><input type="radio" name="cat_style" value="<?= $k ?>" <?= $cs === $k ? 'checked' : '' ?>>
        <span class="so-prev so-<?= $k ?>"><i></i><i></i><i></i></span><b><?= $l ?></b><small><?= $h ?></small></label>
    <?php endforeach; ?>
  </div>
  <label class="a-fld" style="margin-top:14px"><span>Picture fit</span>
    <select name="cat_fit">
      <option value="contain" <?= setting('cat_fit', 'contain') === 'contain' ? 'selected' : '' ?>>Show the full picture (nothing is cut)</option>
      <option value="cover" <?= setting('cat_fit', 'contain') === 'cover' ? 'selected' : '' ?>>Fill the frame (edges may be cut)</option>
    </select></label>
  <div class="a-row">
    <label class="a-check"><input type="checkbox" name="cat_autoslide" value="1" <?= setting('cat_autoslide', '1') === '1' ? 'checked' : '' ?>> Slide automatically (arches &amp; squares)</label>
    <label class="a-fld"><span>Move every (seconds)</span><input type="number" name="cat_slide_seconds" min="2" max="10" value="<?= e(setting('cat_slide_seconds', 3)) ?>"></label>
  </div>
  <label class="a-fld"><span>Text under “Shop by Category”</span><input name="cat_subtitle" value="<?= e(setting('cat_subtitle', 'Soft, pretty pieces for every moment — find what makes you feel beautiful.')) ?>"></label>
  <button class="a-btn a-btn-primary">Save style</button>
</form>

<div class="a-grid-2 wide-left">
  <section class="a-card no-pad">
    <div class="a-table-wrap"><table class="a-table cat-table">
      <thead><tr><th></th><th>Name</th><th>Products</th><th>Order</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($cats as $r): ?>
        <tr class="<?= $edit && $edit['id'] == $r['id'] ? 'hl' : '' ?>">
          <td><img class="thumb" src="<?= e(img_url($r['image'])) ?>" alt=""></td>
          <td><b><?= e($r['name']) ?></b><br><small class="muted">/<?= e($r['slug']) ?></small></td>
          <td><?= isset($kindLabel[$r['kind'] ?? '']) ? '<span class="sb sb-low">' . $kindLabel[$r['kind']] . '</span>' : (int)$r['cnt'] ?></td>
          <td><?= (int)$r['sort_order'] ?></td>
          <td><?= $r['is_active'] ? '<span class="sb sb-in">Visible</span>' : '<span class="sb sb-out">Hidden</span>' ?></td>
          <td class="row-actions">
            <a class="a-btn a-btn-sm" href="?edit=<?= $r['id'] ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Delete this category? Products will stay but become uncategorised.')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="a-btn a-btn-sm a-btn-danger" name="action" value="delete">Delete</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$cats): ?><tr><td colspan="6" class="a-empty">No categories yet.</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </section>

  <section class="a-card">
    <h3><?= $c['id'] ? 'Edit category' : 'Add new category' ?></h3>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
      <label class="a-fld"><span>Name *</span><input name="name" value="<?= e($c['name']) ?>" required></label>
      <label class="a-fld"><span>Description</span><textarea name="description" rows="3"><?= e($c['description']) ?></textarea></label>
      <label class="a-fld"><span>Display order</span><input type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>"></label>
      <label class="a-fld"><span>Type</span>
        <select name="kind"><?php foreach (category_kinds() as $k => $l): ?><option value="<?= $k ?>" <?= ($c['kind'] ?? 'normal') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
      <p class="a-help">“New arrivals” shows products added in the last <?= new_days() ?> days; “Sale items” shows every product with a sale price — no need to assign products to them.</p>
      <span class="a-fld" style="margin-bottom:6px"><span>Category image</span></span>
      <label class="img-drop cat-drop" data-preview><img src="<?= e(img_url($c['image'])) ?>" alt=""><span>Click to upload. Use the same shape for all categories (wide, square or tall — choose the matching style above). JPG, PNG, WEBP or AVIF.</span><input type="file" name="image" accept="image/*,.avif"></label>
      <?php if ($c['id'] && $c['image']): ?><label class="a-check small"><input type="checkbox" name="remove_image" value="1"> Remove image</label><?php endif; ?>
      <label class="a-check"><input type="checkbox" name="is_active" value="1" <?= $c['is_active'] ? 'checked' : '' ?>> Visible on shop</label>
      <button class="a-btn a-btn-primary a-btn-block"><?= $c['id'] ? 'Save category' : 'Add category' ?></button>
      <?php if ($c['id']): ?><a href="categories.php" class="a-muted-link">Cancel</a><?php endif; ?>
    </form>
  </section>
</div>
<?php require __DIR__ . '/includes/bottom.php'; ?>
