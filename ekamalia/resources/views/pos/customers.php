<div class="row g-4">
  <div class="col-lg-4">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Add Customer</h5>
      <form method="post" action="<?= url('/pos/customers/save') ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="">
        <div class="mb-2"><label class="form-label small fw-semibold">Name *</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Phone</label><input type="tel" class="form-control" name="phone"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Address</label><input class="form-control" name="address"></div>
        <button class="btn btn-ek w-100 rounded-pill">Save Customer</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <form class="d-flex gap-2 mb-3" method="get"><input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Search customers…"><button class="btn btn-ek rounded-pill px-4">Search</button></form>
    <?php if ($customers): ?>
    <div class="table-responsive ek-card table-responsive-stack">
    <table class="table align-middle">
      <thead><tr><th>Name</th><th>Phone</th><th>Balance</th><th>Purchases</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($customers as $c): ?>
      <tr>
        <td data-label="Name"><b class="small"><?= e($c['name']) ?></b></td>
        <td data-label="Phone" class="small"><?= e(pk_phone($c['phone'] ?? '')) ?></td>
        <td data-label="Balance"><b class="<?= (float)$c['balance'] > 0 ? 'text-danger' : 'text-success' ?>"><?= money($c['balance']) ?></b></td>
        <td data-label="Purchases" class="small"><?= money($c['total_purchases']) ?></td>
        <td><button class="btn btn-sm btn-outline-secondary" data-edit-cust='<?= json_encode(["id" => $c["id"], "name" => $c["name"], "phone" => $c["phone"], "address" => $c["address"]]) ?>'><i class="fa-solid fa-pen"></i></button></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><div class="ek-empty"><i class="fa-solid fa-users"></i><h5>No customers yet</h5></div><?php endif; ?>
  </div>
</div>
<script>
document.addEventListener('click', e => {
  const b = e.target.closest('[data-edit-cust]');
  if (!b) return;
  const d = JSON.parse(b.dataset.editCust);
  const f = b.closest('.row').querySelector('form');
  f.querySelector('[name=id]').value = d.id;
  f.querySelector('[name=name]').value = d.name || '';
  f.querySelector('[name=phone]').value = d.phone || '';
  f.querySelector('[name=address]').value = d.address || '';
  f.querySelector('button[type=submit], button:not([type])').textContent = 'Update Customer';
  window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
