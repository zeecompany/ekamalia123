<?php $editing = !empty($ad); $me = auth(); ?>
<div class="container py-4" style="max-width:860px">
  <div class="ek-card p-4">
    <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-circle-plus text-success me-2"></i><?= $editing ? 'Edit Ad' : 'Post a Free Ad' ?></h1>
    <p class="text-muted small mb-4">Your ad will be visible across Kamalia &amp; nearby cities. <?= $editing ? '' : 'You have posted ' . (int)$myCount . '/' . (int)$limit . ' active ads.' ?></p>
    <form method="post" action="<?= $editing ? url('/ads/' . $ad['id'] . '/edit') : url('/ads/create') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label fw-semibold">Ad Title * <span class="text-muted fw-normal">(e.g. iPhone 13 Pro Max 256GB PTA Approved)</span></label>
        <input type="text" class="form-control form-control-lg" name="title" value="<?= old('title', $ad['title'] ?? '') ?>" required maxlength="190">
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Category *</label>
          <select class="form-select" name="category_id" id="categorySelect" required>
            <option value="">Select category</option>
            <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= old('category_id', $ad['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Subcategory</label>
          <select class="form-select" name="subcategory_id" id="subcategorySelect" data-selected="<?= e((string)($ad['subcategory_id'] ?? '')) ?>">
            <option value="">— None —</option>
          </select>
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Price (PKR) *</label>
          <input type="number" class="form-control" name="price" value="<?= old('price', $ad['price'] ?? '') ?>" min="0" step="1" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Condition</label>
          <select class="form-select" name="condition">
            <?php foreach (['used' => 'Used', 'new' => 'New', 'refurbished' => 'Refurbished'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= old('condition', $ad['condition'] ?? 'used') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Seller Type</label>
          <select class="form-select" name="seller_type">
            <?php foreach (['individual' => 'Individual', 'dealer' => 'Dealer', 'business' => 'Business'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= old('seller_type', $ad['seller_type'] ?? 'individual') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Description *</label>
        <textarea class="form-control" name="description" rows="6" required minlength="20" maxlength="8000" placeholder="Describe your item honestly — condition, accessories, reason for selling…"><?= old('description', $ad['description'] ?? '') ?></textarea>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">City *</label>
          <select class="form-select" name="city_id" id="citySelect" required>
            <option value="">Select city</option>
            <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= old('city_id', $ad['city_id'] ?? ($me['city_id'] ?? '')) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Area</label>
          <input type="text" class="form-control" name="area" id="areaInput" list="areaList" value="<?= old('area', $ad['area'] ?? '') ?>" placeholder="e.g. Main Bazaar, Chak 341/GB">
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Phone (show on ad)</label>
          <input type="tel" class="form-control" name="phone" value="<?= old('phone', $ad['phone'] ?? ($me['phone'] ?? '')) ?>" placeholder="03XX-XXXXXXX">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">WhatsApp</label>
          <input type="tel" class="form-control" name="whatsapp" value="<?= old('whatsapp', $ad['whatsapp'] ?? ($me['phone'] ?? '')) ?>" placeholder="03XX-XXXXXXX">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Photos <span class="text-muted fw-normal">(up to 8 — JPG/PNG/WEBP)</span></label>
        <input type="file" class="form-control" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
        <?php if ($editing && !empty($images)): ?>
          <div class="d-flex gap-2 mt-2 flex-wrap">
            <?php foreach ($images as $im): ?><img src="<?= e(upload_url($im['image'])) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:8px" alt=""><?php endforeach; ?>
            <div class="text-muted small align-self-center">Upload new photos to replace these.</div>
          </div>
        <?php endif; ?>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Video URL (YouTube)</label>
          <input type="url" class="form-control" name="video_url" value="<?= old('video_url', $ad['video_url'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Tags <span class="text-muted fw-normal">(comma separated)</span></label>
          <input type="text" class="form-control" name="tags" value="<?= old('tags', $ad['tags'] ?? '') ?>" placeholder="iphone, apple, pta approved">
        </div>
      </div>
      <div class="d-flex gap-3 flex-wrap mb-4">
        <div class="form-check"><input class="form-check-input" type="checkbox" name="negotiable" value="1" <?= old('negotiable', $ad['negotiable'] ?? '') ? 'checked' : '' ?>><label class="form-check-label small">Price is negotiable</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="delivery" value="1" <?= old('delivery', $ad['delivery_available'] ?? '') ? 'checked' : '' ?>><label class="form-check-label small">Delivery available</label></div>
      </div>
      <button class="btn btn-ek btn-lg w-100 rounded-pill"><?= $editing ? 'Save Changes' : 'Post My Ad — Free' ?> <i class="fa-solid fa-rocket ms-1"></i></button>
    </form>
  </div>
</div>
<script src="<?= asset('js/dashboard.js') ?>"></script>
