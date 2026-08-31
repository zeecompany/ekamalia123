<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h5 fw-bold mb-0"><?= $product ? 'Edit Product' : 'Add New Product' ?></h1>
  <a class="btn btn-outline-secondary btn-sm rounded-pill" href="<?= url('/seller/products') ?>">Back</a>
</div>
<div class="ek-card p-4">
  <form method="post" action="<?= url('/seller/products/save') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($product['id'] ?? 0) ?>">
    <div class="row g-3">
      <div class="col-md-8"><label class="form-label fw-semibold">Product Name *</label><input class="form-control" name="name" value="<?= old('name', $product['name'] ?? '') ?>" required></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Status</label>
        <select class="form-select" name="status">
          <option value="published" <?= old('status', $product['status'] ?? 'published') === 'published' ? 'selected' : '' ?>><?= setting('products_require_approval') === '1' ? 'Publish (needs approval)' : 'Published' ?></option>
          <option value="draft" <?= old('status', $product['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="hidden" <?= old('status', $product['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Category *</label>
        <select class="form-select" name="category_id" id="categorySelect" required><option value="">Select</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= old('category_id', $product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Subcategory</label>
        <select class="form-select" name="subcategory_id" id="subcategorySelect" data-selected="<?= e((string)($product['subcategory_id'] ?? '')) ?>"><option value="">— None —</option></select></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Brand</label>
        <select class="form-select" name="brand_id"><option value="">— None —</option>
        <?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>" <?= old('brand_id', $product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Price (PKR) *</label><input type="number" class="form-control" name="price" value="<?= old('price', $product['price'] ?? '') ?>" required min="1"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Sale Price</label><input type="number" class="form-control" name="sale_price" value="<?= old('sale_price', $product['sale_price'] ?? '') ?>" min="0"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Cost Price <span class="text-muted fw-normal">(private)</span></label><input type="number" class="form-control" name="cost_price" value="<?= old('cost_price', $product['cost_price'] ?? '') ?>" min="0"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Stock *</label><input type="number" class="form-control" name="stock" value="<?= old('stock', $product['stock'] ?? 0) ?>" min="0" required></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Min Stock (alert)</label><input type="number" class="form-control" name="min_stock" value="<?= old('min_stock', $product['min_stock'] ?? 3) ?>" min="0"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Unit</label><input class="form-control" name="unit" value="<?= old('unit', $product['unit'] ?? 'pcs') ?>"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">SKU</label><input class="form-control" name="sku" value="<?= old('sku', $product['sku'] ?? '') ?>"></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Barcode</label><input class="form-control" name="barcode" value="<?= old('barcode', $product['barcode'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Shipping Fee (PKR)</label><input type="number" class="form-control" name="shipping_fee" value="<?= old('shipping_fee', $product['shipping_fee'] ?? 150) ?>" min="0"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Delivery Time</label><input class="form-control" name="delivery_time" value="<?= old('delivery_time', $product['delivery_time'] ?? '1-2 days') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Warranty</label><input class="form-control" name="warranty" value="<?= old('warranty', $product['warranty'] ?? '') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Return Policy</label><input class="form-control" name="return_policy" value="<?= old('return_policy', $product['return_policy'] ?? '7 days check warranty') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Tax %</label><input type="number" step="0.01" class="form-control" name="tax_percent" value="<?= old('tax_percent', $product['tax_percent'] ?? 0) ?>" min="0"></div>
      <div class="col-12"><label class="form-label fw-semibold">Short Description</label><input class="form-control" name="short_description" value="<?= old('short_description', $product['short_description'] ?? '') ?>" maxlength="300"></div>
      <div class="col-12"><label class="form-label fw-semibold">Full Description</label><textarea class="form-control" name="description" rows="4"><?= old('description', $product['description'] ?? '') ?></textarea></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Product Images (up to 6)</label><input type="file" class="form-control" name="images[]" accept="image/*" multiple>
        <?php if ($product): $imgs = qa('SELECT image FROM product_images WHERE product_id=? ORDER BY sort_order', [$product['id']]); if ($imgs): ?>
        <div class="d-flex gap-1 mt-2 flex-wrap"><?php foreach ($imgs as $im): ?><img src="<?= e(upload_url($im['image'])) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px" alt=""><?php endforeach; ?></div>
        <div class="text-muted" style="font-size:.68rem">Upload new images to replace.</div><?php endif; endif; ?></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Video URL</label><input type="url" class="form-control" name="video_url" value="<?= old('video_url', $product['video_url'] ?? '') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Weight</label><input class="form-control" name="weight" value="<?= old('weight', $product['weight'] ?? '') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Dimensions</label><input class="form-control" name="dimensions" value="<?= old('dimensions', $product['dimensions'] ?? '') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Tags</label><input class="form-control" name="tags" value="<?= old('tags', $product['tags'] ?? '') ?>"></div>
    </div>
    <button class="btn btn-ek btn-lg rounded-pill w-100 mt-3"><?= $product ? 'Save Changes' : 'Publish Product' ?></button>
  </form>
</div>
