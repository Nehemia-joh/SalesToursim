<?php /** quote_builder.php - edit the proposal header, itinerary and priced items. */ ?>
<?php
$quoteCode = code_of('QT', (int)$q['id']);
$inqCode   = !empty($q['inquiry_id']) ? code_of('INQ', (int)$q['inquiry_id']) : '';
$cur       = $q['currency'] ?: base_currency();
$dayIndex  = count($days);
$itemIndex = count($items);
$categories = ['Accommodation', 'Park fees', 'Transport', 'Activity', 'Meal', 'Guide', 'Flight', 'Other'];
?>
<div class="topbar">
  <div>
    <h1>Quote builder: <?= e($quoteCode) ?></h1>
    <div class="hint">
      <?= e($q['client_name'] ?: 'No client') ?><?= $inqCode ? ', ' . e($inqCode) : '' ?>, <?= e($q['tour'] ?: 'Untitled proposal') ?>
    </div>
  </div>
  <div class="tools">
    <a class="btn ghost" href="index.php?page=quotations">← Quotations</a>
    <?php if ($publicUrl): ?>
      <a class="btn ghost" target="_blank" rel="noopener" href="<?= e($publicUrl) ?>">Preview proposal</a>
    <?php endif; ?>
    <a class="btn ghost" href="index.php?page=quotations&action=form&id=<?= (int)$q['id'] ?>">Edit record</a>
  </div>
</div>

<div class="builder-summary">
  <div><span>Status</span><b><?= badge((string)($q['status'] ?? 'Draft')) ?></b></div>
  <div><span>Quote amount</span><b><?= number_format((float)$q['amount'], 2) ?> <?= e($cur) ?></b></div>
  <div><span>Pax</span><b><?= (int)$q['pax'] ?: '—' ?></b></div>
  <div><span>Valid until</span><b><?= $q['valid_until'] ? e(date('d M Y', strtotime($q['valid_until']))) : '—' ?></b></div>
  <div><span>Proposal views</span><b><?= (int)$viewCount ?></b></div>
  <div><span>Last viewed</span><b><?= $lastView ? e(date('d M Y H:i', strtotime($lastView))) : '—' ?></b></div>
</div>

<?php if (!empty($q['client_response'])): ?>
  <div class="fl <?= $q['client_response'] === 'Accepted' ? 'ok' : 'bad' ?>">
    Client response: <b><?= e($q['client_response']) ?></b>
    <?php if (!empty($q['responded_at'])): ?>, <?= e(date('d M Y H:i', strtotime($q['responded_at']))) ?><?php endif; ?>
    <?php if (!empty($q['client_comment'])): ?><br><span class="hint">“<?= e($q['client_comment']) ?>”</span><?php endif; ?>
  </div>
<?php endif; ?>

<form method="post" action="index.php?page=quote_builder_save">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">

  <div class="card form">
    <h2>Proposal header</h2>
    <div class="fgrid">
      <div class="field span2">
        <label>Tour / proposal title *</label>
        <input type="text" name="tour" value="<?= e((string)$q['tour']) ?>" required>
      </div>
      <div class="field">
        <label>Status</label>
        <select name="status">
          <?php foreach (crud_options('list:quotation_status') as $o): ?>
            <option value="<?= e($o) ?>" <?= (string)$q['status'] === $o ? 'selected' : '' ?>><?= e($o) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Quote date</label>
        <input type="date" name="quote_date" value="<?= e((string)($q['quote_date'] ?: date('Y-m-d'))) ?>">
      </div>
      <div class="field">
        <label>Valid until</label>
        <input type="date" name="valid_until" value="<?= e((string)$q['valid_until']) ?>">
      </div>
      <div class="field">
        <label>Version</label>
        <input type="number" name="version" min="1" step="1" value="<?= (int)($q['version'] ?: 1) ?>">
      </div>
      <div class="field">
        <label>Pax</label>
        <input type="number" name="pax" min="0" step="1" value="<?= $q['pax'] !== null ? (int)$q['pax'] : '' ?>">
      </div>
      <div class="field">
        <label>Currency</label>
        <select name="currency">
          <?php foreach (crud_options('currency') as $c): ?>
            <option value="<?= e($c) ?>" <?= (string)$cur === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Quote amount</label>
        <input type="number" name="amount" id="quote-amount" step="0.01" value="<?= e((string)$q['amount']) ?>">
        <small class="hint">Auto-replaced by the base total when priced items are added.</small>
      </div>
      <div class="field span2">
        <label>Cover note</label>
        <textarea name="cover_note" rows="3" placeholder="A short welcome message shown at the top of the proposal."><?= e((string)$q['cover_note']) ?></textarea>
      </div>
      <div class="field span2">
        <label>Inclusions</label>
        <textarea name="inclusions" rows="4" placeholder="One item per line, e.g. Park fees, accommodation, meals..."><?= e((string)$q['inclusions']) ?></textarea>
      </div>
      <div class="field span2">
        <label>Exclusions</label>
        <textarea name="exclusions" rows="4" placeholder="One item per line, e.g. International flights, tips, visa..."><?= e((string)$q['exclusions']) ?></textarea>
      </div>
      <div class="field span2">
        <label>Terms &amp; conditions</label>
        <textarea name="terms" rows="5" placeholder="Payment terms, cancellation policy, validity..."><?= e((string)$q['terms']) ?></textarea>
      </div>
      <div class="field span2">
        <label class="chk"><input type="checkbox" name="public_enabled" value="1" <?= !empty($q['public_token']) && (int)($q['public_enabled'] ?? 0) === 1 ? 'checked' : '' ?>> Public proposal link is active</label>
        <small class="hint">Uncheck to disable the client link without deleting the token.</small>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="builder-head">
      <div>
        <h2>Content library</h2>
        <p class="hint">Insert a saved inclusion, exclusion, term or cover note into the proposal.</p>
      </div>
      <a class="btn ghost" href="index.php?page=content_library" target="_blank" rel="noopener">Manage content</a>
    </div>
    <?php if ($contentBlocks): ?>
      <div class="content-insert">
        <select id="content-select">
          <option value="">Choose saved content</option>
          <?php foreach ($contentBlocks as $c): ?>
            <option value="<?= (int)$c['id'] ?>" data-body="<?= e((string)$c['body']) ?>"><?= e($c['title']) ?><?= $c['category'] ? ' (' . e($c['category']) . ')' : '' ?></option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn ghost" data-insert="cover_note">Cover note</button>
        <button type="button" class="btn ghost" data-insert="inclusions">Inclusions</button>
        <button type="button" class="btn ghost" data-insert="exclusions">Exclusions</button>
        <button type="button" class="btn ghost" data-insert="terms">Terms</button>
      </div>
    <?php else: ?>
      <p class="empty-note">No saved content yet. <a href="index.php?page=content_library&action=form">Add your first content block</a>.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="builder-head">
      <div>
        <h2>Itinerary</h2>
        <p class="hint">Build the day-by-day story the client will see in the proposal.</p>
      </div>
      <button type="button" class="btn ghost" id="add-day">+ Add day</button>
    </div>

    <div id="days-wrap">
      <?php foreach ($days as $i => $d): ?>
        <div class="builder-row day-row">
          <div class="rowhead">
            <b>Day</b>
            <input type="number" name="days[<?= $i ?>][day_no]" min="1" step="1" value="<?= (int)$d['day_no'] ?: ($i + 1) ?>" class="day-no">
            <button type="button" class="btn tiny danger remove-row">Remove</button>
          </div>
          <div class="fgrid">
            <div class="field span2"><label>Title</label><input type="text" name="days[<?= $i ?>][title]" value="<?= e((string)$d['title']) ?>" placeholder="e.g. Arrival in Arusha"></div>
            <div class="field"><label>Accommodation</label><input type="text" name="days[<?= $i ?>][accommodation]" value="<?= e((string)$d['accommodation']) ?>" placeholder="Lodge / camp"></div>
            <div class="field"><label>Meals</label><input type="text" name="days[<?= $i ?>][meals]" value="<?= e((string)$d['meals']) ?>" placeholder="B, L, D"></div>
            <div class="field span2"><label>Description</label><textarea name="days[<?= $i ?>][description]" rows="3"><?= e((string)$d['description']) ?></textarea></div>
            <div class="field span2"><label>Notes</label><textarea name="days[<?= $i ?>][notes]" rows="2"><?= e((string)$d['notes']) ?></textarea></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!$days): ?><p class="empty-note">No itinerary days yet. Add the first day above.</p><?php endif; ?>
  </div>

  <div class="card">
    <div class="builder-head">
      <div>
        <h2>Priced items</h2>
        <p class="hint">Add accommodation, park fees, transport, activities and extras. Optional items are shown separately to the client.</p>
      </div>
      <button type="button" class="btn ghost" id="add-item">+ Add item</button>
    </div>

    <div id="items-wrap">
      <?php foreach ($items as $i => $it): ?>
        <div class="builder-row item-row">
          <div class="rowhead">
            <b>Line <?= $i + 1 ?></b>
            <button type="button" class="btn tiny danger remove-row">Remove</button>
          </div>
          <div class="fgrid">
            <div class="field">
              <label>Category</label>
              <input type="text" name="items[<?= $i ?>][category]" list="item-categories" value="<?= e((string)$it['category']) ?>" placeholder="Accommodation">
            </div>
            <div class="field span2">
              <label>Description</label>
              <input type="text" name="items[<?= $i ?>][description]" value="<?= e((string)$it['description']) ?>" placeholder="e.g. 2 nights Serengeti lodge">
            </div>
            <div class="field">
              <label>Qty</label>
              <input type="number" name="items[<?= $i ?>][qty]" class="item-qty" min="0" step="0.01" value="<?= e((string)($it['qty'] ?? 1)) ?>">
            </div>
            <div class="field">
              <label>Unit</label>
              <input type="text" name="items[<?= $i ?>][unit]" value="<?= e((string)$it['unit']) ?>" placeholder="pax / night">
            </div>
            <div class="field">
              <label>Unit price</label>
              <input type="number" name="items[<?= $i ?>][unit_price]" class="item-price" min="0" step="0.01" value="<?= e((string)($it['unit_price'] ?? 0)) ?>">
            </div>
            <div class="field">
              <label>Optional</label>
              <label class="chk"><input type="checkbox" class="item-optional" name="items[<?= $i ?>][optional]" value="1" <?= (int)($it['optional'] ?? 0) === 1 ? 'checked' : '' ?>> Optional extra</label>
            </div>
            <div class="field">
              <label>Line total</label>
              <div class="line-total"><?= number_format((float)($it['total'] ?? 0), 2) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!$items): ?><p class="empty-note">No priced items yet. Add the first line above.</p><?php endif; ?>

    <div class="builder-totals">
      <div><span>Base total</span><b id="base-total">0.00</b></div>
      <div><span>Optional extras</span><b id="optional-total">0.00</b></div>
      <div><span>Currency</span><b><?= e($cur) ?></b></div>
    </div>
  </div>

  <div class="formfoot">
    <button class="btn primary" type="submit">Save proposal</button>
    <a class="btn ghost" href="index.php?page=quotations">Cancel</a>
  </div>
</form>

<div class="card">
  <div class="builder-head">
    <div>
      <h2>Client message</h2>
      <p class="hint">Pick a saved message template, personalise it with this quote, then copy it into email or WhatsApp.</p>
    </div>
    <a class="btn ghost" href="index.php?page=message_templates" target="_blank" rel="noopener">Manage templates</a>
  </div>
  <?php if ($messages): ?>
    <div class="content-insert">
      <select id="message-select">
        <option value="">Choose a message template</option>
        <?php foreach ($messages as $m): ?>
          <option value="<?= (int)$m['id'] ?>"
                  data-subject="<?= e((string)$m['subject']) ?>"
                  data-body="<?= e((string)$m['body']) ?>"><?= e($m['name']) ?><?= $m['channel'] ? ' (' . e($m['channel']) . ')' : '' ?></option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="btn ghost" id="copy-message">Copy message</button>
    </div>
    <textarea id="message-preview" rows="7" readonly placeholder="Choose a template to preview the message here."></textarea>
  <?php else: ?>
    <p class="empty-note">No message templates yet. <a href="index.php?page=message_templates&action=form">Add your first template</a>.</p>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Public proposal</h2>
  <?php if ($publicUrl): ?>
    <p class="hint">Send this link to the client. It works without a login and records each view.</p>
    <div class="sharebox">
      <input type="text" id="public-url" readonly value="<?= e($publicUrl) ?>">
      <button type="button" class="btn ghost" id="copy-public-url">Copy link</button>
      <a class="btn primary" target="_blank" rel="noopener" href="<?= e($publicUrl) ?>">Open proposal</a>
    </div>
    <div class="alerts proposal-stats">
      <div class="alertbox"><b>Total views</b><span><?= (int)$viewCount ?></span></div>
      <div class="alertbox"><b>Last viewed</b><span><?= $lastView ? e(date('d M Y H:i', strtotime($lastView))) : 'Not yet' ?></span></div>
      <div class="alertbox"><b>Link status</b><span><?= (int)($q['public_enabled'] ?? 1) === 1 ? 'Active' : 'Disabled' ?></span></div>
    </div>
    <form method="post" action="index.php?page=quote_reset_link" onsubmit="return confirm('Generate a new link? The old link will stop working.');">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
      <button class="btn ghost" type="submit">Reset public link</button>
    </form>
  <?php else: ?>
    <p class="hint">Generate a secure, no-login link so the client can read the itinerary and accept or request changes online.</p>
    <form method="post" action="index.php?page=quote_share">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
      <button class="btn primary" type="submit">Generate public link</button>
    </form>
  <?php endif; ?>
</div>

<datalist id="item-categories">
  <?php foreach ($categories as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
</datalist>

<script>
(function () {
  var dayIndex = <?= (int)$dayIndex ?>;
  var itemIndex = <?= (int)$itemIndex ?>;
  var daysWrap = document.getElementById('days-wrap');
  var itemsWrap = document.getElementById('items-wrap');

  function dayHtml(i) {
    return '' +
      '<div class="builder-row day-row">' +
        '<div class="rowhead"><b>Day</b>' +
          '<input type="number" name="days[' + i + '][day_no]" min="1" step="1" value="' + (i + 1) + '" class="day-no">' +
          '<button type="button" class="btn tiny danger remove-row">Remove</button>' +
        '</div>' +
        '<div class="fgrid">' +
          '<div class="field span2"><label>Title</label><input type="text" name="days[' + i + '][title]" placeholder="e.g. Arrival in Arusha"></div>' +
          '<div class="field"><label>Accommodation</label><input type="text" name="days[' + i + '][accommodation]" placeholder="Lodge / camp"></div>' +
          '<div class="field"><label>Meals</label><input type="text" name="days[' + i + '][meals]" placeholder="B, L, D"></div>' +
          '<div class="field span2"><label>Description</label><textarea name="days[' + i + '][description]" rows="3"></textarea></div>' +
          '<div class="field span2"><label>Notes</label><textarea name="days[' + i + '][notes]" rows="2"></textarea></div>' +
        '</div>' +
      '</div>';
  }

  function itemHtml(i) {
    return '' +
      '<div class="builder-row item-row">' +
        '<div class="rowhead"><b>Line</b><button type="button" class="btn tiny danger remove-row">Remove</button></div>' +
        '<div class="fgrid">' +
          '<div class="field"><label>Category</label><input type="text" name="items[' + i + '][category]" list="item-categories" placeholder="Accommodation"></div>' +
          '<div class="field span2"><label>Description</label><input type="text" name="items[' + i + '][description]" placeholder="e.g. 2 nights Serengeti lodge"></div>' +
          '<div class="field"><label>Qty</label><input type="number" name="items[' + i + '][qty]" class="item-qty" min="0" step="0.01" value="1"></div>' +
          '<div class="field"><label>Unit</label><input type="text" name="items[' + i + '][unit]" placeholder="pax / night"></div>' +
          '<div class="field"><label>Unit price</label><input type="number" name="items[' + i + '][unit_price]" class="item-price" min="0" step="0.01" value="0"></div>' +
          '<div class="field"><label>Optional</label><label class="chk"><input type="checkbox" class="item-optional" name="items[' + i + '][optional]" value="1"> Optional extra</label></div>' +
          '<div class="field"><label>Line total</label><div class="line-total">0.00</div></div>' +
        '</div>' +
      '</div>';
  }

  document.getElementById('add-day').addEventListener('click', function () {
    daysWrap.insertAdjacentHTML('beforeend', dayHtml(dayIndex++));
    var empty = document.querySelector('.empty-note');
    if (empty && empty.textContent.indexOf('itinerary') !== -1) empty.remove();
  });

  document.getElementById('add-item').addEventListener('click', function () {
    itemsWrap.insertAdjacentHTML('beforeend', itemHtml(itemIndex++));
    var empty = document.querySelector('.empty-note');
    if (empty && empty.textContent.indexOf('priced') !== -1) empty.remove();
    recalc();
  });

  document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('remove-row')) return;
    var row = e.target.closest('.builder-row');
    if (row) row.remove();
    recalc();
  });

  function recalc() {
    var base = 0;
    var optional = 0;
    document.querySelectorAll('.item-row').forEach(function (row) {
      var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
      var price = parseFloat(row.querySelector('.item-price').value) || 0;
      var total = qty * price;
      row.querySelector('.line-total').textContent = total.toFixed(2);
      if (row.querySelector('.item-optional').checked) optional += total;
      else base += total;
    });
    var baseEl = document.getElementById('base-total');
    var optEl = document.getElementById('optional-total');
    if (baseEl) baseEl.textContent = base.toFixed(2);
    if (optEl) optEl.textContent = optional.toFixed(2);
    var amountEl = document.getElementById('quote-amount');
    if (amountEl && document.querySelectorAll('.item-row').length) amountEl.value = base.toFixed(2);
  }

  document.addEventListener('input', function (e) {
    if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) recalc();
  });
  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('item-optional')) recalc();
  });

  var copyBtn = document.getElementById('copy-public-url');
  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      var input = document.getElementById('public-url');
      input.select();
      input.setSelectionRange(0, 99999);
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value);
      } else {
        document.execCommand('copy');
      }
      copyBtn.textContent = 'Copied';
      setTimeout(function () { copyBtn.textContent = 'Copy link'; }, 1800);
    });
  }

  /* Insert a saved content block into one of the proposal textareas. */
  var contentSelect = document.getElementById('content-select');
  if (contentSelect) {
    document.querySelectorAll('[data-insert]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var opt = contentSelect.options[contentSelect.selectedIndex];
        if (!opt || !opt.value) return;
        var target = document.querySelector('[name="' + btn.getAttribute('data-insert') + '"]');
        if (!target) return;
        var body = opt.getAttribute('data-body') || '';
        target.value = target.value.trim() ? target.value.replace(/\s+$/, '') + '\n\n' + body : body;
        target.focus();
      });
    });
  }

  /* Fill a message template with this quote's details. */
  var msgVars = {
    client_name: <?= json_encode($q['client_name'] ?: 'Valued Client', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    quote_code: <?= json_encode($quoteCode, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    tour: <?= json_encode((string)($q['tour'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    amount: <?= json_encode(number_format((float)($q['amount'] ?? 0), 2, '.', '') . ' ' . $cur, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    valid_until: <?= json_encode($q['valid_until'] ? date('d M Y', strtotime($q['valid_until'])) : '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    company_name: <?= json_encode(setting('company_name', 'Michael Tourism'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    prepared_by: <?= json_encode((string)($q['prepared_by_name'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
  };

  function fillMessage(text) {
    Object.keys(msgVars).forEach(function (key) {
      text = text.split('{{' + key + '}}').join(msgVars[key]);
    });
    return text;
  }

  var messageSelect = document.getElementById('message-select');
  var messagePreview = document.getElementById('message-preview');
  if (messageSelect && messagePreview) {
    var renderMessage = function () {
      var opt = messageSelect.options[messageSelect.selectedIndex];
      if (!opt || !opt.value) { messagePreview.value = ''; return; }
      var subject = fillMessage(opt.getAttribute('data-subject') || '');
      var body = fillMessage(opt.getAttribute('data-body') || '');
      messagePreview.value = (subject ? 'Subject: ' + subject + '\n\n' : '') + body;
    };
    messageSelect.addEventListener('change', renderMessage);
    renderMessage();
  }

  var copyMessage = document.getElementById('copy-message');
  if (copyMessage && messagePreview) {
    copyMessage.addEventListener('click', function () {
      if (!messagePreview.value) return;
      messagePreview.select();
      messagePreview.setSelectionRange(0, 99999);
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(messagePreview.value);
      } else {
        document.execCommand('copy');
      }
      copyMessage.textContent = 'Copied';
      setTimeout(function () { copyMessage.textContent = 'Copy message'; }, 1800);
    });
  }

  recalc();
})();
</script>
