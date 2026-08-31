<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h4 fw-bold mb-0">Database Backups</h1>
  <form method="post" action="<?= url('/admin/backups') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="create">
    <button class="btn btn-ek rounded-pill"><i class="fa-solid fa-database me-1"></i>Create Backup Now</button></form>
</div>
<?php if ($files): ?>
<div class="table-responsive admin-card table-responsive-stack">
<table class="table align-middle mb-0">
  <thead><tr><th>File</th><th>Size</th><th>Created</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($files as $f): ?>
  <tr>
    <td data-label="File"><code class="small"><?= e($f['name']) ?></code></td>
    <td data-label="Size" class="small"><?= e($f['size']) ?></td>
    <td data-label="Created" class="small"><?= e($f['date']) ?></td>
    <td data-label="Actions"><div class="chip-actions d-flex">
      <a class="btn btn-sm btn-outline-primary" href="<?= url('/admin/backups/' . $f['name'] . '/download') ?>"><i class="fa-solid fa-download"></i></a>
      <form method="post" action="<?= url('/admin/backups/' . $f['name'] . '/delete') ?>" onsubmit="return confirm('Delete backup file?')"><button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button></form>
    </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="ek-empty"><i class="fa-solid fa-database"></i><h5>No backups yet</h5><p class="text-muted small">Create your first backup above — downloads as .sql.gz</p></div><?php endif; ?>
