/* eKamalia buyer/seller dashboard helpers */
(function () {
  'use strict';
  if (window.Chart) {
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#63756c';
  }
  window.ekChart = function (id, config) {
    const el = document.getElementById(id);
    if (el && window.Chart) new Chart(el, config);
  };
  /* subcategory loader on product form */
  const catSel = document.getElementById('categorySelect');
  const subSel = document.getElementById('subcategorySelect');
  if (catSel && subSel) {
    const preload = subSel.dataset.selected || '';
    catSel.addEventListener('change', () => {
      fetch('/api/subcategories?category_id=' + catSel.value, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(r => {
          subSel.innerHTML = '<option value="">— None —</option>' + (r.subcategories || []).map(s => `<option value="${s.id}">${s.name}</option>`).join('');
          if (preload) subSel.value = preload;
        });
    });
    if (catSel.value) catSel.dispatchEvent(new Event('change'));
  }
  /* area suggestions */
  const citySel = document.getElementById('citySelect');
  const areaInput = document.getElementById('areaInput');
  if (citySel && areaInput) {
    let dl = document.getElementById('areaList');
    if (!dl) { dl = document.createElement('datalist'); dl.id = 'areaList'; areaInput.after(dl); }
    citySel.addEventListener('change', () => {
      fetch('/api/areas?city_id=' + citySel.value, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(r => { dl.innerHTML = (r.areas || []).map(a => `<option value="${a.name}">`).join(''); });
    });
    if (citySel.value) citySel.dispatchEvent(new Event('change'));
  }
})();
