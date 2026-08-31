<div class="container py-4" style="max-width:760px">
  <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-map-location-dot text-success me-2"></i>List Your Business — Free</h1>
  <p class="text-muted small mb-4">Put your Kamalia business on the map. Listings are reviewed quickly.</p>
  <div class="ek-card p-4 mb-4">
    <form method="post" action="<?= url('/businesses/create') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label fw-semibold">Business Name *</label><input class="form-control" name="name" value="<?= old('name') ?>" required></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Category *</label>
          <select class="form-select" name="category_id" required><option value="">Select</option>
          <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= old('category_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Phone *</label><input type="tel" class="form-control" name="phone" value="<?= old('phone') ?>" required></div>
        <div class="col-md-6"><label class="form-label fw-semibold">WhatsApp</label><input type="tel" class="form-control" name="whatsapp" value="<?= old('whatsapp') ?>"></div>
        <div class="col-md-6"><label class="form-label fw-semibold">City *</label>
          <select class="form-select" name="city_id" id="citySelect" required><option value="">Select</option>
          <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= old('city_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Area</label><input class="form-control" name="area" id="areaInput" list="areaList" value="<?= old('area') ?>"></div>
        <div class="col-12"><label class="form-label fw-semibold">Address *</label><input class="form-control" name="address" value="<?= old('address') ?>" required></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Opening Hours</label><input class="form-control" name="opening_hours" value="<?= old('opening_hours') ?>" placeholder="9:00 AM - 11:00 PM"></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Website</label><input type="url" class="form-control" name="website" value="<?= old('website') ?>"></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Facebook</label><input type="url" class="form-control" name="facebook" value="<?= old('facebook') ?>"></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Instagram</label><input type="url" class="form-control" name="instagram" value="<?= old('instagram') ?>"></div>
        <div class="col-12"><label class="form-label fw-semibold">Description *</label><textarea class="form-control" name="description" rows="3" required><?= old('description') ?></textarea></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Logo</label><input type="file" class="form-control" name="logo" accept="image/*"></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Cover Photo</label><input type="file" class="form-control" name="cover" accept="image/*"></div>
      </div>
      <button class="btn btn-ek btn-lg w-100 rounded-pill mt-3">Submit Business <i class="fa-solid fa-paper-plane ms-1"></i></button>
    </form>
  </div>
  <?php if ($mine): ?>
  <h5 class="fw-bold mb-3">My Business Listings</h5>
  <div class="table-responsive ek-card p-3 table-responsive-stack">
    <table class="table align-middle">
      <thead><tr><th>Business</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($mine as $m): ?>
        <tr><td data-label="Business"><b><?= e($m['name']) ?></b></td><td data-label="Status"><?= status_badge($m['status']) ?></td>
        <td><a class="btn btn-sm btn-outline-success rounded-pill" href="<?= url('/business/' . $m['slug']) ?>">View</a></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<script src="<?= asset('js/dashboard.js') ?>"></script>
