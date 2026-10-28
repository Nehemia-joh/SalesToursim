<?php /** layout.php - HTML shell used by every view. */
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Sales desk') ?> - Michael Tourism</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= !empty($bare) ? 'bare' : '' ?>">
<?php if (!empty($bare)): ?>
  <?= $content ?>
<?php else: ?>
<div class="shell">
  <aside class="side">
    <div class="brand">
      <span class="mark" aria-hidden="true">MT</span>
      <div class="brandtext"><b>Michael Tourism</b><span>Sales desk, Arusha</span></div>
    </div>
    <nav aria-label="Main navigation">
      <a href="index.php?page=dashboard" <?= ($page ?? '') === 'dashboard' ? 'class="on"' : '' ?>>Dashboard</a>
      <?php
        $groups = [
          'Sales pipeline' => [
            ['clients',      'Client database'],
            ['inquiries',    'Inquiry log'],
            ['pipeline',     'Sales pipeline'],
            ['quotations',   'Quotations'],
            ['followups',    'Follow-ups'],
            ['activities',   'Activity log'],
          ],
          'Money' => [
            ['bookings',     'Bookings'],
            ['travelers',    'Travelers'],
            ['schedule',     'Payment schedule'],
            ['payments',     'Payments'],
          ],
          'Management' => [
            ['reports',      'Reports'],
            ['targets',      'Sales targets'],
            ['settings',     'Settings'],
            ['help',         'How to use'],
          ],
          'Library' => [
            ['content_library',   'Content library'],
            ['message_templates', 'Message templates'],
            ['task_templates',    'Task templates'],
          ],
        ];
        foreach ($groups as $label => $items): ?>
          <div class="navgrp"><?= e($label) ?></div>
          <?php foreach ($items as [$p, $txt]): ?>
            <a href="index.php?page=<?= $p ?>" <?= ($page ?? '') === $p ? 'class="on"' : '' ?>><?= e($txt) ?></a>
          <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <?php $meName = (string)($user['name'] ?? 'Michael Tourism'); ?>
    <div class="me">
      <div class="me-row">
        <span class="avatar" aria-hidden="true"><?= e(strtoupper(substr($meName, 0, 1))) ?></span>
        <div>
          <div class="me-name"><?= e($meName) ?></div>
          <div class="me-role"><?= e($user['role'] ?? '') ?></div>
        </div>
      </div>
      <div class="me-links">
        <a href="index.php?page=users">Users</a>
        <a href="index.php?page=logout">Sign out</a>
      </div>
    </div>
  </aside>

  <main class="main">
    <div class="flashes">
    <?php if (!empty($flashes['ok'])): ?><div class="fl ok"><?= e($flashes['ok']) ?></div><?php endif; ?>
      <?php if (!empty($flashes['err'])): ?><div class="fl bad"><?= e($flashes['err']) ?></div><?php endif; ?>
    </div>
    <?= $content ?>
    <footer class="foot">Michael Tourism. Sales Management System, web edition. Base currency: <?= e($base_cur) ?>.</footer>
  </main>
</div>
<?php endif; ?>
</body>
</html>
