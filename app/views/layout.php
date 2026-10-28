<?php /** layout.php - HTML shell used by every view. */
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Sales Manager') ?> — Michael Tourism</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= !empty($bare) ? 'bare' : '' ?>">
<?php if (!empty($bare)): ?>
  <?= $content ?>
<?php else: ?>
<div class="shell">
  <aside class="side">
    <div class="brand">
      <span class="mark">MT</span>
      <div class="brandtext"><b>Michael Tourism</b><span>Sales Manager</span></div>
    </div>
    <nav>
      <a href="index.php?page=dashboard" <?= ($page ?? '') === 'dashboard' ? 'class="on"' : '' ?>>Dashboard</a>
      <?php
        $groups = [
          'Sales Pipeline' => [
            ['clients',      'Client Database'],
            ['inquiries',    'Inquiry Log'],
            ['pipeline',     'Sales Pipeline'],
            ['quotations',   'Quotations'],
            ['followups',    'Follow-Ups'],
            ['activities',   'Activity Log'],
          ],
          'Money' => [
            ['bookings',     'Bookings'],
            ['schedule',     'Payment Schedule'],
            ['payments',     'Payments'],
          ],
          'Management' => [
            ['reports',      'Reports'],
            ['targets',      'Sales Targets'],
            ['settings',     'Settings'],
            ['help',         'How to use'],
          ],
        ];
        foreach ($groups as $label => $items): ?>
          <div class="navgrp"><?= e($label) ?></div>
          <?php foreach ($items as [$p, $txt]): ?>
            <a href="index.php?page=<?= $p ?>" <?= ($page ?? '') === $p ? 'class="on"' : '' ?>><?= e($txt) ?></a>
          <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <div class="me">
      <div><?= e($user['name'] ?? '') ?> <small>(<?= e($user['role'] ?? '') ?>)</small></div>
      <a href="index.php?page=users">Users</a>
      <a href="index.php?page=logout">Sign out</a>
    </div>
  </aside>

  <main class="main">
    <div class="flashes">
    <?php if (!empty($flashes['ok'])): ?><div class="fl ok"><?= e($flashes['ok']) ?></div><?php endif; ?>
      <?php if (!empty($flashes['err'])): ?><div class="fl bad"><?= e($flashes['err']) ?></div><?php endif; ?>
    </div>
    <?= $content ?>
    <footer class="foot">Michael Tourism — Sales Management System (web edition) · base currency: <?= e($base_cur) ?></footer>
  </main>
</div>
<?php endif; ?>
</body>
</html>
