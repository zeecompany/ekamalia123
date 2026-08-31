/* eKamalia Admin helpers */
(function () {
  'use strict';
  window.adminAction = function (url, data, confirmMsg) {
    if (confirmMsg && !window.confirm(confirmMsg)) return Promise.resolve();
    return ekPost(url, data).then(r => {
      toast(r.message || (r.ok ? 'Done' : 'Error'), r.ok ? 'success' : 'danger');
      if (r.ok) setTimeout(() => location.reload(), 600);
    });
  };
  /* chart defaults */
  if (window.Chart) {
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#63756c';
    Chart.defaults.plugins.legend.labels.boxWidth = 12;
    Chart.defaults.plugins.tooltip.backgroundColor = '#10231a';
  }
  window.ekChart = function (id, config) {
    const el = document.getElementById(id);
    if (el && window.Chart) new Chart(el, config);
  };
})();
