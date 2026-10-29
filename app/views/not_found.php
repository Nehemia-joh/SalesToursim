<?php /** not_found.php - public 404 page. */ ?>
<div class="loginwrap" style="grid-template-columns:1fr;min-height:70vh;place-items:center">
  <section class="card form" style="max-width:520px;margin:auto">
    <p class="login-kicker">Michael Tourism</p>
    <h1 style="margin-top:8px">That page is off the map</h1>
    <p class="hint">The address may have changed, or the link may be incomplete.</p>
    <div class="formfoot">
      <a class="btn primary" href="<?= e(url('page=dashboard')) ?>">Go to the sales desk</a>
      <a class="btn ghost" href="<?= e(url('page=help')) ?>">Open help</a>
    </div>
  </section>
</div>
