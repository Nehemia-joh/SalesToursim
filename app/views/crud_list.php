<?php /** crud_list.php - generic module list table. */ ?>
<div class="topbar">
  <h1><?= e($m['title']) ?></h1>
  <div class="tools">
    <form method="get" class="searchform" action="<?= e(url('page=' . $module)) ?>">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search records...">
      <button class="btn ghost" type="submit">Search</button>
      <?php if ($q !== ''): ?><a class="btn ghost" href="<?= e(url('page=' . $module)) ?>">Clear</a><?php endif; ?>
    </form>
    <a class="btn primary" href="<?= e(url('page=' . $module . '&action=form')) ?>">+ New</a>
  </div>
</div>

<div class="card full listcard">
  <div class="tablewrap listwrap">
  <table class="grid">
    <thead><tr>
      <?php foreach ($m['cols'] as [$k, $label]): ?><th><?= e($label) ?></th><?php endforeach; ?>
      <th class="ops">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <?php foreach ($m['cols'] as [$k, , $type]): ?>
          <?php $v = $r[$k] ?? null; ?>
          <?php if ($type === 'badge'):   ?> <td><?= badge($v === null || $v === '' ? '-' : (string)$v) ?></td>
          <?php elseif ($type === 'money'): ?> <td class="num <?= (float)$v < 0 ? 'neg' : '' ?>"><?= money($v, false) ?></td>
          <?php elseif ($type === 'int'):  ?> <td class="num"><?= $v === null ? '—' : e($v) ?></td>
          <?php elseif ($type === 'date'): ?> <td class="dt"><?= $v ? e(date('d M Y', strtotime($v))) : '—' ?></td>
          <?php elseif ($type === 'datetime'): ?> <td class="dt"><?= $v ? e(date('d M Y H:i', strtotime($v))) : '—' ?></td>
          <?php elseif ($type === 'pct'):  ?> <td><?= isset($r['achievement']) && $r['achievement'] !== null ? pct_bar((float)$r['achievement']) : '<span class="bdg dim">no target</span>' ?></td>
          <?php elseif ($type === 'percent'): ?> <td class="num"><?= $v === null || $v === '' ? '—' : number_format((float)$v, 1) . '%' ?></td>
          <?php else: ?> <td class="celltext"><?= $v === null || $v === '' ? '—' : e($v) ?></td>
          <?php endif; ?>
        <?php endforeach; ?>
        <td class="ops">
          <?php if ($module === 'quotations'): ?>
            <a class="btn tiny primary" href="<?= e(url('page=quote_builder&id=' . (int)($r['id'] ?? 0))) ?>">Build</a>
            <?php if (!empty($r['public_token'])): ?>
              <a class="btn tiny ghost" target="_blank" rel="noopener"
                 href="<?= e(absolute_url('page=proposal&token=' . urlencode((string)$r['public_token']))) ?>">Proposal</a>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($module === 'task_templates' && (int)($r['active'] ?? 0) === 1): ?>
            <a class="btn tiny primary" href="<?= e(url('page=followups&action=form&template_id=' . (int)($r['id'] ?? 0))) ?>">Use</a>
          <?php endif; ?>
          <a class="btn tiny" href="<?= e(url('page=' . $module . '&action=form&id=' . (int)($r['id'] ?? 0))) ?>">Edit</a>
          <?php if (in_array(Auth::role(), ['admin', 'manager'], true)): ?>
          <form method="post" action="<?= e(url('page=' . $module . '&action=delete&id=' . (int)($r['id'] ?? 0))) ?>" class="inline"
                onsubmit="return confirm('Delete this record? This cannot be undone.');"><?= csrf_field() ?>
            <button class="btn tiny danger" type="submit">Del</button></form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td class="empty" colspan="99">No records yet. Use “+ New” to add the first one.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
  <div class="count"><?= count($rows) ?> record<?= count($rows) === 1 ? '' : 's' ?>.<?php if (count($m['cols']) > 8): ?> <span class="hint">Scroll sideways to see all columns.</span><?php endif; ?></div>
</div>
