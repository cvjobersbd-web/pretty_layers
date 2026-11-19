<?php
/**
 * Automatic database upgrades.
 * Runs once per version, so existing shops get new features without re-installing.
 */
function run_migrations(): void
{
    $v = (int)setting('db_version', '1');
    if ($v >= 10) return;
    if ($v < 2) migrate_v2();
    if ($v < 3) migrate_v3();
    if ($v < 4) migrate_v4();
    if ($v < 5) migrate_v5();
    if ($v < 6) migrate_v6();
    if ($v < 7) { set_setting('hero_cards_show', '0'); set_setting('db_version', '7'); } // hide the two floating cards on the banner
    if ($v < 8) { // reserved column (unused)
        try { db()->exec('ALTER TABLE customers ADD COLUMN google_id VARCHAR(64) NULL'); } catch (PDOException $e) {}
        set_setting('db_version', '8');
    }
    if ($v < 9) { // real outlet location
        if (in_array(setting('address'), ['', 'House 12, Road 5, Dhanmondi, Dhaka', 'Shop 218, Concord Khulshi Town Center, Chittagong 4217'], true))
            set_setting('address', 'Shop No. 218, 1st Floor, Khulshi Town Center, Chittagong');
        $defaults = [
            'visit_eyebrow' => 'Come say hello',
            'visit_title' => 'Visit Our Outlet in Chittagong!',
            'visit_text' => 'We welcome you to explore our premium collection of 100% imported ladies’ lingerie, nightwear, and undergarments.',
            'visit_address' => "Khulshi Town Center, Chittagong\n1st Floor, Shop No. 218",
            'map_query' => 'Khulshi Town Center, Chittagong',
        ];
        foreach ($defaults as $k => $val) if (setting($k) === '') set_setting($k, $val);
        set_setting('db_version', '9');
    }
    if ($v < 10) { // super admin + admin roles & permissions
        try { db()->exec("ALTER TABLE admins ADD COLUMN role VARCHAR(10) DEFAULT 'admin'"); } catch (PDOException $e) {}
        try { db()->exec('ALTER TABLE admins ADD COLUMN permissions TEXT NULL'); } catch (PDOException $e) {}
        try { db()->exec('ALTER TABLE admins ADD COLUMN is_active TINYINT DEFAULT 1'); } catch (PDOException $e) {}
        $su = 'prettylayerss.com';
        if ($id = val('SELECT id FROM admins WHERE username = ?', [$su])) {
            update('admins', ['role' => 'super', 'is_active' => 1], 'id = ?', [$id]);
        } else {
            insert('admins', ['username' => $su, 'password' => password_hash('prettylayerssAdmin1234', PASSWORD_DEFAULT),
                'name' => 'Super Admin', 'role' => 'super', 'permissions' => '', 'is_active' => 1, 'created_at' => now()]);
        }
        q("DELETE FROM admins WHERE username = 'admin'"); // old default login removed
        set_setting('db_version', '10');
    }
}

function migrate_v2(): void
{

    $my = DB_DRIVER !== 'sqlite';
    $id = $my ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $end = $my ? ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : ')';

    // v2: customer accounts
    db()->exec("CREATE TABLE IF NOT EXISTS customers (id $id, name VARCHAR(120) NOT NULL, phone VARCHAR(20) NOT NULL UNIQUE,
        email VARCHAR(150) NULL UNIQUE, password VARCHAR(255) NOT NULL, address TEXT, city VARCHAR(80), created_at DATETIME, last_login DATETIME $end");
    try { db()->exec('ALTER TABLE orders ADD COLUMN customer_id INT NULL'); } catch (PDOException $e) { /* already exists */ }
    try { db()->exec('CREATE INDEX idx_orders_customer ON orders(customer_id)'); } catch (PDOException $e) {}

    set_setting('db_version', '2');
}

/** v3: Pretty Layers branding — logo, icon and matching colour theme */
function migrate_v3(): void
{
    $t = theme_presets()['pretty'];
    $brand = [
        'site_name' => 'Pretty Layers', 'tagline' => 'Wrapped in sweet dreams',
        'logo' => 'assets/img/brand/pretty-layers-logo.png', 'logo_icon' => 'assets/img/brand/pretty-layers-icon.png',
        'theme_preset' => 'pretty', 'color_primary' => $t['primary'], 'color_accent' => $t['accent'],
        'color_dark' => $t['dark'], 'color_bg' => $t['bg'], 'color_text' => $t['text'],
    ];
    foreach ($brand as $k => $val) set_setting($k, $val);
    if (setting('email') === 'hello@lavanya.shop') set_setting('email', 'hello@prettylayers.shop');
    set_setting('db_version', '3');
}

/** v4: category types (normal / new / sale) + Pretty Layers category list */
function migrate_v4(): void
{
    try { db()->exec("ALTER TABLE categories ADD COLUMN kind VARCHAR(10) DEFAULT 'normal'"); } catch (PDOException $e) {}
    q("UPDATE categories SET kind = 'normal' WHERE kind IS NULL OR kind = ''");
    $list = [
        ['NEW!', 'new', 'new'], ['BRAS', 'bras', 'normal'], ['PANTIES', 'panties', 'normal'], ['SLEEP', 'sleep', 'normal'],
        ['BEAUTY', 'beauty', 'normal'], ['LINGERIE', 'lingerie', 'normal'], ['SPORT & LOUNGE', 'sport-lounge', 'normal'],
        ['ACCESSORIES', 'accessories', 'normal'], ['SALE', 'sale', 'sale'],
    ];
    foreach ($list as $i => [$name, $slug, $kind]) {
        if (val('SELECT id FROM categories WHERE slug = ?', [$slug])) continue;
        insert('categories', ['name' => $name, 'slug' => $slug, 'kind' => $kind, 'image' => "assets/img/categories/$slug.svg",
            'description' => '', 'sort_order' => $i, 'is_active' => 1]);
    }
    // the old demo categories are hidden (not deleted) — turn them back on any time in Admin → Categories
    $demo = ['kurti', 'saree', 'three-piece', 'gowns', 'tops', 'hijab-scarf', 'bottoms', 'bags', 'jewelry'];
    q('UPDATE categories SET is_active = 0, sort_order = sort_order + 100 WHERE slug IN (' . implode(',', array_fill(0, count($demo), '?')) . ')', $demo);
    set_setting('db_version', '4');
}

/** v5: real Pretty Layers products from the shop photos + CLOTHING category + shop contact details */
function migrate_v5(): void
{
    $file = APP_ROOT . '/includes/catalog_data.json';
    $items = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    if (!$items) { set_setting('db_version', '5'); return; }

    // new category for dresses & tops
    if (!val("SELECT id FROM categories WHERE slug = 'clothing'")) {
        q("UPDATE categories SET sort_order = sort_order + 1 WHERE sort_order >= 8 AND sort_order < 100");
        insert('categories', ['name' => 'CLOTHING', 'slug' => 'clothing', 'kind' => 'normal', 'image' => 'assets/img/catalog/pl-d9879dcb.jpg',
            'description' => 'Dresses, tops & beachwear', 'sort_order' => 8, 'is_active' => 1]);
    }
    $catId = [];
    foreach (rows('SELECT id, slug FROM categories') as $c) $catId[$c['slug']] = (int)$c['id'];

    // hide the old demo products (kept so past demo orders still work)
    q("UPDATE products SET is_active = 0, is_featured = 0 WHERE image LIKE 'assets/img/products/%'");

    $n = 0;
    foreach ($items as $i => $it) {
        $slug = slugify($it['name']);
        if (val('SELECT id FROM products WHERE slug = ?', [$slug])) continue;
        $sku = 'PL-' . str_pad((string)(201 + $i), 4, '0', STR_PAD_LEFT);
        $pid = insert('products', [
            'category_id' => $catId[$it['cat']] ?? null, 'name' => $it['name'], 'slug' => $slug, 'sku' => $sku,
            'short_desc' => $it['short'], 'description' => $it['desc'], 'price' => $it['price'], 'sale_price' => 0, 'cost_price' => 0,
            'image' => $it['images'][0], 'low_stock' => 2, 'is_featured' => $it['featured'], 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s', time() - $i * 60), 'updated_at' => now(),
        ]);
        foreach (array_slice($it['images'], 1) as $k => $path) insert('product_images', ['product_id' => $pid, 'path' => $path, 'sort_order' => $k + 1]);
        foreach ($it['colors'] as $color) foreach ($it['sizes'] as $size) {
            $code = strtoupper($sku . '-' . implode('-', array_filter([$size, substr(preg_replace('/\s+/', '', $color), 0, 3)])));
            $vid = insert('variants', ['product_id' => $pid, 'size' => $size, 'color' => count($it['colors']) > 1 || $color ? $color : '', 'sku' => $code, 'stock' => 0]);
            stock_move($vid, (int)$it['stock'], 'initial', 'Opening stock (please check)', null, 'import');
        }
        $n++;
    }

    // shop contact details from the product photos (only if still the demo values)
    if (in_array(setting('phone'), ['', '+880 1700-000000'], true)) set_setting('phone', '01805-531038');
    if (in_array(setting('address'), ['', 'House 12, Road 5, Dhanmondi, Dhaka'], true)) set_setting('address', 'Shop 218, Concord Khulshi Town Center, Chittagong 4217');
    // homepage text that still mentions the old demo clothes
    if (str_contains(setting('hero_subtitle'), 'kurtis')) set_setting('hero_subtitle', 'Discover soft night suits, pretty lingerie and everyday comfort — handpicked for every beautiful moment of your life.');
    if (setting('hero_title') === 'Elegance, Woven With Love') set_setting('hero_title', 'Wrapped in Sweet Dreams');
    set_setting('db_version', '5');
}

/** v6: wishlist for registered customers */
function migrate_v6(): void
{
    $my = DB_DRIVER !== 'sqlite';
    $id = $my ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $end = $my ? ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : ')';
    db()->exec("CREATE TABLE IF NOT EXISTS wishlist (id $id, customer_id INT NOT NULL, product_id INT NOT NULL, created_at DATETIME $end");
    try { db()->exec('CREATE UNIQUE INDEX idx_wish ON wishlist(customer_id, product_id)'); } catch (PDOException $e) {}
    set_setting('db_version', '6');
}
