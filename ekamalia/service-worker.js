/* eKamalia service worker — static shell caching, network-first pages */
const VERSION = 'ekamalia-v1';
const SHELL = [
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/assets/img/logo.png',
  '/assets/img/placeholder.svg',
  '/assets/img/avatar-default.svg',
  '/manifest.json'
];
self.addEventListener('install', e => {
  e.waitUntil(caches.open(VERSION).then(c => c.addAll(SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', e => {
  e.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(k => k !== VERSION).map(k => caches.delete(k)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;
  /* never cache dynamic or authenticated endpoints */
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin') || url.pathname.startsWith('/pos') ||
      url.pathname.startsWith('/checkout') || url.pathname.startsWith('/cart') || url.pathname.startsWith('/chat') ||
      url.pathname.startsWith('/dashboard') || url.pathname.startsWith('/seller') || url.pathname.startsWith('/install')) return;
  /* static assets: cache-first */
  if (url.pathname.startsWith('/assets/') || /\.(png|jpg|jpeg|svg|webp|ico|css|js)$/.test(url.pathname)) {
    e.respondWith(
      caches.match(req).then(hit => hit || fetch(req).then(res => {
        if (res.ok) { const copy = res.clone(); caches.open(VERSION).then(c => c.put(req, copy)); }
        return res;
      }).catch(() => caches.match('/assets/img/placeholder.svg')))
    );
    return;
  }
  /* pages: network-first with offline fallback */
  e.respondWith(
    fetch(req).then(res => {
      if (res.ok && req.headers.get('accept')?.includes('text/html')) {
        const copy = res.clone(); caches.open(VERSION).then(c => c.put(req, copy));
      }
      return res;
    }).catch(() => caches.match(req).then(hit => hit || caches.match('/offline.html')))
  );
});
