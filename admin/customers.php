<?php
require __DIR__ . '/includes/init.php';

if (is_post()) {
    csrf_check();
    $c = row('SELECT * FROM customers WHERE id = ?', [(int)post('id')]);
    if ($c && post('action') === 'reset') {
        $pw = (string)random_int(100000, 999999);
        update('customers', ['password' => password_hash($pw, PASSWORD_DEFAULT)], 'id = ?', [$c['id']]);
        flash('success', "New password for {$c['name']} (" . (has_real_phone($c) ? $c['phone'] : $c['email']) . "): $pw — share it with the customer.");
    } elseif ($c && post('action') === 'delete') {
        q('UPDATE orders SET customer_id = NULL WHERE customer_id = ?', [$c['id']]);
        q('DELETE FROM customers WHERE id = ?', [$c['id']]);
        flash('success', 'Customer account deleted. Their orders are kept.');
    }
    redirect('customers.php');
}

$qtext = get('q');
$where = '1=1'; $params = [];
if ($qtext !== '') { $where = '(c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)'; $params = ["%$qtext%", "%$qtext%", "%$qtext%"]; }
$page = max(1, (int)get('page', 1)); $per = 20;
$total = (int)val("SELECT COUNT(*) FROM customers c WHERE $where", $params);
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$list = rows("SELECT c.*, (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) AS orders_count,
                (SELECT COALESCE(SUM(total),0) FROM orders o WHERE o.customer_id = c.id AND o.status <> 'cancelled') AS spent
              FROM customers c WHERE $where ORDER BY c.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);

$title = 'Customers'; $active = 'customers';
require __DIR__ . '/includes/top.php';
?>
<form class="a-filters" method="get">
  <input name="q" value="<?= e($qtext) ?>" placeholder="Search name, phone or email…"><button class="a-btn">Search</button>
  <span class="a-count"><?= $total ?> registered customer(s)</span>
</form>
<div class="a-card no-pad">
  <div class="a-table-wrap"><table class="a-table">
    <thead><tr><th>Customer</th><th>Phone</th><th>Email</th><th>City</th><th>Orders</th><th>Spent</th><th>Joined</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $c): ?>
      <tr>
        <td><div class="cell-prod"><span class="cust-av"><?= e(mb_strtoupper(mb_substr($c['name'], 0, 1))) ?></span><b><?= e($c['name']) ?></b></div></td>
        <td class="nw"><?= has_real_phone($c) ? e($c['phone']) : '<span class="muted">—</span>' ?></td>
        <td><?= e($c['email'] ?: '—') ?></td>
        <td><?= e($c['city'] ?: '—') ?></td>
        <td><a class="a-link" href="orders.php?q=<?= urlencode($c['phone']) ?>"><?= (int)$c['orders_count'] ?> order(s)</a></td>
        <td class="nw"><?= money($c['spent']) ?></td>
        <td class="nw"><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
        <td class="row-actions">
          <form method="post" onsubmit="return confirm('Create a new password for this customer?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="a-btn a-btn-sm" name="action" value="reset">Reset password</button></form>
          <form method="post" onsubmit="return confirm('Delete this customer account? Orders will be kept.')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="a-btn a-btn-sm a-btn-danger" name="action" value="delete">Delete</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$list): ?><tr><td colspan="8" class="a-empty">No registered customers yet.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?= pager($page, $pages) ?>
<?php require __DIR__ . '/includes/bottom.php'; ?>
