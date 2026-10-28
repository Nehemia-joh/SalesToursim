<?php /** proposal_not_found.php - public proposal error page. */ ?>
<div class="proposal-page">
  <div class="proposal-empty">
    <div class="proposal-brand">
      <span class="mark">MT</span>
      <div>
        <b><?= e(setting('company_name', 'Michael Tourism')) ?></b>
        <span><?= e(setting('company_tagline', 'Safari sales & travel proposals')) ?></span>
      </div>
    </div>
    <h1>This proposal is not available</h1>
    <p>The link may have expired, been disabled, or been replaced with a newer version.</p>
    <p class="hint">Please contact your travel consultant for an updated proposal.</p>
  </div>
</div>
