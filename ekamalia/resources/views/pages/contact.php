<div class="container py-4" style="max-width:900px">
  <div class="row g-4">
    <div class="col-md-7">
      <div class="ek-card p-4">
        <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-headset me-2 text-success"></i><?= e(t('nav.contact')) ?></h1>
        <p class="text-muted small mb-4">Suggestion, complaint ya partnership? Write to us — hum 24 hours me reply karte hain.</p>
        <form method="post" action="<?= url('/contact') ?>">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Name *</label><input class="form-control" name="name" value="<?= old('name') ?>" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Email *</label><input type="email" class="form-control" name="email" value="<?= old('email') ?>" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="tel" class="form-control" name="phone" value="<?= old('phone') ?>"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Subject</label>
              <select class="form-select" name="subject">
                <option>General</option><option>Order Issue</option><option>Seller Support</option><option>Business Directory</option><option>Bijli Updates</option><option>Partnership</option><option>Report Abuse</option>
              </select></div>
            <div class="col-12"><label class="form-label fw-semibold">Message *</label><textarea class="form-control" name="message" rows="5" required><?= old('message') ?></textarea></div>
          </div>
          <button class="btn btn-ek btn-lg w-100 rounded-pill mt-3">Send Message <i class="fa-solid fa-paper-plane ms-1"></i></button>
        </form>
      </div>
    </div>
    <div class="col-md-5">
      <div class="ek-card p-4 mb-3">
        <h5 class="fw-bold mb-3">Contact Info</h5>
        <div class="d-flex flex-column gap-3 small">
          <div><i class="fa-solid fa-envelope text-success me-2"></i><?= e(setting('site_email', 'hello@ekamalia.com')) ?></div>
          <div><i class="fa-solid fa-phone text-success me-2"></i><?= e(setting('site_phone', '0300-0000000')) ?></div>
          <div><i class="fa-solid fa-location-dot text-success me-2"></i><?= e(setting('site_address')) ?></div>
          <?php if (setting('whatsapp_number')): ?><a class="text-success" target="_blank" rel="noopener" href="<?= e(wa_link(setting('whatsapp_number'), 'Hello eKamalia!')) ?>"><i class="fa-brands fa-whatsapp me-2"></i>Chat on WhatsApp</a><?php endif; ?>
        </div>
      </div>
      <div class="ek-card p-4" style="background:linear-gradient(135deg,#0B7A3E,#14915a);color:#fff;border:0">
        <h5 class="fw-bold">⚡ Bijli Updates</h5>
        <p class="small mb-3" style="opacity:.9">Check feeder-wise electricity status of Kamalia anytime.</p>
        <a class="btn btn-warning btn-sm rounded-pill fw-bold" href="<?= url('/bijli') ?>">Open Bijli Updates</a>
      </div>
    </div>
  </div>
</div>
