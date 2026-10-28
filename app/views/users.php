<?php /** users.php */
$title = 'Users';
?>
<div class="topbar"><h1>User accounts</h1><span class="bdg info">System admin only</span></div>

<div class="card full">
  <h2>Existing users</h2>
  <div class="tablewrap">
  <table class="grid">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th class="ops">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><b><?= e($r['name']) ?></b></td><td><?= e($r['email']) ?></td>
        <td><?= badge(ucfirst($r['role'])) ?></td>
        <td><?= badge($r['active'] ? 'Active' : 'Inactive') ?></td>
        <td class="dt"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
        <td class="ops"><a class="btn tiny" href="index.php?page=users&edit=<?= (int)$r['id'] ?>#user-form">Edit below</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php $eu = $u ?? null; ?>
<div class="card form" id="user-form">
  <h2><?= $eu ? 'Edit ' . e($eu['name']) : 'Add a user' ?></h2>
  <?php if ($eu): ?><p class="hint">Editing an existing account. <a href="index.php?page=users">Cancel</a>.</p><?php endif; ?>
  <form method="post" action="index.php?page=save_user">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($eu['id'] ?? 0) ?>">
    <div class="fgrid">
      <div class="field"><label>Full name *</label><input type="text" name="name" required value="<?= e($eu['name'] ?? '') ?>"></div>
      <div class="field"><label>Email *</label><input type="email" name="email" required value="<?= e($eu['email'] ?? '') ?>"></div>
      <div class="field"><label>Role</label>
        <select name="role">
          <?php foreach (['sales', 'manager', 'admin'] as $role): ?>
            <option value="<?= $role ?>" <?= ($eu['role'] ?? 'sales') === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field"><label>New password (leave blank to keep)</label><input type="password" name="password" minlength="8"></div>
      <div class="field"><label>Status</label>
        <label class="chk"><input type="checkbox" name="active" <?= !$eu || $eu['active'] ? 'checked' : '' ?>> Active</label></div>
    </div>
    <div class="formfoot"><button class="btn primary" type="submit">Save user</button></div>
  </form>
  <p class="hint">To deactivate someone use the status checkbox; their past records are kept. Passwords must be 8+ characters.</p>
</div>
