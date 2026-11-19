<?php
/** Storefront header. Set $pageTitle before including. */
$siteName = setting('site_name', 'Lavanya');
$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' . $siteName : $siteName . ' — ' . setting('tagline', '');
$navCats = rows('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name');
$current = basename($_SERVER['SCRIPT_NAME']);
$logo = setting('logo');
$icon = logo_icon();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e(setting('tagline')) ?>">
<?= favicon_tag() ?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<style><?= theme_css() ?></style>
</head>
<body>
<svg class="svg-defs" width="0" height="0" aria-hidden="true" focusable="false"><defs>
  <filter id="lq-goo"><feGaussianBlur in="SourceGraphic" stdDeviation="5" result="b"/><feColorMatrix in="b" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -9" result="g"/><feComposite in="SourceGraphic" in2="g" operator="atop"/></filter>
</defs></svg>

<?php if (setting('loader_enabled', '1') === '1'): ?>
<div id="loader" aria-hidden="true">
  <div class="loader-inner">
    <div class="loader-orb">
      <span class="loader-ring"></span>
      <span class="loader-ring r2"></span>
      <div class="loader-logo"><?php if ($icon): ?><img src="<?= e(img_url($icon)) ?>" alt=""><?php else: ?><span><?= e(mb_substr($siteName, 0, 1)) ?></span><?php endif; ?></div>
    </div>
    <div class="loader-name"><?php foreach (preg_split('//u', $siteName, -1, PREG_SPLIT_NO_EMPTY) as $i => $ch): ?><span style="animation-delay:<?= $i * 0.08 ?>s"><?= $ch === ' ' ? '&nbsp;' : e($ch) ?></span><?php endforeach; ?></div>
    <div class="loader-bar"><i></i></div>
  </div>
</div>
<?php endif; ?>

<?php $ann = setting('announcement'); if (setting('topbar_show', '1') === '1' && ($ann || setting('topbar_left', setting('phone')))): ?>
<div class="topbar">
  <div class="container topbar-in">
    <span class="tb-left"><?php $tl = setting('topbar_left', setting('phone')); $tel = preg_replace('/[^\d+]/', '', $tl);
      echo strlen($tel) >= 7 ? '<a href="tel:' . e($tel) . '">' . e($tl) . '</a>' : e($tl); ?></span>
    <div class="marquee"><span><?= e($ann) ?></span></div>
    <?php if (($tr = setting('topbar_right', 'Track order')) !== '-'): ?><a class="tb-right" href="<?= e(setting('topbar_right_link', url('track.php'))) ?>"><?= e($tr) ?></a><?php else: ?><span class="tb-right"></span><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<header class="site-header" id="siteHeader">
  <div class="container header-in">
    <button class="icon-btn burger" id="burger" aria-label="Menu"><span></span><span></span><span></span></button>
    <a class="brand" href="<?= url('index.php') ?>"><?= logo_html('logo') ?></a>
    <nav class="main-nav" id="mainNav" aria-label="Main menu">
      <div class="drawer-head">
        <a class="drawer-logo" href="<?= url('index.php') ?>"><?= logo_html('logo') ?></a>
        <button class="drawer-close" id="navClose" type="button" aria-label="Close menu">✕</button>
      </div>
      <span class="nav-liquid" aria-hidden="true"><i class="lq-blob"></i><i class="lq-drop"></i></span>
      <?php if (nav_show('home')): ?><a href="<?= url('index.php') ?>" class="<?= $current === 'index.php' ? 'active' : '' ?>"><?= e(nav_label('home')) ?></a><?php endif; ?>
      <?php if (nav_show('shop')): ?><a href="<?= url('shop.php') ?>" class="<?= $current === 'shop.php' && !get('cat') && !get('sale') && !get('new') ? 'active' : '' ?>"><?= e(nav_label('shop')) ?></a><?php endif; ?>
      <?php if (nav_show('cats')): ?><div class="has-drop">
        <a href="<?= url('shop.php') ?>" class="drop-toggle <?= get('cat') ? 'active' : '' ?>"><?= e(nav_label('cats')) ?> <svg width="10" height="10" viewBox="0 0 10 10"><path d="M1 3l4 4 4-4" stroke="currentColor" fill="none" stroke-width="1.5"/></svg></a>
        <div class="dropdown">
          <?php foreach ($navCats as $c): ?>
            <a href="<?= url('shop.php?cat=' . urlencode($c['slug'])) ?>"><img src="<?= e(img_url($c['image'])) ?>" alt=""><?= e($c['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php if (nav_show('new')): ?><a href="<?= url('shop.php?new=1&sort=new') ?>" class="<?= get('new') ? 'active' : '' ?>"><?= e(nav_label('new')) ?></a><?php endif; ?>
      <?php if (nav_show('track')): ?><a href="<?= url('track.php') ?>" class="<?= $current === 'track.php' ? 'active' : '' ?>"><?= e(nav_label('track')) ?></a><?php endif; ?>
      <?php if (customer()): ?><a href="<?= url('account.php') ?>" class="nav-mobile-only">My Account</a><a href="<?= url('logout.php') ?>" class="nav-mobile-only">Logout</a>
      <?php else: ?><a href="<?= url('login.php') ?>" class="nav-mobile-only">Login</a><a href="<?= url('register.php') ?>" class="nav-mobile-only">Create Account</a><?php endif; ?>
      <div class="drawer-foot">
        <?php if ($ph = setting('phone')): ?><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $ph)) ?>" class="drawer-call">📞 <?= e($ph) ?></a><?php endif; ?>
        <?= social_icons_html('drawer-social') ?>
      </div>
    </nav>
    <div class="header-actions">
      <button class="icon-btn" id="searchToggle" aria-label="Search">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
      </button>
      <?php if ($me = customer()): ?>
        <a class="user-btn" href="<?= url('account.php') ?>" title="My account"><span><?= e(mb_strtoupper(mb_substr($me['name'], 0, 1))) ?></span><em><?= e(explode(' ', $me['name'])[0]) ?></em></a>
      <?php else: ?>
        <a class="icon-btn" href="<?= url('login.php') ?>" aria-label="Login" title="Login / Sign up">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </a>
      <?php endif; ?>
      <a class="icon-btn wish-head" href="<?= url('wishlist.php') ?>" aria-label="Wishlist" title="Wishlist">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20.5s-7.5-4.6-9.2-9.4C1.6 7.6 3.9 4 7.4 4c2 0 3.6 1.1 4.6 2.7C13 5.1 14.6 4 16.6 4c3.5 0 5.8 3.6 4.6 7.1-1.7 4.8-9.2 9.4-9.2 9.4z"/></svg>
        <span class="cart-count wish-count" data-wish-count <?= wishlist_ids() ? '' : 'hidden' ?>><?= count(wishlist_ids()) ?></span>
      </a>
      <a class="icon-btn cart-btn" href="<?= url('cart.php') ?>" aria-label="Cart">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 8h14l-1.2 11.2a2 2 0 0 1-2 1.8H8.2a2 2 0 0 1-2-1.8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
        <span class="cart-count" data-cart-count><?= cart_count() ?></span>
      </a>
    </div>
  </div>
  <form class="search-bar" id="searchBar" action="<?= url('shop.php') ?>" method="get">
    <div class="container">
      <input type="search" name="q" value="<?= e(get('q')) ?>" placeholder="Search kurti, saree, gown…" autocomplete="off">
      <button class="btn btn-accent">Search</button>
    </div>
  </form>
</header>
<div class="nav-overlay" id="navOverlay"></div>

<div class="toast-wrap" id="toasts">
<?php foreach (flashes() as [$t, $m]): ?><div class="toast toast-<?= e($t) ?>"><?= e($m) ?></div><?php endforeach; ?>
</div>
<main>
