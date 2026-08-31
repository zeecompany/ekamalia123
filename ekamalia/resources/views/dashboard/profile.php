<h1 class="h4 fw-bold mb-4"><?= e(t('nav.profile')) ?></h1>
<div class="ek-card p-4">
  <form method="post" action="<?= url('/dashboard/profile') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="d-flex align-items-center gap-3 mb-4">
      <img src="<?= $me['avatar'] ? upload_url($me['avatar']) : asset('img/avatar-default.svg') ?>" class="rounded-circle" style="width:84px;height:84px;object-fit:cover" alt="">
      <div><label class="form-label small fw-semibold">Profile photo</label>
      <input type="file" class="form-control form-control-sm" name="avatar" accept="image/*"></div>
    </div>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label fw-semibold">Full name</label><input class="form-control" name="name" value="<?= e($me['name']) ?>" required></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Mobile number</label><input type="tel" class="form-control" name="phone" value="<?= e($me['phone'] ?? '') ?>" placeholder="03XX-XXXXXXX"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Date of birth</label><input type="date" class="form-control" name="dob" value="<?= e($me['dob'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">City</label>
        <select class="form-select" name="city_id"><option value="">Select</option>
        <?php foreach ($cities as $c): ?><option value="<?= $c['id'] ?>" <?= $me['city_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="profile_public" value="1" <?= $me['profile_public'] ? 'checked' : '' ?>>
        <label class="form-check-label small">Show my name publicly on my ads &amp; comments</label></div></div>
    </div>
    <button class="btn btn-ek rounded-pill px-4 mt-3">Save Profile</button>
  </form>
</div>
