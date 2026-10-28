<?php /** login.php */
$title = 'Sign in';
?>
<div class="loginwrap">
  <div class="login card">
    <div class="brand big"><span class="mark">MT</span><b>Michael Tourism</b><span>Sales Management System</span></div>
    <p class="login-sub">Sign in to manage your pipeline and bookings.</p>
    <?php if ($err): ?><div class="fl bad"><?= e($err) ?></div><?php endif; ?>
    <form method="post" action="index.php?page=login">
      <?= csrf_field() ?>
      <div class="field"><label>Email</label>
        <input type="email" name="email" autofocus required value="admin@michaeltourism.com"></div>
      <div class="field"><label>Password</label>
        <input type="password" name="password" required></div>
      <button class="btn primary wide" type="submit">Sign in</button>
    </form>
    <p class="hint">Demo: <b>admin@michaeltourism.com</b> · <b>demo1234</b></p>
  </div>
</div>
