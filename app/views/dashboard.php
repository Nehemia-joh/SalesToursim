<?php /** dashboard.php - management dashboard */
$title = 'Dashboard';
$maxFunnel = max(1, max(array_merge([1], array_column($funnel, 'value'))));
$maxFunnelN = max(1, max(array_merge([1], array_column($funnel, 'n'))));
// Keep the Action Center compact: open only the highest-priority list.
$openAction = $todos['overdue_fu'] ? 'fu'
  : ($todos['overdue_sch'] ? 'pay'
  : ($todos['expiring'] ? 'exp'
  : ($todos['week_fu'] ? 'week' : '')));
?>
<div class="topbar">
  <div>
    <h1>Sales Dashboard</h1>
    <div class="hint">Live summary from every worksheet · amounts in <?= e($base_cur) ?></div>
  </div>
  <div class="tools">
    <span class="bdg info"><?= date('d M Y') ?></span>
    <a class="btn primary" href="index.php?page=inquiries&action=form">+ New inquiry</a>
  </div>
</div>

<div class="flow">
  <a href="index.php?page=inquiries&action=form"><b>1</b><span>New inquiry</span></a>
  <a href="index.php?page=pipeline&action=form"><b>2</b><span>Pipeline</span></a>
  <a href="index.php?page=quotations&action=form"><b>3</b><span>Quotation</span></a>
  <a href="index.php?page=followups&action=form"><b>4</b><span>Follow-up</span></a>
  <a href="index.php?page=bookings&action=form"><b>5</b><span>Booking</span></a>
  <a href="index.php?page=schedule&action=form"><b>6</b><span>Payment plan</span></a>
  <a href="index.php?page=payments&action=form"><b>7</b><span>Payment received</span></a>
</div>

<div class="cards">
  <div class="kpi"><div class="k">Inquiries</div><div class="v"><?= number_format($kpi['inquiries']) ?></div><div class="s"><?= $kpi['open_leads'] ?> open · <?= $kpi['won'] ?> won · <?= $kpi['lost'] ?> lost</div></div>
  <div class="kpi"><div class="k">Firm Bookings</div><div class="v"><?= number_format($kpi['firm_bookings']) ?></div><div class="s"><?= $kpi['confirmed_bookings'] ?> confirmed · <?= $kpi['completed_bookings'] ?> completed</div></div>
  <div class="kpi"><div class="k">Booking Value</div><div class="v"><?= money($kpi['booking_value'], true) ?></div><div class="s"><?= $kpi['all_bookings'] ?> bookings on file</div></div>
  <div class="kpi"><div class="k">Actual Revenue</div><div class="v"><?= money($kpi['actual_revenue'], true) ?></div><div class="s">gross profit <?= money($kpi['gross_profit'], true) ?> (<?= number_format($kpi['margin_pct'], 1) ?>%)</div></div>
  <div class="kpi"><div class="k">Cash Received</div><div class="v"><?= money($kpi['payments_received'], true) ?></div><div class="s">outstanding <?= money($kpi['outstanding'], true) ?></div></div>
  <div class="kpi"><div class="k">Weighted Pipeline</div><div class="v"><?= money_precise($kpi['weighted_pipeline'], true) ?></div><div class="s">open <?= money($kpi['weighted_open'], true) ?> · <?= $kpi['open_pipeline'] ?> opps</div></div>
  <div class="kpi <?= $kpi['overdue_tasks'] > 0 ? 'alert' : '' ?>"><div class="k">Overdue Follow-ups</div><div class="v"><?= $kpi['overdue_tasks'] ?></div><div class="s">chase these now</div></div>
  <div class="kpi <?= $kpi['overdue_invoices_n'] > 0 ? 'alert' : '' ?>"><div class="k">Overdue Invoices</div><div class="v"><?= $kpi['overdue_invoices_n'] ?></div><div class="s"><?= money($kpi['overdue_invoices_amt'], true) ?> to collect</div></div>
  <div class="kpi"><div class="k"><?= date('M') ?> Revenue</div><div class="v"><?= money($kpi['month_revenue'], true) ?></div><div class="s">this month</div></div>
  <div class="kpi"><div class="k">Avg Booking Value</div><div class="v"><?= money($kpi['avg_booking_value'], true) ?></div><div class="s"><?= number_format($kpi['avg_days_confirm'], 1) ?> days inquiry → booking</div></div>
  <div class="kpi"><div class="k">Inquiry → Won</div><div class="v"><?= number_format($kpi['win_rate'], 1) ?>%</div><div class="s"><?= $kpi['lost_opps'] ?> lost opportunities</div></div>
  <div class="kpi"><div class="k">Cash Collection</div><div class="v"><?= number_format($kpi['collection_pct'], 1) ?>%</div><div class="s"><?= $kpi['sched_paid_n'] ?>/<?= $kpi['sched_total_n'] ?> milestones collected</div></div>
</div>

<div class="two">
  <div class="card action-center">
    <h2>Action Center</h2>

    <details class="acc" name="dash-actions">
      <summary>Team follow-up load <span class="cnt"><?= array_sum(array_column($owners, 'overdue')) ?> overdue · <?= array_sum(array_column($owners, 'due_week')) ?> this week</span></summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>Salesperson</th><th class="num">Overdue</th><th class="num">Due this week</th><th class="num">Open tasks</th></tr></thead>
          <tbody>
          <?php $tO = $tW = $tT = 0; foreach ($owners as $o): $tO += (int)$o['overdue']; $tW += (int)$o['due_week']; $tT += (int)$o['open_tasks']; ?>
            <tr>
              <td><b><?= e($o['name']) ?></b></td>
              <td class="num <?= (int)$o['overdue'] > 0 ? 'neg' : '' ?>"><?= (int)$o['overdue'] ?></td>
              <td class="num"><?= (int)$o['due_week'] ?></td>
              <td class="num"><?= (int)$o['open_tasks'] ?></td>
            </tr>
          <?php endforeach; ?>
            <tr class="totrow"><td>TOTAL</td><td class="num"><?= $tO ?></td><td class="num"><?= $tW ?></td><td class="num"><?= $tT ?></td></tr>
          </tbody>
        </table>
        </div>
      </div>
    </details>

    <details class="acc" name="dash-actions" <?= $openAction === 'fu' ? 'open' : '' ?>>
      <summary>Overdue follow-ups <span class="cnt"><?= count($todos['overdue_fu']) ?></span></summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>ID</th><th>Client</th><th>Action</th><th>Due</th><th>Owner</th></tr></thead>
          <tbody>
          <?php foreach ($todos['overdue_fu'] as $t): ?>
            <tr><td class="cd"><?= e($t['code']) ?></td><td><?= e($t['client_name']) ?></td><td class="celltext"><?= e($t['action']) ?></td>
              <td><?= badge('Overdue') ?> <?= e(date('d M', strtotime($t['due_date']))) ?></td><td><?= e($t['sp_name']) ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$todos['overdue_fu']): ?><tr><td colspan="5" class="empty">Nothing overdue. 🎉</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
        <div class="dash-more"><a href="index.php?page=followups">Open follow-ups →</a></div>
      </div>
    </details>

    <details class="acc" name="dash-actions" <?= $openAction === 'week' ? 'open' : '' ?>>
      <summary>Due this week <span class="cnt"><?= count($todos['week_fu']) ?></span></summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>ID</th><th>Client</th><th>Action</th><th>Due</th><th>Owner</th></tr></thead>
          <tbody>
          <?php foreach ($todos['week_fu'] as $t): ?>
            <tr><td class="cd"><?= e($t['code']) ?></td><td><?= e($t['client_name']) ?></td><td class="celltext"><?= e($t['action']) ?></td>
              <td><?= e(date('d', strtotime($t['due_date']))) ?>&nbsp;<?= e(date('M', strtotime($t['due_date']))) ?></td><td><?= e($t['sp_name']) ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$todos['week_fu']): ?><tr><td colspan="5" class="empty">Nothing due this week.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
        <div class="dash-more"><a href="index.php?page=followups">Open follow-ups →</a></div>
      </div>
    </details>

    <h3>Cash &amp; quote alerts</h3>
    <div class="alerts three">
      <div class="alertbox <?= $kpi['sched_overdue_n'] > 0 ? 'bad' : '' ?>">
        <b>Overdue milestones</b><span><?= $kpi['sched_overdue_n'] ?> · <?= money($kpi['sched_overdue_amt'], true) ?></span>
      </div>
      <div class="alertbox">
        <b>Due within 7 days</b><span><?= $kpi['sched_due7d_n'] ?> · <?= money($kpi['sched_due7d_amt'], true) ?></span>
      </div>
      <div class="alertbox">
        <b>Tentative holds</b><span><?= $kpi['tentative_n'] ?> · <?= money($kpi['tentative_val'], true) ?></span>
      </div>
      <div class="alertbox <?= $kpi['quotes_expiring'] > 0 ? 'warn' : '' ?>">
        <b>Quotes expiring</b><span><?= $kpi['quotes_expiring'] ?> within 7 days</span>
      </div>
      <div class="alertbox <?= $kpi['quotes_awaiting'] > 0 ? 'warn' : '' ?>">
        <b>Awaiting client response</b><span><?= $kpi['quotes_awaiting'] ?> sent / revised proposals</span>
      </div>
      <div class="alertbox">
        <b>Proposal views (7 days)</b><span><?= $kpi['proposal_views_7d'] ?> views · <?= $kpi['quotes_opened_7d'] ?> proposals opened</span>
      </div>
    </div>

    <details class="acc" name="dash-actions" <?= $openAction === 'pay' ? 'open' : '' ?>>
      <summary>Payment alarms <span class="cnt">overdue <?= count($todos['overdue_sch']) ?> · due ≤7d <?= count($todos['due_sch']) ?></span></summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>Ref</th><th>Booking</th><th>Client</th><th>Due</th><th class="num">Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach (array_merge($todos['overdue_sch'], $todos['due_sch']) as $s): ?>
            <tr><td class="cd"><?= e($s['code']) ?></td><td class="cd"><?= e($s['bk_code']) ?></td><td><?= e($s['client_name'] ?? '') ?></td>
              <td><?= e(date('d M', strtotime($s['due_date']))) ?></td><td class="num"><?= money($s['amount_due']) ?></td>
              <td><?= badge(strtotime($s['due_date']) < time() ? 'Overdue' : 'Unpaid') ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$todos['overdue_sch'] && !$todos['due_sch']): ?><tr><td colspan="6" class="empty">No payment alarms.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
        <div class="dash-more"><a href="index.php?page=schedule">Open payment schedule →</a></div>
      </div>
    </details>

    <details class="acc" name="dash-actions">
      <summary>Tentative holds <span class="cnt"><?= $kpi['tentative_n'] ?> · <?= money($kpi['tentative_val'], true) ?></span></summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>Ref</th><th>Client</th><th>Tour</th><th class="num">Value</th><th>Owner</th></tr></thead>
          <tbody>
          <?php foreach ($todos['tentative'] as $t): ?>
            <tr><td class="cd"><?= e($t['bk_code']) ?></td><td><?= e($t['client_name']) ?></td><td class="celltext"><?= e($t['tour']) ?></td>
              <td class="num"><?= money($t['booking_value']) ?></td><td><?= e($t['sp_name']) ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$todos['tentative']): ?><tr><td colspan="5" class="empty">No tentative holds.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
        <div class="dash-more"><a href="index.php?page=bookings">Open bookings →</a></div>
      </div>
    </details>

    <details class="acc" name="dash-actions" <?= $openAction === 'exp' ? 'open' : '' ?>>
      <summary>Quotes expiring within 7 days <span class="cnt"><?= count($todos['expiring']) ?></span></summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>Ref</th><th>Client</th><th>Tour</th><th class="num">Amount</th><th>Expires</th></tr></thead>
          <tbody>
          <?php foreach ($todos['expiring'] as $t): ?>
            <tr><td class="cd"><?= e($t['qt_code']) ?></td><td><?= e($t['client_name']) ?></td><td class="celltext"><?= e($t['tour']) ?></td>
              <td class="num"><?= money($t['amount']) ?></td><td><?= e(date('d M', strtotime($t['valid_until']))) ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$todos['expiring']): ?><tr><td colspan="5" class="empty">Nothing expiring this week.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
        <div class="dash-more"><a href="index.php?page=quotations">Open quotations →</a></div>
      </div>
    </details>
  </div>

  <div class="card">
    <h2>Team Leaderboard</h2>
    <div class="tablewrap">
    <table class="grid small leaderboard">
      <thead><tr><th>#</th><th>Rep</th><th class="num">Inq</th><th class="num">Bkgs</th><th class="num">Revenue</th><th class="num">Target</th><th>Achv.</th><th class="num">Wtd Pipe</th></tr></thead>
      <tbody>
      <?php $lInq = $lBk = $lRev = $lTgt = $lPipe = 0; foreach ($lb as $i => $r): $lInq += (int)$r['inq_n']; $lBk += (int)$r['bk_n']; $lRev += (float)$r['revenue']; $lTgt += (float)$r['target']; $lPipe += (float)$r['open_pipeline']; ?>
        <tr><td><b><?= badge((string)($i + 1)) ?></b></td><td><b><?= e($r['name']) ?></b></td>
          <td class="num"><?= (int)$r['inq_n'] ?></td><td class="num"><?= (int)$r['bk_n'] ?></td>
          <td class="num"><?= money($r['revenue']) ?></td>
          <td class="num <?= (float)$r['revenue'] < (float)$r['target'] ? 'neg' : '' ?>"><?= money($r['target']) ?></td>
          <td><?= pct_bar((float)$r['achievement']) ?></td>
          <td class="num"><?= money_precise($r['open_pipeline']) ?></td></tr>
      <?php endforeach; ?>
        <tr class="totrow"><td></td><td>TOTAL</td><td class="num"><?= $lInq ?></td><td class="num"><?= $lBk ?></td>
          <td class="num"><?= money($lRev) ?></td><td class="num"><?= money($lTgt) ?></td>
          <td><?= pct_bar(pct($lRev, $lTgt)) ?></td><td class="num"><?= money_precise($lPipe) ?></td></tr>
      </tbody>
    </table>
    </div>

    <details class="acc" open>
      <summary>Sales Funnel</summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>Stage</th><th class="num">Opps</th><th>Value</th><th>Share</th></tr></thead>
          <tbody>
          <?php foreach ($funnel as $f): ?>
            <tr><td><?= badge($f['sales_stage']) ?></td>
              <td class="num"><?= (int)$f['n'] ?></td>
              <td><div class="fbar"><div style="width:<?= round(100 * $f['value'] / $maxFunnel) ?>%"></div></div>
                  <span class="fnum"><?= money($f['value'], true) ?></span></td>
              <td><div class="fbar alt"><div style="width:<?= round(100 * $f['n'] / $maxFunnelN) ?>%"></div></div></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
    </details>

    <details class="acc">
      <summary>Top Clients (firm bookings)</summary>
      <div class="accbody">
        <div class="tablewrap">
        <table class="grid small">
          <thead><tr><th>Client</th><th class="num">Bookings</th><th class="num">Revenue</th></tr></thead>
          <tbody>
          <?php foreach ($topClients as $c): ?>
            <tr><td><b><?= e($c['name']) ?></b></td><td class="num"><?= (int)$c['bk_n'] ?></td><td class="num"><?= money($c['revenue']) ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$topClients): ?><tr><td colspan="3" class="empty">No firm bookings yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </details>
  </div>
</div>

<div class="card full">
  <div class="topbar" style="margin-bottom:8px">
    <div>
      <h2 style="margin:0">Running &amp; upcoming tours</h2>
      <p class="hint" style="margin:4px 0 0">Tours on the road now, plus departures in the next 7 days. Showing up to 4 in each list.</p>
    </div>
    <div class="tools">
      <a class="btn ghost" href="index.php?page=bookings">All bookings</a>
      <a class="btn ghost" href="index.php?page=travelers">Travelers</a>
    </div>
  </div>

  <div class="two equal">
    <div>
      <h3>On tour now (<?= count($todos['running_tours']) ?>)</h3>
      <div class="tablewrap">
      <table class="grid small">
        <thead><tr><th>Ref</th><th>Tour</th><th>Client</th><th>Dates</th><th class="num">Pax</th><th class="num">Travelers</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($todos['running_tours'], 0, 4) as $t): ?>
          <tr>
            <td class="cd"><?= e($t['bk_code']) ?></td>
            <td class="celltext"><?= e($t['tour']) ?></td>
            <td><?= e($t['client_name'] ?? '') ?></td>
            <td class="dt"><?= e(date('d M', strtotime($t['arrival']))) ?> → <?= e(date('d M Y', strtotime($t['departure']))) ?></td>
            <td class="num"><?= (int)$t['pax'] ?></td>
            <td class="num"><?= (int)$t['traveler_count'] ?></td>
            <td><?= badge((string)$t['status']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$todos['running_tours']): ?><tr><td colspan="7" class="empty">No tours on the road today.</td></tr><?php endif; ?>
        </tbody>
      </table>
      </div>
      <?php if (count($todos['running_tours']) > 4): ?>
        <div class="dash-more"><a href="index.php?page=bookings">View all <?= count($todos['running_tours']) ?> running tours →</a></div>
      <?php endif; ?>
    </div>

    <div>
      <h3>Departing in the next 7 days (<?= count($todos['upcoming_tours']) ?>)</h3>
      <div class="tablewrap">
      <table class="grid small">
        <thead><tr><th>Ref</th><th>Tour</th><th>Client</th><th>Arrival</th><th class="num">Pax</th><th class="num">Travelers</th><th>Owner</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($todos['upcoming_tours'], 0, 4) as $t): ?>
          <tr>
            <td class="cd"><?= e($t['bk_code']) ?></td>
            <td class="celltext"><?= e($t['tour']) ?></td>
            <td><?= e($t['client_name'] ?? '') ?></td>
            <td class="dt"><?= e(date('d M', strtotime($t['arrival']))) ?> <span class="hint">(in <?= (int)$t['days_to_arrival'] ?>d)</span></td>
            <td class="num"><?= (int)$t['pax'] ?></td>
            <td class="num"><?= (int)$t['traveler_count'] ?></td>
            <td><?= e($t['sp_name'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$todos['upcoming_tours']): ?><tr><td colspan="7" class="empty">No departures in the next 7 days.</td></tr><?php endif; ?>
        </tbody>
      </table>
      </div>
      <?php if (count($todos['upcoming_tours']) > 4): ?>
        <div class="dash-more"><a href="index.php?page=bookings">View all <?= count($todos['upcoming_tours']) ?> upcoming tours →</a></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card full">
  <details class="acc">
    <summary><span class="acc-h">12-Month Revenue vs Target</span> <span class="cnt">click to expand</span></summary>
    <div class="accbody">
      <div class="legend">
        <span><i style="background:#0F766E"></i> Actual revenue</span>
        <span><i style="background:#D8D2C4"></i> Target</span>
        <span class="hint">Hover a bar for the exact figure.</span>
      </div>
      <?= svg_grouped_bars($trend, 'label', [
          ['key' => 'rev',    'label' => 'Revenue', 'color' => '#0F766E'],
          ['key' => 'target', 'label' => 'Target',  'color' => '#D8D2C4'],
      ], 1000, 240) ?>

      <details class="acc">
        <summary>Monthly detail <span class="cnt">12 months</span></summary>
        <div class="accbody">
          <div class="tablewrap">
          <table class="grid small">
            <thead><tr><th>Month</th><th class="num">Inquiries</th><th class="num">Bookings</th><th class="num">Revenue</th><th class="num">Target</th><th>Achievement</th></tr></thead>
            <tbody>
            <?php foreach ($trend as $t): ?>
              <tr><td><b><?= e($t['label'] . ' ' . substr($t['ym'], 0, 4)) ?></b></td>
                <td class="num"><?= $t['inq'] ?></td><td class="num"><?= $t['bk'] ?></td>
                <td class="num"><?= money($t['rev']) ?></td>
                <td class="num"><?= $t['target'] > 0 ? money($t['target']) : '—' ?></td>
                <td><?= $t['target'] > 0 ? pct_bar(pct($t['rev'], $t['target'])) : '<span class="bdg dim">no target</span>' ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        </div>
      </details>
    </div>
  </details>
</div>
