<?php
require __DIR__ . '/includes/init.php';   // only the Super Admin can open this page (checked in init.php)

$edit = get('edit') ? row('SELECT * FROM admins WHERE id = ?', [(int)get('edit')]) : null;

if (is_post()) {
    csrf_check();
    $act = post('action');
    $id = (int)post('id');
    $me = (int)admin()['id'];
    if ($act === 'save') {
        $uname = post('username');
        $pass = (string)($_POST['password'] ?? '');
        $perms = array_values(array_intersect(array_keys(admin_sections()), (array)($_POST['perms'] ?? [])));
        $target = $id ? row('SELECT * FROM admins WHERE id = ?', [$id]) : null;
        if (strlen($uname) < 3) flash('error', 'Username must be at least 3 characters.');
        elseif (val('SELECT id FROM admins WHERE username = ? AND id <> ?', [$uname, $id])) flash('error', 'This username is already used.');
        elseif (!$id && strlen($pass) < 6) flash('error', 'Password must be at least 6 characters.');
        elseif ($pass !== '' && strlen($pass) < 6) flash('error', 'Password must be at least 6 characters.');
        elseif (!$id || ($target && $target['role'] !== 'super')) {
            if (!$perms) { flash('error', 'Tick at least one section this admin can access.'); redirect('admins.php' . ($id ? '?edit=' . $id : '')); }
            $data = ['username' => $uname, 'name' => post('name') ?: $uname, 'permissions' => implode(',', $perms), 'is_active' => post('is_active') ? 1 : 0];
            if ($pass !== '') $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
            if ($id) { update('admins', $data, 'id = ? AND role <> ?', [$id, 'super']); flash('success', "Admin “{$uname}” updated."); }
            else { insert('admins', $data + ['role' => 'admin', 'created_at' => now()]); flash('success', "Admin “{$uname}” created. Share the username & password with them."); }
            redirect('admins.php');
        } else {
            // editing the super admin (yourself): only username / password
            $data = ['username' => $uname];
            if ($pass !== '') $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
            update('admins', $data, 'id = ?', [$id]);
            if ($id === $me) { $_SESSION['admin']['username'] = $uname; admin(true); }
            flash('success', 'Super Admin login updated.');
            redirect('admins.php');
        }
        redirect('admins.php' . ($id ? '?edit=' . $id : ''));
    } elseif ($act === 'delete') {
        $t = row('SELECT * FROM admins WHERE id = ?', [$id]);
        if (!$t || $t['role'] === 'super' || $id === $me) flash('error', 'The Super Admin cannot be deleted.');
        else { q('DELETE FROM admins WHERE id = ?', [$id]); flash('success', "Admin “{$t['username']}” deleted."); }
        redirect('admins.php');
    }
}

$list = rows("SELECT * FROM admins ORDER BY CASE WHEN role = 'super' THEN 0 ELSE 1 END, username");
$f = $edit ?: ['id' => 0, 'username' => '', 'name' => '', 'role' => 'admin', 'permissions' => 'dashboard,orders', 'is_active' => 1];
$fperms = explode(',', (string)$f['permissions']);
$isSuperForm = ($f['role'] ?? '') === 'super';
$sec = admin_sections();

$title = 'Admin Users'; $active = 'admins';
require __DIR__ . '/includes/top.php';
?>
<div class="a-grid-2 wide-left">
  <section class="a-card no-pad">
    <div class="a-table-wrap"><table class="a-table">
      <thead><tr><th>Username</th><th>Role</th><th>Access</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($list as $a): $isSu = $a['role'] === 'super'; ?>
        <tr class="<?= $edit && $edit['id'] == $a['id'] ? 'hl' : '' ?>">
          <td><b><?= e($a['username']) ?></b><?php if ($a['name'] && $a['name'] !== $a['username']): ?><br><small class="muted"><?= e($a['name']) ?></small><?php endif; ?></td>
          <td><?= $isSu ? '<span class="sb sb-in">Super Admin</span>' : '<span class="sb sb-low">Admin</span>' ?></td>
          <td class="perm-cell"><?php if ($isSu): ?><small class="muted">Everything</small><?php else:
              foreach (array_filter(explode(',', (string)$a['permissions'])) as $p) if (isset($sec[$p])) echo '<span class="perm-chip">' . e($sec[$p][1]) . '</span>'; endif; ?></td>
          <td><?= ($a['is_active'] ?? 1) ? '<span class="sb sb-in">Active</span>' : '<span class="sb sb-out">Disabled</span>' ?></td>
          <td class="row-actions">
            <a class="a-btn a-btn-sm" href="?edit=<?= $a['id'] ?>">Edit</a>
            <?php if (!$isSu): ?><form method="post" onsubmit="return confirm('Delete admin <?= e($a['username']) ?>?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="a-btn a-btn-sm a-btn-danger" name="action" value="delete">Delete</button></form><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </section>

  <form method="post" class="a-card">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
    <h3><?= $isSuperForm ? 'Edit Super Admin login' : ($f['id'] ? 'Edit admin' : 'Add new admin') ?></h3>
    <label class="a-fld"><span>Username *</span><input name="username" value="<?= e($f['username']) ?>" required autocomplete="off"></label>
    <?php if (!$isSuperForm): ?><label class="a-fld"><span>Name (optional)</span><input name="name" value="<?= e($f['name'] !== $f['username'] ? $f['name'] : '') ?>" placeholder="e.g. Shop manager"></label><?php endif; ?>
    <label class="a-fld"><span><?= $f['id'] ? 'New password <small class="muted">(leave empty to keep the current one)</small>' : 'Password *' ?></span>
      <input type="text" name="password" <?= $f['id'] ? '' : 'required' ?> minlength="6" autocomplete="new-password" placeholder="At least 6 characters"></label>
    <?php if (!$isSuperForm): ?>
      <span class="a-fld" style="margin-bottom:6px"><span>Access to admin panel sections *</span></span>
      <div class="perm-grid">
        <?php foreach ($sec as $k => [, $label]): ?>
          <label class="perm-opt"><input type="checkbox" name="perms[]" value="<?= $k ?>" <?= in_array($k, $fperms, true) ? 'checked' : '' ?>><span><?= e($label) ?></span></label>
        <?php endforeach; ?>
      </div>
      <label class="a-check"><input type="checkbox" name="is_active" value="1" <?= ($f['is_active'] ?? 1) ? 'checked' : '' ?>> Active (untick to block this login)</label>
    <?php else: ?>
      <p class="a-help">The Super Admin always has full access and can manage all other admins.</p>
    <?php endif; ?>
    <button class="a-btn a-btn-primary a-btn-block"><?= $f['id'] ? 'Save changes' : 'Create admin' ?></button>
    <?php if ($f['id']): ?><a href="admins.php" class="a-muted-link">Cancel</a><?php endif; ?>
  </form>
</div>
<?php require __DIR__ . '/includes/bottom.php'; ?>
