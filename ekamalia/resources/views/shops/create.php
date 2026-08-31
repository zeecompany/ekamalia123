<div class="container py-4" style="max-width:820px">
  <?php if ($shop): ?>
    <?php if ($shop['status'] === 'pending'): ?>
      <div class="ek-card p-4 text-center">
        <div style="font-size:56px" class="text-warning mb-2"><i class="fa-solid fa-hourglass-half"></i></div>
        <h1 class="h4 fw-bold"><?= e($shop['name']) ?> is pending approval</h1>
        <p class="text-muted">Our team is reviewing your shop. You'll get a notification as soon as it's approved (usually within 24 hours).<br>JazakAllah for your patience!</p>
        <a class="btn btn-outline-success rounded-pill px-4" href="<?= url('/dashboard') ?>">Back to Dashboard</a>
      </div>
    <?php elseif ($shop['status'] === 'rejected'): ?>
      <div class="ek-card p-4">
        <div class="text-center mb-3"><i class="fa-solid fa-circle-xmark text-danger" style="font-size:44px"></i>
          <h1 class="h5 fw-bold mt-2">Shop not approved</h1>
          <p class="text-muted small"><?= e($shop['status_note'] ?: 'Please review our seller policy and submit again.') ?></p></div>
        <form method="post" action="<?= url('/shops/create') ?>"><?= csrf_field() ?>
          <p class="fw-semibold small">Submit again with corrections:</p>
          <?php include views_path('partials/shop-form-fields'); ?>
        </form>
      </div>
    <?php else: ?>
      <div class="ek-card p-4 text-center">
        <div style="font-size:56px" class="text-success mb-2"><i class="fa-solid fa-circle-check"></i></div>
        <h1 class="h4 fw-bold">You already have a shop 🎉</h1>
        <p class="text-muted"><?= e($shop['name']) ?> is live!</p>
        <a class="btn btn-ek rounded-pill px-4" href="<?= url('/seller') ?>">Open Seller Dashboard</a>
      </div>
    <?php endif; ?>
  <?php else: ?>
  <div class="text-center mb-4">
    <h1 class="h3 fw-bold"><i class="fa-solid fa-shop me-2 text-success"></i>Create Your Free Shop</h1>
    <p class="text-muted">Sell to customers across Kamalia &amp; nearby cities. Free registration — approval within 24 hours.</p>
  </div>
  <div class="ek-card p-4">
    <form method="post" action="<?= url('/shops/create') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <?php include views_path('partials/shop-form-fields'); ?>
      <button class="btn btn-ek btn-lg w-100 rounded-pill mt-3">Submit Shop for Approval <i class="fa-solid fa-paper-plane ms-1"></i></button>
    </form>
  </div>
  <?php endif; ?>
</div>
