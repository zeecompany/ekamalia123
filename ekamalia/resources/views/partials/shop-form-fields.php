<div class="row g-3">
  <div class="col-md-6"><label class="form-label fw-semibold">Shop Name *</label><input class="form-control" name="name" value="<?= old('name') ?>" required maxlength="160"></div>
  <div class="col-md-6"><label class="form-label fw-semibold">Owner Name *</label><input class="form-control" name="owner_name" value="<?= old('owner_name', auth()['name'] ?? '') ?>" required></div>
  <div class="col-md-6"><label class="form-label fw-semibold">Shop Email</label><input type="email" class="form-control" name="email" value="<?= old('email', auth()['email'] ?? '') ?>"></div>
  <div class="col-md-3"><label class="form-label fw-semibold">Phone *</label><input type="tel" class="form-control" name="phone" value="<?= old('phone', auth()['phone'] ?? '') ?>" placeholder="03XX-XXXXXXX" required></div>
  <div class="col-md-3"><label class="form-label fw-semibold">WhatsApp</label><input type="tel" class="form-control" name="whatsapp" value="<?= old('whatsapp') ?>"></div>
  <div class="col-md-6"><label class="form-label fw-semibold">CNIC <span class="text-muted fw-normal">(optional)</span></label><input class="form-control" name="cnic" value="<?= old('cnic') ?>" placeholder="36302-XXXXXXX-X"></div>
  <div class="col-md-6"><label class="form-label fw-semibold">Business Category</label>
    <select class="form-select" name="category_id"><option value="">Select</option>
    <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= old('category_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label fw-semibold">City *</label>
    <select class="form-select" name="city_id" id="citySelect" required><option value="">Select</option>
    <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= old('city_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label fw-semibold">Area / Market</label><input class="form-control" name="area" id="areaInput" list="areaList" value="<?= old('area') ?>" placeholder="e.g. Karkhana Bazar"></div>
  <div class="col-12"><label class="form-label fw-semibold">Shop Address *</label><input class="form-control" name="address" value="<?= old('address') ?>" required></div>
  <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" name="description" rows="3" placeholder="What do you sell? What makes your shop special?"><?= old('description') ?></textarea></div>
  <div class="col-md-6"><label class="form-label fw-semibold">Business Hours</label><input class="form-control" name="business_hours" value="<?= old('business_hours') ?>" placeholder="10:00 AM - 10:00 PM"></div>
  <div class="col-md-3"><label class="form-label fw-semibold">Delivery?</label>
    <select class="form-select" name="delivery_available"><option value="1">Yes</option><option value="0">No</option></select></div>
  <div class="col-md-3"><label class="form-label fw-semibold">Delivery Fee (Rs)</label><input type="number" class="form-control" name="delivery_fee" value="<?= old('delivery_fee', '150') ?>" min="0"></div>
  <div class="col-md-6"><label class="form-label fw-semibold">Shop Logo</label><input type="file" class="form-control" name="logo" accept="image/*"></div>
  <div class="col-md-6"><label class="form-label fw-semibold">Cover Image</label><input type="file" class="form-control" name="cover" accept="image/*"></div>
  <div class="col-12"><label class="form-label fw-semibold">Bank / Payment Info <span class="text-muted fw-normal">(shown to buyers for manual payments)</span></label>
    <textarea class="form-control" name="bank_info" rows="2" placeholder="Bank name, account title, number/IBAN, JazzCash/Easypaisa…"><?= old('bank_info') ?></textarea></div>
</div>
<script src="<?= asset('js/dashboard.js') ?>"></script>
