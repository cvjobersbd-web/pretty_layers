</main>
<?php $fCats = rows('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name LIMIT 6'); ?>
<footer class="site-footer">
  <div class="footer-wave"><svg viewBox="0 0 1440 60" preserveAspectRatio="none"><path d="M0 60V20c120 25 240 38 360 30S600 10 720 10s240 30 360 34 240-14 360-24V60z" fill="currentColor"/></svg></div>
  <div class="footer-main">
    <div class="container footer-grid">
      <div class="f-brand">
        <a class="brand brand-light" href="<?= url('index.php') ?>"><?= logo_html('logo') ?></a>
        <p><?= nl2br(e(setting('footer_text', setting('tagline') . '. Thoughtfully designed clothing and accessories for every woman — delivered to your doorstep anywhere in Bangladesh.'))) ?></p>
        <?php if (setting('footer_social_show', '1') === '1'): ?><?= social_icons_html('socials') ?><?php endif; ?>
      </div>
      <div>
        <h4>Shop</h4>
        <?php foreach ($fCats as $c): ?><a href="<?= url('shop.php?cat=' . urlencode($c['slug'])) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
      </div>
      <div>
        <h4>Help</h4>
        <a href="<?= url(customer() ? 'account.php' : 'login.php') ?>"><?= customer() ? 'My account' : 'Login / Sign up' ?></a>
        <a href="<?= url('track.php') ?>">Track your order</a>
        <a href="<?= url('cart.php') ?>">Shopping bag</a>
        <a href="<?= url('shop.php?new=1&sort=new') ?>">New arrivals</a>
      </div>
      <div>
        <h4>Contact</h4>
        <p class="f-contact"><?= e(setting('address')) ?></p>
        <p class="f-contact"><?= e(setting('phone')) ?></p>
        <p class="f-contact"><?= e(setting('email')) ?></p>
        <div class="pay-badges"><span>Cash on Delivery</span><?php if (setting('bkash_number')): ?><span>bKash</span><?php endif; ?><?php if (setting('nagad_number')): ?><span>Nagad</span><?php endif; ?></div>
      </div>
    </div>
  </div>
  <div class="footer-bottom"><div class="container"><?= e(str_replace('{year}', date('Y'), setting('footer_copyright', '© {year} ' . setting('site_name', 'Pretty Layers') . '. All rights reserved.'))) ?></div></div>
</footer>
<?php $bn = basename($_SERVER['SCRIPT_NAME']); $wc = count(wishlist_ids()); $cc = cart_count(); ?>
<nav class="bottom-nav" aria-label="Quick menu">
  <a href="<?= url('index.php') ?>" class="<?= $bn === 'index.php' ? 'on' : '' ?>"><svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg><span>Home</span></a>
  <a href="<?= url('shop.php') ?>" class="<?= in_array($bn, ['shop.php', 'product.php'], true) ? 'on' : '' ?>"><svg viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg><span>Products</span></a>
  <a href="<?= url('cart.php') ?>" class="<?= in_array($bn, ['cart.php', 'checkout.php'], true) ? 'on' : '' ?>"><i class="bn-ico"><svg viewBox="0 0 24 24"><path d="M2.5 3.5h2.6l2.3 11.2a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.4-1.1l1.8-7.3H6.2"/><circle cx="9.5" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg><b class="bn-badge" data-cart-count <?= $cc ? '' : 'hidden' ?>><?= $cc ?></b></i><span>Cart</span></a>
  <a href="<?= url('wishlist.php') ?>" class="<?= $bn === 'wishlist.php' ? 'on' : '' ?>"><i class="bn-ico"><svg viewBox="0 0 24 24"><path d="M12 20.5s-7.5-4.6-9.2-9.4C1.6 7.6 3.9 4 7.4 4c2 0 3.6 1.1 4.6 2.7C13 5.1 14.6 4 16.6 4c3.5 0 5.8 3.6 4.6 7.1-1.7 4.8-9.2 9.4-9.2 9.4z"/></svg><b class="bn-badge" data-wish-count <?= $wc ? '' : 'hidden' ?>><?= $wc ?></b></i><span>Wishlist</span></a>
  <a href="<?= url(customer() ? 'account.php' : 'login.php') ?>" class="<?= in_array($bn, ['account.php', 'login.php', 'register.php'], true) ? 'on' : '' ?>"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg><span>Account</span></a>
</nav>
<a href="#" class="to-top" id="toTop" aria-label="Back to top"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 15l6-6 6 6"/></svg></a>
<script>window.SHOP = { cartUrl: <?= json_encode(url('cart.php')) ?>, wishUrl: <?= json_encode(url('wishlist.php')) ?>, csrf: <?= json_encode(csrf_token()) ?> };</script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
</body>
</html>
