<?php
/** Shared helper functions */

/* ---------------- basic ---------------- */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function url(string $path = ''): string { return ($GLOBALS['ROOT'] ?? '') . ltrim($path, '/'); }

function redirect(string $to): void { header('Location: ' . $to); exit; }

function img_url(?string $path): string
{
    if (!$path) return url('assets/img/ui/placeholder.svg');
    if (preg_match('~^https?://~', $path)) return $path;
    return url($path);
}

function money($n): string
{
    return setting('currency_symbol', '৳') . ' ' . number_format((float)$n, 0);
}

function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('~[^a-z0-9]+~', '-', $s);
    $s = trim($s, '-');
    return $s ?: 'item-' . substr(md5(uniqid()), 0, 6);
}

function unique_slug(string $table, string $base, int $ignoreId = 0): string
{
    $slug = slugify($base); $try = $slug; $i = 2;
    while (val("SELECT id FROM $table WHERE slug = ? AND id <> ?", [$try, $ignoreId])) { $try = $slug . '-' . $i++; }
    return $try;
}

function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

function post($k, $d = '') { return isset($_POST[$k]) ? (is_string($_POST[$k]) ? trim($_POST[$k]) : $_POST[$k]) : $d; }

function get($k, $d = '') { return isset($_GET[$k]) ? (is_string($_GET[$k]) ? trim($_GET[$k]) : $_GET[$k]) : $d; }

/* ---------------- flash + csrf ---------------- */
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }

function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }

function csrf_check(): void
{
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Session expired. Please go back and try again.');
    }
}

/* ---------------- settings ---------------- */
function settings_all(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        try { foreach (rows('SELECT skey, svalue FROM settings') as $r) $cache[$r['skey']] = $r['svalue']; } catch (Throwable $t) {}
    }
    return $cache;
}

function setting(string $key, $default = '')
{
    $all = settings_all();
    return (isset($all[$key]) && $all[$key] !== '') ? $all[$key] : $default;
}

function set_setting(string $key, $value): void
{
    if (val('SELECT COUNT(*) FROM settings WHERE skey = ?', [$key])) {
        q('UPDATE settings SET svalue = ? WHERE skey = ?', [(string)$value, $key]);
    } else {
        q('INSERT INTO settings (skey, svalue) VALUES (?, ?)', [$key, (string)$value]);
    }
    settings_all(true);
}

/* ---------------- theme / colours ---------------- */
function theme_presets(): array
{
    return [
        'pretty'   => ['name' => 'Pretty Layers',   'primary' => '#C27BD6', 'accent' => '#B01FB3', 'dark' => '#4B1648', 'bg' => '#FCF6FB', 'text' => '#2E1F2D'],
        'pantone'  => ['name' => 'Pantone Classic', 'primary' => '#A58CC5', 'accent' => '#A41E2C', 'dark' => '#0F4A34', 'bg' => '#F5F2EB', 'text' => '#2A2230'],
        'rose'     => ['name' => 'Rose Blush',      'primary' => '#E59BB0', 'accent' => '#C2185B', 'dark' => '#4A1F3D', 'bg' => '#FFF6F7', 'text' => '#2E1F28'],
        'lavender' => ['name' => 'Lavender Dream',  'primary' => '#B39DDB', 'accent' => '#7E57C2', 'dark' => '#2E1B4E', 'bg' => '#FAF7FF', 'text' => '#2A2238'],
        'gold'     => ['name' => 'Royal Gold',      'primary' => '#D4A843', 'accent' => '#B23A48', 'dark' => '#1F2A44', 'bg' => '#FBF8F1', 'text' => '#1E2230'],
        'mint'     => ['name' => 'Mint & Peach',    'primary' => '#8CC7B5', 'accent' => '#E9775A', 'dark' => '#24564B', 'bg' => '#FBF7F2', 'text' => '#22312D'],
    ];
}

function hex_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

/** mix colour $a with $b; $w = weight of $b (0..1) */
function mix(string $a, string $b, float $w): string
{
    [$r1, $g1, $b1] = hex_rgb($a); [$r2, $g2, $b2] = hex_rgb($b);
    return sprintf('#%02x%02x%02x', round($r1 + ($r2 - $r1) * $w), round($g1 + ($g2 - $g1) * $w), round($b1 + ($b2 - $b1) * $w));
}

function valid_hex($c): bool { return (bool)preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string)$c); }

function theme_css(): string
{
    $p = theme_presets()['pretty'];
    $c = [
        'primary' => setting('color_primary', $p['primary']),
        'accent'  => setting('color_accent', $p['accent']),
        'dark'    => setting('color_dark', $p['dark']),
        'bg'      => setting('color_bg', $p['bg']),
        'text'    => setting('color_text', $p['text']),
    ];
    foreach ($c as $k => $v) if (!valid_hex($v)) $c[$k] = $p[$k];
    [$pr, $pg, $pb] = hex_rgb($c['primary']);
    [$ar, $ag, $ab] = hex_rgb($c['accent']);
    [$dr, $dg, $db] = hex_rgb($c['dark']);
    return ":root{"
        . "--primary:{$c['primary']};--primary-rgb:$pr,$pg,$pb;"
        . "--primary-dark:" . mix($c['primary'], '#000000', .22) . ";"
        . "--primary-soft:" . mix($c['primary'], '#ffffff', .72) . ";"
        . "--primary-softer:" . mix($c['primary'], '#ffffff', .88) . ";"
        . "--accent:{$c['accent']};--accent-rgb:$ar,$ag,$ab;"
        . "--accent-dark:" . mix($c['accent'], '#000000', .2) . ";"
        . "--accent-soft:" . mix($c['accent'], '#ffffff', .86) . ";"
        . "--dark:{$c['dark']};--dark-rgb:$dr,$dg,$db;"
        . "--dark-soft:" . mix($c['dark'], '#ffffff', .85) . ";"
        . "--bg:{$c['bg']};"
        . "--surface:" . mix($c['bg'], '#ffffff', .6) . ";"
        . "--line:" . mix($c['bg'], '#000000', .08) . ";"
        . "--text:{$c['text']};"
        . "--muted:" . mix($c['text'], $c['bg'], .45) . ";"
        . "}";
}

/* ---------------- uploads ---------------- */
/** Saves an uploaded image, returns relative path like uploads/products/abc.jpg or null */
function save_upload(array $file, string $subdir, ?string &$error = null): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $error = 'Upload failed (code ' . $file['error'] . ').'; return null; }
    if ($file['size'] > 5 * 1024 * 1024) { $error = 'Image must be smaller than 5 MB.'; return null; }
    $ext = image_type($file['tmp_name']);
    if (!$ext) { $error = 'Only JPG, PNG, WEBP, AVIF or GIF images are allowed.'; return null; }
    $dir = APP_ROOT . '/uploads/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], "$dir/$name")) { $error = 'Could not save the file. Check folder permissions of /uploads.'; return null; }
    return "uploads/$subdir/$name";
}

/** Normalise a multi-file input ($_FILES['x']) into a list of single files */
function files_list(array $f): array
{
    $out = [];
    if (!isset($f['name']) || !is_array($f['name'])) return $out;
    foreach ($f['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}

function delete_upload(?string $path): void
{
    if ($path && str_starts_with($path, 'uploads/') && is_file(APP_ROOT . '/' . $path)) @unlink(APP_ROOT . '/' . $path);
}

/* ---------------- products ---------------- */
function product_sql(): string
{
    return "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
              (SELECT COALESCE(SUM(v.stock),0) FROM variants v WHERE v.product_id = p.id) AS total_stock,
              (SELECT COUNT(*) FROM variants v WHERE v.product_id = p.id) AS variant_count,
              (SELECT MIN(v.id) FROM variants v WHERE v.product_id = p.id) AS first_variant,
              (SELECT COALESCE(SUM(oi.qty),0) FROM order_items oi JOIN orders o ON o.id = oi.order_id
                 WHERE oi.product_id = p.id AND o.status <> 'cancelled') AS sold
            FROM products p LEFT JOIN categories c ON c.id = p.category_id";
}

function final_price(array $p): float
{
    return ((float)$p['sale_price'] > 0 && (float)$p['sale_price'] < (float)$p['price']) ? (float)$p['sale_price'] : (float)$p['price'];
}

function discount_pct(array $p): int
{
    $f = final_price($p);
    return $f < (float)$p['price'] ? (int)round(100 - $f / (float)$p['price'] * 100) : 0;
}

function stock_status(int $stock, int $threshold): string
{
    if ($stock <= 0) return 'out';
    if ($stock <= $threshold) return 'low';
    return 'in';
}

/* ---------------- inventory ---------------- */
/**
 * Change stock of a variant and record it in the stock history.
 * Negative $delta never takes stock below zero (returns false instead).
 */
function stock_move(int $variantId, int $delta, string $type, string $note = '', ?string $ref = null, string $by = 'system'): bool
{
    if ($delta === 0) return true;
    if ($delta < 0) {
        $ok = q('UPDATE variants SET stock = stock + ? WHERE id = ? AND stock >= ?', [$delta, $variantId, -$delta])->rowCount();
        if (!$ok) return false;
    } else {
        q('UPDATE variants SET stock = stock + ? WHERE id = ?', [$delta, $variantId]);
    }
    $v = row('SELECT product_id, stock FROM variants WHERE id = ?', [$variantId]);
    insert('stock_movements', [
        'variant_id' => $variantId, 'product_id' => $v['product_id'], 'qty_change' => $delta,
        'balance_after' => $v['stock'], 'type' => $type, 'note' => $note, 'ref' => $ref, 'created_by' => $by, 'created_at' => now(),
    ]);
    return true;
}

function variant_label(array $v): string
{
    $parts = array_filter([$v['size'] ?? '', $v['color'] ?? '']);
    return $parts ? implode(' / ', $parts) : 'Standard';
}

/* ---------------- cart ---------------- */
function cart(): array { return $_SESSION['cart'] ?? []; }

function cart_set(int $variantId, int $qty): void
{
    if ($qty <= 0) unset($_SESSION['cart'][$variantId]);
    else $_SESSION['cart'][$variantId] = $qty;
}

function cart_count(): int { return array_sum(cart()); }

/** Returns cart lines with live price & stock (quantities are clamped to stock) */
function cart_lines(): array
{
    $cart = cart();
    if (!$cart) return [];
    $ids = array_map('intval', array_keys($cart));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $list = rows("SELECT v.id AS variant_id, v.size, v.color, v.stock, v.sku AS vsku, p.*
                  FROM variants v JOIN products p ON p.id = v.product_id
                  WHERE v.id IN ($in) AND p.is_active = 1", $ids);
    $lines = [];
    foreach ($list as $r) {
        $qty = min((int)$cart[$r['variant_id']], (int)$r['stock']);
        if ($qty <= 0) { unset($_SESSION['cart'][$r['variant_id']]); continue; }
        $_SESSION['cart'][$r['variant_id']] = $qty;
        $price = final_price($r);
        $r['qty'] = $qty; $r['unit'] = $price; $r['line_total'] = $price * $qty;
        $lines[] = $r;
    }
    foreach ($ids as $id) if (!array_filter($list, fn($r) => (int)$r['variant_id'] === $id)) unset($_SESSION['cart'][$id]);
    return $lines;
}

function shipping_fee(float $subtotal, string $zone): float
{
    $free = (float)setting('free_shipping_over', 0);
    if ($free > 0 && $subtotal >= $free) return 0;
    return $zone === 'inside' ? (float)setting('shipping_inside', 70) : (float)setting('shipping_outside', 130);
}

function order_statuses(): array
{
    return ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
}

function payment_methods(): array
{
    $m = ['cod' => 'Cash on Delivery'];
    if (setting('bkash_number')) $m['bkash'] = 'bKash';
    if (setting('nagad_number')) $m['nagad'] = 'Nagad';
    return $m;
}

/* ---------------- logo ---------------- */
function logo_html(string $class = 'logo'): string
{
    $name = setting('site_name', 'Lavanya');
    $logo = setting('logo');
    if ($logo) return '<img class="' . $class . '-img" src="' . e(img_url($logo)) . '" alt="' . e($name) . '">';
    return '<span class="' . $class . '-text"><span class="logo-mark">' . e(mb_substr($name, 0, 1)) . '</span>' . e($name) . '</span>';
}

/* ---------------- customer accounts ---------------- */
/** Logged-in customer (fresh from DB) or null */
function customer(): ?array
{
    static $c = false;
    if ($c !== false) return $c;
    $id = $_SESSION['customer_id'] ?? 0;
    $c = $id ? row('SELECT * FROM customers WHERE id = ?', [$id]) : null;
    if ($id && !$c) unset($_SESSION['customer_id']);
    return $c;
}

/** Normalise a Bangladeshi mobile number to 01XXXXXXXXX, or '' if invalid */
function normalize_phone(string $p): string
{
    $d = preg_replace('/\D/', '', $p);
    if (str_starts_with($d, '880')) $d = substr($d, 2);
    return preg_match('/^01[3-9]\d{8}$/', $d) ? $d : '';
}

function customer_login(array $c): void
{
    session_regenerate_id(true);
    $_SESSION['customer_id'] = (int)$c['id'];
    q('UPDATE customers SET last_login = ? WHERE id = ?', [now(), $c['id']]);
    // move the guest wishlist into the account
    foreach ($_SESSION['wish'] ?? [] as $pid)
        if (!val('SELECT id FROM wishlist WHERE customer_id = ? AND product_id = ?', [$c['id'], $pid]))
            insert('wishlist', ['customer_id' => $c['id'], 'product_id' => (int)$pid, 'created_at' => now()]);
    unset($_SESSION['wish']);
}

/** Only allow redirects back to our own pages */
function safe_next(string $next, string $fallback): string
{
    return preg_match('~^[a-z0-9_\-]+\.php(\?[^\s]*)?$~i', $next) ? $next : $fallback;
}

/** Small square-ish brand mark (loader + favicon); falls back to the main logo */
function logo_icon(): string
{
    return setting('logo_icon') ?: setting('logo');
}

/* ---------------- loading animation ---------------- */
function loader_styles(): array
{
    return ['rings' => 'Spinning rings', 'ripple' => 'Water ripple', 'orbit' => 'Orbit dots', 'flip' => '3D coin flip',
            'bounce' => 'Bounce', 'shine' => 'Glow & shine', 'heart' => 'Heartbeat', 'sparkle' => 'Sparkle ring'];
}
function loader_bgs(): array
{
    return ['soft' => 'Soft (theme)', 'white' => 'Plain white', 'color' => 'Theme colour', 'dark' => 'Dark'];
}
/** Loader markup. $o overrides saved settings (used by the admin previews). */
function loader_markup(array $o = []): string
{
    $style = $o['style'] ?? setting('loader_style', 'rings');
    if (!isset(loader_styles()[$style])) $style = 'rings';
    $showName = $o['name'] ?? (setting('loader_name', '1') === '1');
    $showBar  = $o['bar'] ?? (setting('loader_bar', '1') === '1');
    $siteName = setting('site_name', 'Lavanya');
    $icon = logo_icon();
    $h = '<div class="ldr ld-' . $style . ($showName ? '' : ' no-name') . ($showBar ? '' : ' no-bar') . '"><div class="loader-orb">'
       . '<span class="ld-x ld-ring"></span><span class="ld-x ld-ring r2"></span>'
       . '<span class="ld-x ld-wave"></span><span class="ld-x ld-wave w2"></span><span class="ld-x ld-wave w3"></span>'
       . '<span class="ld-x ld-track"></span><span class="ld-x ld-dots"><i></i><i></i><i></i></span>'
       . '<span class="ld-x ld-halo"></span><span class="ld-x ld-dash"></span><span class="ld-x ld-dash d2"></span><span class="ld-x ld-shadow"></span>'
       . '<div class="loader-logo">' . ($icon ? '<img src="' . e(img_url($icon)) . '" alt="">' : '<span>' . e(mb_substr($siteName, 0, 1)) . '</span>') . '</div></div>'
       . '<div class="loader-name">';
    foreach (preg_split('//u', $siteName, -1, PREG_SPLIT_NO_EMPTY) as $i => $ch)
        $h .= '<span style="animation-delay:' . ($i * 0.08) . 's">' . ($ch === ' ' ? '&nbsp;' : e($ch)) . '</span>';
    return $h . '</div><div class="loader-bar"><i></i></div></div>';
}

/** <link rel="icon"> tag for the browser tab (storefront + admin) */
function favicon_tag(): string
{
    $icon = logo_icon();
    if ($icon) {
        $src = img_url($icon);
        $f = __DIR__ . '/../' . ltrim($icon, '/');
        if (!preg_match('~^https?://~', $icon) && is_file($f)) $src .= '?v=' . filemtime($f);
        return '<link rel="icon" href="' . e($src) . '"><link rel="apple-touch-icon" href="' . e($src) . '">';
    }
    $l = e(mb_substr(setting('site_name', 'Lavanya'), 0, 1));
    return "<link rel=\"icon\" href=\"data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='30' fill='%23A58CC5'/><text x='32' y='44' font-size='34' text-anchor='middle' fill='white' font-family='Georgia'>$l</text></svg>\">";
}

/** Homepage banner images uploaded by the admin */
function hero_images(): array
{
    $list = json_decode((string)setting('hero_images', '[]'), true);
    return is_array($list) ? array_values(array_filter($list, 'is_string')) : [];
}

/** Detect a real image file by its content; returns extension or null */
function image_type(string $tmp): ?string
{
    $head = (string)@file_get_contents($tmp, false, null, 0, 32);
    // AVIF / HEIF-AVIF: "....ftypavif" or "....ftypavis" (older PHP getimagesize can't read it)
    if (strlen($head) >= 12 && substr($head, 4, 4) === 'ftyp' && in_array(substr($head, 8, 4), ['avif', 'avis'], true)) return 'avif';
    $info = @getimagesize($tmp);
    if (!$info) return null;
    $map = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    if (defined('IMAGETYPE_AVIF')) $map[IMAGETYPE_AVIF] = 'avif';
    return $map[$info[2]] ?? null;
}

/** Asset URL with a version that changes whenever the file changes (no stale browser cache) */
function asset(string $path): string
{
    $f = APP_ROOT . '/' . $path;
    return url($path) . '?v=' . (is_file($f) ? filemtime($f) : '1');
}

/* ---------------- categories ---------------- */
function category_kinds(): array
{
    return ['normal' => 'Normal category', 'new' => 'New arrivals (automatic)', 'sale' => 'Sale items (automatic)'];
}

/** SQL condition for "new" products (added in the last N days) */
function new_days(): int { return max(1, (int)setting('new_days', 30)); }

/** Active categories with product counts (automatic ones counted by rule) */
function categories_with_counts(int $limit = 0): array
{
    $cats = rows('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS cnt
                  FROM categories c WHERE c.is_active = 1 ORDER BY c.sort_order, c.name' . ($limit ? " LIMIT $limit" : ''));
    $newCnt = null; $saleCnt = null;
    foreach ($cats as &$c) {
        if (($c['kind'] ?? 'normal') === 'new') $c['cnt'] = $newCnt ??= (int)val('SELECT COUNT(*) FROM products WHERE is_active = 1 AND created_at >= ?', [date('Y-m-d H:i:s', time() - new_days() * 86400)]);
        if (($c['kind'] ?? 'normal') === 'sale') $c['cnt'] = $saleCnt ??= (int)val('SELECT COUNT(*) FROM products WHERE is_active = 1 AND sale_price > 0 AND sale_price < price');
    }
    return $cats;
}

/** Categories a product can belong to (not the automatic ones) */
function product_categories(): array
{
    return rows("SELECT id, name, is_active FROM categories WHERE kind = 'normal' OR kind IS NULL ORDER BY is_active DESC, sort_order, name");
}

/* ---------------- wishlist ---------------- */
/** Product ids in the wishlist (account for logged-in customers, session for guests) */
function wishlist_ids(): array
{
    static $ids = null;
    if ($ids !== null) return $ids;
    $cid = $_SESSION['customer_id'] ?? 0;
    $ids = $cid ? array_map('intval', array_column(rows('SELECT product_id FROM wishlist WHERE customer_id = ? ORDER BY id DESC', [$cid]), 'product_id'))
                : array_values(array_map('intval', $_SESSION['wish'] ?? []));
    return $ids;
}

/** Add or remove a product; returns true if it is now in the wishlist */
function wishlist_toggle(int $pid): bool
{
    $cid = $_SESSION['customer_id'] ?? 0;
    $in = in_array($pid, wishlist_ids(), true);
    if ($cid) {
        if ($in) q('DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?', [$cid, $pid]);
        else insert('wishlist', ['customer_id' => $cid, 'product_id' => $pid, 'created_at' => now()]);
    } else {
        $w = array_map('intval', $_SESSION['wish'] ?? []);
        $_SESSION['wish'] = $in ? array_values(array_diff($w, [$pid])) : array_merge([$pid], $w);
    }
    return !$in;
}

function wish_button(int $pid, string $class = 'wish-btn'): string
{
    $on = in_array($pid, wishlist_ids(), true);
    return '<button type="button" class="' . $class . ($on ? ' on' : '') . '" data-wish="' . $pid . '" aria-label="Add to wishlist" title="Wishlist">'
        . '<svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M12 20.5s-7.5-4.6-9.2-9.4C1.6 7.6 3.9 4 7.4 4c2 0 3.6 1.1 4.6 2.7C13 5.1 14.6 4 16.6 4c3.5 0 5.8 3.6 4.6 7.1-1.7 4.8-9.2 9.4-9.2 9.4z"/></svg></button>';
}

/* ---------------- editable homepage / navbar texts ---------------- */
function home_feature_defaults(): array
{
    return [
        ['Nationwide Delivery', 'Free over ' . money(setting('free_shipping_over', 3000))],
        ['Cash on Delivery', 'Pay when you receive'],
        ['Easy 7-Day Exchange', 'Size not right? No worries'],
        ['Friendly Support', setting('phone')],
    ];
}

/** Navbar menu items: key => [default label, url, active-check] */
function nav_items(): array
{
    return [
        'home'  => ['Home', 'index.php'],
        'shop'  => ['Shop', 'shop.php'],
        'cats'  => ['Categories', 'shop.php'],
        'new'   => ['New In', 'shop.php?new=1&sort=new'],
        'track' => ['Track Order', 'track.php'],
    ];
}
function nav_label(string $k): string { return setting("nav_{$k}_label", nav_items()[$k][0]); }
function nav_show(string $k): bool { return setting("nav_{$k}_show", '1') === '1'; }

/** Homepage promo cards (3 image cards under the categories) */
function promo_defaults(): array
{
    return [
        1 => ['eyebrow' => 'Sweet dreams', 'title' => "Cosy Night\nSuits", 'link_text' => 'Shop sleepwear →', 'cat' => 'sleep', 'image' => 'assets/img/catalog/pl-af5c3d9d.jpg'],
        2 => ['eyebrow' => 'Lace & love', 'title' => "Pretty\nLingerie", 'link_text' => 'Explore now →', 'cat' => 'lingerie', 'image' => 'assets/img/catalog/pl-c9c6d158.jpg'],
        3 => ['eyebrow' => 'New styles', 'title' => "Dresses\n& Tops", 'link_text' => 'Discover →', 'cat' => 'clothing', 'image' => 'assets/img/catalog/pl-d9879dcb.jpg'],
    ];
}
function promo_styles(): array
{
    return ['arch' => 'Arch window', 'overlay' => 'Full photo', 'card' => 'Photo card', 'soft' => 'Soft pastel'];
}
/** One promo card with saved values falling back to defaults */
function promo(int $n): array
{
    $d = promo_defaults()[$n];
    $p = [];
    foreach (['eyebrow', 'title', 'link_text', 'cat', 'image'] as $k) $p[$k] = setting("promo{$n}_{$k}", $d[$k]);
    $p['link'] = setting("promo{$n}_link");
    $p['show'] = setting("promo{$n}_show", '1') === '1';
    $p['url'] = $p['link'] !== '' ? $p['link'] : url('shop.php' . ($p['cat'] !== '' ? '?cat=' . urlencode($p['cat']) : ''));
    return $p;
}

/* ---------------- homepage sections on/off ---------------- */
function home_sections(): array
{
    // key => [label, setting key]
    return [
        'hero' => ['Top banner (hero)', 'hero_show'], 'features' => ['Feature boxes', 'features_show'],
        'cats' => ['Shop by Category', 'cats_show'], 'promos' => ['Promo cards', 'promos_show'],
        'products' => ['Product tabs (Loved by Women…)', 'products_show'], 'sale' => ['Sale banner with timer', 'sale_show'],
        'visit' => ['Visit Our Store', 'visit_show'], 'newsletter' => ['Newsletter', 'newsletter_show'],
    ];
}
function section_on(string $k): bool { return setting(home_sections()[$k][1], '1') === '1'; }

/* ---------------- homepage product tabs ---------------- */
function product_tabs(): array
{
    return ['new' => 'New Arrivals', 'feat' => 'Featured', 'best' => 'Best Sellers', 'sale' => 'On Sale'];
}
function tab_ids(string $k): array
{
    $ids = json_decode((string)setting("tab_{$k}_ids", '[]'), true);
    return is_array($ids) ? array_values(array_map('intval', $ids)) : [];
}
/** Products for one tab: hand-picked (manual) or automatic */
function tab_products(string $k): array
{
    $limit = max(1, min(24, (int)setting('products_count', 8)));
    $base = product_sql() . ' WHERE p.is_active = 1';
    if (setting("tab_{$k}_mode", 'auto') === 'manual' && ($ids = tab_ids($k))) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $byId = array_column(rows($base . " AND p.id IN ($in)", $ids), null, 'id');
        $out = [];
        foreach ($ids as $id) if (isset($byId[$id])) $out[] = $byId[$id];
        return array_slice($out, 0, 24);
    }
    $q = [
        'new'  => ' ORDER BY p.created_at DESC, p.id DESC',
        'feat' => ' AND p.is_featured = 1 ORDER BY p.id DESC',
        'best' => ' ORDER BY sold DESC, p.id DESC',
        'sale' => ' AND p.sale_price > 0 AND p.sale_price < p.price ORDER BY p.id DESC',
    ][$k];
    return rows($base . $q . " LIMIT $limit");
}

/** True when the customer has a valid mobile number saved */
function has_real_phone(array $c): bool { return normalize_phone((string)$c['phone']) !== ''; }
function shown_phone(array $c): string { return has_real_phone($c) ? $c['phone'] : ''; }

/* ---------------- social media (Admin → Footer Setting) ---------------- */
function social_networks(): array
{
    $i = fn($d, $fill = true) => '<svg width="18" height="18" viewBox="0 0 24 24" ' . ($fill ? 'fill="currentColor"' : 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"') . '>' . $d . '</svg>';
    return [
        'facebook'  => ['Facebook', 'https://facebook.com/yourpage', $i('<path d="M14 8h3V4h-3c-2.8 0-4 1.8-4 4.3V10H7v4h3v8h4v-8h3l1-4h-4V8.6c0-.4.3-.6.6-.6z"/>')],
        'instagram' => ['Instagram', 'https://instagram.com/yourpage', $i('<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>', false)],
        'tiktok'    => ['TikTok', 'https://tiktok.com/@yourpage', $i('<path d="M16 3c.4 2.4 1.9 4 4.5 4.3v3.3c-1.7 0-3.2-.5-4.5-1.4v6.3A5.6 5.6 0 1 1 10.4 10v3.4a2.3 2.3 0 1 0 2.2 2.3V3z"/>')],
        'youtube'   => ['YouTube', 'https://youtube.com/@yourchannel', $i('<path d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12 31 31 0 0 0 2 15.8a3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1c.3-1.2.4-2.5.4-3.8s-.1-2.6-.4-3.8zM10 15V9l5.2 3z"/>')],
        'whatsapp'  => ['WhatsApp', '01XXXXXXXXX or https://wa.me/8801…', $i('<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.3c.3-.3.6-.3.8-.3h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.1 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.7-.1l2 1c.3.1.5.2.5.3.1.2.1.8-.1 1.3z"/>')],
        'messenger' => ['Messenger', 'https://m.me/yourpage', $i('<path d="M12 2C6.4 2 2 6.1 2 11.3c0 2.9 1.4 5.5 3.7 7.2V22l3.4-1.9c.9.3 1.9.4 2.9.4 5.6 0 10-4.1 10-9.2S17.6 2 12 2zm1 12.4-2.5-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7z"/>')],
        'x'         => ['X (Twitter)', 'https://x.com/yourpage', $i('<path d="M17.8 3h3.1l-6.8 7.8L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.5z"/>')],
        'pinterest' => ['Pinterest', 'https://pinterest.com/yourpage', $i('<path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.4 1.8-2.4.9 0 1.3.6 1.3 1.4 0 .8-.5 2.1-.8 3.3-.2 1 .5 1.8 1.5 1.8 1.8 0 3.2-1.9 3.2-4.7 0-2.4-1.8-4.2-4.3-4.2-2.9 0-4.6 2.2-4.6 4.4 0 .9.3 1.8.8 2.3l.1.4-.3 1.2c0 .2-.2.3-.4.2-1.3-.6-2.1-2.5-2.1-4 0-3.3 2.4-6.3 6.9-6.3 3.6 0 6.4 2.6 6.4 6 0 3.6-2.3 6.5-5.4 6.5-1.1 0-2.1-.6-2.4-1.2l-.7 2.5c-.2.9-.9 2.1-1.3 2.8A10 10 0 1 0 12 2z"/>')],
    ];
}

/** Filled-in social links: key => [label, url, svg] */
function social_links(): array
{
    $out = [];
    foreach (social_networks() as $k => [$label, , $svg]) {
        $v = trim((string)setting("social_{$k}", in_array($k, ['facebook', 'instagram'], true) ? setting($k) : ''));
        if ($v === '' || $v === '#') continue;
        if ($k === 'whatsapp' && !preg_match('~^https?://~', $v)) {
            $d = preg_replace('/\D/', '', $v);
            if (str_starts_with($d, '01')) $d = '88' . $d;
            $v = 'https://wa.me/' . $d;
        } elseif (!preg_match('~^https?://~', $v)) $v = 'https://' . ltrim($v, '/');
        $out[$k] = [$label, $v, $svg];
    }
    return $out;
}

function social_icons_html(string $class = 'socials'): string
{
    $links = social_links();
    if (!$links) return '';
    $h = '<div class="' . $class . '">';
    foreach ($links as $k => [$label, $url, $svg]) $h .= '<a href="' . e($url) . '" target="_blank" rel="noopener" aria-label="' . e($label) . '" title="' . e($label) . '" class="soc-' . $k . '">' . $svg . '</a>';
    return $h . '</div>';
}
