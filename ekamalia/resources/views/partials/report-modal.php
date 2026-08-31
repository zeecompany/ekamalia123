<!-- Report modal (included on pages that set $reportModal=true) -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content rounded-4 border-0" method="post" action="<?= url('/report') ?>" data-report-form>
      <?= csrf_field() ?>
      <input type="hidden" name="item_type" id="repType" value="">
      <input type="hidden" name="item_id" id="repId" value="">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-flag text-danger me-2"></i>Report this listing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label small fw-semibold">Reason</label>
        <select class="form-select mb-3" name="reason" required>
          <option value="spam">Spam</option><option value="fraud">Fraud / Scam</option>
          <option value="fake">Fake product</option><option value="illegal">Illegal item</option>
          <option value="harassment">Harassment</option><option value="duplicate">Duplicate</option>
          <option value="wrong_category">Wrong category</option><option value="other">Other</option>
        </select>
        <label class="form-label small fw-semibold">Details (optional)</label>
        <textarea class="form-control" name="details" rows="3" maxlength="500" placeholder="Tell us what's wrong…"></textarea>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger rounded-pill px-4">Submit Report</button>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('click', e => {
  const b = e.target.closest('[data-report]');
  if (!b) return;
  document.getElementById('repType').value = b.dataset.report;
  document.getElementById('repId').value = b.dataset.id;
  new bootstrap.Modal(document.getElementById('reportModal')).show();
});
</script>
