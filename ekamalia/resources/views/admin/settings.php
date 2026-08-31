<h1 class="h4 fw-bold mb-4">Settings</h1>
<ul class="nav nav-pills flex-wrap mb-4 gap-1">
  <?php foreach (['general' => 'General', 'email' => 'Email / SMTP', 'otp' => 'OTP & Security', 'features' => 'Features', 'commerce' => 'Commerce', 'seo' => 'SEO & Social', 'maintenance' => 'Maintenance'] as $k => $v): ?>
  <li class="nav-item"><a class="nav-link <?= $tab === $k ? 'active' : '' ?>" href="?tab=<?= $k ?>"><?= $v ?></a></li>
  <?php endforeach; ?>
</ul>
<form method="post" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="<?= e($tab) ?>">
  <div class="admin-card"><div class="ac-body">
  <?php if ($tab === 'general'): ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Site Name</label><input class="form-control" name="site_name" value="<?= e(setting('site_name', '')) ?>"></div>
      <div class="col-md-6"><label class="form-label">Tagline</label><input class="form-control" name="site_tagline" value="<?= e(setting('site_tagline', '')) ?>"></div>
      <div class="col-md-4"><label class="form-label">Site Email</label><input type="email" class="form-control" name="site_email" value="<?= e(setting('site_email', '')) ?>"></div>
      <div class="col-md-4"><label class="form-label">Site Phone</label><input type="tel" class="form-control" name="site_phone" value="<?= e(setting('site_phone', '')) ?>"></div>
      <div class="col-md-4"><label class="form-label">WhatsApp Number</label><input type="tel" class="form-control" name="whatsapp_number" value="<?= e(setting('whatsapp_number', '')) ?>"></div>
      <div class="col-md-8"><label class="form-label">Site Address</label><input class="form-control" name="site_address" value="<?= e(setting('site_address', '')) ?>"></div>
      <div class="col-md-4"><label class="form-label">Footer Text</label><input class="form-control" name="footer_text" value="<?= e(setting('footer_text', '')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Primary Color</label><input class="form-control" name="primary_color" value="<?= e(setting('primary_color', '#0B7A3E')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Theme Color</label><input class="form-control" name="app_theme_color" value="<?= e(setting('app_theme_color', '')) ?>"></div>
      <div class="col-md-6"><label class="form-label">Custom Logo</label><input type="file" class="form-control" name="logo_file" accept="image/*">
        <?php if (setting('custom_logo')): ?><img src="<?= e(upload_url(setting('custom_logo'))) ?>" style="height:36px" class="mt-1" alt=""><?php endif; ?></div>
    </div>
  <?php elseif ($tab === 'email'): ?>
    <div class="row g-3">
      <div class="col-md-8"><label class="form-label">SMTP Host</label><input class="form-control" name="smtp_host" value="<?= e(setting('smtp_host', '')) ?>" placeholder="smtp.hostinger.com"></div>
      <div class="col-md-4"><label class="form-label">SMTP Port</label><input type="number" class="form-control" name="smtp_port" value="<?= e(setting('smtp_port', '465')) ?>"></div>
      <div class="col-md-4"><label class="form-label">SMTP Username</label><input class="form-control" name="smtp_user" value="<?= e(setting('smtp_user', '')) ?>"></div>
      <div class="col-md-4"><label class="form-label">SMTP Password</label><input type="password" class="form-control" name="smtp_pass" value="<?= setting('smtp_pass') ? '••••••••' : '' ?>" placeholder="<?= setting('smtp_pass') ? '(saved — type to change)' : '' ?>"></div>
      <div class="col-md-4"><label class="form-label">Encryption</label><select class="form-select" name="smtp_encryption">
        <?php foreach (['ssl', 'tls', 'none'] as $enc): ?><option value="<?= $enc ?>" <?= setting('smtp_encryption', 'ssl') === $enc ? 'selected' : '' ?>><?= strtoupper($enc) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label">From Name</label><input class="form-control" name="smtp_from_name" value="<?= e(setting('smtp_from_name', 'eKamalia')) ?>"></div>
      <div class="col-md-6"><label class="form-label">From Email</label><input type="email" class="form-control" name="smtp_from_email" value="<?= e(setting('smtp_from_email', '')) ?>"></div>
      <div class="col-12 d-flex align-items-end gap-2">
        <input type="email" class="form-control" name="_test_email" id="testEmail" style="max-width:280px" placeholder="test@example.com" value="<?= e(setting('site_email', '')) ?>">
        <button type="button" class="btn btn-outline-success rounded-pill" id="btnTestEmail">Send Test Email</button>
      </div>
    </div>
  <?php elseif ($tab === 'otp'): ?>
    <div class="row g-3">
      <div class="col-md-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="otp_enabled" value="1" id="otp1" <?= setting('otp_enabled') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="otp1">OTP system enabled</label></div>
        <div class="text-muted" style="font-size:.7rem">Email OTP verification via SMTP.</div></div>
      <div class="col-md-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="reg_otp_required" value="1" id="otp2" <?= setting('reg_otp_required') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="otp2">OTP required on registration</label></div></div>
      <div class="col-md-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="login_otp_required" value="1" id="otp3" <?= setting('login_otp_required') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="otp3">OTP required on login</label></div></div>
      <div class="col-md-4"><label class="form-label">OTP Expiry (minutes)</label><input type="number" class="form-control" name="otp_expiry_minutes" value="<?= e(setting('otp_expiry_minutes', '10')) ?>"></div>
    </div>
  <?php elseif ($tab === 'features'): ?>
    <div class="row g-3">
      <?php
      $toggles = [
        'ads_require_approval' => 'Classified ads need approval',
        'products_require_approval' => 'Products need approval',
        'shops_require_approval' => 'Shops need approval',
        'reviews_require_approval' => 'Reviews need approval',
        'comments_enabled' => 'Comments enabled',
        'chat_enabled' => 'Buyer-seller chat enabled',
        'pos_enabled' => 'POS system enabled',
        'cod_enabled' => 'Cash on Delivery enabled',
        'bank_transfer_enabled' => 'Bank transfer enabled',
      ];
      foreach ($toggles as $k => $lbl): ?>
      <div class="col-md-6 col-lg-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="<?= $k ?>" value="1" id="f-<?= $k ?>" <?= setting($k) === '1' ? 'checked' : '' ?>><label class="form-check-label small" for="f-<?= $k ?>"><?= $lbl ?></label></div></div>
      <?php endforeach; ?>
      <div class="col-6 col-md-3"><label class="form-label">Free Ads Limit</label><input type="number" class="form-control" name="free_ads_limit" value="<?= e(setting('free_ads_limit', '5')) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label">Items Per Page</label><input type="number" class="form-control" name="per_page" value="<?= e(setting('per_page', '24')) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label">Max Upload (MB)</label><input type="number" class="form-control" name="max_upload_mb" value="<?= e(setting('max_upload_mb', '5')) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label">Default Delivery Fee</label><input type="number" class="form-control" name="delivery_default_fee" value="<?= e(setting('delivery_default_fee', '150')) ?>"></div>
    </div>
  <?php elseif ($tab === 'commerce'): ?>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Currency Code</label><input class="form-control" name="currency" value="<?= e(setting('currency', 'PKR')) ?>"></div>
      <div class="col-md-4"><label class="form-label">Currency Symbol</label><input class="form-control" name="currency_symbol" value="<?= e(setting('currency_symbol', 'Rs')) ?>"></div>
      <div class="col-md-4"><label class="form-label">Commission %</label><input type="number" step="0.1" class="form-control" name="commission_percent" value="<?= e(setting('commission_percent', '0')) ?>"></div>
      <div class="col-12"><label class="form-label">Payment Instructions (checkout bank-transfer box)</label><textarea class="form-control" name="payment_instructions" rows="3"><?= e(setting('payment_instructions', '')) ?></textarea></div>
    </div>
  <?php elseif ($tab === 'seo'): ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">SEO Title</label><input class="form-control" name="seo_title" value="<?= e(setting('seo_title', '')) ?>"></div>
      <div class="col-md-6"><label class="form-label">SEO Keywords</label><input class="form-control" name="seo_keywords" value="<?= e(setting('seo_keywords', '')) ?>"></div>
      <div class="col-12"><label class="form-label">SEO Description</label><textarea class="form-control" name="seo_description" rows="2"><?= e(setting('seo_description', '')) ?></textarea></div>
      <div class="col-md-6"><label class="form-label">OG Image</label><input type="file" class="form-control" name="og_image" accept="image/*"></div>
      <div class="col-md-3"><label class="form-label">Facebook</label><input class="form-control" name="social_fb" value="<?= e(setting('social_fb', '')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Instagram</label><input class="form-control" name="social_ig" value="<?= e(setting('social_ig', '')) ?>"></div>
      <div class="col-md-3"><label class="form-label">X / Twitter</label><input class="form-control" name="social_x" value="<?= e(setting('social_x', '')) ?>"></div>
      <div class="col-md-3"><label class="form-label">YouTube</label><input class="form-control" name="social_yt" value="<?= e(setting('social_yt', '')) ?>"></div>
    </div>
  <?php elseif ($tab === 'maintenance'): ?>
    <div class="row g-3">
      <div class="col-md-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" id="mm" <?= setting('maintenance_mode') === '1' ? 'checked' : '' ?>><label class="form-check-label fw-bold" for="mm">Maintenance mode ON</label></div>
        <div class="text-muted" style="font-size:.7rem">Site shows the 503 page; /login and /admin stay open.</div></div>
      <div class="col-md-8"><label class="form-label">Maintenance Message</label><textarea class="form-control" name="maintenance_message" rows="3"><?= e(setting('maintenance_message', '')) ?></textarea></div>
    </div>
  <?php endif; ?>
  </div></div>
  <button class="btn btn-ek rounded-pill px-5 mt-3"><i class="fa-solid fa-floppy-disk me-1"></i>Save <?= e(ucfirst($tab)) ?> Settings</button>
</form>
<script>
document.getElementById('btnTestEmail') && document.getElementById('btnTestEmail').addEventListener('click', () => {
  const em = document.getElementById('testEmail').value;
  ekPost('<?= url('/admin/settings/test-email') ?>', { email: em }).then(r => toast(r.message, r.ok ? 'success' : 'danger'));
});
</script>
