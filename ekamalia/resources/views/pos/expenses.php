<div class="row g-4">
  <div class="col-lg-4">
    <div class="ek-card p-4">
      <h5 class="fw-bold mb-3">Add Expense</h5>
      <form method="post" action="<?= url('/pos/expenses/save') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small fw-semibold">Category</label>
          <select class="form-select" name="category">
            <?php foreach (['rent' => 'Rent', 'salary' => 'Salary', 'utilities' => 'Utilities', 'transport' => 'Transport', 'purchase' => 'Stock Purchase', 'marketing' => 'Marketing', 'other' => 'Other'] as $k => $v): ?>
            <option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Amount (Rs) *</label><input type="number" class="form-control" name="amount" required min="1"></div>
        <div class="mb-2"><label class="form-label small fw-semibold">Note</label><input class="form-control" name="note"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Date</label><input type="date" class="form-control" name="expense_date" value="<?= date('Y-m-d') ?>"></div>
        <button class="btn btn-ek w-100 rounded-pill">Save Expense</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <h5 class="fw-bold mb-3">Recent Expenses</h5>
    <?php foreach ($expenses as $x): ?>
    <div class="ek-card p-3 mb-2 d-flex justify-content-between align-items-center">
      <div><span class="badge text-bg-light border text-uppercase"><?= e($x['category']) ?></span>
        <span class="ms-1 small"><?= e($x['note'] ?? '') ?></span>
        <div class="text-muted" style="font-size:.7rem"><?= e(fmt_date($x['expense_date'], 'd M Y')) ?></div></div>
      <div class="d-flex align-items-center gap-2">
        <b class="text-danger"><?= money($x['amount']) ?></b>
        <form method="post" action="<?= url('/pos/expenses/' . $x['id'] . '/delete') ?>" onsubmit="return confirm('Delete expense?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$expenses): ?><p class="text-muted small">No expenses recorded.</p><?php endif; ?>
  </div>
</div>
