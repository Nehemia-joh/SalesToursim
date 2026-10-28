<?php /** login.php */
$title = 'Sign in';
?>
<div class="loginwrap">
  <section class="login-story">
    <div class="login-brand">
      <span class="mark" aria-hidden="true">MT</span>
      <b>Michael Tourism</b>
    </div>

    <div class="login-copy">
      <p class="login-kicker">Arusha, Tanzania</p>
      <h1>Plan the journey.<br>Keep the numbers straight.</h1>
      <p>One desk for inquiries, proposals, bookings, payments and travelers — from first contact to the road.</p>
      <div class="login-route" aria-hidden="true">
        <span></span><i></i><span></span><i></i><span></span><i></i><span></span>
      </div>
    </div>

    <div class="login-foot">Sales management system. Base currency <?= e($base_cur) ?>.</div>
  </section>

  <section class="login-panel">
    <div class="login-formwrap">
      <p class="login-welcome">Welcome back</p>
      <h2>Sign in to the sales desk</h2>
      <p class="login-sub">Use your work email and password.</p>

      <?php if ($err): ?><div class="fl bad"><?= e($err) ?></div><?php endif; ?>

      <form method="post" action="index.php?page=login">
        <?= csrf_field() ?>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" autofocus required value="admin@michaeltourism.com">
        </div>
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>
        <button class="btn primary wide" type="submit">Sign in</button>
      </form>

      <p class="hint">Demo login: <b>admin@michaeltourism.com</b> / <b>demo1234</b></p>
    </div>
  </section>
</div>
