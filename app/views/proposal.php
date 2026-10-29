<?php /** proposal.php - public client-facing proposal. */
$quoteCode  = code_of('QT', (int)$q['id']);
$clientName = $q['client_name'] ?: 'Valued Client';
$preparedBy = $q['prepared_by_name'] ?: $company['name'];
$cur        = $q['currency'] ?: base_currency();
$baseTotal  = 0.0;
$optionalTotal = 0.0;
foreach ($items as $it) {
    if ((int)($it['optional'] ?? 0) === 1) $optionalTotal += (float)($it['total'] ?? 0);
    else $baseTotal += (float)($it['total'] ?? 0);
}
$displayTotal = $baseTotal > 0 ? $baseTotal : (float)($q['amount'] ?? 0);
$isAccepted = (($q['client_response'] ?? '') === 'Accepted');
$validUntil = $q['valid_until'] ? date('d M Y', strtotime($q['valid_until'])) : '';
$quoteDate  = $q['quote_date'] ? date('d M Y', strtotime($q['quote_date'])) : date('d M Y');
?>
<div class="proposal-page">

  <div class="proposal-toolbar no-print">
    <button type="button" class="btn primary" onclick="window.print()">Print / Save as PDF</button>
    <span class="hint">Proposal <?= e($quoteCode) ?>. Prepared <?= e($quoteDate) ?>.</span>
  </div>

  <?php if ($notice): ?>
    <div class="fl ok proposal-notice"><?= e($notice) ?></div>
  <?php endif; ?>

  <header class="proposal-hero">
    <div class="proposal-brandrow">
      <div class="proposal-brand">
        <span class="mark">MT</span>
        <div>
          <b><?= e($company['name']) ?></b>
          <span><?= e($company['tagline']) ?></span>
        </div>
      </div>
      <div class="proposal-code">
        <span>Proposal</span>
        <b><?= e($quoteCode) ?></b>
      </div>
    </div>

    <div class="proposal-title">
      <div class="proposal-eyebrow">Prepared exclusively for <?= e($clientName) ?></div>
      <h1><?= e($q['tour'] ?: 'Your Safari Proposal') ?></h1>
      <p class="proposal-sub">
        <?= e($quoteDate) ?><?= $validUntil ? ', valid until ' . e($validUntil) : '' ?>
        <?php if (!empty($q['pax'])): ?>, <?= (int)$q['pax'] ?> traveler<?= (int)$q['pax'] === 1 ? '' : 's' ?><?php endif; ?>
      </p>
    </div>

    <div class="proposal-route" aria-hidden="true">
      <span></span><i></i><span></span><i></i><span></span><i></i><span></span>
    </div>
  </header>

  <?php if (!empty($q['cover_note'])): ?>
    <section class="proposal-cover">
      <?= nl2br(e($q['cover_note'])) ?>
    </section>
  <?php endif; ?>

  <section class="proposal-meta">
    <div><span>Prepared for</span><b><?= e($clientName) ?></b></div>
    <div><span>Prepared by</span><b><?= e($preparedBy) ?></b></div>
    <div><span>Quote date</span><b><?= e($quoteDate) ?></b></div>
    <div><span>Valid until</span><b><?= $validUntil ? e($validUntil) : 'On request' ?></b></div>
  </section>

  <?php if ($days): ?>
    <section class="proposal-section">
      <h2>Your itinerary</h2>
      <div class="proposal-days">
        <?php foreach ($days as $d): ?>
          <article class="proposal-day">
            <div class="proposal-dayno">Day <?= (int)$d['day_no'] ?></div>
            <div class="proposal-daybody">
              <h3><?= e($d['title'] ?: 'Day ' . (int)$d['day_no']) ?></h3>
              <?php if (!empty($d['description'])): ?><p><?= nl2br(e($d['description'])) ?></p><?php endif; ?>
              <div class="proposal-daymeta">
                <?php if (!empty($d['accommodation'])): ?><span><b>Accommodation:</b> <?= e($d['accommodation']) ?></span><?php endif; ?>
                <?php if (!empty($d['meals'])): ?><span><b>Meals:</b> <?= e($d['meals']) ?></span><?php endif; ?>
              </div>
              <?php if (!empty($d['notes'])): ?><p class="proposal-note"><?= nl2br(e($d['notes'])) ?></p><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="proposal-section">
    <h2>Price breakdown</h2>
    <?php if ($items): ?>
      <div class="tablewrap">
        <table class="proposal-table">
          <thead>
            <tr><th>Item</th><th>Description</th><th class="num">Qty</th><th class="num">Unit price</th><th class="num">Total</th></tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr class="<?= (int)($it['optional'] ?? 0) === 1 ? 'optional-row' : '' ?>">
                <td><?= e($it['category'] ?: 'Item') ?><?= (int)($it['optional'] ?? 0) === 1 ? ' <span class="bdg info">Optional</span>' : '' ?></td>
                <td><?= e($it['description'] ?: '—') ?></td>
                <td class="num"><?= e(rtrim(rtrim(number_format((float)($it['qty'] ?? 0), 2), '0'), '.')) ?><?= $it['unit'] ? ' ' . e($it['unit']) : '' ?></td>
                <td class="num"><?= number_format((float)($it['unit_price'] ?? 0), 2) ?></td>
                <td class="num"><?= number_format((float)($it['total'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="proposal-totals">
        <div><span>Base total</span><b><?= number_format($baseTotal, 2) ?> <?= e($cur) ?></b></div>
        <?php if ($optionalTotal > 0): ?><div><span>Optional extras</span><b><?= number_format($optionalTotal, 2) ?> <?= e($cur) ?></b></div><?php endif; ?>
        <div class="grand"><span>Proposal total<?= $optionalTotal > 0 ? ' (base)' : '' ?></span><b><?= number_format($displayTotal, 2) ?> <?= e($cur) ?></b></div>
      </div>
    <?php else: ?>
      <div class="proposal-total-card">
        <span>Proposal total</span>
        <b><?= number_format($displayTotal, 2) ?> <?= e($cur) ?></b>
      </div>
    <?php endif; ?>
  </section>

  <?php if (!empty($q['inclusions']) || !empty($q['exclusions'])): ?>
    <section class="proposal-section proposal-two-col">
      <?php if (!empty($q['inclusions'])): ?>
        <div>
          <h2>Included</h2>
          <div class="preline"><?= nl2br(e($q['inclusions'])) ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($q['exclusions'])): ?>
        <div>
          <h2>Not included</h2>
          <div class="preline"><?= nl2br(e($q['exclusions'])) ?></div>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <?php if (!empty($q['terms'])): ?>
    <section class="proposal-section">
      <h2>Terms &amp; conditions</h2>
      <div class="preline proposal-terms"><?= nl2br(e($q['terms'])) ?></div>
    </section>
  <?php endif; ?>

  <?php if ($isAccepted): ?>
    <section class="proposal-accepted">
      <b>Proposal accepted</b>
      <p>Thank you — your acceptance has been recorded. We will contact you shortly to confirm the booking and next steps.</p>
      <?php if (!empty($q['client_comment'])): ?><p class="hint">Your note: “<?= e($q['client_comment']) ?>”</p><?php endif; ?>
    </section>
  <?php else: ?>
    <section class="proposal-actions no-print">
      <h2>Ready to go?</h2>
      <p class="hint">Accept this proposal or tell us what you would like changed. We will reply personally.</p>
      <form method="post" action="<?= e(url('page=proposal_respond&token=' . $token)) ?>">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="field">
          <label>Questions or requested changes (optional)</label>
          <textarea name="comment" rows="3" placeholder="Tell us if you want different dates, lodges, activities or a different budget."></textarea>
        </div>
        <div class="proposal-buttons">
          <button class="btn primary" type="submit" name="response" value="accepted">Accept this proposal</button>
          <button class="btn ghost" type="submit" name="response" value="changes">Request changes</button>
          <button class="btn danger" type="submit" name="response" value="declined"
                  onclick="return confirm('Decline this proposal?');">Decline</button>
        </div>
      </form>
    </section>
  <?php endif; ?>

  <footer class="proposal-footer">
    <div>
      <b><?= e($company['name']) ?></b>
      <span><?= e($company['tagline']) ?></span>
    </div>
    <div class="proposal-contact">
      <?php if ($company['phone']): ?><span><?= e($company['phone']) ?></span><?php endif; ?>
      <?php if ($company['email']): ?><span><?= e($company['email']) ?></span><?php endif; ?>
      <?php if ($company['website']): ?><span><?= e($company['website']) ?></span><?php endif; ?>
    </div>
  </footer>
</div>
