<?php /** crud_form.php - generic add/edit form. */
$nouns = [
    'CLI' => 'client', 'INQ' => 'inquiry', 'OPP' => 'opportunity', 'QT' => 'quotation',
    'FU' => 'follow-up', 'BK' => 'booking', 'SCH' => 'payment milestone', 'PAY' => 'payment',
    'TGT' => 'sales target', 'ACT' => 'activity', 'TRV' => 'traveler', 'CNT' => 'content block',
    'MSG' => 'message template', 'TSK' => 'task template',
];
$noun = $nouns[$m['prefix']] ?? 'record';
$heading = $id ? 'Edit ' . $noun . ' (' . code_of($m['prefix'], $id) . ')' : 'New ' . $noun;
$draftKey   = $draftKey ?? draft_key_crud($module, (int)$id);
$draftRoute = $draftRoute ?? draft_route_crud($module, (int)$id);
$draftLabel = $draftLabel ?? draft_label_crud($m, (int)$id);
$draftMeta  = $draftMeta ?? null;
?>
<div class="topbar">
  <h1><?= e($heading) ?></h1>
  <a class="btn ghost" href="<?= e(url('page=' . $module)) ?>">← Back to list</a>
</div>

<div class="card form">
  <form method="post" action="<?= e(url('page=' . $module . '&action=save')) ?>"
        data-draft-key="<?= e($draftKey) ?>" data-draft-route="<?= e($draftRoute) ?>"
        data-draft-label="<?= e($draftLabel) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <?php if (!empty($draftMeta)): ?>
      <div class="draft-banner" data-draft-banner>
        <div>
          <b>Partial save restored</b>
          <span>Saved <?= e(date('d M Y H:i', strtotime((string)$draftMeta['updated_at']))) ?>.
            Keep editing, or discard it and start from the saved record.</span>
        </div>
        <button type="button" class="btn tiny ghost" data-draft-discard>Discard partial save</button>
      </div>
    <?php endif; ?>
    <div class="fgrid">
      <?php foreach ($m['fields'] as $f):
            [$name, $label, $type] = $f;
            $req = ($f[3] ?? '') === 'req';
            $val = $row[$name] ?? '';

            // new-record defaults (today, current month/year, status, current user)
            if ($val === null || $val === '') {
                $def = $m['defaults'][$name] ?? '';
                if ($def === 'today')             $val = date('Y-m-d');
                elseif ($def === '+3 days')       $val = date('Y-m-d', strtotime('+3 days'));
                elseif ($def === '+7 days')       $val = date('Y-m-d', strtotime('+7 days'));
                elseif ($def === 'current_month') $val = (int)date('n');
                elseif ($def === 'current_year')  $val = (int)date('Y');
                elseif ($def !== '')              $val = $def;
            }
            if ($type === 'users' && ($val === null || $val === '')) $val = $user['id'] ?? '';
            if ($type === 'currency' && ($val === null || $val === '')) $val = base_currency();
            $reqAttr = $req ? ' required' : '';
      ?>
        <div class="field <?= $type === 'textarea' ? 'span2' : '' ?>">
          <label><?= e($label) ?><?= $req ? ' *' : '' ?></label>
          <?php if ($type === 'text'): ?>
            <input type="text" name="<?= e($name) ?>" value="<?= e((string)$val) ?>"<?= $reqAttr ?>>
            <?php if ($name === 'new_client_name'): ?><small class="hint">Leave blank if the client is already in the list above.</small><?php endif; ?>
          <?php elseif ($type === 'email'): ?>
            <input type="email" name="<?= e($name) ?>" value="<?= e((string)$val) ?>"<?= $reqAttr ?>>
          <?php elseif ($type === 'date'): ?>
            <input type="date" name="<?= e($name) ?>" value="<?= e((string)$val) ?>"<?= $reqAttr ?>>
          <?php elseif ($type === 'int'): ?>
            <input type="number" step="1" name="<?= e($name) ?>" value="<?= $val === null ? '' : e((string)$val) ?>"<?= $reqAttr ?>>
          <?php elseif ($type === 'money'): ?>
            <input type="number" step="0.01" name="<?= e($name) ?>" value="<?= $val === null ? '' : e((string)$val) ?>"<?= $reqAttr ?>>
          <?php elseif ($type === 'textarea'): ?>
            <textarea name="<?= e($name) ?>" rows="3"<?= $reqAttr ?>><?= e((string)$val) ?></textarea>
          <?php elseif ($type === 'bool'): ?>
            <label class="chk"><input type="checkbox" name="<?= e($name) ?>" value="1" <?= (int)$val === 1 ? 'checked' : '' ?>> Yes</label>
          <?php elseif ($type === 'currency'): ?>
            <select name="<?= e($name) ?>"<?= $reqAttr ?>>
              <?php foreach (crud_options('currency') as $c): ?>
                <option value="<?= e($c) ?>" <?= (string)$val === $c ? 'selected' : '' ?>><?= e($c) ?></option>
              <?php endforeach; ?>
            </select>
          <?php elseif (strpos($type, 'list:') === 0): ?>
            <select name="<?= e($name) ?>"<?= $reqAttr ?>>
              <option value="">None</option>
              <?php foreach (crud_options($type) as $o): ?>
                <option value="<?= e($o) ?>" <?= (string)$val === $o ? 'selected' : '' ?>><?= e($o) ?></option>
              <?php endforeach; ?>
            </select>
          <?php elseif ($type === 'users'): ?>
            <select name="<?= e($name) ?>"<?= $reqAttr ?>>
              <option value="">None</option>
              <?php foreach (crud_options('users') as $uid => $uname): ?>
                <option value="<?= (int)$uid ?>" <?= (string)$val === (string)$uid ? 'selected' : '' ?>><?= e($uname) ?></option>
              <?php endforeach; ?>
            </select>
          <?php elseif (strpos($type, 'ref:') === 0): ?>
            <?php
              $ropts = crud_options($type);
              // On non-inquiry forms the client is always chosen from the list;
              // only the inquiry form offers inline client creation.
              if ($type === 'ref:clients' && $module !== 'inquiries') $ropts[null] = 'Select client';
              $inqOwners = [];
              if ($type === 'ref:inquiries') {
                  foreach (DB::all('SELECT id, assigned_to FROM inquiries') as $ir)
                      $inqOwners[(int)$ir['id']] = (int)$ir['assigned_to'];
              }
            ?>
            <select name="<?= e($name) ?>"<?= $reqAttr ?>>
              <?php foreach ($ropts as $rid => $rlabel): ?>
                <option value="<?= $rid === null ? '' : (int)$rid ?>"<?= $type === 'ref:inquiries' && isset($inqOwners[(int)$rid]) ? ' data-owner="' . $inqOwners[(int)$rid] . '"' : '' ?> <?= (string)$val === (string)$rid ? 'selected' : '' ?>><?= e($rlabel) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($type === 'ref:inquiries'): ?>
              <small class="hint">Client, tour, pax and expected value are copied from this inquiry when you save.</small>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="formfoot">
      <button class="btn primary" type="submit"><?= $id ? 'Save changes' : 'Add record' ?></button>
      <button class="btn ghost" type="button" data-draft-save>Save partial</button>
      <a class="btn ghost" href="<?= e(url('page=' . $module)) ?>">Cancel</a>
      <span class="draft-status" data-draft-status></span>
    </div>
  </form>
</div>
<script>
(function () {
  var newClient = document.querySelector('[name="new_client_name"]');
  var clientSel = document.querySelector('[name="client_id"]');
  if (newClient && clientSel) {
    function sync() { clientSel.required = newClient.value.trim() === ''; }
    newClient.addEventListener('input', sync);
    sync();
  }

  // When an inquiry is chosen, the server fills client / tour / amount.
  // Relax the browser's required checks so those auto-filled fields do not block the form.
  var inq = document.querySelector('[name="inquiry_id"]');
  if (inq) {
    var linked = ['client_id', 'tour', 'amount']
      .map(function (n) { return document.querySelector('[name="' + n + '"]'); })
      .filter(Boolean);
    linked.forEach(function (el) { if (el.required) el.dataset.req = '1'; });
    function syncLinked() {
      var has = inq.value !== '';
      linked.forEach(function (el) {
        el.required = !has && el.dataset.req === '1';
      });
    }
    inq.addEventListener('change', syncLinked);
    syncLinked();

    // Copy the inquiry owner into the linked record's owner field until the
    // user manually chooses a different owner.
    var ownerSel = document.querySelector('[name="salesperson"], [name="prepared_by"], [name="assigned_to"]');
    if (ownerSel) {
      function syncOwner() {
        var opt = inq.options[inq.selectedIndex];
        var owner = opt ? opt.getAttribute('data-owner') : '';
        if (owner && !ownerSel.dataset.touched) ownerSel.value = owner;
      }
      ownerSel.addEventListener('change', function () { ownerSel.dataset.touched = '1'; });
      inq.addEventListener('change', syncOwner);
      syncOwner();
    }
  }
})();
</script>
