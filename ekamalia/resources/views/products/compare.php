<div class="container py-4">
  <h1 class="h4 fw-bold mb-3"><i class="fa-solid fa-scale-balanced me-2 text-success"></i>Compare Products</h1>
  <?php if (!$products): ?>
    <div class="ek-empty"><i class="fa-solid fa-scale-balanced"></i><h5>Nothing to compare yet</h5>
      <p class="small">Open any product and click "Compare" to add it here (up to 4).</p>
      <a class="btn btn-ek rounded-pill px-4" href="<?= url('/products') ?>">Browse Products</a></div>
  <?php else: ?>
  <div class="table-responsive ek-card p-3">
    <table class="table align-middle text-center">
      <tr>
        <td></td>
        <?php foreach ($products as $p): ?>
        <td style="min-width:180px">
          <img src="<?= e(img_or($p['image'] ?? '')) ?>" class="rounded-3 mb-2" style="width:100%;max-width:150px;aspect-ratio:1;object-fit:cover" alt="">
          <div class="fw-semibold small text-truncate"><?= e($p['name']) ?></div>
          <button class="btn btn-link btn-sm text-danger p-0" onclick="ekPost('<?= url('/compare/remove') ?>',{product_id:<?= $p['id'] ?>}).then(()=>location.reload())">Remove</button>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr><td class="fw-bold small text-muted text-start">Price</td><?php foreach ($products as $p): ?><td class="fw-bold text-success"><?= money($p['sale_price'] ?: $p['price']) ?></td><?php endforeach; ?></tr>
      <tr><td class="fw-bold small text-muted text-start">Brand</td><?php foreach ($products as $p): ?><td class="small"><?= e($p['brand_name'] ?? '—') ?></td><?php endforeach; ?></tr>
      <tr><td class="fw-bold small text-muted text-start">Rating</td><?php foreach ($products as $p): ?><td class="small"><i class="fa-solid fa-star text-warning"></i> <?= number_format((float)$p['rating_avg'], 1) ?> (<?= (int)$p['rating_count'] ?>)</td><?php endforeach; ?></tr>
      <tr><td class="fw-bold small text-muted text-start">Shop</td><?php foreach ($products as $p): ?><td class="small"><a href="<?= url('/shop/' . $p['shop_slug']) ?>"><?= e($p['shop_name']) ?></a></td><?php endforeach; ?></tr>
      <tr><td class="fw-bold small text-muted text-start">Stock</td><?php foreach ($products as $p): ?><td class="small"><?= (int)$p['stock'] > 0 ? 'In stock (' . (int)$p['stock'] . ')' : 'Out of stock' ?></td><?php endforeach; ?></tr>
      <tr><td></td><?php foreach ($products as $p): ?><td><button class="btn btn-ek btn-sm rounded-pill" data-cart-add="<?= $p['id'] ?>"><i class="fa-solid fa-cart-plus"></i></button></td><?php endforeach; ?></tr>
    </table>
  </div>
  <?php endif; ?>
</div>
