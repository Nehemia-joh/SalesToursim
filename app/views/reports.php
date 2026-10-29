<?php /** reports.php - Management Reports */ 
$title = 'Reports';
?>
<div class="topbar">
  <div>
    <h1>Management reports</h1>
    <div class="hint">The full Excel report pack. All figures in <?= e($base_cur) ?>.</div>
  </div>
  <div class="tools">
    <a class="btn ghost" href="<?= e(url('page=dashboard')) ?>">← Dashboard</a>
  </div>
</div>

<div class="two equal">
  <div class="card">
    <h2>Sold state</h2>
    <table class="grid small">
      <tbody>
        <tr><td>Total inquiries</td><td class="num"><b><?= number_format($rep['inquiries']) ?></b></td></tr>
        <tr><td>Open leads</td><td class="num"><b><?= number_format($rep['open_leads']) ?></b></td></tr>
        <tr><td>Confirmed bookings</td><td class="num"><b><?= number_format($rep['confirmed']) ?></b></td></tr>
        <tr><td>Completed bookings</td><td class="num"><b><?= number_format($rep['completed']) ?></b></td></tr>
        <tr><td>Lost opportunities</td><td class="num"><b><?= number_format($rep['lost_opps']) ?></b></td></tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Money</h2>
    <table class="grid small">
      <tbody>
        <tr><td>Total booking value</td><td class="num"><b><?= money($rep['booking_value'], true) ?></b></td></tr>
        <tr><td>Actual revenue</td><td class="num"><b><?= money($rep['revenue'], true) ?></b></td></tr>
        <tr><td>Direct costs</td><td class="num"><b><?= money($rep['cost'], true) ?></b></td></tr>
        <tr><td>Gross profit</td><td class="num"><b><?= money($rep['gross_profit'], true) ?></b> <span class="hint">(<?= number_format($rep['margin_pct'], 1) ?>%)</span></td></tr>
        <tr><td>Payments received</td><td class="num"><b><?= money($rep['payments'], true) ?></b></td></tr>
        <tr><td>Outstanding balances</td><td class="num"><b><?= money($rep['outstanding'], true) ?></b></td></tr>
        <tr><td>Overdue invoices</td><td class="num"><b class="<?= $rep['overdue_invoices_amt'] > 0 ? 'neg' : '' ?>"><?= money($rep['overdue_invoices_amt'], true) ?></b> <span class="hint">(<?= $rep['overdue_invoices_n'] ?>)</span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Pipeline &amp; operations</h2>
    <table class="grid small">
      <tbody>
        <tr><td>Estimated pipeline value</td><td class="num"><b><?= money($rep['estimated_pipeline'], true) ?></b></td></tr>
        <tr><td>Weighted pipeline value</td><td class="num"><b><?= money_precise($rep['weighted_pipeline'], true) ?></b></td></tr>
        <tr><td>Open pipeline (next 60 days)</td><td class="num"><b><?= money($rep['open_pipeline_60'], true) ?></b></td></tr>
        <tr><td>Overdue follow-up tasks</td><td class="num"><b class="<?= $rep['overdue_tasks'] > 0 ? 'neg' : '' ?>"><?= number_format($rep['overdue_tasks']) ?></b></td></tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Conversion &amp; collection</h2>
    <table class="grid small">
      <tbody>
        <tr><td>Inquiry → Booking win rate</td><td class="num"><b><?= number_format($rep['conversion'], 1) ?>%</b></td></tr>
        <tr><td>Quotation acceptance rate</td><td class="num"><b><?= number_format($rep['quote_win_rate'], 1) ?>%</b> <span class="hint">(<?= $rep['quotes_accepted'] ?>/<?= $rep['quotes'] ?>)</span></td></tr>
        <tr><td>Average booking value</td><td class="num"><b><?= money($rep['avg_lead_value'], true) ?></b></td></tr>
        <tr><td>Average days inquiry → booking</td><td class="num"><b><?= number_format($rep['avg_days_confirm'], 1) ?></b></td></tr>
        <tr><td>Cash collection coverage</td><td class="num"><b><?= number_format($rep['collection'], 1) ?>%</b> <span class="hint">(<?= $rep['sched_paid'] ?>/<?= $rep['sched_total'] ?> milestones)</span></td></tr>
        <tr><td>Cash received vs revenue</td><td class="num"><b><?= number_format($rep['cash_vs_revenue'], 1) ?>%</b></td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card full">
  <h2>Revenue vs target by month</h2>
  <div class="legend">
    <span><i style="background:#2E5A4A"></i> Actual revenue</span>
    <span><i style="background:#C9B98F"></i> Target</span>
  </div>
  <?= svg_grouped_bars($monthly, 'label', [
      ['key' => 'revenue', 'label' => 'Revenue', 'color' => '#2E5A4A'],
      ['key' => 'target',  'label' => 'Target',  'color' => '#C9B98F'],
  ], 1000, 280) ?>
</div>

<div class="card">
  <h2>Per salesperson</h2>
  <div class="tablewrap">
  <table class="grid small">
    <thead><tr><th>Rep</th><th class="num">Inq</th><th class="num">Won</th><th class="num">Bookings</th><th class="num">Book Value</th><th class="num">Revenue</th><th class="num">Target</th><th>Achievement</th><th class="num">Wtd Pipe</th></tr></thead>
    <tbody>
    <?php foreach ($team as $r): ?>
      <tr><td><b><?= e($r['name']) ?></b></td>
        <td class="num"><?= $r['inq_n'] ?></td><td class="num"><?= $r['won_n'] ?></td><td class="num"><?= $r['bk_n'] ?></td>
        <td class="num"><?= money($r['bk_value']) ?></td><td class="num"><?= money($r['revenue']) ?></td>
        <td class="num"><?= money($r['target']) ?></td>
        <td><?= (float)$r['target'] > 0 ? pct_bar(pct($r['revenue'], $r['target'])) : '<span class="bdg dim">no target</span>' ?></td>
        <td class="num"><?= money_precise($r['open_pipeline']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <h2>Monthly detail</h2>
  <div class="tablewrap">
  <table class="grid small">
    <thead><tr><th>Month</th><th class="num">Target</th><th class="num">Revenue</th><th>Achievement</th></tr></thead>
    <tbody>
    <?php foreach ($monthly as $mrow): ?>
      <tr><td><b><?= e($mrow['label']) ?></b></td>
        <td class="num"><?= $mrow['target'] > 0 ? money($mrow['target']) : '—' ?></td>
        <td class="num"><?= money($mrow['revenue']) ?></td>
        <td><?= $mrow['target'] > 0 ? pct_bar(pct($mrow['revenue'], $mrow['target'])) : '<span class="bdg dim">no target</span>' ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="two equal">
  <div class="card">
    <h2>Lead source performance</h2>
    <div class="tablewrap">
    <table class="grid small">
      <thead><tr><th>Source</th><th class="num">Inquiries</th><th class="num">Won</th><th class="num">Win %</th><th class="num">Wtd Pipeline</th></tr></thead>
      <tbody>
      <?php foreach ($sources as $s): ?>
        <tr><td><b><?= e($s['src']) ?></b></td>
          <td class="num"><?= $s['inq_n'] ?></td><td class="num"><?= $s['won_n'] ?></td>
          <td class="num"><?= number_format(pct($s['won_n'], $s['inq_n']), 1) ?>%</td>
          <td class="num"><?= money($s['pipeline']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="card">
    <h2>Lost / cold reasons</h2>
    <div class="tablewrap">
    <table class="grid small">
      <thead><tr><th>Reason</th><th class="num">Opps</th><th class="num">Value at Risk</th></tr></thead>
      <tbody>
      <?php foreach ($lost as $l): ?>
        <tr><td class="celltext"><?= e($l['reason']) ?></td><td class="num"><?= $l['n'] ?></td><td class="num"><?= money($l['value'], true) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$lost): ?><tr><td colspan="3" class="empty">No lost deals logged.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<div class="card">
  <h2>Upcoming travel</h2>
  <div class="tablewrap">
  <table class="grid small">
    <thead><tr><th>Ref</th><th>Client</th><th>Tour</th><th>Arrival</th><th class="num">Pax</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($upcoming as $u): ?>
      <tr><td class="cd"><?= e($u['bk_code']) ?></td><td><?= e($u['client_name']) ?></td><td class="celltext"><?= e($u['tour']) ?></td>
        <td><?= e(date('d M Y', strtotime($u['arrival']))) ?></td><td class="num"><?= $u['pax'] ?></td>
        <td><?= badge($u['status']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$upcoming): ?><tr><td colspan="6" class="empty">No confirmed travel ahead.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
