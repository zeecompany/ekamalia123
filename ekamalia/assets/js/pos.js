/* ============ eKamalia POS terminal logic ============ */
(function () {
  'use strict';
  const grid = document.getElementById('prodGrid');
  if (!grid) return;
  const cart = []; // {id,name,price,qty,stock,image}
  const $ = id => document.getElementById(id);
  const fmt = n => 'Rs ' + Number(n).toLocaleString();

  function render() {
    const wrap = $('cartLines');
    $('cartEmpty') && $('cartEmpty').remove();
    if (!cart.length) {
      wrap.innerHTML = '<div class="text-center text-muted py-5 small" id="cartEmpty">Cart is empty — click products to add</div>';
    } else {
      wrap.innerHTML = cart.map((it, i) => `
        <div class="cart-line">
          <img src="${it.image}" alt="">
          <div class="flex-grow-1 min-w-0">
            <div class="small fw-semibold text-truncate">${it.name}</div>
            <div class="d-flex align-items-center gap-1 mt-1">
              <button class="qty-btn" data-i="${i}" data-d="-1">−</button>
              <input class="form-control form-control-sm text-center" style="width:50px" value="${it.qty}" readonly>
              <button class="qty-btn" data-i="${i}" data-d="1">+</button>
              <span class="ms-auto small fw-bold">${fmt(it.price * it.qty)}</span>
              <button class="btn btn-link btn-sm text-danger p-0" data-del="${i}"><i class="fa-regular fa-trash-can"></i></button>
            </div>
          </div>
        </div>`).join('');
    }
    const sub = cart.reduce((a, b) => a + b.price * b.qty, 0);
    const disc = parseFloat($('posDiscount').value || 0);
    const tax = (sub - disc) * (parseFloat($('posTax').value || 0) / 100);
    const total = Math.max(0, sub - disc + tax);
    $('posSubtotal').textContent = fmt(sub);
    $('posAdj').textContent = (disc ? '-' + fmt(disc) : '') + (tax ? ' +' + fmt(tax) : '') || 'Rs 0';
    $('posTotal').textContent = fmt(total);
    $('btnTotal').textContent = fmt(total);
    $('btnCharge').disabled = !cart.length;
    localStorage.setItem('pos_cart', JSON.stringify(cart));
    localStorage.setItem('pos_customer', $('posCustomer').value);
    localStorage.setItem('pos_disc', $('posDiscount').value);
    localStorage.setItem('pos_tax', $('posTax').value);
  }

  function addProduct(p) {
    if (!p || p.stock <= 0) { toast('Out of stock', 'danger'); return; }
    const ex = cart.find(x => x.id === p.id);
    if (ex) { if (ex.qty + 1 > p.stock) { toast('Only ' + p.stock + ' in stock', 'warning'); return; } ex.qty++; }
    else cart.push({ ...p, qty: 1 });
    render();
  }

  grid.addEventListener('click', e => {
    const tile = e.target.closest('.prod-tile');
    if (!tile) return;
    try { addProduct(JSON.parse(tile.dataset.product)); } catch (err) {}
  });

  /* barcode: Enter in search field adds exact match */
  $('barcodeInput').addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const code = e.target.value.trim();
    if (!code) return;
    const all = window.POS_PRODUCTS || [];
    const match = all.find(p => String(p.barcode || '') === code) || all.find(p => String(p.sku || '') === code) ||
      all.find(p => p.name.toLowerCase() === code.toLowerCase());
    if (match) { addProduct(match); e.target.value = ''; } else { $('posSearchForm').submit(); }
  });

  /* restore cart: resumed hold takes priority over localStorage */
  try {
    const resumed = window.POS_RESUME;
    if (resumed && Array.isArray(resumed.cart) && resumed.cart.length) {
      for (const line of resumed.cart) {
        const p = (window.POS_PRODUCTS || []).find(x => x.id === line.id);
        if (p) cart.push({ ...p, qty: Math.min(line.qty, Math.max(1, p.stock)) });
      }
      localStorage.setItem('pos_resume_meta', JSON.stringify({ customer: resumed.customer_id, disc: resumed.discount, tax: resumed.tax_percent }));
    } else {
      const saved = JSON.parse(localStorage.getItem('pos_cart') || '[]');
      if (saved.length) { cart.push(...saved); }
    }
  } catch (e) {}
  const restore = () => {
    const meta = (() => { try { return JSON.parse(localStorage.getItem('pos_resume_meta') || 'null'); } catch (e) { return null; } })();
    if (meta) {
      if (meta.customer) $('posCustomer').value = meta.customer;
      if (meta.disc) $('posDiscount').value = meta.disc;
      if (meta.tax) $('posTax').value = meta.tax;
      localStorage.removeItem('pos_resume_meta');
    } else {
      const c = localStorage.getItem('pos_customer'); if (c) $('posCustomer').value = c;
      const d = localStorage.getItem('pos_disc'); if (d) $('posDiscount').value = d;
      const t = localStorage.getItem('pos_tax'); if (t) $('posTax').value = t;
    }
  };
  setTimeout(() => { restore(); render(); }, 0);

  window.clearCart = function () {
    if (cart.length && !ekConfirm('Clear the current sale?')) return;
    cart.length = 0; localStorage.removeItem('pos_cart'); render();
  };

  document.addEventListener('click', e => {
    const qb = e.target.closest('.qty-btn');
    if (qb) { const it = cart[+qb.dataset.i]; it.qty = Math.min(Math.max(1, it.qty + +qb.dataset.d), it.stock); render(); }
    const del = e.target.closest('[data-del]');
    if (del) { cart.splice(+del.dataset.del, 1); render(); }
    const pm = e.target.closest('.pay-method');
    if (pm) {
      document.querySelectorAll('.pay-method').forEach(x => x.classList.remove('active', 'border-success', 'text-success'));
      pm.classList.add('active', 'border-success', 'text-success');
      pm.dataset.payMethod = pm.dataset.v;
    }
  });
  let payMethod = 'cash';
  document.querySelectorAll('.pay-method').forEach(b => b.addEventListener('click', () => payMethod = b.dataset.v));
  ['posDiscount', 'posTax', 'posCustomer'].forEach(id => $(id).addEventListener('change', render));

  /* charge modal */
  const payModal = new bootstrap.Modal($('payModal'));
  $('btnCharge').addEventListener('click', () => {
    $('payDue').textContent = $('posTotal').textContent;
    $('payReceived').value = '';
    $('payChange').textContent = 'Rs 0';
    payModal.show();
    setTimeout(() => $('payReceived').focus(), 400);
  });
  $('payReceived').addEventListener('input', () => {
    const due = parseFloat($('posTotal').textContent.replace(/[^\d.]/g, '')) || 0;
    const rec = parseFloat($('payReceived').value || 0);
    $('payChange').textContent = fmt(Math.max(0, rec - due));
  });

  function submitSale(type, hold) {
    if (!cart.length) return;
    const body = {
      cart: JSON.stringify(cart.map(x => ({ id: x.id, qty: x.qty }))),
      type, hold: hold ? '1' : '',
      discount: $('posDiscount').value || 0, tax_percent: $('posTax').value || 0,
      customer_id: $('posCustomer').value || '', payment_method: payMethod,
      paid_amount: parseFloat($('payReceived').value || 0) || 0
    };
    return ekPost(window.POS_CONFIG.checkoutUrl, body).then(r => {
      if (!r.ok) { toast(r.message, 'danger'); return; }
      toast(r.message);
      localStorage.removeItem('pos_cart');
      if (r.type === 'quotation') setTimeout(() => location.href = window.POS_CONFIG.invoiceUrl.replace('type=sale', 'type=quotation'), 700);
      else setTimeout(() => location.href = window.EK_BASE + '/pos/invoice/' + r.sale_id + '/print', 500);
    });
  }
  $('btnConfirmPay').addEventListener('click', () => submitSale('sale', false).then(() => payModal.hide()));
  $('btnHold').addEventListener('click', () => submitSale('sale', true));
  $('btnQuote').addEventListener('click', () => submitSale('quotation', false));
})();
