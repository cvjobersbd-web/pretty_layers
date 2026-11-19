<?php
require __DIR__ . '/includes/init.php';

$tab = get('tab', 'general');
if ($tab === 'hero') $tab = 'home';
$presets = theme_presets();

if (is_post()) {
    csrf_check();
    $section = post('section');
    if ($section === 'general') {
        foreach (['site_name', 'tagline', 'announcement'] as $k) set_setting($k, post($k));
        if (setting('site_name') === '') set_setting('site_name', 'Lavanya');
        set_setting('loader_enabled', post('loader_enabled') ? '1' : '0');
        $err = null;
        if ($logo = save_upload($_FILES['logo'] ?? [], 'logo', $err)) { delete_upload(setting('logo')); set_setting('logo', $logo); }
        elseif (post('remove_logo')) { delete_upload(setting('logo')); set_setting('logo', ''); }
        if ($err) flash('error', 'Logo: ' . $err);
        $err2 = null;
        if ($ic = save_upload($_FILES['logo_icon'] ?? [], 'logo', $err2)) { delete_upload(setting('logo_icon')); set_setting('logo_icon', $ic); }
        elseif (post('remove_icon')) { delete_upload(setting('logo_icon')); set_setting('logo_icon', ''); }
        if ($err2) flash('error', 'Icon: ' . $err2);
        flash('success', 'Site name & logo saved.');
    } elseif ($section === 'hero') {
        $keys = ['hero_badge', 'hero_title', 'hero_subtitle', 'hero_card1_title', 'hero_card1_sub', 'hero_card2_title', 'hero_card2_sub',
                 'hero_btn1_text', 'hero_btn1_link', 'hero_btn2_text', 'hero_btn2_link'];
        for ($n = 1; $n <= 3; $n++) { $keys[] = "stat{$n}_num"; $keys[] = "stat{$n}_label"; }
        for ($n = 1; $n <= 4; $n++) { $keys[] = "feat{$n}_title"; $keys[] = "feat{$n}_sub"; }
        foreach ($keys as $k) set_setting($k, post($k));
        set_setting('stats_show', post('stats_show') ? '1' : '0');
        foreach (home_sections() as [, $sk]) set_setting($sk, post($sk) ? '1' : '0');
        // product tabs
        foreach (['products_eyebrow', 'products_title', 'products_btn'] as $k) set_setting($k, post($k));
        set_setting('products_count', (string)max(1, min(24, (int)post('products_count', 8))));
        foreach (array_keys(product_tabs()) as $k) {
            set_setting("tab_{$k}_label", post("tab_{$k}_label"));
            set_setting("tab_{$k}_show", post("tab_{$k}_show") ? '1' : '0');
            set_setting("tab_{$k}_mode", post('all_auto') ? 'auto' : (post("tab_{$k}_mode") === 'manual' ? 'manual' : 'auto'));
            set_setting("tab_{$k}_ids", json_encode(array_values(array_unique(array_map('intval', (array)($_POST["tab_{$k}_ids"] ?? []))))));
        }
        // sale banner + visit store + newsletter texts
        foreach (['sale_eyebrow', 'sale_title', 'sale_highlight', 'sale_text', 'sale_btn_text', 'sale_btn_link', 'sale_end',
                  'visit_eyebrow', 'visit_title', 'visit_text', 'visit_address', 'map_query', 'visit_phone', 'visit_email', 'visit_btn_text', 'visit_btn_link',
                  'nl_eyebrow', 'nl_title', 'nl_text'] as $k) set_setting($k, post($k));
        set_setting('sale_timer_show', post('sale_timer_show') ? '1' : '0');
        set_setting('map_show', post('map_show') ? '1' : '0');
        foreach (['sale_img1' => 'Sale picture 1', 'sale_img2' => 'Sale picture 2', 'store_photo' => 'Store photo'] as $k => $lbl) {
            $ie = null;
            if ($up = save_upload($_FILES[$k] ?? [], 'banners', $ie)) { delete_upload(setting($k)); set_setting($k, $up); }
            elseif (post($k . '_reset')) { delete_upload(setting($k)); set_setting($k, ''); }
            if ($ie) flash('error', "$lbl: $ie");
        }
        // promo cards
        set_setting('promo_style', array_key_exists(post('promo_style'), promo_styles()) ? post('promo_style') : 'arch');
        for ($n = 1; $n <= 3; $n++) {
            foreach (['eyebrow', 'title', 'link_text', 'cat', 'link'] as $k) set_setting("promo{$n}_{$k}", post("promo{$n}_{$k}"));
            set_setting("promo{$n}_show", post("promo{$n}_show") ? '1' : '0');
            $perr = null;
            if ($pimg = save_upload($_FILES["promo{$n}_img"] ?? [], 'banners', $perr)) { delete_upload(setting("promo{$n}_image")); set_setting("promo{$n}_image", $pimg); }
            elseif (post("promo{$n}_reset")) { delete_upload(setting("promo{$n}_image")); set_setting("promo{$n}_image", ''); }
            if ($perr) flash('error', "Card $n image: " . $perr);
        }
        set_setting('hero_cards_show', post('hero_cards_show') ? '1' : '0');
        set_setting('hero_fit', post('hero_fit') === 'contain' ? 'contain' : 'cover');
        set_setting('hero_interval', (string)min(15, max(2, (int)post('hero_interval', 4))));
        // slider images: keep / reorder / remove / add
        $imgs = hero_images();
        $order = (array)($_POST['img_order'] ?? []);
        $remove = array_map('intval', (array)($_POST['img_remove'] ?? []));
        $keep = [];
        foreach ($imgs as $i => $path) {
            if (in_array($i, $remove, true)) { delete_upload($path); continue; }
            $keep[] = ['path' => $path, 'o' => (int)($order[$i] ?? $i + 1)];
        }
        usort($keep, fn($a, $b) => $a['o'] <=> $b['o']);
        $imgs = array_column($keep, 'path');
        $errs = [];
        foreach (files_list($_FILES['hero_new'] ?? []) as $file) {
            $err = null; $path = save_upload($file, 'banners', $err);
            if ($path) $imgs[] = $path; elseif ($err) $errs[] = $file['name'] . ': ' . $err;
        }
        set_setting('hero_images', json_encode(array_values($imgs)));
        if ($errs) flash('error', 'Some images were skipped — ' . implode('; ', $errs));
        flash('success', post('all_auto') ? 'All product tabs now show products automatically.' : 'Home page settings saved.');
    } elseif ($section === 'navbar') {
        foreach (['topbar_left', 'announcement', 'topbar_right', 'topbar_right_link'] as $k) set_setting($k, post($k));
        set_setting('topbar_show', post('topbar_show') ? '1' : '0');
        foreach (array_keys(nav_items()) as $k) {
            set_setting("nav_{$k}_label", post("nav_{$k}_label"));
            set_setting("nav_{$k}_show", post("nav_{$k}_show") ? '1' : '0');
        }
        flash('success', 'Navbar settings saved.');
    } elseif ($section === 'footer') {
        foreach (array_keys(social_networks()) as $k) set_setting("social_{$k}", post("social_{$k}"));
        foreach (['footer_text', 'footer_copyright'] as $k) set_setting($k, post($k));
        set_setting('footer_social_show', post('footer_social_show') ? '1' : '0');
        flash('success', 'Footer settings saved.');
    } elseif ($section === 'colors') {
        $preset = post('theme_preset');
        foreach (['primary', 'accent', 'dark', 'bg', 'text'] as $k) {
            $v = post('color_' . $k);
            if (valid_hex($v)) set_setting('color_' . $k, $v);
        }
        set_setting('theme_preset', isset($presets[$preset]) ? $preset : 'custom');
        flash('success', 'Colour theme updated across the whole site.');
    } elseif ($section === 'store') {
        foreach (['phone', 'email', 'address', 'bkash_number', 'nagad_number', 'currency_symbol'] as $k) set_setting($k, post($k));
        foreach (['shipping_inside', 'shipping_outside', 'free_shipping_over', 'low_stock_default'] as $k) set_setting($k, max(0, (float)post($k)));
        flash('success', 'Store & delivery settings saved.');
    } elseif ($section === 'account') {
        $a = row('SELECT * FROM admins WHERE id = ?', [admin()['id']]);
        $new = (string)($_POST['new_password'] ?? '');
        if (!$a || !password_verify((string)($_POST['current_password'] ?? ''), $a['password'])) flash('error', 'Current password is wrong.');
        elseif (strlen($new) < 6) flash('error', 'New password must be at least 6 characters.');
        elseif ($new !== ($_POST['confirm_password'] ?? '')) flash('error', 'New passwords do not match.');
        else {
            $uname = post('username') ?: $a['username'];
            if ($uname !== $a['username'] && val('SELECT id FROM admins WHERE username = ? AND id <> ?', [$uname, $a['id']])) { flash('error', 'That username is taken.'); }
            else {
                update('admins', ['username' => $uname, 'password' => password_hash($new, PASSWORD_DEFAULT)], 'id = ?', [$a['id']]);
                $_SESSION['admin']['username'] = $uname;
                flash('success', 'Login details updated.');
            }
        }
    }
    redirect('settings.php?tab=' . urlencode($section === 'hero' ? 'home' : ($section ?: 'general')));
}

$s = settings_all(true);
$val = fn($k, $d = '') => e($s[$k] ?? $d);
$title = 'Settings & Design'; $active = 'settings';
require __DIR__ . '/includes/top.php';
?>
<div class="pill-tabs">
  <?php foreach (['general' => 'Name & Logo', 'home' => 'Home Setting', 'navbar' => 'Navbar Setting', 'footer' => 'Footer Setting', 'colors' => 'Colour theme', 'store' => 'Store & Delivery', 'account' => 'Admin account'] as $k => $l): ?>
    <a href="?tab=<?= $k ?>" class="<?= $tab === $k ? 'on' : '' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>

<?php if ($tab === 'general'): ?>
<form method="post" enctype="multipart/form-data" class="a-grid-2 wide-left">
  <?= csrf_field() ?><input type="hidden" name="section" value="general">
  <section class="a-card">
    <h3>Site identity</h3>
    <div class="a-row">
      <label class="a-fld"><span>Site name *</span><input name="site_name" value="<?= $val('site_name', 'Lavanya') ?>" required></label>
      <label class="a-fld"><span>Tagline</span><input name="tagline" value="<?= $val('tagline') ?>"></label>
    </div>
    <label class="a-fld"><span>Announcement bar (top of every page)</span><input name="announcement" value="<?= $val('announcement') ?>" placeholder="Leave empty to hide"></label>
    <p class="a-help">Homepage banner text &amp; images are in the <a href="?tab=hero">Homepage Banner</a> tab.</p>
    <button class="a-btn a-btn-primary">Save changes</button>
  </section>
  <section class="a-card">
    <h3>Logo</h3>
    <div class="logo-preview"><div class="lp-light"><?= logo_html('logo') ?></div><div class="lp-dark"><?= logo_html('logo') ?></div></div>
    <label class="img-drop small" data-preview><img src="<?= e(img_url($s['logo'] ?? '')) ?>" alt="" style="object-fit:contain"><span>Upload logo (PNG with transparent background works best, ~400×120)</span><input type="file" name="logo" accept="image/*,.avif"></label>
    <?php if (!empty($s['logo'])): ?><label class="a-check small"><input type="checkbox" name="remove_logo" value="1"> Remove logo (use site name instead)</label><?php endif; ?>
    <p class="a-help">If no logo is uploaded, the site name is shown as a stylish text logo.</p>
    <h3 class="mt">Icon (loader &amp; browser tab)</h3>
    <label class="img-drop small" data-preview><img src="<?= e(img_url($s['logo_icon'] ?? '')) ?>" alt="" style="object-fit:contain"><span>Small square mark, e.g. just the first letter of your logo</span><input type="file" name="logo_icon" accept="image/*,.avif"></label>
    <?php if (!empty($s['logo_icon'])): ?><label class="a-check small"><input type="checkbox" name="remove_icon" value="1"> Remove icon (use main logo)</label><?php endif; ?>
    <h3 class="mt">Loading animation</h3>
    <label class="a-check"><input type="checkbox" name="loader_enabled" value="1" <?= ($s['loader_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Show logo loading animation</label>
    <p class="a-help">Shows your logo with a spinning ring while pages load. Plays fully on the first visit and very briefly afterwards.</p>
  </section>
</form>

<?php elseif ($tab === 'home'): $himgs = hero_images(); $fd = home_feature_defaults();
      $autoCount = (int)val('SELECT COUNT(*) FROM products WHERE is_active = 1') . '+'; ?>
<p class="a-help">Change any text on the home page. Leave a box <b>empty</b> to use the default text (shown in grey), or type a single <b>-</b> to hide that item.</p>
<form method="post" enctype="multipart/form-data">
<nav class="jump-nav">
  <a href="#hs-sections">Sections on/off</a><a href="#hs-banner">Top banner</a><a href="#hs-numbers">Numbers &amp; boxes</a>
  <a href="#hs-promos">Promo cards</a><a href="#hs-products">Product tabs</a><a href="#hs-sale">Sale banner</a><a href="#hs-visit">Visit store</a><a href="#hs-news">Newsletter</a>
</nav>
<section class="a-card" id="hs-sections">
  <h3>Sections on the home page</h3>
  <p class="a-help">Untick a section to hide it from the home page. Nothing is deleted — tick it again to bring it back.</p>
  <div class="sec-toggles">
    <?php foreach (home_sections() as $k => [$label, $sk]): ?>
      <label class="sec-toggle"><input type="checkbox" name="<?= $sk ?>" value="1" <?= section_on($k) ? 'checked' : '' ?>><span class="sw-ui"></span><b><?= e($label) ?></b></label>
    <?php endforeach; ?>
  </div>
</section>
<div class="a-grid-2 wide-left" id="hs-banner">
  <?= csrf_field() ?><input type="hidden" name="section" value="hero">
  <section class="a-card">
    <h3>Banner slider images</h3>
    <p class="a-help">These pictures rotate inside the big arch on the homepage. Best size: <b>portrait, about 800 × 1000 px</b> (JPG, PNG, WEBP or AVIF, max 5 MB each).
      <?php if (!$himgs): ?><br>No images uploaded yet — the shop is showing your <b>featured products</b> automatically.<?php endif; ?></p>
    <?php if ($himgs): ?>
      <div class="hero-grid">
        <?php foreach ($himgs as $i => $path): ?>
          <div class="hero-item">
            <img src="<?= e(img_url($path)) ?>" alt="">
            <div class="hero-item-bar">
              <label>Order <input type="number" name="img_order[<?= $i ?>]" value="<?= $i + 1 ?>"></label>
              <label class="hero-rm"><input type="checkbox" name="img_remove[]" value="<?= $i ?>"> Remove</label>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <label class="upload-zone">
      <input type="file" name="hero_new[]" accept="image/*,.avif" multiple id="heroNew">
      <span class="uz-ico">⬆</span>
      <b>Click to add banner images</b>
      <small>You can select several pictures at once (hold Ctrl)</small>
      <span class="uz-files" id="heroNewList"></span>
    </label>
    <div class="a-row">
      <label class="a-fld"><span>Picture fit</span>
        <select name="hero_fit">
          <option value="cover" <?= ($s['hero_fit'] ?? 'cover') === 'cover' ? 'selected' : '' ?>>Fill the arch (edges may be cropped)</option>
          <option value="contain" <?= ($s['hero_fit'] ?? '') === 'contain' ? 'selected' : '' ?>>Show the whole picture</option>
        </select></label>
      <label class="a-fld"><span>Change slide every (seconds)</span><input type="number" name="hero_interval" min="2" max="15" value="<?= $val('hero_interval', 4) ?>"></label>
    </div>
    <button class="a-btn a-btn-primary">Save home page</button>
  </section>

  <section class="a-card">
    <h3>Banner text</h3>
    <label class="a-fld"><span>Small badge text</span><input name="hero_badge" value="<?= $val('hero_badge') ?>"></label>
    <label class="a-fld"><span>Big headline</span><input name="hero_title" value="<?= $val('hero_title') ?>"></label>
    <label class="a-fld"><span>Sub text</span><textarea name="hero_subtitle" rows="3"><?= $val('hero_subtitle') ?></textarea></label>
    <h3 class="mt">Buttons</h3>
    <div class="a-row">
      <label class="a-fld"><span>Button 1 text</span><input name="hero_btn1_text" value="<?= $val('hero_btn1_text') ?>" placeholder="Shop the collection"></label>
      <label class="a-fld"><span>Button 1 link</span><input name="hero_btn1_link" value="<?= $val('hero_btn1_link') ?>" placeholder="shop.php"></label>
    </div>
    <div class="a-row">
      <label class="a-fld"><span>Button 2 text</span><input name="hero_btn2_text" value="<?= $val('hero_btn2_text') ?>" placeholder="New arrivals"></label>
      <label class="a-fld"><span>Button 2 link</span><input name="hero_btn2_link" value="<?= $val('hero_btn2_link') ?>" placeholder="shop.php?new=1&sort=new"></label>
    </div>
    <h3 class="mt">Floating cards</h3>
    <label class="a-check"><input type="checkbox" name="hero_cards_show" value="1" <?= ($s['hero_cards_show'] ?? '0') === '1' ? 'checked' : '' ?>> Show the two small cards on the picture</label>
    <div class="a-row">
      <label class="a-fld"><span>Card 1 title</span><input name="hero_card1_title" value="<?= $val('hero_card1_title') ?>" placeholder="Handpicked"></label>
      <label class="a-fld"><span>Card 1 sub text</span><input name="hero_card1_sub" value="<?= $val('hero_card1_sub') ?>" placeholder="Premium fabrics"></label>
    </div>
    <div class="a-row">
      <label class="a-fld"><span>Card 2 title</span><input name="hero_card2_title" value="<?= $val('hero_card2_title') ?>" placeholder="Auto: Up to X% off"></label>
      <label class="a-fld"><span>Card 2 sub text</span><input name="hero_card2_sub" value="<?= $val('hero_card2_sub') ?>" placeholder="This season only"></label>
    </div>
    <p class="a-help">Leave a field empty to use the default text shown in grey.</p>
    <a href="../index.php" target="_blank" class="a-btn a-btn-ghost">View homepage ↗</a>
  </section>
</div>

<div class="a-grid-2" id="hs-numbers">
  <section class="a-card">
    <h3>Numbers under the banner</h3>
    <label class="a-check"><input type="checkbox" name="stats_show" value="1" <?= ($s['stats_show'] ?? '1') === '1' ? 'checked' : '' ?>> Show these numbers</label>
    <?php foreach ([[$autoCount . ' (automatic)', 'Unique styles'], ['64', 'Districts delivered'], ['4.9★', 'Happy customers']] as $i => [$dn, $dl]): $n = $i + 1; ?>
      <div class="a-row a-row-num">
        <label class="a-fld"><span>Number <?= $n ?></span><input name="stat<?= $n ?>_num" value="<?= $val("stat{$n}_num") ?>" placeholder="<?= e($dn) ?>"></label>
        <label class="a-fld"><span>Text <?= $n ?></span><input name="stat<?= $n ?>_label" value="<?= $val("stat{$n}_label") ?>" placeholder="<?= e($dl) ?>"></label>
      </div>
    <?php endforeach; ?>
  </section>
  <section class="a-card">
    <h3>Feature boxes (4 boxes under the banner)</h3>
    <?php foreach ($fd as $i => [$dt, $dsub]): $n = $i + 1; ?>
      <div class="a-row">
        <label class="a-fld"><span>Box <?= $n ?> title</span><input name="feat<?= $n ?>_title" value="<?= $val("feat{$n}_title") ?>" placeholder="<?= e($dt) ?>"></label>
        <label class="a-fld"><span>Box <?= $n ?> small text</span><input name="feat<?= $n ?>_sub" value="<?= $val("feat{$n}_sub") ?>" placeholder="<?= e($dsub) ?>"></label>
      </div>
    <?php endforeach; ?>
  </section>
</div>
<?php $pcats = rows('SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, name'); $ps = setting('promo_style', 'arch'); ?>
<section class="a-card promo-admin" id="hs-promos">
  <div class="a-card-head"><h3>Promo cards (3 picture cards under “Shop by Category”)</h3>
    </div>
  <span class="a-fld" style="margin-bottom:8px"><span>Design style</span></span>
  <div class="pstyle-grid">
    <?php foreach (promo_styles() as $k => $l): ?>
      <label class="pstyle"><input type="radio" name="promo_style" value="<?= $k ?>" <?= $ps === $k ? 'checked' : '' ?>>
        <span class="pst pst-<?= $k ?>"><i class="pst-img"></i><i class="pst-t1"></i><i class="pst-t2"></i><i class="pst-t3"></i></span><b><?= $l ?></b></label>
    <?php endforeach; ?>
  </div>
  <div class="promo-cols">
    <?php for ($n = 1; $n <= 3; $n++): $pr = promo($n); $pd = promo_defaults()[$n]; ?>
      <div class="promo-col">
        <div class="promo-col-head"><b>Card <?= $n ?></b><label class="a-check" style="margin:0"><input type="checkbox" name="promo<?= $n ?>_show" value="1" <?= $pr['show'] ? 'checked' : '' ?>> Show</label></div>
        <label class="img-drop promo-drop" data-preview><img src="<?= e(img_url($pr['image'])) ?>" alt=""><span>Click to change picture</span><input type="file" name="promo<?= $n ?>_img" accept="image/*,.avif"></label>
        <?php if (setting("promo{$n}_image") !== ''): ?><label class="a-check small"><input type="checkbox" name="promo<?= $n ?>_reset" value="1"> Use the default picture</label><?php endif; ?>
        <label class="a-fld"><span>Small top text</span><input name="promo<?= $n ?>_eyebrow" value="<?= $val("promo{$n}_eyebrow") ?>" placeholder="<?= e($pd['eyebrow']) ?>"></label>
        <label class="a-fld"><span>Title <small class="muted">(Enter = new line)</small></span><textarea name="promo<?= $n ?>_title" rows="2" placeholder="<?= e($pd['title']) ?>"><?= $val("promo{$n}_title") ?></textarea></label>
        <label class="a-fld"><span>Link text</span><input name="promo<?= $n ?>_link_text" value="<?= $val("promo{$n}_link_text") ?>" placeholder="<?= e($pd['link_text']) ?>"></label>
        <label class="a-fld"><span>Opens category</span>
          <select name="promo<?= $n ?>_cat"><option value="">All products</option>
            <?php foreach ($pcats as $c): ?><option value="<?= e($c['slug']) ?>" <?= $pr['cat'] === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
        <label class="a-fld"><span>…or custom link <small class="muted">(optional)</small></span><input name="promo<?= $n ?>_link" value="<?= $val("promo{$n}_link") ?>" placeholder="e.g. product.php?slug=..."></label>
      </div>
    <?php endfor; ?>
  </div>
</section>
<?php $allProds = rows("SELECT p.id, p.name, p.image, p.is_featured, c.name AS cat FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1 ORDER BY p.name"); ?>
<section class="a-card" id="hs-products">
  <div class="a-card-head"><h3>Product tabs — “<?= e(setting('products_title', 'Loved by Women Like You')) ?>”</h3>
    <button class="a-btn a-btn-sm" name="all_auto" value="1" onclick="return confirm('Show products automatically in all tabs? (Your picked products are remembered.)')">⚡ Make all tabs automatic</button></div>
  <div class="a-row a-row-3">
    <label class="a-fld"><span>Small top text</span><input name="products_eyebrow" value="<?= $val('products_eyebrow') ?>" placeholder="Our collection"></label>
    <label class="a-fld"><span>Title</span><input name="products_title" value="<?= $val('products_title') ?>" placeholder="Loved by Women Like You"></label>
    <label class="a-fld"><span>Products per tab (automatic)</span><input type="number" min="1" max="24" name="products_count" value="<?= $val('products_count', 8) ?>"></label>
  </div>
  <p class="a-help"><b>Automatic</b> = New Arrivals shows the newest products, Featured shows products marked ★, Best Sellers shows the most sold, On Sale shows products with a sale price.
    <b>Choose products</b> = only the products you tick are shown.</p>
  <div class="tab-editors">
  <?php foreach (product_tabs() as $k => $def): $mode = setting("tab_{$k}_mode", 'auto'); $sel = tab_ids($k); ?>
    <div class="tab-ed" data-tab-ed>
      <div class="tab-ed-head">
        <label class="a-check" style="margin:0"><input type="checkbox" name="tab_<?= $k ?>_show" value="1" <?= setting("tab_{$k}_show", '1') === '1' ? 'checked' : '' ?>></label>
        <input class="tab-name" name="tab_<?= $k ?>_label" value="<?= $val("tab_{$k}_label") ?>" placeholder="<?= e($def) ?>">
        <div class="mode-switch">
          <label><input type="radio" name="tab_<?= $k ?>_mode" value="auto" <?= $mode !== 'manual' ? 'checked' : '' ?>><span>⚡ Automatic</span></label>
          <label><input type="radio" name="tab_<?= $k ?>_mode" value="manual" <?= $mode === 'manual' ? 'checked' : '' ?>><span>✋ Choose products</span></label>
        </div>
        <span class="pick-count"><b data-count><?= count($sel) ?></b> picked</span>
      </div>
      <div class="picker">
        <input type="search" class="pick-search" placeholder="Search products…">
        <div class="pick-list">
          <?php foreach ($allProds as $pp): ?>
            <label class="pick" data-name="<?= e(strtolower($pp['name'] . ' ' . $pp['cat'])) ?>"><input type="checkbox" name="tab_<?= $k ?>_ids[]" value="<?= $pp['id'] ?>" <?= in_array((int)$pp['id'], $sel, true) ? 'checked' : '' ?>>
              <img src="<?= e(img_url($pp['image'])) ?>" alt="" loading="lazy"><span><b><?= e($pp['name']) ?></b><small><?= e($pp['cat'] ?? '') ?><?= $pp['is_featured'] ? ' · ★' : '' ?></small></span></label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <label class="a-fld" style="max-width:360px;margin-top:14px"><span>Button text under the products</span><input name="products_btn" value="<?= $val('products_btn') ?>" placeholder="View all products"></label>
</section>

<div class="a-grid-2" id="hs-sale">
  <section class="a-card">
    <h3>Sale banner — text &amp; timer</h3>
    <div class="a-row">
      <label class="a-fld"><span>Small top text</span><input name="sale_eyebrow" value="<?= $val('sale_eyebrow') ?>" placeholder="Limited time"></label>
      <label class="a-fld"><span>Title</span><input name="sale_title" value="<?= $val('sale_title') ?>" placeholder="Season Sale"></label>
    </div>
    <label class="a-fld"><span>Highlighted line (italic)</span><input name="sale_highlight" value="<?= $val('sale_highlight') ?>" placeholder="up to 10% off (automatic from your sale prices)"></label>
    <label class="a-fld"><span>Description</span><textarea name="sale_text" rows="2" placeholder="Your favourite night suits, lingerie and dresses at the sweetest prices. Hurry — stock is limited!"><?= $val('sale_text') ?></textarea></label>
    <label class="a-check"><input type="checkbox" name="sale_timer_show" value="1" <?= setting('sale_timer_show', '1') === '1' ? 'checked' : '' ?>> Show the countdown timer</label>
    <label class="a-fld"><span>Sale ends on <small class="muted">(empty = end of this month; the timer hides itself when time is up)</small></span>
      <input type="datetime-local" name="sale_end" value="<?= e(setting('sale_end') ? date('Y-m-d\TH:i', strtotime(setting('sale_end'))) : '') ?>"></label>
    <div class="a-row">
      <label class="a-fld"><span>Button text</span><input name="sale_btn_text" value="<?= $val('sale_btn_text') ?>" placeholder="Shop the sale"></label>
      <label class="a-fld"><span>Button link</span><input name="sale_btn_link" value="<?= $val('sale_btn_link') ?>" placeholder="shop.php?cat=sale"></label>
    </div>
  </section>
  <section class="a-card">
    <h3>Sale banner — pictures</h3>
    <div class="two-pics">
      <?php foreach (['sale_img1' => ['Picture 1 (left)', 'assets/img/catalog/pl-6fd6d88f.jpg'], 'sale_img2' => ['Picture 2 (right)', 'assets/img/catalog/pl-1461cf1f.jpg']] as $k => [$lbl, $def]): ?>
        <div>
          <label class="img-drop" data-preview><img src="<?= e(img_url(setting($k, $def))) ?>" alt=""><span><?= $lbl ?> — click to change</span><input type="file" name="<?= $k ?>" accept="image/*,.avif"></label>
          <?php if (setting($k) !== ''): ?><label class="a-check small"><input type="checkbox" name="<?= $k ?>_reset" value="1"> Use default picture</label><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<div class="a-grid-2 wide-left" id="hs-visit">
  <section class="a-card">
    <h3>Visit Our Store — text &amp; location</h3>
    <div class="a-row">
      <label class="a-fld"><span>Small top text</span><input name="visit_eyebrow" value="<?= $val('visit_eyebrow') ?>" placeholder="Come say hello"></label>
      <label class="a-fld"><span>Title</span><input name="visit_title" value="<?= $val('visit_title') ?>" placeholder="Visit Our Outlet in Chittagong!"></label>
    </div>
    <label class="a-fld"><span>Description</span><textarea name="visit_text" rows="2" placeholder="Try on your favourite pieces, get help finding the perfect size, and discover new arrivals before they go online."><?= $val('visit_text') ?></textarea></label>
    <label class="a-fld"><span>📍 Address <small class="muted">(Enter = new line)</small></span><textarea name="visit_address" rows="2" placeholder="<?= e(setting('address')) ?>"><?= $val('visit_address') ?></textarea></label>
    <label class="a-fld"><span>🗺 Map location <small class="muted">(what you would type in Google Maps — or - to hide the map)</small></span><input name="map_query" value="<?= $val('map_query') ?>" placeholder="Khulshi Town Center, Chittagong"></label>
    <label class="a-check"><input type="checkbox" name="map_show" value="1" <?= setting('map_show', '1') === '1' ? 'checked' : '' ?>> Show the map on the home page</label>
    <div class="a-row">
      <label class="a-fld"><span>📞 Phone</span><input name="visit_phone" value="<?= $val('visit_phone') ?>" placeholder="<?= e(setting('phone')) ?>"></label>
      <label class="a-fld"><span>✉ Email</span><input name="visit_email" value="<?= $val('visit_email') ?>" placeholder="<?= e(setting('email')) ?>"></label>
    </div>
    <div class="a-row">
      <label class="a-fld"><span>Button text</span><input name="visit_btn_text" value="<?= $val('visit_btn_text') ?>" placeholder="Shop online"></label>
      <label class="a-fld"><span>Button link</span><input name="visit_btn_link" value="<?= $val('visit_btn_link') ?>" placeholder="shop.php"></label>
    </div>
  </section>
  <section class="a-card">
    <h3>Store photo</h3>
    <label class="img-drop" data-preview><img src="<?= e(img_url(setting('store_photo', 'assets/img/catalog/pl-287f9bb8.jpg'))) ?>" alt=""><span>Click to change (portrait photo works best)</span><input type="file" name="store_photo" accept="image/*,.avif"></label>
    <?php if (setting('store_photo') !== ''): ?><label class="a-check small"><input type="checkbox" name="store_photo_reset" value="1"> Use default photo</label><?php endif; ?>
  </section>
</div>

<section class="a-card" id="hs-news">
  <h3>Newsletter box</h3>
  <div class="a-row a-row-3">
    <label class="a-fld"><span>Small top text</span><input name="nl_eyebrow" value="<?= $val('nl_eyebrow') ?>" placeholder="Stay in style"></label>
    <label class="a-fld"><span>Title</span><input name="nl_title" value="<?= $val('nl_title') ?>" placeholder="Join our circle & get 10% off"></label>
    <label class="a-fld"><span>Description</span><input name="nl_text" value="<?= $val('nl_text') ?>" placeholder="Be the first to hear about new arrivals, festive drops and members-only offers."></label>
  </div>
</section>

<div class="save-bar"><button class="a-btn a-btn-primary">Save home page</button><a href="../index.php" target="_blank" class="a-btn a-btn-ghost">View homepage ↗</a></div>
</form>

<?php elseif ($tab === 'navbar'): ?>
<p class="a-help">Change the top bar and the menu. Leave a box <b>empty</b> to use the default text (shown in grey).</p>
<form method="post">
  <?= csrf_field() ?><input type="hidden" name="section" value="navbar">
  <div class="a-grid-2">
    <section class="a-card">
      <h3>Top bar (thin bar above the menu)</h3>
      <label class="a-check"><input type="checkbox" name="topbar_show" value="1" <?= ($s['topbar_show'] ?? '1') === '1' ? 'checked' : '' ?>> Show the top bar</label>
      <label class="a-fld"><span>Left text (phone number)</span><input name="topbar_left" value="<?= $val('topbar_left') ?>" placeholder="<?= e(setting('phone')) ?>"></label>
      <label class="a-fld"><span>Middle text (announcement)</span><input name="announcement" value="<?= $val('announcement') ?>" placeholder="Free delivery on orders over ৳3000 • Cash on delivery all over Bangladesh"></label>
      <div class="a-row">
        <label class="a-fld"><span>Right link text (- to hide)</span><input name="topbar_right" value="<?= $val('topbar_right') ?>" placeholder="Track order"></label>
        <label class="a-fld"><span>Right link goes to</span><input name="topbar_right_link" value="<?= $val('topbar_right_link') ?>" placeholder="track.php"></label>
      </div>
    </section>
    <section class="a-card">
      <h3>Menu items</h3>
      <p class="a-help">Rename a menu item or untick it to hide it.</p>
      <div class="nav-rows">
        <?php foreach (nav_items() as $k => [$def]): ?>
          <div class="nav-row">
            <label class="a-check"><input type="checkbox" name="nav_<?= $k ?>_show" value="1" <?= nav_show($k) ? 'checked' : '' ?>></label>
            <input name="nav_<?= $k ?>_label" value="<?= $val("nav_{$k}_label") ?>" placeholder="<?= e($def) ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
  <div class="save-bar"><button class="a-btn a-btn-primary">Save navbar</button><a href="../index.php" target="_blank" class="a-btn a-btn-ghost">View site ↗</a></div>
</form>

<?php elseif ($tab === 'footer'): ?>
<form method="post">
  <?= csrf_field() ?><input type="hidden" name="section" value="footer">
  <div class="a-grid-2 wide-left">
    <section class="a-card">
      <h3>Social media links</h3>
      <p class="a-help">Paste the link of each page you have. Only the ones you fill in are shown in the footer and in the mobile menu — leave the others empty.</p>
      <label class="a-check"><input type="checkbox" name="footer_social_show" value="1" <?= setting('footer_social_show', '1') === '1' ? 'checked' : '' ?>> Show social icons in the footer</label>
      <div class="social-fields">
        <?php foreach (social_networks() as $k => [$label, $ph, $svg]): $cur = setting("social_{$k}", in_array($k, ['facebook', 'instagram'], true) && setting($k) !== '#' ? setting($k) : ''); ?>
          <label class="social-fld"><span class="soc-ico soc-<?= $k ?>"><?= $svg ?></span>
            <span class="soc-in"><b><?= e($label) ?></b><input name="social_<?= $k ?>" value="<?= e($cur) ?>" placeholder="<?= e($ph) ?>"></span></label>
        <?php endforeach; ?>
      </div>
      <p class="a-help">For WhatsApp you can type just the number (e.g. 01805531038) — the chat link is made automatically.</p>
    </section>
    <section class="a-card">
      <h3>Footer text</h3>
      <label class="a-fld"><span>About text (under the logo)</span><textarea name="footer_text" rows="4" placeholder="<?= e(setting('tagline')) ?>. Thoughtfully designed clothing and accessories for every woman — delivered to your doorstep anywhere in Bangladesh."><?= $val('footer_text') ?></textarea></label>
      <label class="a-fld"><span>Copyright line <small class="muted">({year} = current year)</small></span><input name="footer_copyright" value="<?= $val('footer_copyright') ?>" placeholder="© {year} <?= e(setting('site_name')) ?>. All rights reserved."></label>
      <p class="a-help">Phone, email and address in the footer come from <a href="?tab=store">Store &amp; Delivery</a>.</p>
      <h3 class="mt">Preview</h3>
      <div class="footer-preview"><?= social_icons_html('fp-icons') ?: '<span class="muted">No social links yet</span>' ?></div>
    </section>
  </div>
  <div class="save-bar"><button class="a-btn a-btn-primary">Save footer</button><a href="../index.php#" target="_blank" class="a-btn a-btn-ghost">View site ↗</a></div>
</form>

<?php elseif ($tab === 'colors'): ?>
<form method="post" class="a-grid-2 wide-left" id="themeForm">
  <?= csrf_field() ?><input type="hidden" name="section" value="colors">
  <section class="a-card">
    <h3>Ready-made colour combinations</h3>
    <div class="preset-grid">
      <?php foreach ($presets as $k => $pr): ?>
        <label class="preset">
          <input type="radio" name="theme_preset" value="<?= $k ?>" <?= ($s['theme_preset'] ?? 'pretty') === $k ? 'checked' : '' ?> data-colors='<?= e(json_encode($pr)) ?>'>
          <span class="sw"><i style="background:<?= $pr['primary'] ?>"></i><i style="background:<?= $pr['accent'] ?>"></i><i style="background:<?= $pr['dark'] ?>"></i><i style="background:<?= $pr['bg'] ?>"></i></span>
          <b><?= e($pr['name']) ?></b>
        </label>
      <?php endforeach; ?>
      <label class="preset"><input type="radio" name="theme_preset" value="custom" <?= ($s['theme_preset'] ?? '') === 'custom' ? 'checked' : '' ?>><span class="sw sw-custom">🎨</span><b>Custom</b></label>
    </div>
    <h3 class="mt">Fine-tune colours</h3>
    <div class="color-grid">
      <?php foreach (['primary' => ['Main colour', 'Logo circle, highlights, soft backgrounds'], 'accent' => ['Accent / button colour', 'Buttons, prices, sale badges'], 'dark' => ['Dark colour', 'Headings, top bar, footer'], 'bg' => ['Background', 'Page background'], 'text' => ['Text colour', 'Normal text']] as $k => [$l, $h]): ?>
        <label class="color-fld"><input type="color" name="color_<?= $k ?>" value="<?= $val('color_' . $k, $presets['pretty'][$k]) ?>" data-var="<?= $k ?>"><span><b><?= $l ?></b><small><?= $h ?></small><code><?= $val('color_' . $k) ?></code></span></label>
      <?php endforeach; ?>
    </div>
    <button class="a-btn a-btn-primary">Apply colours to site</button>
  </section>
  <section class="a-card">
    <h3>Live preview</h3>
    <div class="theme-preview" id="themePreview">
      <div class="tp-top">Free delivery over ৳3000</div>
      <div class="tp-head"><span class="tp-logo"><?php if (!empty($s['logo'])): ?><img src="<?= e(img_url($s['logo'])) ?>" alt="" style="height:34px"><?php else: ?><i><?= e(mb_substr($s['site_name'] ?? 'L', 0, 1)) ?></i><?= $val('site_name') ?><?php endif; ?></span><span class="tp-nav">Home · Shop · Sale</span></div>
      <div class="tp-hero"><small>NEW SEASON</small><h4>Elegance, Woven With Love</h4><span class="tp-btn">Shop now</span><span class="tp-btn ghost">Offers</span></div>
      <div class="tp-cards"><div><span class="tp-img"></span><b>Kurti</b><em>৳ 1,250</em></div><div><span class="tp-img"></span><b>Gown</b><em>৳ 2,990</em></div></div>
      <div class="tp-foot">© Footer</div>
    </div>
  </section>
</form>

<?php elseif ($tab === 'store'): ?>
<form method="post" class="a-grid-2">
  <?= csrf_field() ?><input type="hidden" name="section" value="store">
  <section class="a-card">
    <h3>Contact</h3>
    <label class="a-fld"><span>Phone</span><input name="phone" value="<?= $val('phone') ?>"></label>
    <label class="a-fld"><span>Email</span><input name="email" value="<?= $val('email') ?>"></label>
    <label class="a-fld"><span>Address</span><input name="address" value="<?= $val('address') ?>"></label>
    <p class="a-help">Social media links are in <a href="?tab=footer">Footer Setting</a>.</p>
  </section>
  <section class="a-card">
    <h3>Delivery charges</h3>
    <div class="a-row"><label class="a-fld"><span>Inside Dhaka</span><input type="number" min="0" name="shipping_inside" value="<?= $val('shipping_inside', 70) ?>"></label><label class="a-fld"><span>Outside Dhaka</span><input type="number" min="0" name="shipping_outside" value="<?= $val('shipping_outside', 130) ?>"></label></div>
    <label class="a-fld"><span>Free delivery when order is over (0 = never)</span><input type="number" min="0" name="free_shipping_over" value="<?= $val('free_shipping_over', 0) ?>"></label>
    <h3 class="mt">Payments</h3>
    <div class="a-row"><label class="a-fld"><span>bKash number (empty = off)</span><input name="bkash_number" value="<?= $val('bkash_number') ?>"></label><label class="a-fld"><span>Nagad number (empty = off)</span><input name="nagad_number" value="<?= $val('nagad_number') ?>"></label></div>
    <div class="a-row"><label class="a-fld"><span>Currency symbol</span><input name="currency_symbol" value="<?= $val('currency_symbol', '৳') ?>"></label><label class="a-fld"><span>Default low-stock alert</span><input type="number" min="0" name="low_stock_default" value="<?= $val('low_stock_default', 5) ?>"></label></div>
    <button class="a-btn a-btn-primary">Save settings</button>
  </section>
</form>

<?php else: ?>
<form method="post" class="a-card" style="max-width:520px">
  <?= csrf_field() ?><input type="hidden" name="section" value="account">
  <h3>Change login details</h3>
  <label class="a-fld"><span>Username</span><input name="username" value="<?= e(admin_name()) ?>"></label>
  <label class="a-fld"><span>Current password *</span><input type="password" name="current_password" required></label>
  <label class="a-fld"><span>New password *</span><input type="password" name="new_password" minlength="6" required></label>
  <label class="a-fld"><span>Confirm new password *</span><input type="password" name="confirm_password" minlength="6" required></label>
  <button class="a-btn a-btn-primary">Update login</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/includes/bottom.php'; ?>
