<?php
require __DIR__ . '/includes/init.php';

if (is_post()) {
    csrf_check();
    $ids = array_map('intval', post('action') === 'delete_one' ? [post('id')] : (array)($_POST['ids'] ?? []));
    $restock = (bool)post('restock');
    $n = 0;
    foreach ($ids as $id) if ($id && delete_order($id, $restock)) $n++;
    flash($n ? 'success' : 'error', $n ? "$n order(s) deleted permanently." : 'Please select at least one order.');
    redirect('orders.php?' . http_build_query(array_intersect_key($_GET, array_flip(['status', 'q', 'page']))));
}

$status = get('status'); $qtext = get('q');
$where = ['1=1']; $params = [];
if ($status && isset(order_statuses()[$status])) { $where[] = 'status = ?'; $params[] = $status; }
if ($qtext !== '') { $where[] = '(order_no LIKE ? OR customer_name LIKE ? OR phone LIKE ?)'; array_push($params, "%$qtext%", "%$qtext%", "%$qtext%"); }
$w = ' WHERE ' . implode(' AND ', $where);
$page = max(1, (int)get('page', 1)); $per = 20;
$total = (int)val("SELECT COUNT(*) FROM orders $w", $params);
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$list = rows("SELECT o.*, (SELECT SUM(qty) FROM order_items oi WHERE oi.order_id = o.id) AS items FROM orders o $w ORDER BY created_at DESC, id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$counts = [];
foreach (rows('SELECT status, COUNT(*) AS c FROM orders GROUP BY status') as $r) $counts[$r['status']] = $r['c'];

$title = 'Orders'; $active = 'orders';
require __DIR__ . '/includes/top.php';
?>
<div class="pill-tabs">
  <a href="orders.php" class="<?= !$status ? 'on' : '' ?>">All <em><?= array_sum($counts) ?></em></a>
  <?php foreach (order_statuses() as $k => $l): ?><a href="?status=<?= $k ?>" class="<?= $status === $k ? 'on' : '' ?>"><?= $l ?> <em><?= (int)($counts[$k] ?? 0) ?></em></a><?php endforeach; ?>
</div>
<form class="a-filters" method="get">
  <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
  <input name="q" value="<?= e($qtext) ?>" placeholder="Search order no, name or phone…"><button class="a-btn">Search</button>
  <span class="a-count"><?= $total ?> order(s)</span>
</form>
<form method="post" id="bulkForm" onsubmit="return confirmDelete(this)">
<?= csrf_field() ?><input type="hidden" name="action" value="delete_bulk"><input type="hidden" name="restock" value="1" id="restockField">
<div class="bulk-bar" id="bulkBar" hidden>
  <b><span id="bulkCount">0</span> selected</b>
  <button class="a-btn a-btn-sm a-btn-danger">🗑 Delete selected</button>
</div>
<div class="a-card no-pad">
  <div class="a-table-wrap"><table class="a-table">
    <thead><tr><th class="chk-col"><input type="checkbox" id="checkAll" title="Select all"></th><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $o): ?>
      <tr>
        <td class="chk-col"><input type="checkbox" name="ids[]" value="<?= $o['id'] ?>" class="row-check"></td>
        <td><a class="a-link" href="order-view.php?id=<?= $o['id'] ?>"><b>#<?= e($o['order_no']) ?></b></a></td>
        <td><?= e($o['customer_name']) ?><br><small class="muted"><?= e($o['phone']) ?> · <?= e($o['city']) ?></small></td>
        <td><?= (int)$o['items'] ?></td>
        <td><b><?= money($o['total']) ?></b></td>
        <td><?= e(strtoupper($o['payment_method'])) ?><?php if ($o['trx_id']): ?><br><small class="muted"><?= e($o['trx_id']) ?></small><?php endif; ?></td>
        <td><?= status_badge($o['status']) ?></td>
        <td><?= e(date('d M Y', strtotime($o['created_at']))) ?><br><small class="muted"><?= e(date('h:i A', strtotime($o['created_at']))) ?></small></td>
        <td class="row-actions"><a class="a-btn a-btn-sm" href="order-view.php?id=<?= $o['id'] ?>">Open</a>
          <button type="button" class="a-btn a-btn-sm a-btn-danger" data-del-one="<?= $o['id'] ?>" data-no="<?= e($o['order_no']) ?>" title="Delete">🗑</button></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$list): ?><tr><td colspan="9" class="a-empty">No orders found.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
</form>
<form method="post" id="oneForm" hidden><?= csrf_field() ?><input type="hidden" name="action" value="delete_one"><input type="hidden" name="id" id="oneId"><input type="hidden" name="restock" value="1" id="oneRestock"></form>
<script>
function askRestock() {
  return confirm('Put the products of these orders back into stock?\n\nOK = yes, return items to stock\nCancel = no, keep stock as it is\n\n(Delivered and cancelled orders never change stock.)');
}
function confirmDelete(f) {
  const n = f.querySelectorAll('.row-check:checked').length;
  if (!n) { alert('Please select at least one order.'); return false; }
  if (!confirm('Delete ' + n + ' order(s) permanently? This cannot be undone.')) return false;
  document.getElementById('restockField').value = askRestock() ? '1' : '';
  return true;
}
document.querySelectorAll('[data-del-one]').forEach(b => b.addEventListener('click', () => {
  if (!confirm('Delete order #' + b.dataset.no + ' permanently? This cannot be undone.')) return;
  document.getElementById('oneRestock').value = askRestock() ? '1' : '';
  document.getElementById('oneId').value = b.dataset.delOne;
  document.getElementById('oneForm').submit();
}));
const all = document.getElementById('checkAll'), checks = [...document.querySelectorAll('.row-check')];
const upd = () => { const n = checks.filter(c => c.checked).length; document.getElementById('bulkBar').hidden = !n; document.getElementById('bulkCount').textContent = n; all.checked = n && n === checks.length; };
all && all.addEventListener('change', () => { checks.forEach(c => c.checked = all.checked); upd(); });
checks.forEach(c => c.addEventListener('change', upd));
</script>
<?= pager($page, $pages) ?>
<?php require __DIR__ . '/includes/bottom.php'; ?>
