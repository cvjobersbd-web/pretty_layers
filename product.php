<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/partials.php';

$p = row(product_sql() . ' WHERE p.slug = ? AND p.is_active = 1', [get('slug')]);
if (!$p) { http_response_code(404); $pageTitle = 'Not found'; require __DIR__ . '/includes/header.php';
    echo '<section class="container section empty-state"><h3>Sorry, this product is not available.</h3><a class="btn btn-accent" href="' . url('shop.php') . '">Continue shopping</a></section>';
    require __DIR__ . '/includes/footer.php'; exit; }

$variants = rows('SELECT id, size, color, stock FROM variants WHERE product_id = ? ORDER BY id', [$p['id']]);
$gallery = array_merge([$p['image']], array_column(rows('SELECT path FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$p['id']]), 'path'));
$gallery = array_values(array_filter($gallery));
if (!$gallery) $gallery = [''];
$sizeOrder = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
$sizes = array_values(array_unique(array_filter(array_column($variants, 'size'), 'strlen')));
usort($sizes, function ($a, $b) use ($sizeOrder) {
    $ia = array_search(strtoupper($a), $sizeOrder); $ib = array_search(strtoupper($b), $sizeOrder);
    return ($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib) ?: strcmp($a, $b);
});
$colors = array_values(array_unique(array_filter(array_column($variants, 'color'), 'strlen')));
$stock = (int)$p['total_stock'];
$status = stock_status($stock, (int)$p['low_stock']);
$off = discount_pct($p);
$related = rows(product_sql() . ' WHERE p.is_active = 1 AND p.category_id = ? AND p.id <> ? ORDER BY sold DESC LIMIT 4', [$p['category_id'], $p['id']]);
if (count($related) < 4) $related = array_merge($related, rows(product_sql() . ' WHERE p.is_active = 1 AND p.id <> ? AND (p.category_id IS NULL OR p.category_id <> ?) ORDER BY p.is_featured DESC, p.id DESC LIMIT ' . (4 - count($related)), [$p['id'], (int)$p['category_id']]));

$pageTitle = $p['name'];
require __DIR__ . '/includes/header.php';
?>
<section class="container pd-wrap">
  <nav class="crumbs"><a href="<?= url('index.php') ?>">Home</a><span>/</span><a href="<?= url('shop.php?cat=' . urlencode($p['category_slug'] ?? '')) ?>"><?= e($p['category_name'] ?? 'Shop') ?></a><span>/</span><b><?= e($p['name']) ?></b></nav>
  <div class="pd-grid">
    <div class="pd-gallery">
      <div class="pd-main" id="pdMain">
        <img src="<?= e(img_url($gallery[0])) ?>" alt="<?= e($p['name']) ?>" id="pdMainImg">
        <?php if ($off): ?><span class="badge badge-sale pd-badge">-<?= $off ?>%</span><?php endif; ?>
      </div>
      <?php if (count($gallery) > 1): ?>
        <div class="pd-thumbs">
          <?php foreach ($gallery as $i => $g): ?><button class="<?= $i === 0 ? 'on' : '' ?>" data-src="<?= e(img_url($g)) ?>"><img src="<?= e(img_url($g)) ?>" alt=""></button><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="pd-info">
      <span class="p-cat"><?= e($p['category_name'] ?? '') ?></span>
      <h1><?= e($p['name']) ?></h1>
      <div class="pd-meta"><span class="stars">★★★★★</span><span class="muted">SKU: <?= e($p['sku']) ?></span></div>
      <div class="pd-price">
        <?= wish_button((int)$p['id'], 'wish-btn wish-lg') ?>
        <strong><?= money(final_price($p)) ?></strong>
        <?php if ($off): ?><del><?= money($p['price']) ?></del><span class="save">Save <?= money($p['price'] - final_price($p)) ?></span><?php endif; ?>
      </div>
      <?php if ($p['short_desc']): ?><p class="pd-short"><?= e($p['short_desc']) ?></p><?php endif; ?>

      <form method="post" action="<?= url('cart.php') ?>" class="pd-form js-add-cart" id="pdForm" data-variants='<?= e(json_encode($variants)) ?>' data-low="<?= (int)$p['low_stock'] ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="variant_id" id="variantId" value="<?= count($variants) === 1 ? (int)$variants[0]['id'] : '' ?>">

        <?php if ($colors): ?>
          <div class="opt-group"><div class="opt-label">Colour: <b data-chosen="color"><?= count($colors) === 1 ? e($colors[0]) : 'Select' ?></b></div>
            <div class="opts" data-opt="color"><?php foreach ($colors as $c): ?><button type="button" class="opt <?= count($colors) === 1 ? 'on' : '' ?>" data-value="<?= e($c) ?>"><?= e($c) ?></button><?php endforeach; ?></div>
          </div>
        <?php endif; ?>
        <?php if ($sizes): ?>
          <div class="opt-group"><div class="opt-label">Size: <b data-chosen="size"><?= count($sizes) === 1 ? e($sizes[0]) : 'Select' ?></b> <a href="#sizeguide" class="size-guide-link" data-open-tab="tab-size">Size guide</a></div>
            <div class="opts" data-opt="size"><?php foreach ($sizes as $s): ?><button type="button" class="opt opt-size <?= count($sizes) === 1 ? 'on' : '' ?>" data-value="<?= e($s) ?>"><?= e($s) ?></button><?php endforeach; ?></div>
          </div>
        <?php endif; ?>

        <div class="stock-line stock-<?= $status ?>" id="stockLine">
          <?php if ($status === 'out'): ?>Out of stock<?php elseif (count($variants) > 1): ?>Select options to see availability<?php elseif ($status === 'low'): ?>Hurry! Only <?= $stock ?> left in stock<?php else: ?>In stock<?php endif; ?>
        </div>

        <div class="pd-actions">
          <div class="qty"><button type="button" data-qty="-1" aria-label="Decrease">−</button><input type="number" name="qty" id="qtyInput" value="1" min="1" max="<?= max(1, $stock) ?>"><button type="button" data-qty="1" aria-label="Increase">+</button></div>
          <button class="btn btn-accent btn-lg" id="addBtn" <?= $status === 'out' ? 'disabled' : '' ?>>Add to bag</button>
          <button class="btn btn-dark btn-lg" name="buy_now" value="1" id="buyBtn" data-no-ajax <?= $status === 'out' ? 'disabled' : '' ?>>Buy now</button>
        </div>
      </form>

      <ul class="pd-perks">
        <li><span>🚚</span>Delivery in 1–3 days inside Dhaka, 3–5 days outside</li>
        <li><span>💵</span>Cash on delivery available</li>
        <li><span>↺</span>Easy 7-day exchange on unused items</li>
      </ul>
    </div>
  </div>

  <div class="pd-tabs" id="sizeguide">
    <div class="tabs" data-tabs>
      <button class="tab on" data-tab="tab-desc">Description</button>
      <button class="tab" data-tab="tab-size">Size guide</button>
      <button class="tab" data-tab="tab-ship">Delivery &amp; returns</button>
    </div>
    <div class="tab-pane on prose" id="tab-desc"><?= nl2br(e($p['description'] ?: 'No description yet.')) ?></div>
    <div class="tab-pane" id="tab-size">
      <table class="size-table"><thead><tr><th>Size</th><th>Bust (in)</th><th>Waist (in)</th><th>Hip (in)</th><th>Length (in)</th></tr></thead>
        <tbody><tr><td>S</td><td>34</td><td>28</td><td>36</td><td>42</td></tr><tr><td>M</td><td>36</td><td>30</td><td>38</td><td>43</td></tr><tr><td>L</td><td>38</td><td>32</td><td>40</td><td>44</td></tr><tr><td>XL</td><td>40</td><td>34</td><td>42</td><td>45</td></tr></tbody></table>
      <p class="muted">Measurements may vary by ±0.5 inch.</p>
    </div>
    <div class="tab-pane prose" id="tab-ship">
      Inside Dhaka delivery charge: <b><?= money(setting('shipping_inside', 70)) ?></b>. Outside Dhaka: <b><?= money(setting('shipping_outside', 130)) ?></b>.
      <?php if ((float)setting('free_shipping_over') > 0): ?>Free delivery on orders over <b><?= money(setting('free_shipping_over')) ?></b>.<?php endif; ?><br><br>
      Not the right fit? Exchange unused items with original tags within 7 days of delivery. Please contact us at <?= e(setting('phone')) ?>.
    </div>
  </div>

  <?php if ($related): ?>
    <div class="section">
      <?= section_head('You may also like', 'Related Products') ?>
      <div class="p-grid"><?php foreach ($related as $r) echo product_card($r); ?></div>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
