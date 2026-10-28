<?php /** settings.php - lists + fx + company */
$title = 'Settings';
?>
<div class="topbar"><h1>Settings</h1><a class="btn ghost" href="index.php?page=users">Manage Users →</a></div>

<div class="two">
  <div class="card">
    <h2>Dropdown Lists (Sales Lists)</h2>
    <p class="hint">These feed the dropdowns in every form — add your own categories, statuses and sources here. The internal key is shown in small text.</p>
    <?php foreach ($lists as $name => $items): ?>
      <details <?= $name === 'lead_source' ? 'open' : '' ?>>
        <summary><b><?= e(ucwords(str_replace('_', ' ', $name))) ?></b> <span class="bdg dim"><?= count($items) ?> items</span> <small class="hint"><?= e($name) ?></small></summary>
        <table class="grid small">
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr><td><?= e($it['value']) ?></td>
              <td class="ops"><form class="inline" method="post" action="index.php?page=delete_list_item">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                <button class="btn tiny danger" type="submit">×</button></form></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </details>
    <?php endforeach; ?>
    <form class="addform" method="post" action="index.php?page=add_list_item">
      <input type="hidden" name="_list" value="1"><?= csrf_field() ?>
      <div class="fgrid">
        <div class="field"><label>List name</label>
          <input type="text" name="list_name" required list="listnames" placeholder="e.g. lead_source">
          <datalist id="listnames">
            <?php foreach (array_keys($lists) as $ln): ?><option value="<?= e($ln) ?>"><?php endforeach; ?>
          </datalist>
          <small class="hint">Pick an existing list or type a new key (lowercase_with_underscores).</small></div>
        <div class="field"><label>Value</label>
          <input type="text" name="value" required placeholder="e.g. En-route etc."></div>
      </div>
      <button class="btn primary" type="submit">Add list item</button>
    </form>
  </div>

  <div class="card">
    <h2>Currency Conversion (rate to base)</h2>
    <p class="hint">1 unit of the currency = this many <?= e($base_cur) ?>. Salesperson uses these to normalise mixed-currency money.</p>
    <form method="post" action="index.php?page=save_fx">
      <?= csrf_field() ?>
      <table class="grid small">
        <thead><tr><th>Currency</th><th>Rate to <?= e($base_cur) ?></th><th>Updated</th></tr></thead>
        <tbody>
        <?php foreach ($fx as $r): ?>
          <tr>
            <td><b><?= e($r['currency']) ?></b></td>
            <td><input type="text" name="rate[]" value="<?= e($r['rate_to_base']) ?>"></td>
            <td class="dt"><?= e(date('d M Y', strtotime($r['updated_at']))) ?></td>
          </tr>
          <input type="hidden" name="currency[]" value="<?= e($r['currency']) ?>">
        <?php endforeach; ?>
        <tr>
          <td><input type="text" name="currency[]" placeholder="AUD" maxlength="3" style="width:60px"></td>
          <td><input type="text" name="rate[]" placeholder="0.65"></td><td>—</td>
        </tr>
        </tbody>
      </table>
      <button class="btn primary" type="submit">Save FX rates</button>
    </form>

    <h2>Company Settings</h2>
    <table class="grid small">
      <thead><tr><th>Key</th><th>Value</th></tr></thead>
      <tbody>
      <?php foreach ($company as $s): ?><tr><td><b><?= e($s['k']) ?></b></td><td><?= e($s['v']) ?></td></tr><?php endforeach; ?>
      <?php if (!$company): ?><tr><td colspan="2" class="empty">No company settings in the DB yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <form method="post" action="index.php?page=save_company">
      <?= csrf_field() ?>
      <div class="fgrid">
        <div class="field"><label>Key (e.g. company_name)</label><input type="text" name="k" required></div>
        <div class="field"><label>Value</label><input type="text" name="v" required></div>
      </div>
      <button class="btn primary" type="submit">Save setting</button>
    </form>
  </div>
</div>
