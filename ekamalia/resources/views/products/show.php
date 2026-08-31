<?php
$me = auth(); $isOwnerShop = $me && (int)$me['id'] === (int)$p['sid'];
$sale = $p['sale_price'] && (float)$p['sale_price'] > 0 && (float)$p['sale_price'] < (float)$p['price'];
$price = $sale ? (float)$p['sale_price'] : (float)$p['price'];
$disc = $sale ? round(100 - ($price / (float)$p['price'] * 100)) : 0;
$canReview = false; $myOrderId = null;
if ($me) {
    $myOrderId = qv('SELECT o.id FROM orders o JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id=? AND oi.product_id=? AND o.status IN ("delivered","completed") LIMIT 1', [$me['id'], $p['id']]);
    $canReview = $myOrderId && !q1('SELECT id FROM reviews WHERE user_id=? AND item_type="product" AND item_id=?', [$me['id'], $p['id']]);
}
?>
<div class="container py-4">
  <nav aria-label="breadcrumb" class="ek-breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= url('/products') ?>">Products</a></li>
    <li class="breadcrumb-item active text-truncate" style="max-width:240px"><?= e($p['name']) ?></li></ol></nav>
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="gallery-main mb-2 position-relative">
        <img src="<?= e($images ? upload_url($images[0]['image']) : asset('img/placeholder.svg')) ?>" alt="<?= e($p['name']) ?>">
        <?php if ($disc > 0): ?><span class="ek-flag discount position-absolute m-3" style="font-size:.8rem">-<?= $disc ?>% OFF</span><?php endif; ?>
      </div>
      <?php if (count($images) > 1): ?>
      <div class="row g-2 row-cols-4 row-cols-md-6 gallery-thumbs mb-3">
        <?php foreach ($images as $i => $im): ?><div class="col"><img src="<?= e(upload_url($im['image'])) ?>" data-full="<?= e(upload_url($im['image'])) ?>" class="<?= $i === 0 ? 'active' : '' ?>" alt="" loading="lazy"></div><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <ul class="nav nav-tabs mt-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#descTab">Description</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specTab">Specifications</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#revTab">Reviews (<?= count($reviews) ?>)</button></li>
      </ul>
      <div class="tab-content ek-card p-4 border-top-0 rounded-bottom-4">
        <div class="tab-pane fade show active" id="descTab"><div style="line-height:1.9;white-space:pre-wrap"><?= e($p['description'] ?: $p['short_description']) ?></div></div>
        <div class="tab-pane fade" id="specTab">
          <div class="row">
            <div class="col-md-6">
              <?php foreach ([['SKU', $p['sku']], ['Brand', $p['brand_name']], ['Condition', 'New'], ['Unit', $p['unit']], ['Weight', $p['weight']], ['Dimensions', $p['dimensions']]] as $row): if (!$row[1]) continue; ?>
                <div class="spec-row"><span class="k"><?= e($row[0]) ?></span><b><?= e($row[1]) ?></b></div>
              <?php endforeach; ?>
            </div>
            <div class="col-md-6">
              <?php foreach ([['Warranty', $p['warranty']], ['Return Policy', $p['return_policy']], ['Delivery Time', $p['delivery_time'] ?: $p['shop_delivery_time']], ['Shipping Fee', money($p['shipping_fee'])], ['Tax', $p['tax_percent'] . '%'], ['Stock', (int)$p['stock'] . ' ' . e($p['unit'])]] as $row): if ($row[1] === null || $row[1] === '') continue; ?>
                <div class="spec-row"><span class="k"><?= e($row[0]) ?></span><b><?= e((string)$row[1]) ?></b></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="revTab">
          <?php if ($canReview): ?>
          <form data-review-form method="post" action="<?= url('/review') ?>" class="border rounded-4 p-3 mb-3">
            <?= csrf_field() ?>
            <input type="hidden" name="item_type" value="product"><input type="hidden" name="item_id" value="<?= $p['id'] ?>">
            <h6 class="fw-bold">Write your review <span class="badge text-bg-success"><?= e(t('misc.verified_purchase')) ?></span></h6>
            <div class="mb-2">
              <?php for ($i = 1; $i <= 5; $i++): ?><label class="me-1" style="cursor:pointer"><input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?> class="d-none"><i class="fa-solid fa-star text-warning star-pick" data-v="<?= $i ?>"></i></label><?php endfor; ?>
            </div>
            <textarea class="form-control mb-2" name="body" rows="3" required placeholder="How was the product & delivery?"></textarea>
            <button class="btn btn-ek rounded-pill px-4 btn-sm">Submit Review</button>
          </form>
          <?php endif; ?>
          <?php foreach ($reviews as $r): ?>
          <div class="border-bottom py-3">
            <div class="d-flex align-items-center gap-2">
              <img src="<?= asset('img/avatar-default.svg') ?>" style="width:34px;height:34px;border-radius:50%" alt="">
              <div><span class="fw-semibold small"><?= e($r['user_name']) ?></span>
                <div class="stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></div></div>
              <?php if ($r['is_verified_purchase']): ?><span class="badge text-bg-success ms-auto"><?= e(t('misc.verified_purchase')) ?></span><?php endif; ?>
            </div>
            <p class="mb-1 mt-2 small"><?= e($r['body']) ?></p>
            <?php if ($r['seller_reply']): ?>
              <div class="bg-light rounded-3 p-2 small mt-2"><b class="text-success"><?= e($p['shop_name']) ?>:</b> <?= e($r['seller_reply']) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          <?php if (!$reviews): ?><p class="text-muted small mb-0">No reviews yet. Be the first after your purchase!</p><?php endif; ?>
        </div>
      </div>
      <?php $comments = $comments ?? []; $commentTarget = ['type' => 'product', 'id' => (int)$p['id']]; include views_path('partials/comments-block'); ?>
    </div>

    <div class="col-lg-5">
      <div class="ek-card p-4 mb-3">
        <h1 class="h4 fw-bold"><?= e($p['name']) ?></h1>
        <div class="d-flex align-items-center gap-2 my-2 small text-muted">
          <?php if ((int)$p['rating_count'] > 0): ?><span class="stars"><i class="fa-solid fa-star"></i> <?= number_format((float)$p['rating_avg'], 1) ?></span><span>(<?= (int)$p['rating_count'] ?> reviews)</span><?php endif; ?>
          <span><i class="fa-solid fa-eye me-1"></i><?= number_format((int)$p['views']) ?></span>
        </div>
        <div class="price-tag my-2"><?= money($price) ?>
          <?php if ($sale): ?><span class="old fs-6"><?= money($p['price']) ?></span><span class="badge text-bg-danger ms-2">Save <?= money((float)$p['price'] - $price) ?></span><?php endif; ?>
        </div>
        <div class="mb-3 small">
          <?php if ((int)$p['stock'] > 0): ?><span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>In Stock (<?= (int)$p['stock'] ?>)</span>
          <?php else: ?><span class="text-danger fw-semibold"><i class="fa-solid fa-circle-xmark me-1"></i><?= e(t('product.out')) ?></span><?php endif; ?>
        </div>
        <?php if ($variants): ?>
        <div class="mb-3"><?php foreach ($variants as $v): ?><span class="chip"><?= e($v['name']) ?>: <b><?= e($v['value']) ?></b></span> <?php endforeach; ?></div>
        <?php endif; ?>
        <div class="row g-2 align-items-center mb-3">
          <div class="col-4"><label class="form-label small fw-semibold mb-0">Quantity</label>
            <input type="number" id="qtyInput" class="form-control" value="1" min="1" max="<?= max(1, (int)$p['stock']) ?>"></div>
        </div>
        <div class="d-grid gap-2">
          <button class="btn btn-ek btn-lg rounded-pill" data-cart-add="<?= $p['id'] ?>" data-qty-from="#qtyInput" <?= (int)$p['stock'] <= 0 ? 'disabled' : '' ?>><i class="fa-solid fa-cart-plus me-2"></i><?= e(t('btn.add_to_cart')) ?></button>
          <button class="btn btn-warning btn-lg rounded-pill fw-bold" onclick="buyNow(<?= $p['id'] ?>)" <?= (int)$p['stock'] <= 0 ? 'disabled' : '' ?>>⚡ <?= e(t('btn.buy_now')) ?></button>
        </div>
        <hr>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-outline-secondary btn-sm rounded-pill flex-fill <?= $wishlisted ? 'active text-danger' : '' ?>" data-wishlist="product" data-id="<?= $p['id'] ?>"><i class="fa-solid fa-heart me-1"></i>Wishlist</button>
          <button class="btn btn-outline-secondary btn-sm rounded-pill flex-fill" data-like="product" data-id="<?= $p['id'] ?>"><i class="fa-<?= $liked ? 'solid' : 'regular' ?> fa-heart me-1"></i><span class="like-count"><?= (int)$p['likes_count'] ?></span></button>
          <button class="btn btn-outline-secondary btn-sm rounded-pill flex-fill" data-share="<?= e(url('/product/' . $p['slug'])) ?>"><i class="fa-solid fa-share-nodes me-1"></i>Share</button>
          <?php if (!$inCompare): ?><button class="btn btn-outline-secondary btn-sm rounded-pill flex-fill" onclick="ekPost('<?= url('/compare/add') ?>',{product_id:<?= $p['id'] ?>}).then(r=>{toast(r.message);setTimeout(()=>location.href='<?= url('/compare') ?>',700)})"><i class="fa-solid fa-scale-balanced me-1"></i>Compare</button>
          <?php else: ?><a class="btn btn-success btn-sm rounded-pill flex-fill" href="<?= url('/compare') ?>"><i class="fa-solid fa-check me-1"></i>In Compare</a><?php endif; ?>
        </div>
        <div class="d-flex justify-content-between mt-3 small">
          <a class="text-danger" data-report="product" data-id="<?= $p['id'] ?>" href="#"><i class="fa-regular fa-flag me-1"></i>Report</a>
          <a class="text-decoration-none" href="<?= url('/shops') ?>"><i class="fa-solid fa-store me-1"></i>More from this shop</a>
        </div>
      </div>

      <div class="ek-card p-4 mb-3">
        <div class="d-flex align-items-center gap-3">
          <img src="<?= e(img_or('', 'assets/img/shop-logo.svg')) ?>" style="width:56px;height:56px;border-radius:14px;object-fit:cover" alt="">
          <div class="flex-grow-1">
            <a class="fw-bold" href="<?= url('/shop/' . $p['shop_slug']) ?>"><?= e($p['shop_name']) ?>
              <?php if ($p['shop_verified']): ?><i class="fa-solid fa-circle-check verified-tick"></i><?php endif; ?></a>
            <div class="text-muted" style="font-size:.74rem"><i class="fa-solid fa-location-dot me-1"></i><?= e($p['city_name'] ?? 'Kamalia') ?></div>
          </div>
          <a class="btn btn-ek btn-sm rounded-pill px-3" href="<?= url('/shop/' . $p['shop_slug']) ?>"><?= e(t('btn.visit_shop')) ?></a>
        </div>
        <hr>
        <div class="d-flex flex-wrap gap-3 small text-muted">
          <?php if ($p['shop_whatsapp']): ?><a class="text-success" target="_blank" rel="noopener" href="<?= e(wa_link($p['shop_whatsapp'], 'Assalam-o-Alaikum! I want to order: ' . $p['name'])) ?>"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a><?php endif; ?>
          <button data-chat-start="product" data-id="<?= $p['id'] ?>" class="btn btn-link btn-sm p-0"><i class="fa-regular fa-comment-dots me-1"></i>Chat with seller</button>
        </div>
      </div>
    </div>
  </div>

  <?php if ($related): ?>
  <section class="ek-section">
    <div class="ek-section-head"><h2>You May Also Like</h2></div>
    <div class="swiper rowSwiper pb-4">
      <div class="swiper-wrapper"><?php foreach ($related as $p2): $p = $p2; ?><div class="swiper-slide h-auto"><?php include views_path('partials/product-card'); ?></div><?php endforeach; ?></div>
      <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
    </div>
  </section>
  <?php endif; ?>
</div>
<script>
window.EK_PAGE_TYPE = 'product'; window.EK_PAGE_ID = <?= (int)$p['id'] ?>;
function buyNow(pid) {
  const qty = document.getElementById('qtyInput').value || 1;
  ekPost('<?= url('/cart/add') ?>', { product_id: pid, quantity: qty }).then(r => {
    if (r.ok) location.href = '<?= url('/checkout') ?>'; else toast(r.message, 'danger');
  });
}
/* star picker */
document.querySelectorAll('.star-pick').forEach(s => s.addEventListener('click', () => {
  const v = +s.dataset.v;
  document.querySelectorAll('.star-pick').forEach((x, i) => x.style.color = i < v ? '#f5b301' : '#d9dfdb');
}));
</script>
<?php if ($me && $isOwnerShop): ?><div class="container"><div class="alert alert-info small">You own this product — <a href="<?= url('/seller/products/' . $p['id'] . '/edit') ?>">edit it</a>.</div></div><?php endif; ?>
