<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';

$lines = cart_lines();
if (!$lines) { flash('error', 'Your bag is empty.'); redirect(url('cart.php')); }
$subtotal = array_sum(array_column($lines, 'line_total'));
$methods = payment_methods();
$errors = [];
$f = ['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'city' => '', 'zone' => 'inside', 'note' => '', 'payment' => 'cod', 'trx' => ''];
$me = customer();
if ($me) {
    $f = array_merge($f, ['name' => $me['name'], 'phone' => shown_phone($me), 'email' => (string)$me['email'], 'address' => (string)$me['address'], 'city' => (string)$me['city']]);
    if (preg_match('/dhaka/i', $f['city'])) $f['zone'] = 'inside'; elseif ($f['city'] !== '') $f['zone'] = 'outside';
}

if (is_post()) {
    csrf_check();
    foreach ($f as $k => $v) $f[$k] = post($k, $v);
    if (mb_strlen($f['name']) < 2) $errors['name'] = 'Please enter your full name.';
    if (!preg_match('/^(\+?88)?01[3-9]\d{8}$/', preg_replace('/[\s-]/', '', $f['phone']))) $errors['phone'] = 'Enter a valid Bangladeshi mobile number (e.g. 01712345678).';
    if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email address is not valid.';
    if (mb_strlen($f['address']) < 6) $errors['address'] = 'Please enter your full delivery address.';
    if ($f['city'] === '') $errors['city'] = 'Please enter your city / district.';
    if (!in_array($f['zone'], ['inside', 'outside'], true)) $f['zone'] = 'inside';
    if (!isset($methods[$f['payment']])) $f['payment'] = 'cod';
    if ($f['payment'] !== 'cod' && mb_strlen($f['trx']) < 6) $errors['trx'] = 'Please enter the transaction ID you received after payment.';

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $lines = cart_lines();
            if (!$lines) throw new RuntimeException('Your bag is empty.');
            $subtotal = array_sum(array_column($lines, 'line_total'));
            $ship = shipping_fee($subtotal, $f['zone']);
            do { $no = 'LV' . date('ymd') . random_int(1000, 9999); } while (val('SELECT id FROM orders WHERE order_no = ?', [$no]));
            $oid = insert('orders', [
                'order_no' => $no, 'customer_name' => $f['name'], 'phone' => normalize_phone($f['phone']) ?: $f['phone'], 'email' => $f['email'], 'address' => $f['address'],
                'city' => $f['city'], 'zone' => $f['zone'], 'note' => $f['note'], 'subtotal' => $subtotal, 'shipping' => $ship, 'total' => $subtotal + $ship,
                'payment_method' => $f['payment'], 'trx_id' => $f['payment'] === 'cod' ? '' : $f['trx'], 'status' => 'pending',
                'customer_id' => $me['id'] ?? null, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($lines as $l) {
                // Inventory: deduct stock (fails safely if someone else bought the last piece)
                if (!stock_move((int)$l['variant_id'], -(int)$l['qty'], 'order', 'Order placed', $no, 'customer')) {
                    throw new RuntimeException('Sorry, “' . $l['name'] . ' (' . variant_label($l) . ')” just went out of stock. Please review your bag.');
                }
                insert('order_items', [
                    'order_id' => $oid, 'product_id' => $l['id'], 'variant_id' => $l['variant_id'], 'name' => $l['name'], 'size' => $l['size'], 'color' => $l['color'],
                    'sku' => $l['vsku'] ?: $l['sku'], 'image' => $l['image'], 'price' => $l['unit'], 'cost_price' => $l['cost_price'], 'qty' => $l['qty'],
                ]);
            }
            // remember the address for next time
            if ($me && (string)$me['address'] === '') update('customers', ['address' => $f['address'], 'city' => $f['city']], 'id = ?', [$me['id']]);
            $np = normalize_phone($f['phone']);
            if ($me && !has_real_phone($me) && $np && !val('SELECT id FROM customers WHERE phone = ?', [$np])) update('customers', ['phone' => $np], 'id = ?', [$me['id']]);
            $pdo->commit();
            $_SESSION['cart'] = [];
            $_SESSION['last_order'] = $no;
            redirect(url('order-success.php?no=' . urlencode($no)));
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', $ex instanceof RuntimeException ? $ex->getMessage() : 'Could not place the order. Please try again.');
            redirect(url('cart.php'));
        }
    }
}

$shipIn = shipping_fee($subtotal, 'inside');
$shipOut = shipping_fee($subtotal, 'outside');
$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
$err = fn($k) => isset($errors[$k]) ? '<small class="field-err">' . e($errors[$k]) . '</small>' : '';
?>
<section class="page-hero page-hero-sm"><div class="container"><nav class="crumbs"><a href="<?= url('cart.php') ?>">Bag</a><span>/</span><b>Checkout</b></nav><h1>Checkout</h1></div></section>

<section class="container section-sm">
  <form method="post" class="checkout-grid" id="checkoutForm" data-subtotal="<?= $subtotal ?>" data-ship-in="<?= $shipIn ?>" data-ship-out="<?= $shipOut ?>" data-currency="<?= e(setting('currency_symbol', '৳')) ?>">
    <?= csrf_field() ?>
    <div class="co-main">
      <?php if (!$me): ?>
        <div class="co-login">👋 Have an account? <a href="<?= url('login.php?next=checkout.php') ?>">Login</a> for faster checkout and order history — or continue as guest below.</div>
      <?php endif; ?>
      <div class="co-card">
        <h3><span class="step">1</span>Delivery details</h3>
        <div class="form-grid">
          <label class="fld <?= isset($errors['name']) ? 'has-err' : '' ?>"><span>Full name *</span><input name="name" value="<?= e($f['name']) ?>" required><?= $err('name') ?></label>
          <label class="fld <?= isset($errors['phone']) ? 'has-err' : '' ?>"><span>Mobile number *</span><input name="phone" value="<?= e($f['phone']) ?>" placeholder="01XXXXXXXXX" required><?= $err('phone') ?></label>
          <label class="fld <?= isset($errors['email']) ? 'has-err' : '' ?>"><span>Email (optional)</span><input type="email" name="email" value="<?= e($f['email']) ?>"><?= $err('email') ?></label>
          <label class="fld <?= isset($errors['city']) ? 'has-err' : '' ?>"><span>City / District *</span><input name="city" value="<?= e($f['city']) ?>" required><?= $err('city') ?></label>
          <label class="fld fld-full <?= isset($errors['address']) ? 'has-err' : '' ?>"><span>Full address *</span><textarea name="address" rows="2" placeholder="House, road, area" required><?= e($f['address']) ?></textarea><?= $err('address') ?></label>
          <label class="fld fld-full"><span>Order note (optional)</span><input name="note" value="<?= e($f['note']) ?>" placeholder="Anything we should know?"></label>
        </div>
      </div>
      <div class="co-card">
        <h3><span class="step">2</span>Delivery area</h3>
        <div class="choice-row">
          <label class="choice"><input type="radio" name="zone" value="inside" <?= $f['zone'] === 'inside' ? 'checked' : '' ?>><span><b>Inside Dhaka</b><small><?= $shipIn ? money($shipIn) : 'Free' ?> · 1–3 days</small></span></label>
          <label class="choice"><input type="radio" name="zone" value="outside" <?= $f['zone'] === 'outside' ? 'checked' : '' ?>><span><b>Outside Dhaka</b><small><?= $shipOut ? money($shipOut) : 'Free' ?> · 3–5 days</small></span></label>
        </div>
      </div>
      <div class="co-card">
        <h3><span class="step">3</span>Payment method</h3>
        <div class="choice-row">
          <?php foreach ($methods as $k => $label): ?>
            <label class="choice"><input type="radio" name="payment" value="<?= $k ?>" <?= $f['payment'] === $k ? 'checked' : '' ?>><span><b><?= e($label) ?></b><small><?= $k === 'cod' ? 'Pay when you receive' : 'Send money & enter TrxID' ?></small></span></label>
          <?php endforeach; ?>
        </div>
        <div class="pay-info" id="payInfo" <?= $f['payment'] === 'cod' ? 'hidden' : '' ?>>
          <p>Please <b>Send Money</b> the total amount to our <span data-pay-name><?= e($methods[$f['payment']] ?? '') ?></span> personal number:
            <b class="pay-num" data-num-bkash="<?= e(setting('bkash_number')) ?>" data-num-nagad="<?= e(setting('nagad_number')) ?>"><?= e(setting($f['payment'] . '_number')) ?></b>, then enter the Transaction ID below.</p>
          <label class="fld <?= isset($errors['trx']) ? 'has-err' : '' ?>"><span>Transaction ID *</span><input name="trx" value="<?= e($f['trx']) ?>" placeholder="e.g. 9BX7K2L1QZ"><?= $err('trx') ?></label>
        </div>
      </div>
    </div>

    <aside class="summary co-summary">
      <h3>Your order</h3>
      <div class="sum-items">
        <?php foreach ($lines as $l): ?>
          <div class="sum-item"><span class="si-img"><img src="<?= e(img_url($l['image'])) ?>" alt=""><i><?= (int)$l['qty'] ?></i></span><div><b><?= e($l['name']) ?></b><small><?= e(variant_label($l)) ?></small></div><span><?= money($l['line_total']) ?></span></div>
        <?php endforeach; ?>
      </div>
      <div class="sum-row"><span>Subtotal</span><b><?= money($subtotal) ?></b></div>
      <div class="sum-row"><span>Delivery</span><b id="shipAmt"><?= money($f['zone'] === 'inside' ? $shipIn : $shipOut) ?></b></div>
      <div class="sum-row sum-total"><span>Total</span><b id="totalAmt"><?= money($subtotal + ($f['zone'] === 'inside' ? $shipIn : $shipOut)) ?></b></div>
      <button class="btn btn-accent btn-lg btn-block">Place order</button>
      <p class="secure">By placing your order you agree to our delivery &amp; exchange policy.</p>
    </aside>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
