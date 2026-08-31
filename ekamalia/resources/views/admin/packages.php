<div class="row g-4">
  <div class="col-lg-6">
    <div class="admin-card mb-4"><div class="ac-head"><h6>Seller Packages</h6></div><div class="ac-body p-0">
      <?php foreach ($packages as $p): ?>
      <form class="p-3 border-bottom" method="post" action="<?= url('/admin/packages') ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
        <div class="row g-2">
          <div class="col-6"><label class="form-label">Name</label><input class="form-control form-control-sm" name="name" value="<?= e($p['name']) ?>"></div>
          <div class="col-6"><label class="form-label">Price (Rs)</label><input type="number" class="form-control form-control-sm" name="price" value="<?= (int)$p['price'] ?>"></div>
          <div class="col-4"><label class="form-label">Days</label><input type="number" class="form-control form-control-sm" name="duration_days" value="<?= (int)$p['duration_days'] ?>"></div>
          <div class="col-4"><label class="form-label">Ad Limit</label><input type="number" class="form-control form-control-sm" name="ad_limit" value="<?= (int)$p['ad_limit'] ?>"></div>
          <div class="col-4"><label class="form-label">Product Limit</label><input type="number" class="form-control form-control-sm" name="product_limit" value="<?= (int)$p['product_limit'] ?>"></div>
          <div class="col-6"><label class="form-label">Featured Ads</label><input type="number" class="form-control form-control-sm" name="featured_ads" value="<?= (int)$p['featured_ads'] ?>"></div>
          <div class="col-6 d-flex align-items-end gap-3">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="can_shop" value="1" <?= $p['can_shop'] ? 'checked' : '' ?>><label class="form-check-label small">Shop</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="can_coupons" value="1" <?= $p['can_coupons'] ? 'checked' : '' ?>><label class="form-check-label small">Coupons</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="can_pos" value="1" <?= $p['can_pos'] ? 'checked' : '' ?>><label class="form-check-label small">POS</label></div>
          </div>
        </div>
        <button class="btn btn-ek-sm mt-2">Save</button>
        <a class="btn btn-sm btn-outline-danger ms-1" href="<?= url('/admin/packages/' . $p['id'] . '/delete') ?>" onclick="return confirm('Delete package?')">Delete</a>
      </form>
      <?php endforeach; ?>
    </div></div>
    <div class="admin-card"><div class="ac-head"><h6>New Seller Package</h6></div><div class="ac-body">
      <form method="post" action="<?= url('/admin/packages') ?>">
        <?= csrf_field() ?>
        <div class="row g-2">
          <div class="col-6"><input class="form-control form-control-sm" name="name" placeholder="Package name" required></div>
          <div class="col-6"><input type="number" class="form-control form-control-sm" name="price" placeholder="Price" value="0"></div>
          <div class="col-4"><input type="number" class="form-control form-control-sm" name="duration_days" placeholder="Days" value="30"></div>
          <div class="col-4"><input type="number" class="form-control form-control-sm" name="ad_limit" placeholder="Ad limit" value="10"></div>
          <div class="col-4"><input type="number" class="form-control form-control-sm" name="product_limit" placeholder="Product limit" value="50"></div>
        </div>
        <button class="btn btn-ek w-100 rounded-pill mt-2">Create Package</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="admin-card"><div class="ac-head"><h6>POS Packages</h6></div><div class="ac-body p-0">
      <?php foreach ($posPackages as $p): ?>
      <form class="p-3 border-bottom row g-2 align-items-end" method="post" action="<?= url('/admin/pos-packages') ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
        <div class="col-4"><label class="form-label">Name</label><input class="form-control form-control-sm" name="name" value="<?= e($p['name']) ?>"></div>
        <div class="col-3"><label class="form-label">Price</label><input type="number" class="form-control form-control-sm" name="price" value="<?= (int)$p['price'] ?>"></div>
        <div class="col-2"><label class="form-label">Days</label><input type="number" class="form-control form-control-sm" name="duration_days" value="<?= (int)$p['duration_days'] ?>"></div>
        <div class="col-3"><label class="form-label">Max Users</label><input type="number" class="form-control form-control-sm" name="max_users" value="<?= (int)$p['max_users'] ?>"></div>
        <div class="col-4"><label class="form-label">Max Products</label><input type="number" class="form-control form-control-sm" name="max_products" value="<?= (int)$p['max_products'] ?>"></div>
        <div class="col-4"><label class="form-label">Status</label><select class="form-select form-select-sm" name="status"><option value="active" <?= $p['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $p['status'] !== 'active' ? 'selected' : '' ?>>Inactive</option></select></div>
        <div class="col-4"><button class="btn btn-ek-sm w-100">Save</button></div>
      </form>
      <?php endforeach; ?>
      <form class="p-3 row g-2 align-items-end" method="post" action="<?= url('/admin/pos-packages') ?>">
        <?= csrf_field() ?>
        <div class="col-4"><input class="form-control form-control-sm" name="name" placeholder="New POS package" required></div>
        <div class="col-3"><input type="number" class="form-control form-control-sm" name="price" placeholder="Price" value="0"></div>
        <div class="col-2"><input type="number" class="form-control form-control-sm" name="duration_days" value="365"></div>
        <div class="col-3"><button class="btn btn-ek-sm w-100">Add</button></div>
      </form>
    </div></div>
  </div>
</div>
