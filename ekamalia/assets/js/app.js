/* ============================================================
   eKamalia — Frontend JavaScript
   Kamalia's Digital Bazaar & Commerce Platform
   ============================================================ */
(function () {
  'use strict';
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* ---------- fetch helpers ---------- */
  window.ekPost = function (url, data = {}, isForm = false) {
    const opts = { method: 'POST', headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' } };
    if (isForm) { opts.body = data; }
    else { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(data); }
    return fetch(url, opts).then(r => r.json()).catch(() => ({ ok: false, message: 'Network error' }));
  };

  window.ekGet = function (url) {
    return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).catch(() => ({ ok: false }));
  };

  /* ---------- modern toast notifications ---------- */
  let toastWrap = document.querySelector('.toast-ek');
  if (!toastWrap) {
    toastWrap = document.createElement('div');
    toastWrap.className = 'toast-ek';
    document.body.appendChild(toastWrap);
  }

  window.toast = function (message, type = 'success') {
    const el = document.createElement('div');
    el.className = 't ' + type;
    const icon = type === 'success' ? 'fa-circle-check' : type === 'danger' ? 'fa-circle-xmark' : 'fa-triangle-exclamation';
    el.innerHTML = `<i class="fa-solid ${icon} fs-5"></i><span></span>`;
    el.querySelector('span').textContent = message;
    toastWrap.appendChild(el);
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(-10px) scale(0.95)';
      el.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
      setTimeout(() => el.remove(), 320);
    }, 3400);
  };

  /* ---------- confirm helper ---------- */
  window.ekConfirm = function (msg) {
    return window.confirm(msg || 'Are you sure you want to proceed?');
  };

  /* ---------- scroll reveal observer ---------- */
  const io = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) {
        en.target.classList.add('in');
        io.unobserve(en.target);
      }
    });
  }, { threshold: 0.06 });

  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  /* ---------- animated KPI counters ---------- */
  const cio = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (!en.isIntersecting) return;
      cio.unobserve(en.target);
      const el = en.target, target = parseInt(el.dataset.count || '0', 10), dur = 1300, t0 = performance.now();
      const step = t => {
        const p = Math.min(1, (t - t0) / dur);
        el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    });
  }, { threshold: 0.35 });

  document.querySelectorAll('.ek-counter').forEach(el => cio.observe(el));

  /* ---------- lazy image fade-in ---------- */
  document.querySelectorAll('img[data-fade]').forEach(img => {
    if (img.complete) {
      img.style.opacity = '1';
      return;
    }
    img.style.opacity = '0';
    img.style.transition = 'opacity 0.45s ease';
    img.addEventListener('load', () => img.style.opacity = '1');
    img.addEventListener('error', () => img.style.opacity = '1');
  });

  /* ---------- header dropdowns toggle ---------- */
  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-dd]');
    document.querySelectorAll('.ek-dropdown.open').forEach(d => {
      if (!btn || d.id !== btn.dataset.dd) d.classList.remove('open');
    });
    if (btn) {
      const dd = document.getElementById(btn.dataset.dd);
      if (dd) dd.classList.toggle('open');
    }
  });

  /* ---------- mobile drawer ---------- */
  const drawer = document.getElementById('ekDrawer'), backdrop = document.getElementById('ekDrawerBackdrop');
  window.ekDrawer = function (open) {
    if (!drawer) return;
    drawer.classList.toggle('open', open);
    backdrop && backdrop.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  };
  backdrop && backdrop.addEventListener('click', () => ekDrawer(false));

  /* ---------- global search suggestions ---------- */
  const sInput = document.getElementById('globalSearch'), sBox = document.getElementById('searchSuggest');
  let sTimer = null;
  if (sInput && sBox) {
    sInput.addEventListener('input', () => {
      clearTimeout(sTimer);
      const q = sInput.value.trim();
      if (q.length < 2) {
        sBox.style.display = 'none';
        return;
      }
      sTimer = setTimeout(() => {
        ekGet('/api/search/suggest?q=' + encodeURIComponent(q)).then(r => {
          if (!r.ok || !r.results || !r.results.length) {
            sBox.style.display = 'none';
            return;
          }
          sBox.innerHTML = `
            <div class="sg-section-title">Matching Marketplace Results</div>
            ` + r.results.map(it => `
            <a class="sg-item" href="${it.url}">
              <img src="${it.image}" alt="" loading="lazy">
              <div class="flex-grow-1 min-w-0">
                <div class="small fw-bold text-truncate">${it.title}</div>
                <div class="text-muted" style="font-size:0.75rem">${it.subtitle}</div>
              </div>
              <span class="sg-type-badge">${it.type}</span>
            </a>`).join('');
          sBox.style.display = 'block';
        });
      }, 220);
    });

    document.addEventListener('click', e => {
      if (!e.target.closest('.ek-search-form')) sBox.style.display = 'none';
    });
  }

  /* ---------- sticky header shadow transition ---------- */
  const header = document.querySelector('.ek-header');
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        header.style.boxShadow = '0 6px 20px rgba(15, 23, 42, 0.08)';
      } else {
        header.style.boxShadow = '0 2px 12px rgba(15, 23, 42, 0.04)';
      }
    }, { passive: true });
  }

  /* ---------- AJAX actions: wishlist / like / follow / cart ---------- */
  document.addEventListener('click', e => {
    const wl = e.target.closest('[data-wishlist]');
    if (wl) {
      e.preventDefault();
      if (!window.EK_LOGGED_IN && !window.EK_USER) {
        toast('Please login to save items to your wishlist', 'warning');
        setTimeout(() => location.href = (window.EK_BASE || '') + '/login', 700);
        return;
      }
      ekPost('/api/wishlist/toggle', { type: wl.dataset.type, id: wl.dataset.id }).then(r => {
        if (!r.ok) return toast(r.message || 'Error saving item', 'danger');
        wl.classList.toggle('active', r.added);
        const ic = wl.querySelector('i');
        if (ic) ic.className = 'fa-solid fa-heart';
        toast(r.added ? 'Saved to wishlist ❤' : 'Removed from wishlist');
        document.querySelectorAll(`[data-wishlist][data-type="${wl.dataset.type}"][data-id="${wl.dataset.id}"]`).forEach(b => b.classList.toggle('active', r.added));
      });
      return;
    }

    const lk = e.target.closest('[data-like]');
    if (lk) {
      e.preventDefault();
      if (!window.EK_LOGGED_IN && !window.EK_USER) {
        toast('Please login to like this item', 'warning');
        setTimeout(() => location.href = (window.EK_BASE || '') + '/login', 700);
        return;
      }
      ekPost('/api/like/toggle', { type: lk.dataset.type, id: lk.dataset.id }).then(r => {
        if (!r.ok) return toast(r.message || 'Error', 'danger');
        lk.classList.toggle('active', r.liked);
        const cnt = lk.querySelector('.like-count');
        if (cnt && typeof r.count !== 'undefined') cnt.textContent = r.count;
        const ic = lk.querySelector('i');
        if (ic) ic.className = r.liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
      });
      return;
    }

    const fl = e.target.closest('[data-follow]');
    if (fl) {
      e.preventDefault();
      if (!window.EK_LOGGED_IN && !window.EK_USER) {
        toast('Please login to follow this shop', 'warning');
        setTimeout(() => location.href = (window.EK_BASE || '') + '/login', 700);
        return;
      }
      ekPost('/api/follow/toggle', { shop: fl.dataset.follow }).then(r => {
        if (!r.ok) return toast(r.message || 'Error', 'danger');
        fl.innerHTML = r.following ? '<i class="fa-solid fa-check me-1"></i>Following' : '<i class="fa-solid fa-plus me-1"></i>Follow';
        fl.classList.toggle('btn-outline-success', r.following);
        fl.classList.toggle('btn-ek', !r.following);
        toast(r.following ? 'Now following store' : 'Unfollowed store');
      });
      return;
    }

    const ct = e.target.closest('[data-cart-add]');
    if (ct) {
      e.preventDefault();
      const qty = parseInt(document.querySelector(ct.dataset.qtyFrom || '#qtyInput')?.value || ct.dataset.qty || '1', 10);
      ct.disabled = true;
      ekPost('/cart/add', { product_id: ct.dataset.cartAdd, quantity: qty }).then(r => {
        ct.disabled = false;
        if (!r.ok) return toast(r.message || 'Could not add product', 'danger');
        toast(r.message || 'Added to cart successfully');
        updateCartBadge(r.cart_count);
        if (r.cart_count_html !== undefined) {
          const b = document.getElementById('cartBodyWrap');
          if (b && r.reload) location.reload();
        }
        if (ct.dataset.reload) setTimeout(() => location.reload(), 500);
      });
      return;
    }

    const sh = e.target.closest('[data-share]');
    if (sh) {
      e.preventDefault();
      const url = sh.dataset.share || location.href, title = sh.dataset.title || document.title;
      if (navigator.share) {
        navigator.share({ title, url }).catch(() => {});
      } else {
        navigator.clipboard.writeText(url).then(() => toast('Link copied to clipboard! Share on WhatsApp or anywhere.'));
      }
      return;
    }
  });

  function updateCartBadge(n) {
    document.querySelectorAll('[data-cart-badge]').forEach(b => {
      b.textContent = n;
      b.style.display = n > 0 ? 'flex' : 'none';
    });
  }

  /* ---------- cart page qty / remove ---------- */
  document.addEventListener('click', e => {
    const qBtn = e.target.closest('[data-cart-qty]');
    if (qBtn) {
      const row = qBtn.closest('[data-cart-row]');
      const pid = row.dataset.cartRow, cur = parseInt(row.querySelector('.ci-qty').value, 10);
      const next = Math.max(1, cur + parseInt(qBtn.dataset.cartQty, 10));
      ekPost('/cart/update', { product_id: pid, quantity: next }).then(r => {
        if (r.ok) location.reload();
        else toast(r.message, 'danger');
      });
    }
    const rm = e.target.closest('[data-cart-remove]');
    if (rm) {
      ekPost('/cart/remove', { product_id: rm.dataset.cartRemove }).then(r => {
        if (r.ok) {
          toast('Item removed from cart');
          location.reload();
        }
      });
    }
  });

  /* ---------- coupons ---------- */
  const cpBtn = document.getElementById('applyCoupon');
  cpBtn && cpBtn.addEventListener('click', () => {
    const code = document.getElementById('couponInput').value.trim();
    ekPost('/cart/coupon', { code }).then(r => {
      toast(r.message, r.ok ? 'success' : 'danger');
      if (r.ok) location.reload();
    });
  });

  const cpRm = document.getElementById('removeCoupon');
  cpRm && cpRm.addEventListener('click', () => {
    ekPost('/cart/coupon', { code: '' }).then(() => location.reload());
  });

  /* ---------- image gallery ---------- */
  document.querySelectorAll('.gallery-thumbs img').forEach(t => {
    t.addEventListener('click', () => {
      document.querySelectorAll('.gallery-thumbs img').forEach(x => x.classList.remove('active'));
      t.classList.add('active');
      const main = document.querySelector('.gallery-main img');
      if (main) main.src = t.dataset.full || t.src;
    });
  });

  /* ---------- comments & reports ---------- */
  document.addEventListener('submit', e => {
    const f = e.target;
    if (f.matches('[data-comment-form]')) {
      e.preventDefault();
      const fd = new FormData(f);
      ekPost(f.action, fd, true).then(r => {
        if (!r.ok) return toast(r.message || 'Could not post comment', 'danger');
        toast('Comment posted successfully!');
        setTimeout(() => location.reload(), 450);
      });
    }
    if (f.matches('[data-report-form]')) {
      e.preventDefault();
      const fd = new FormData(f);
      ekPost(f.action, fd, true).then(r => {
        toast(r.ok ? 'Report submitted. Our moderation team will review it.' : (r.message || 'Error'), r.ok ? 'success' : 'danger');
        if (r.ok) {
          bootstrap.Modal.getInstance(document.getElementById('reportModal'))?.hide();
        }
      });
    }
  });

  /* ---------- bijli auto-refresh ---------- */
  const bijliWrap = document.getElementById('bijliLive');
  if (bijliWrap) {
    const refresh = () => fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.text()).then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const fresh = doc.getElementById('bijliLive');
        if (fresh) { bijliWrap.innerHTML = fresh.innerHTML; }
      }).catch(() => {});
    setInterval(refresh, 60000);
  }

  /* ---------- language switcher ---------- */
  document.querySelectorAll('[data-lang]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    ekPost('/lang', { lang: a.dataset.lang }).then(() => location.reload());
  }));

  /* ---------- Swiper hero slider init ---------- */
  if (window.Swiper && document.querySelector('.heroSwiper')) {
    new Swiper('.heroSwiper', {
      loop: true,
      effect: 'fade',
      fadeEffect: { crossFade: true },
      speed: 750,
      autoplay: { delay: 5400, disableOnInteraction: false },
      pagination: { el: '.heroSwiper .swiper-pagination', clickable: true },
      keyboard: { enabled: true },
      a11y: { enabled: true },
      on: {
        slideChangeTransitionStart(sw) {
          const el = sw.slides[sw.activeIndex];
          el.classList.remove('kenburns', 'zoomin', 'slide-up');
          void el.offsetWidth;
          const anim = el.dataset.anim || 'fade';
          if (anim === 'kenburns') el.classList.add('kenburns');
          else if (anim === 'zoom') el.classList.add('zoomin', 'slide-up');
          else if (anim === 'slide') el.classList.add('slide-up');
          else el.classList.add('slide-up');
        }
      }
    });
  }

  /* ---------- Swiper listing row sliders ---------- */
  if (window.Swiper) {
    document.querySelectorAll('.rowSwiper').forEach(el => {
      new Swiper(el, {
        slidesPerView: 1.35,
        spaceBetween: 12,
        watchOverflow: true,
        navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') },
        breakpoints: {
          480: { slidesPerView: 2.15, spaceBetween: 12 },
          768: { slidesPerView: 3.2, spaceBetween: 14 },
          992: { slidesPerView: 4.15, spaceBetween: 16 },
          1400: { slidesPerView: 5.15, spaceBetween: 16 }
        }
      });
    });

    document.querySelectorAll('.shopSwiper').forEach(el => {
      new Swiper(el, {
        slidesPerView: 1.2,
        spaceBetween: 14,
        navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') },
        breakpoints: {
          576: { slidesPerView: 2, spaceBetween: 14 },
          992: { slidesPerView: 3.1, spaceBetween: 16 },
          1400: { slidesPerView: 4, spaceBetween: 16 }
        }
      });
    });
  }

  /* ---------- notifications polling ---------- */
  if (window.EK_USER || window.EK_LOGGED_IN) {
    const pollNotifs = () => ekGet('/api/notifications/poll').then(r => {
      if (!r.ok) return;
      document.querySelectorAll('[data-notif-badge]').forEach(b => {
        b.textContent = r.unread;
        b.style.display = r.unread > 0 ? 'flex' : 'none';
      });
      const list = document.getElementById('notifList');
      if (list && r.html !== undefined) list.innerHTML = r.html;
      const ml = document.getElementById('msgBadge');
      if (ml) {
        ml.textContent = r.unread_messages;
        ml.style.display = r.unread_messages > 0 ? 'flex' : 'none';
      }
    });
    pollNotifs();
    setInterval(pollNotifs, 30000);
  }
})();

/* ---------- centralized chat starter ---------- */
document.addEventListener('click', e => {
  const b = e.target.closest('[data-chat-start]');
  if (!b) return;
  e.preventDefault();
  if (!window.EK_LOGGED_IN) {
    location.href = (window.EK_BASE || '') + '/login';
    return;
  }
  const msg = prompt('Assalam-o-Alaikum! Write your message to the seller:', 'Assalam-o-Alaikum, is this still available in Kamalia?');
  if (msg === null) return;
  const fd = new FormData();
  fd.append('type', b.dataset.chatStart);
  fd.append('id', b.dataset.id);
  fd.append('body', msg);
  fetch((window.EK_BASE || '') + '/chat/start', {
    method: 'POST',
    headers: { 'X-CSRF-Token': (document.querySelector('meta[name=csrf-token]') || {}).content },
    body: fd
  })
    .then(r => r.ok ? r : Promise.reject())
    .then(() => location.href = (window.EK_BASE || '') + '/messages')
    .catch(() => toast('Could not start chat', 'danger'));
});
