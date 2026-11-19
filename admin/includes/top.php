<?php
/** Admin layout — top. Set $title and $active before including. */
$pendingCount = (int)val("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$lowCount = (int)val('SELECT COUNT(*) FROM variants v JOIN products p ON p.id = v.product_id WHERE p.is_active = 1 AND v.stock <= p.low_stock');
$nav = [
    'dashboard' => ['index.php', 'Dashboard', '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>'],
    'products'  => ['products.php', 'Products', '<path d="M20 7l-8-4-8 4 8 4 8-4zM4 7v10l8 4 8-4V7M12 11v10"/>'],
    'categories'=> ['categories.php', 'Categories', '<path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/>'],
    'orders'    => ['orders.php', 'Orders', '<path d="M6 2h12l2 4v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6zM4 6h16M9 10a3 3 0 0 0 6 0"/>', $pendingCount],
    'customers' => ['customers.php', 'Customers', '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-7 7-7s7 3 7 7M16 4a4 4 0 0 1 0 8M22 21c0-3-2-6-5-6.7"/>'],
    'inventory' => ['inventory.php', 'Inventory', '<path d="M3 9l9-6 9 6v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1zM8 21v-7h8v7M8 17h8"/>', $lowCount],
    'history'   => ['stock-history.php', 'Stock History', '<path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5M12 7v5l3 3"/>'],
    'settings'  => ['settings.php', 'Settings & Design', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>'],
];
$nav = array_filter($nav, fn($k) => can($k), ARRAY_FILTER_USE_KEY);
if (is_super()) $nav['admins'] = ['admins.php', 'Admin Users', '<path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/>'];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Admin') ?> · <?= e(setting('site_name', 'Lavanya')) ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600&family=Playfair+Display:wght@600&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
<style><?= theme_css() ?></style>
</head>
<body class="admin">
<aside class="a-side" id="aSide">
  <a class="a-brand" href="<?= admin_home() ?>"><?= logo_html('logo') ?><small>Admin</small></a>
  <nav class="a-nav">
    <?php foreach ($nav as $k => $n): ?>
      <a href="<?= $n[0] ?>" class="<?= ($active ?? '') === $k ? 'on' : '' ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><?= $n[2] ?></svg>
        <span><?= $n[1] ?></span><?php if (!empty($n[3])): ?><em class="<?= $k === 'inventory' ? 'warn' : '' ?>"><?= $n[3] ?></em><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="a-side-foot">
    <a href="../index.php" target="_blank">↗ View shop</a>
    <a href="logout.php">⎋ Log out</a>
  </div>
</aside>
<div class="a-overlay" id="aOverlay"></div>
<div class="a-main">
  <header class="a-top">
    <button class="a-burger" id="aBurger" aria-label="Menu">☰</button>
    <h1><?= e($title ?? '') ?></h1>
    <div class="a-top-r">
      <?php if (!empty($topAction)) echo $topAction; ?>
      <span class="a-user"><span><?= e(strtoupper(mb_substr(admin_name(), 0, 1))) ?></span><?= e(admin_name()) ?><?= is_super() ? ' <em class="role-tag">Super Admin</em>' : '' ?></span>
    </div>
  </header>
  <div class="a-content">
  <?php foreach (flashes() as [$t, $m]): ?><div class="a-alert a-alert-<?= e($t) ?>"><?= e($m) ?></div><?php endforeach; ?>
