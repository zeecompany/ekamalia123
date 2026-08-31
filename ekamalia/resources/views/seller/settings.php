<h1 class="h4 fw-bold mb-4">Shop Settings</h1>
<div class="ek-card p-4">
  <form method="post" action="<?= url('/seller/settings') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-8"><label class="form-label fw-semibold">Shop Name</label><input class="form-control" name="name" value="<?= e($shop['name']) ?>" required></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Owner Name</label><input class="form-control" name="owner_name" value="<?= e($shop['owner_name'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" name="email" value="<?= e($shop['email'] ?? '') ?>"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Phone</label><input type="tel" class="form-control" name="phone" value="<?= e($shop['phone'] ?? '') ?>"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">WhatsApp</label><input type="tel" class="form-control" name="whatsapp" value="<?= e($shop['whatsapp'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Category</label>
        <select class="form-select" name="category_id"><option value="">—</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $shop['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">City</label>
        <select class="form-select" name="city_id" id="citySelect"><option value="">—</option>
        <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= $shop['city_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Area</label><input class="form-control" name="area" id="areaInput" value="<?= e($shop['area'] ?? '') ?>"></div>
      <div class="col-12"><label class="form-label fw-semibold">Address</label><input class="form-control" name="address" value="<?= e($shop['address'] ?? '') ?>"></div>
      <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" name="description" rows="3"><?= e($shop['description'] ?? '') ?></textarea></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Business Hours</label><input class="form-control" name="business_hours" value="<?= e($shop['business_hours'] ?? '') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Delivery</label>
        <select class="form-select" name="delivery_available"><option value="1" <?= $shop['delivery_available'] ? 'selected' : '' ?>>Available</option><option value="0" <?= !$shop['delivery_available'] ? 'selected' : '' ?>>Not available</option></select></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Delivery Fee (Rs)</label><input type="number" class="form-control" name="delivery_fee" value="<?= (int)$shop['delivery_fee'] ?>"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Shop Logo</label><input type="file" class="form-control" name="logo" accept="image/*">
        <?php if ($shop['logo']): ?><img src="<?= e(upload_url($shop['logo'])) ?>" style="width:56px;height:56px;border-radius:10px;object-fit:cover" class="mt-2" alt=""><?php endif; ?></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Cover Image</label><input type="file" class="form-control" name="cover" accept="image/*">
        <?php if ($shop['cover']): ?><img src="<?= e(upload_url($shop['cover'])) ?>" style="width:90px;height:50px;border-radius:8px;object-fit:cover" class="mt-2" alt=""><?php endif; ?></div>
      <div class="col-12"><label class="form-label fw-semibold">Bank / Payment Info</label><textarea class="form-control" name="bank_info" rows="2"><?= e($shop['bank_info'] ?? '') ?></textarea></div>
    </div>
    <button class="btn btn-ek rounded-pill px-5 mt-3">Save Settings</button>
  </form>
</div>
