<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/partials.php';

$base = product_sql() . ' WHERE p.is_active = 1';
$featured = rows($base . ' AND p.is_featured = 1 ORDER BY p.id DESC LIMIT 8');
$newest   = rows($base . ' ORDER BY p.created_at DESC, p.id DESC LIMIT 8');
$cats     = categories_with_counts();
$heroImgs = hero_images() ?: array_slice(array_filter(array_column($featured ?: $newest, 'image')), 0, 4);
$heroFit = setting('hero_fit', 'cover') === 'contain' ? 'fit-contain' : '';
if (!$heroImgs) $heroImgs = ['assets/img/products/gown-tiered-lavender.svg'];
$maxOff = (int)val("SELECT MAX(ROUND(100 - sale_price * 100 / price)) FROM products WHERE is_active = 1 AND sale_price > 0 AND sale_price < price AND price > 0");
$catBySlug = array_column($cats, null, 'slug');
$pick = fn($slug, $fallback) => $catBySlug[$slug] ?? $fallback;
$saleEndRaw = setting('sale_end');
$saleEnds = ($saleEndRaw !== '' && strtotime($saleEndRaw)) ? strtotime($saleEndRaw) : strtotime(date('Y-m-t 23:59:59'));
$pageTitle = null;
require __DIR__ . '/includes/header.php';
?>

<?php if (section_on('hero')): ?>
<!-- ============ HERO ============ -->
<section class="hero">
  <div class="hero-deco d1"></div><div class="hero-deco d2"></div>
  <div class="container hero-in">
    <div class="hero-copy">
      <span class="hero-badge"><i></i><?= e(setting('hero_badge', 'New Season Collection')) ?></span>
      <h1><?= e(setting('hero_title', 'Elegance, Woven With Love')) ?></h1>
      <p><?= e(setting('hero_subtitle')) ?></p>
      <div class="hero-cta">
        <?php if (setting('hero_btn1_text', 'Shop the collection') !== '-'): ?><a href="<?= e(setting('hero_btn1_link', url('shop.php'))) ?>" class="btn btn-accent btn-lg"><?= e(setting('hero_btn1_text', 'Shop the collection')) ?></a><?php endif; ?>
        <?php if (setting('hero_btn2_text', 'New arrivals') !== '-'): ?><a href="<?= e(setting('hero_btn2_link', url('shop.php?new=1&sort=new'))) ?>" class="btn btn-ghost btn-lg"><?= e(setting('hero_btn2_text', 'New arrivals')) ?></a><?php endif; ?>
      </div>
      <?php if (setting('stats_show', '1') === '1'): $autoCount = (int)val('SELECT COUNT(*) FROM products WHERE is_active = 1') . '+'; ?>
      <div class="hero-stats">
        <?php foreach ([[$autoCount, 'Unique styles'], ['64', 'Districts delivered'], ['4.9★', 'Happy customers']] as $i => [$dn, $dl]): $n = $i + 1;
          $num = setting("stat{$n}_num", $dn); $lab = setting("stat{$n}_label", $dl); if ($num === '-') continue; ?>
          <div><b><?= e($num) ?></b><span><?= e($lab) ?></span></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="hero-art">
      <div class="arch arch-back"></div>
      <div class="arch arch-main <?= $heroFit ?>" data-interval="<?= (int)setting('hero_interval', 4) * 1000 ?>">
        <?php foreach ($heroImgs as $i => $im): ?>
          <img src="<?= e(img_url($im)) ?>" alt="" class="hero-slide <?= $i === 0 ? 'on' : '' ?>">
        <?php endforeach; ?>
      </div>
      <?php if (setting('hero_cards_show', '0') === '1'): ?>
      <div class="float-card fc1">
        <span class="fc-ico">✿</span><div><b><?= e(setting('hero_card1_title', 'Handpicked')) ?></b><small><?= e(setting('hero_card1_sub', 'Premium fabrics')) ?></small></div>
      </div>
      <div class="float-card fc2">
        <span class="fc-ico fc-red">%</span><div><b><?= e(setting('hero_card2_title', 'Up to ' . max($maxOff, 10) . '% off')) ?></b><small><?= e(setting('hero_card2_sub', 'This season only')) ?></small></div>
      </div>
      <?php endif; ?>
      <div class="hero-dots" id="heroDots"><?php foreach ($heroImgs as $i => $im): ?><button class="<?= $i === 0 ? 'on' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endforeach; ?></div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ FEATURES STRIP ============ -->
<?php $featIcons = [
  '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
  '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 9v.01M18 15v.01"/>',
  '<path d="M4 12a8 8 0 1 0 2.4-5.7L4 8.6"/><path d="M4 4v4.6h4.6"/>',
  '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2.5" y="13" width="4" height="6" rx="1.5"/><rect x="17.5" y="13" width="4" height="6" rx="1.5"/>',
];
$featDefaults = home_feature_defaults();
if (setting('features_show', '1') === '1'): ?>
<section class="features container">
  <?php foreach ($featIcons as $i => $ico): $n = $i + 1; $t = setting("feat{$n}_title", $featDefaults[$i][0]); if ($t === '-') continue; ?>
  <div class="feature reveal"><span class="f-ico"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><?= $ico ?></svg></span><div><b><?= e($t) ?></b><small><?= e(setting("feat{$n}_sub", $featDefaults[$i][1])) ?></small></div></div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if (section_on('cats')): ?>
<!-- ============ CATEGORIES ============ -->
<section class="section">
  <div class="container">
    <?= section_head('Browse', 'Shop by Category', setting('cat_subtitle', 'Soft, pretty pieces for every moment — find what makes you feel beautiful.')) ?>
    <?php $catStyle = in_array(setting('cat_style', 'arch'), ['landscape', 'square', 'arch'], true) ? setting('cat_style', 'arch') : 'arch';
          $catFit = setting('cat_fit', 'contain') === 'cover' ? 'cover' : 'contain'; ?>
    <div class="cat-scroller">
    <?php if ($catStyle !== 'landscape'): ?><button class="cat-arrow prev" type="button" aria-label="Previous">‹</button><button class="cat-arrow next" type="button" aria-label="Next">›</button><?php endif; ?>
    <div class="cat-row cat-<?= $catStyle ?> cat-fit-<?= $catFit ?>" id="catRow" data-autoslide="<?= $catStyle !== 'landscape' && setting('cat_autoslide', '1') === '1' ? (int)setting('cat_slide_seconds', 3) * 1000 : 0 ?>">
      <?php foreach ($cats as $c): ?>
        <a class="cat-card reveal" href="<?= url('shop.php?cat=' . urlencode($c['slug'])) ?>">
          <span class="cat-img"><img src="<?= e(img_url($c['image'])) ?>" alt="<?= e($c['name']) ?>" loading="lazy"></span>
          <span class="cat-label"><b><?= e($c['name']) ?></b><small><?= (int)$c['cnt'] ?> items</small></span>
        </a>
      <?php endforeach; ?>
    </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ PROMO BANNERS ============ -->
<?php $promoStyle = array_key_exists(setting('promo_style', 'arch'), promo_styles()) ? setting('promo_style', 'arch') : 'arch';
      $promoCls = ['promo-purple', 'promo-cherry', 'promo-green'];
      $promos = array_filter([1 => promo(1), 2 => promo(2), 3 => promo(3)], fn($p) => $p['show']); ?>
<?php if ($promos && setting('promos_show', '1') === '1'): ?>
<section class="container promo-grid promo-style-<?= $promoStyle ?> promo-count-<?= count($promos) ?>">
  <?php foreach ($promos as $n => $pr): ?>
  <a class="promo <?= $promoCls[$n - 1] ?> reveal" href="<?= e($pr['url']) ?>">
    <div class="promo-copy"><?php if ($pr['eyebrow'] !== '-'): ?><span class="eyebrow"><?= e($pr['eyebrow']) ?></span><?php endif; ?>
      <h3><?= nl2br(e($pr['title'])) ?></h3><?php if ($pr['link_text'] !== '-'): ?><span class="link-arrow"><?= e($pr['link_text']) ?></span><?php endif; ?></div>
    <img src="<?= e(img_url($pr['image'])) ?>" alt="<?= e(str_replace("\n", ' ', $pr['title'])) ?>" loading="lazy">
  </a>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- ============ PRODUCT TABS ============ -->
<?php if (section_on('products')):
  $tabs = [];
  foreach (product_tabs() as $k => $def) {
      if (setting("tab_{$k}_show", '1') !== '1') continue;
      $list = tab_products($k);
      if ($list) $tabs[$k] = [setting("tab_{$k}_label", $def), $list];
  }
  if ($tabs): $firstTab = array_key_first($tabs); ?>
<section class="section">
  <div class="container">
    <?= section_head(setting('products_eyebrow', 'Our collection'), setting('products_title', 'Loved by Women Like You')) ?>
    <?php if (count($tabs) > 1): ?>
    <div class="tabs reveal" data-tabs>
      <?php foreach ($tabs as $k => [$label]): ?><button class="tab <?= $k === $firstTab ? 'on' : '' ?>" data-tab="t-<?= $k ?>"><?= e($label) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php foreach ($tabs as $k => [, $list]): ?>
      <div class="p-grid tab-pane <?= $k === $firstTab ? 'on' : '' ?>" id="t-<?= $k ?>">
        <?php foreach ($list as $p) echo product_card($p); ?>
      </div>
    <?php endforeach; ?>
    <div class="center"><a class="btn btn-outline" href="<?= url('shop.php') ?>"><?= e(setting('products_btn', 'View all products')) ?></a></div>
  </div>
</section>
<?php endif; endif; ?>

<!-- ============ SALE BANNER ============ -->
<?php if (section_on('sale')): $saleOver = $saleEnds < time(); ?>
<section class="container">
  <div class="sale-banner reveal">
    <div class="sb-pattern"></div>
    <div class="sb-copy">
      <?php if (($sx = setting('sale_eyebrow', 'Limited time')) !== '-'): ?><span class="eyebrow"><?= e($sx) ?></span><?php endif; ?>
      <h2><?= e(setting('sale_title', 'Season Sale')) ?><?php if (($hl = setting('sale_highlight', 'up to ' . max($maxOff, 10) . '% off')) !== '-'): ?><br><em><?= e($hl) ?></em><?php endif; ?></h2>
      <?php if (($st = setting('sale_text', 'Your favourite night suits, lingerie and dresses at the sweetest prices. Hurry — stock is limited!')) !== '-'): ?><p><?= e($st) ?></p><?php endif; ?>
      <?php if (setting('sale_timer_show', '1') === '1' && !$saleOver): ?>
      <div class="countdown" data-countdown="<?= e(date('c', $saleEnds)) ?>">
        <div><b data-d>00</b><span>Days</span></div><div><b data-h>00</b><span>Hours</span></div><div><b data-m>00</b><span>Mins</span></div><div><b data-s>00</b><span>Secs</span></div>
      </div>
      <?php endif; ?>
      <?php if (($sb = setting('sale_btn_text', 'Shop the sale')) !== '-'): ?><a href="<?= e(setting('sale_btn_link', url('shop.php?cat=sale'))) ?>" class="btn btn-light btn-lg"><?= e($sb) ?></a><?php endif; ?>
    </div>
    <div class="sb-art">
      <img src="<?= e(img_url(setting('sale_img1', 'assets/img/catalog/pl-6fd6d88f.jpg'))) ?>" alt="" class="sb-i1">
      <img src="<?= e(img_url(setting('sale_img2', 'assets/img/catalog/pl-1461cf1f.jpg'))) ?>" alt="" class="sb-i2">
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ VISIT OUR STORE ============ -->
<?php if (section_on('visit')): $vAddr = setting('visit_address', setting('address')); $vPhone = setting('visit_phone', setting('phone')); $vMail = setting('visit_email', setting('email')); ?>
<section class="section">
  <div class="container visit reveal">
    <div class="visit-img"><img src="<?= e(img_url(setting('store_photo', 'assets/img/catalog/pl-287f9bb8.jpg'))) ?>" alt="<?= e(setting('site_name')) ?> store" loading="lazy"></div>
    <div class="visit-copy">
      <?php if (($vx = setting('visit_eyebrow', 'Come say hello')) !== '-'): ?><span class="eyebrow"><?= e($vx) ?></span><?php endif; ?>
      <h2><?= e(setting('visit_title', 'Visit Our Outlet in Chittagong!')) ?></h2>
      <?php if (($vt = setting('visit_text', 'We welcome you to explore our premium collection of 100% imported ladies’ lingerie, nightwear, and undergarments.')) !== '-'): ?><p><?= nl2br(e($vt)) ?></p><?php endif; ?>
      <ul class="visit-list">
        <?php if ($vAddr !== '-'): ?><li><span>📍</span><div><?= nl2br(e($vAddr)) ?></div></li><?php endif; ?>
        <?php if ($vPhone !== '-'): ?><li><span>📞</span><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $vPhone)) ?>"><?= e($vPhone) ?></a></li><?php endif; ?>
        <?php if ($vMail !== '-' && $vMail !== ''): ?><li><span>✉</span><a href="mailto:<?= e($vMail) ?>"><?= e($vMail) ?></a></li><?php endif; ?>
      </ul>
      <?php $mapQ = setting('map_query', 'Khulshi Town Center, Chittagong'); ?>
      <div class="visit-btns">
        <?php if (($vb = setting('visit_btn_text', 'Shop online')) !== '-'): ?><a href="<?= e(setting('visit_btn_link', url('shop.php'))) ?>" class="btn btn-accent btn-lg"><?= e($vb) ?></a><?php endif; ?>
        <?php if ($mapQ !== '-'): ?><a href="https://www.google.com/maps/search/?api=1&amp;query=<?= urlencode($mapQ) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-lg">📍 Get directions</a><?php endif; ?>
      </div>
    </div>
    <?php if ($mapQ !== '-' && setting('map_show', '1') === '1'): ?>
    <div class="visit-map">
      <iframe src="https://maps.google.com/maps?q=<?= urlencode($mapQ) ?>&amp;z=17&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?= e(setting('site_name')) ?> location"></iframe>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ============ NEWSLETTER ============ -->
<?php if (section_on('newsletter')): ?>
<section class="container">
  <div class="newsletter reveal">
    <div>
      <span class="eyebrow"><?= e(setting('nl_eyebrow', 'Stay in style')) ?></span>
      <h2><?= e(setting('nl_title', 'Join our circle & get 10% off')) ?></h2>
      <p><?= e(setting('nl_text', 'Be the first to hear about new arrivals, festive drops and members-only offers.')) ?></p>
    </div>
    <form method="post" action="<?= url('subscribe.php') ?>" class="nl-form">
      <?= csrf_field() ?>
      <input type="email" name="email" placeholder="Your email address" required>
      <button class="btn btn-accent">Subscribe</button>
    </form>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
