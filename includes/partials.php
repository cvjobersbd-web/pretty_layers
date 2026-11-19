<?php
/** Re-usable storefront snippets */

function product_card(array $p): string
{
    $link = url('product.php?slug=' . urlencode($p['slug']));
    $stock = (int)$p['total_stock'];
    $status = stock_status($stock, (int)$p['low_stock']);
    $off = discount_pct($p);
    $isNew = strtotime($p['created_at']) > time() - 86400 * 14;
    ob_start(); ?>
  <article class="p-card reveal">
    <?= wish_button((int)$p['id']) ?>
    <a class="p-media" href="<?= $link ?>">
      <img src="<?= e(img_url($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
      <div class="p-badges">
        <?php if ($off): ?><span class="badge badge-sale">-<?= $off ?>%</span><?php endif; ?>
        <?php if ($isNew): ?><span class="badge badge-new">New</span><?php endif; ?>
        <?php if ($status === 'out'): ?><span class="badge badge-out">Sold out</span><?php endif; ?>
      </div>
      <?php if ($status === 'low'): ?><span class="p-low">Only <?= $stock ?> left</span><?php endif; ?>
    </a>
    <div class="p-body">
      <span class="p-cat"><?= e($p['category_name'] ?? '') ?></span>
      <h3 class="p-title"><a href="<?= $link ?>"><?= e($p['name']) ?></a></h3>
      <div class="p-foot">
        <div class="price">
          <strong><?= money(final_price($p)) ?></strong>
          <?php if ($off): ?><del><?= money($p['price']) ?></del><?php endif; ?>
        </div>
        <?php if ($status === 'out'): ?>
          <span class="p-btn disabled" title="Out of stock">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M6 6l12 12"/></svg></span>
        <?php elseif ((int)$p['variant_count'] === 1): ?>
          <form method="post" action="<?= url('cart.php') ?>" class="js-add-cart">
            <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="variant_id" value="<?= (int)$p['first_variant'] ?>"><input type="hidden" name="qty" value="1">
            <button class="p-btn" title="Add to bag" aria-label="Add to bag"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 8h14l-1.2 11.2a2 2 0 0 1-2 1.8H8.2a2 2 0 0 1-2-1.8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/><path d="M12 12v5M9.5 14.5h5"/></svg></button>
          </form>
        <?php else: ?>
          <a class="p-btn" href="<?= $link ?>" title="Choose size" aria-label="Choose size"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        <?php endif; ?>
      </div>
    </div>
  </article>
<?php return ob_get_clean();
}

function section_head(string $eyebrow, string $title, string $sub = ''): string
{
    return '<div class="sec-head reveal"><span class="eyebrow">' . e($eyebrow) . '</span><h2>' . e($title) . '</h2>'
        . ($sub ? '<p>' . e($sub) . '</p>' : '') . '<span class="flourish"><i></i><b></b><i></i></span></div>';
}
