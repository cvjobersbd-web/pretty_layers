<?php
/**
 * One-time installer: creates the database tables, the admin account and demo products.
 * Delete this file after installation.
 */
define('INSTALLING', true);
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';

$lock = APP_ROOT . '/data/installed.lock';
$done = is_file($lock);
$error = '';
$ok = false;

function schema(): array
{
    $my = DB_DRIVER !== 'sqlite';
    $id = $my ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $end = $my ? ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : ')';
    return [
        "CREATE TABLE IF NOT EXISTS settings (skey VARCHAR(100) NOT NULL PRIMARY KEY, svalue TEXT $end",
        "CREATE TABLE IF NOT EXISTS admins (id $id, username VARCHAR(60) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, name VARCHAR(100), created_at DATETIME $end",
        "CREATE TABLE IF NOT EXISTS categories (id $id, name VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL UNIQUE, image VARCHAR(255), description TEXT, sort_order INT DEFAULT 0, is_active TINYINT DEFAULT 1 $end",
        "CREATE TABLE IF NOT EXISTS products (id $id, category_id INT, name VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL UNIQUE, sku VARCHAR(80),
            short_desc VARCHAR(255), description TEXT, price DECIMAL(10,2) NOT NULL DEFAULT 0, sale_price DECIMAL(10,2) DEFAULT 0, cost_price DECIMAL(10,2) DEFAULT 0,
            image VARCHAR(255), low_stock INT DEFAULT 5, is_featured TINYINT DEFAULT 0, is_active TINYINT DEFAULT 1, created_at DATETIME, updated_at DATETIME $end",
        "CREATE TABLE IF NOT EXISTS product_images (id $id, product_id INT NOT NULL, path VARCHAR(255) NOT NULL, sort_order INT DEFAULT 0 $end",
        "CREATE TABLE IF NOT EXISTS variants (id $id, product_id INT NOT NULL, size VARCHAR(40) DEFAULT '', color VARCHAR(40) DEFAULT '', sku VARCHAR(80), stock INT NOT NULL DEFAULT 0 $end",
        "CREATE TABLE IF NOT EXISTS orders (id $id, order_no VARCHAR(30) NOT NULL UNIQUE, customer_name VARCHAR(120), phone VARCHAR(30), email VARCHAR(150),
            address TEXT, city VARCHAR(80), zone VARCHAR(20), note TEXT, subtotal DECIMAL(10,2), shipping DECIMAL(10,2), total DECIMAL(10,2),
            payment_method VARCHAR(20), trx_id VARCHAR(60), status VARCHAR(20) DEFAULT 'pending', stock_returned TINYINT DEFAULT 0, created_at DATETIME, updated_at DATETIME $end",
        "CREATE TABLE IF NOT EXISTS order_items (id $id, order_id INT NOT NULL, product_id INT, variant_id INT, name VARCHAR(200), size VARCHAR(40), color VARCHAR(40),
            sku VARCHAR(80), image VARCHAR(255), price DECIMAL(10,2), cost_price DECIMAL(10,2), qty INT $end",
        "CREATE TABLE IF NOT EXISTS stock_movements (id $id, variant_id INT, product_id INT, qty_change INT, balance_after INT, type VARCHAR(20), note VARCHAR(255),
            ref VARCHAR(60), created_by VARCHAR(60), created_at DATETIME $end",
        "CREATE TABLE IF NOT EXISTS newsletter (id $id, email VARCHAR(150) NOT NULL UNIQUE, created_at DATETIME $end",
        "CREATE INDEX idx_var_product ON variants(product_id)",
        "CREATE INDEX idx_items_order ON order_items(order_id)",
        "CREATE INDEX idx_moves_product ON stock_movements(product_id)",
    ];
}

function seed_settings(): void
{
    $p = theme_presets()['pretty'];
    $defaults = [
        'site_name' => 'Lavanya', 'tagline' => 'Grace in every thread', 'logo' => '',
        'color_primary' => $p['primary'], 'color_accent' => $p['accent'], 'color_dark' => $p['dark'], 'color_bg' => $p['bg'], 'color_text' => $p['text'],
        'theme_preset' => 'pretty', 'loader_enabled' => '1', 'currency_symbol' => '৳',
        'announcement' => 'Free delivery on orders over ৳3000  •  Cash on delivery all over Bangladesh',
        'phone' => '+880 1700-000000', 'email' => 'hello@prettylayers.shop', 'address' => 'House 12, Road 5, Dhanmondi, Dhaka',
        'facebook' => '#', 'instagram' => '#',
        'shipping_inside' => '70', 'shipping_outside' => '130', 'free_shipping_over' => '3000',
        'bkash_number' => '01700-000000', 'nagad_number' => '01800-000000', 'low_stock_default' => '5',
        'hero_title' => 'Elegance, Woven With Love', 'hero_subtitle' => 'Discover handpicked kurtis, sarees and gowns made for every beautiful moment of your life.',
        'hero_badge' => 'New Season Collection',
    ];
    foreach ($defaults as $k => $v) if (val('SELECT COUNT(*) FROM settings WHERE skey = ?', [$k]) == 0) set_setting($k, $v);
}

function seed_demo(): void
{
    if (val('SELECT COUNT(*) FROM products') > 0) return;
    $img = fn($n) => "assets/img/products/$n.svg";
    $cats = [
        ['Kurti', 'kurti-floral-lavender'], ['Saree', 'saree-jamdani-green'], ['Three Piece', 'threepiece-lawn-mint'],
        ['Gowns', 'gown-tiered-lavender'], ['Tops', 'top-peplum-milk'], ['Hijab & Scarf', 'hijab-chiffon-rose'],
        ['Bottoms', 'palazzo-wide-green'], ['Bags', 'bag-tote-cherry'], ['Jewelry', 'jewelry-jhumka-gold'],
    ];
    $catId = [];
    foreach ($cats as $i => [$name, $im]) {
        $catId[$name] = insert('categories', ['name' => $name, 'slug' => slugify($name), 'image' => $img($im), 'description' => '', 'sort_order' => $i, 'is_active' => 1]);
    }
    $sizes = ['S', 'M', 'L', 'XL'];
    $free = ['Free Size'];
    $desc = "Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch";
    // name, category, image, price, sale, cost, sizes, colours, featured, stock pattern
    $P = [
        ['Floral Embroidered Cotton Kurti', 'Kurti', 'kurti-floral-lavender', 1450, 1250, 780, $sizes, ['Lavender'], 1, [6, 12, 9, 3]],
        ['Mint Garden Cotton Kurti', 'Kurti', 'kurti-floral-mint', 1450, 0, 780, $sizes, ['Mint'], 0, [4, 8, 7, 2]],
        ['Block Print Straight Kurti', 'Kurti', 'kurti-blockprint-cherry', 1150, 0, 600, $sizes, ['Cherry'], 1, [10, 14, 11, 6]],
        ['Jamdani Silk Saree', 'Saree', 'saree-jamdani-green', 4800, 4200, 2900, $free, ['Forest Green'], 1, [7]],
        ['Soft Georgette Saree', 'Saree', 'saree-georgette-blush', 3200, 0, 1800, $free, ['Blush Pink'], 0, [3]],
        ['Embroidered Lawn Three Piece', 'Three Piece', 'threepiece-lawn-mint', 2650, 2350, 1500, $sizes, ['Mint'], 1, [5, 9, 8, 4]],
        ['Pastel Cotton Three Piece', 'Three Piece', 'threepiece-pastel-lavender', 2200, 0, 1250, $sizes, ['Lavender'], 0, [0, 2, 1, 0]],
        ['Princess Flare Party Gown', 'Gowns', 'gown-princess-cherry', 3900, 0, 2200, $sizes, ['Cherry Red'], 1, [3, 6, 5, 2]],
        ['Lavender Tiered Maxi Gown', 'Gowns', 'gown-tiered-lavender', 3500, 2990, 1900, $sizes, ['Lavender'], 1, [6, 8, 6, 3]],
        ['Puff Sleeve Peplum Top', 'Tops', 'top-peplum-milk', 990, 0, 480, $sizes, ['Milk White'], 1, [12, 15, 10, 8]],
        ['Ruffle Georgette Top', 'Tops', 'top-ruffle-green', 1090, 890, 520, $sizes, ['Forest Green'], 0, [8, 10, 9, 5]],
        ['Premium Chiffon Hijab', 'Hijab & Scarf', 'hijab-chiffon-rose', 450, 0, 190, $free, ['Dusty Rose'], 0, [40]],
        ['Printed Viscose Scarf', 'Hijab & Scarf', 'scarf-printed-purple', 550, 0, 230, $free, ['Purple'], 1, [25]],
        ['Wide Leg Palazzo', 'Bottoms', 'palazzo-wide-green', 850, 0, 400, $sizes, ['Forest Green'], 0, [9, 11, 10, 6]],
        ['Checked Cotton Palazzo', 'Bottoms', 'palazzo-check-cherry', 890, 750, 410, $sizes, ['Cherry'], 0, [0, 0, 0, 0]],
        ['Classic Leather Tote Bag', 'Bags', 'bag-tote-cherry', 2450, 1990, 1300, $free, ['Cherry'], 1, [8]],
        ['Mini Crossbody Bag', 'Bags', 'bag-crossbody-milk', 1650, 0, 900, $free, ['Cream'], 0, [4]],
        ['Kundan Jhumka Earrings', 'Jewelry', 'jewelry-jhumka-gold', 650, 0, 250, $free, ['Gold'], 1, [30]],
        ['Pearl Drop Jhumka', 'Jewelry', 'jewelry-pearl-silver', 550, 490, 220, $free, ['Silver'], 0, [18]],
    ];
    $t = time() - 86400 * 45;
    foreach ($P as $i => [$name, $cat, $im, $price, $sale, $cost, $sz, $cols, $feat, $stocks]) {
        $code = 'LV-' . str_pad((string)($i + 101), 4, '0', STR_PAD_LEFT);
        $pid = insert('products', [
            'category_id' => $catId[$cat], 'name' => $name, 'slug' => unique_slug('products', $name), 'sku' => $code,
            'short_desc' => 'A graceful ' . strtolower($cat) . ' piece from our new season collection.',
            'description' => $desc, 'price' => $price, 'sale_price' => $sale, 'cost_price' => $cost, 'image' => $img($im),
            'low_stock' => 5, 'is_featured' => $feat, 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s', $t + $i * 86400 * 2), 'updated_at' => now(),
        ]);
        foreach ($cols as $col) foreach ($sz as $k => $s) {
            $vid = insert('variants', ['product_id' => $pid, 'size' => $s === 'Free Size' ? '' : $s, 'color' => $col, 'sku' => $code . '-' . ($s === 'Free Size' ? 'FS' : $s), 'stock' => 0]);
            stock_move($vid, $stocks[$k] ?? 0, 'initial', 'Opening stock', null, 'installer');
        }
    }
    // A few demo orders so the dashboard is not empty
    $demo = [
        ['Nusrat Jahan', '01711-223344', 'Mirpur 10, Dhaka', 'Dhaka', 'inside', 'delivered', 6, [[1, 2, 1], [18, 1, 2]]],
        ['Farzana Akter', '01822-556677', 'Agrabad, Chattogram', 'Chattogram', 'outside', 'shipped', 4, [[9, 2, 1]]],
        ['Sadia Islam', '01933-778899', 'Uttara Sector 7, Dhaka', 'Dhaka', 'inside', 'confirmed', 2, [[4, 1, 1], [12, 1, 2]]],
        ['Tanjila Rahman', '01644-112233', 'Zindabazar, Sylhet', 'Sylhet', 'outside', 'pending', 0, [[10, 3, 2], [16, 1, 1]]],
    ];
    foreach ($demo as $n => [$cn, $ph, $ad, $city, $zone, $st, $daysAgo, $items]) {
        $when = date('Y-m-d H:i:s', time() - $daysAgo * 86400 - 3600 * ($n + 2));
        $no = 'LV' . date('ymd', strtotime($when)) . (1001 + $n);
        $oid = insert('orders', ['order_no' => $no, 'customer_name' => $cn, 'phone' => $ph, 'email' => '', 'address' => $ad, 'city' => $city, 'zone' => $zone,
            'note' => '', 'subtotal' => 0, 'shipping' => 0, 'total' => 0, 'payment_method' => 'cod', 'trx_id' => '', 'status' => $st, 'created_at' => $when, 'updated_at' => $when]);
        $sub = 0;
        foreach ($items as [$pid, $vIndex, $qty]) {
            $p = row('SELECT * FROM products WHERE id = ?', [$pid]);
            $vs = rows('SELECT * FROM variants WHERE product_id = ? ORDER BY id', [$pid]);
            $v = $vs[min($vIndex, count($vs) - 1)];
            $price = final_price($p);
            insert('order_items', ['order_id' => $oid, 'product_id' => $pid, 'variant_id' => $v['id'], 'name' => $p['name'], 'size' => $v['size'], 'color' => $v['color'],
                'sku' => $v['sku'], 'image' => $p['image'], 'price' => $price, 'cost_price' => $p['cost_price'], 'qty' => $qty]);
            stock_move((int)$v['id'], -$qty, 'order', 'Order placed', $no, 'customer');
            $sub += $price * $qty;
        }
        $ship = shipping_fee($sub, $zone);
        update('orders', ['subtotal' => $sub, 'shipping' => $ship, 'total' => $sub + $ship], 'id = ?', [$oid]);
    }
}

if (!$done && is_post()) {
    $user = trim($_POST['username'] ?? '');
    $pass = (string)($_POST['password'] ?? '');
    $demo = !empty($_POST['demo']);
    if (strlen($user) < 3 || strlen($pass) < 6) {
        $error = 'Username must be at least 3 characters and password at least 6 characters.';
    } else {
        try {
            if (DB_DRIVER !== 'sqlite') {
                $tmp = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', DB_NAME) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                $tmp = null;
            }
            foreach (schema() as $sql) {
                try { db()->exec($sql); } catch (PDOException $ex) { if (!str_starts_with($sql, 'CREATE INDEX')) throw $ex; }
            }
            seed_settings();
            if (val('SELECT id FROM admins WHERE username = ?', [$user])) {
                q('UPDATE admins SET password = ? WHERE username = ?', [password_hash($pass, PASSWORD_DEFAULT), $user]);
            } else {
                insert('admins', ['username' => $user, 'password' => password_hash($pass, PASSWORD_DEFAULT), 'name' => 'Administrator', 'created_at' => now()]);
            }
            if ($demo) seed_demo();
            if (!is_dir(APP_ROOT . '/data')) mkdir(APP_ROOT . '/data', 0775, true);
            run_migrations();
            try { q("UPDATE admins SET role = 'super', is_active = 1 WHERE username = ?", [$user]); } catch (Throwable $t) {}
            file_put_contents($lock, 'Installed ' . now());
            $ok = true;
        } catch (Throwable $ex) {
            $error = 'Installation failed: ' . $ex->getMessage();
        }
    }
}
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install — Lavanya Shop</title>
<style>
body{margin:0;font-family:system-ui,Segoe UI,Roboto,sans-serif;background:#F5F2EB;color:#2A2230;display:grid;place-items:center;min-height:100vh;padding:20px;box-sizing:border-box}
.card{background:#fff;max-width:440px;width:100%;border-radius:22px;padding:36px;box-shadow:0 20px 60px rgba(15,74,52,.12);border-top:6px solid #A58CC5}
h1{margin:0 0 4px;font-family:Georgia,serif;color:#0F4A34}p{color:#6c6470;line-height:1.6}
label{display:block;font-weight:600;font-size:14px;margin:16px 0 6px}input[type=text],input[type=password]{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid #ddd;border-radius:12px;font-size:15px}
.btn{display:inline-block;margin-top:22px;background:#A41E2C;color:#fff;border:0;padding:13px 26px;border-radius:40px;font-size:15px;font-weight:600;cursor:pointer;text-decoration:none}
.err{background:#fde8ea;color:#A41E2C;padding:12px 14px;border-radius:12px}.ok{background:#e6f2ec;color:#0F4A34;padding:12px 14px;border-radius:12px}
.chk{display:flex;gap:8px;align-items:center;font-weight:400}code{background:#f3eef9;padding:2px 6px;border-radius:6px}
</style></head><body><div class="card">
<h1>Lavanya Installer</h1>
<?php if ($done && !$ok): ?>
  <p class="ok">The shop is already installed.</p>
  <p>For security, please delete <code>install.php</code> from your server.</p>
  <a class="btn" href="index.php">Open shop</a>
<?php elseif ($ok): ?>
  <p class="ok">✔ Installation complete!</p>
  <p>Admin panel: <code>/admin</code><br>Username: <b><?= e($user) ?></b></p>
  <p>For security, please delete <code>install.php</code> now.</p>
  <a class="btn" href="index.php">Open shop</a> &nbsp; <a class="btn" style="background:#0F4A34" href="admin/login.php">Admin panel</a>
<?php else: ?>
  <p>Database: <b><?= DB_DRIVER === 'sqlite' ? 'SQLite (file)' : 'MySQL — ' . e(DB_NAME) . '@' . e(DB_HOST) ?></b><br>
  <small>Change this in <code>config.php</code> before installing.</small></p>
  <?php if ($error): ?><p class="err"><?= e($error) ?></p><?php endif; ?>
  <form method="post">
    <label>Admin username</label><input type="text" name="username" value="<?= e($_POST['username'] ?? 'admin') ?>" required>
    <label>Admin password</label><input type="password" name="password" placeholder="At least 6 characters" required>
    <label class="chk"><input type="checkbox" name="demo" value="1" checked> Add demo products, categories &amp; orders</label>
    <button class="btn">Install now</button>
  </form>
<?php endif; ?>
</div></body></html>
