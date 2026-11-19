<?php
/** Admin bootstrap: loads the app and requires login */
$ROOT = '../';
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

/** Sections an admin can be given access to: key => [page, label] */
function admin_sections(): array
{
    return [
        'dashboard' => ['index.php', 'Dashboard'], 'products' => ['products.php', 'Products'], 'categories' => ['categories.php', 'Categories'],
        'orders' => ['orders.php', 'Orders'], 'customers' => ['customers.php', 'Customers'], 'inventory' => ['inventory.php', 'Inventory'],
        'history' => ['stock-history.php', 'Stock History'], 'settings' => ['settings.php', 'Settings & Design'],
    ];
}

/** Logged-in admin, fresh from the database (logged out if deleted/disabled) */
function admin(bool $refresh = false): ?array
{
    static $a = false;
    if ($a !== false && !$refresh) return $a;
    $id = $_SESSION['admin']['id'] ?? 0;
    $a = $id ? row('SELECT * FROM admins WHERE id = ?', [$id]) : null;
    if ($a && isset($a['is_active']) && !$a['is_active']) $a = null;
    if ($id && !$a) unset($_SESSION['admin']);
    return $a;
}
function admin_name(): string { return admin()['username'] ?? 'admin'; }
function is_super(): bool { return (admin()['role'] ?? '') === 'super'; }
function admin_perms(): array
{
    if (is_super()) return array_keys(admin_sections());
    return array_values(array_filter(explode(',', (string)(admin()['permissions'] ?? ''))));
}
function can(string $section): bool { return is_super() || in_array($section, admin_perms(), true); }

if (!defined('ADMIN_PUBLIC')) {
    if (!admin()) redirect('login.php');
    // page-level access check
    $page = basename($_SERVER['SCRIPT_NAME']);
    $map = ['product-edit.php' => 'products', 'order-view.php' => 'orders'];
    foreach (admin_sections() as $k => [$file]) $map[$file] = $k;
    if ($page === 'admins.php' && !is_super()) { flash('error', 'Only the Super Admin can manage admins.'); redirect(admin_home()); }
    if (isset($map[$page]) && !can($map[$page])) {
        $home = admin_home();
        if ($home === $page) { http_response_code(403); exit('You do not have access to any section. Please contact the Super Admin.'); }
        flash('error', 'You do not have access to that section.');
        redirect($home);
    }
}

/** First page this admin is allowed to open */
function admin_home(): string
{
    foreach (admin_sections() as $k => [$file]) if (can($k)) return $file;
    return 'logout.php';
}

/** Status badge html for orders */
function status_badge(string $s): string
{
    $label = order_statuses()[$s] ?? ucfirst($s);
    return '<span class="st st-' . e($s) . '">' . e($label) . '</span>';
}

function stock_badge(int $stock, int $low): string
{
    $s = stock_status($stock, $low);
    $t = ['in' => 'In stock', 'low' => 'Low stock', 'out' => 'Out of stock'][$s];
    return '<span class="sb sb-' . $s . '">' . $stock . ' · ' . $t . '</span>';
}

function movement_label(string $t): string
{
    return ['initial' => 'Opening stock', 'restock' => 'Restock', 'adjust' => 'Adjustment', 'order' => 'Sold (order)', 'cancel' => 'Returned (cancel)', 'edit' => 'Manual edit'][$t] ?? ucfirst($t);
}

function pager(int $page, int $pages): string
{
    if ($pages <= 1) return '';
    $h = '<nav class="a-pager">';
    for ($i = 1; $i <= $pages; $i++) {
        $q = array_merge($_GET, ['page' => $i]);
        $h .= '<a class="' . ($i === $page ? 'on' : '') . '" href="?' . e(http_build_query($q)) . '">' . $i . '</a>';
    }
    return $h . '</nav>';
}

/**
 * Permanently delete an order and its items from the database.
 * Items of orders that were not delivered or cancelled go back into stock.
 */
function delete_order(int $id, bool $restock = true): bool
{
    $o = row('SELECT * FROM orders WHERE id = ?', [$id]);
    if (!$o) return false;
    $pdo = db();
    $pdo->beginTransaction();
    try {
        if ($restock && !$o['stock_returned'] && !in_array($o['status'], ['delivered', 'cancelled'], true)) {
            foreach (rows('SELECT oi.*, v.id AS vid FROM order_items oi LEFT JOIN variants v ON v.id = oi.variant_id WHERE oi.order_id = ?', [$id]) as $it)
                if ($it['vid']) stock_move((int)$it['vid'], (int)$it['qty'], 'cancel', 'Order deleted', $o['order_no'], admin_name());
        }
        q('DELETE FROM order_items WHERE order_id = ?', [$id]);
        q('DELETE FROM orders WHERE id = ?', [$id]);
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        return false;
    }
}
