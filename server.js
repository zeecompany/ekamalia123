/**
 * eKamalia — Live Preview Server
 * Serves the full premium eKamalia marketplace with realistic local data & interactive features.
 */
const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = process.env.PORT || 8080;
const ROOT = path.join(__dirname, 'ekamalia');

// Helpers
const money = n => 'Rs ' + Number(n).toLocaleString('en-PK');
const timeAgo = (str) => '2 hours ago';

// Demo Data
const cities = [
  { id: 1, name: 'Kamalia City', slug: 'kamalia', is_primary: 1, feeder_count: 6 },
  { id: 2, name: 'Karkhana Bazar', slug: 'karkhana-bazar', is_primary: 0, feeder_count: 2 },
  { id: 3, name: 'Railway Road', slug: 'railway-road', is_primary: 0, feeder_count: 2 },
  { id: 4, name: 'Chak 341', slug: 'chak-341', is_primary: 0, feeder_count: 1 },
  { id: 5, name: 'Pirmahal Road', slug: 'pirmahal-road', is_primary: 0, feeder_count: 2 },
  { id: 6, name: 'Toba Road', slug: 'toba-road', is_primary: 0, feeder_count: 1 },
];

const categories = [
  { id: 1, name: 'Mobile Phones & Tablets', slug: 'mobiles', icon: 'fa-mobile-screen', ads_count: 142, product_count: 86, children: [
    { id: 11, name: 'Smartphones', slug: 'smartphones', cnt: 98 },
    { id: 12, name: 'Accessories', slug: 'mobile-accessories', cnt: 34 },
    { id: 13, name: 'Tablets & iPads', slug: 'tablets', cnt: 10 }
  ]},
  { id: 2, name: 'Kamalia Khaddar & Fabrics', slug: 'kamalia-khaddar', icon: 'fa-shirt', ads_count: 189, product_count: 120, children: [
    { id: 21, name: 'Classic Khaddar Men', slug: 'khaddar-men', cnt: 78 },
    { id: 22, name: 'Winter Wool Khaddar', slug: 'winter-khaddar', cnt: 45 },
    { id: 23, name: 'Printed Shawls & Suits', slug: 'khaddar-shawls', cnt: 66 }
  ]},
  { id: 3, name: 'Vehicles & Bikes', slug: 'vehicles', icon: 'fa-car', ads_count: 94, product_count: 15, children: [
    { id: 31, name: 'Motorcycles & Scooters', slug: 'motorcycles', cnt: 62 },
    { id: 32, name: 'Cars & Commercial', slug: 'cars', cnt: 24 },
    { id: 33, name: 'Auto Spare Parts', slug: 'auto-parts', cnt: 8 }
  ]},
  { id: 4, name: 'Property & Real Estate', slug: 'property', icon: 'fa-house-chimney', ads_count: 76, product_count: 0, children: [
    { id: 41, name: 'Houses for Sale', slug: 'houses-sale', cnt: 28 },
    { id: 42, name: 'Commercial Plots & Shops', slug: 'commercial-property', cnt: 32 },
    { id: 43, name: 'Agricultural Land', slug: 'agricultural-land', cnt: 16 }
  ]},
  { id: 5, name: 'Electronics & Home Appliances', slug: 'electronics', icon: 'fa-tv', ads_count: 112, product_count: 64, children: [
    { id: 51, name: 'Inverters & Solar Panels', slug: 'solar-inverters', cnt: 42 },
    { id: 52, name: 'Refrigerators & ACs', slug: 'appliances', cnt: 38 },
    { id: 53, name: 'LED TVs & Sound', slug: 'led-tv', cnt: 32 }
  ]},
  { id: 6, name: 'Furniture & Home Decor', slug: 'furniture', icon: 'fa-couch', ads_count: 68, product_count: 45, children: [
    { id: 61, name: 'Sofa Sets & Bedding', slug: 'sofas-beds', cnt: 32 },
    { id: 62, name: 'Dining & Tables', slug: 'dining-tables', cnt: 21 },
    { id: 63, name: 'Wardrobes & Almari', slug: 'wardrobes', cnt: 15 }
  ]},
  { id: 7, name: 'Services & Artisans', slug: 'services', icon: 'fa-screwdriver-wrench', ads_count: 54, product_count: 12, children: [
    { id: 71, name: 'Electricians & AC Repair', slug: 'electricians', cnt: 22 },
    { id: 72, name: 'Tailoring & Khaddar Weaving', slug: 'tailoring', cnt: 18 },
    { id: 73, name: 'Plumbing & Construction', slug: 'plumbing', cnt: 14 }
  ]},
  { id: 8, name: 'Jobs & Employment', slug: 'jobs', icon: 'fa-briefcase', ads_count: 38, product_count: 0, children: [
    { id: 81, name: 'Sales & Marketing', slug: 'sales-jobs', cnt: 16 },
    { id: 82, name: 'Teaching & Tutoring', slug: 'teaching-jobs', cnt: 12 },
    { id: 83, name: 'Drivers & Helpers', slug: 'staff-jobs', cnt: 10 }
  ]},
  { id: 9, name: 'Animals & Livestock', slug: 'livestock', icon: 'fa-paw', ads_count: 48, product_count: 5, children: [
    { id: 91, name: 'Cows & Buffaloes', slug: 'cattle', cnt: 26 },
    { id: 92, name: 'Goats & Sheep', slug: 'goats', cnt: 14 },
    { id: 93, name: 'Birds & Parrots', slug: 'birds', cnt: 8 }
  ]},
  { id: 10, name: 'Agriculture & Crops', slug: 'agriculture', icon: 'fa-seedling', ads_count: 60, product_count: 22, children: [
    { id: 101, name: 'Seeds & Fertilizers', slug: 'seeds-fertilizers', cnt: 24 },
    { id: 102, name: 'Sugarcane & Wheat Crops', slug: 'crops', cnt: 20 },
    { id: 103, name: 'Tractors & Farm Machinery', slug: 'tractors', cnt: 16 }
  ]},
  { id: 11, name: 'Fashion & Jewelry', slug: 'fashion', icon: 'fa-gem', ads_count: 82, product_count: 70, children: [
    { id: 111, name: 'Bridal & Party Wear', slug: 'bridal-wear', cnt: 40 },
    { id: 112, name: 'Footwear & Khussa', slug: 'footwear-khussa', cnt: 28 },
    { id: 113, name: 'Jewelry & Watches', slug: 'jewelry', cnt: 14 }
  ]},
  { id: 12, name: 'Health & Beauty', slug: 'health-beauty', icon: 'fa-heart-pulse', ads_count: 36, product_count: 42, children: [
    { id: 121, name: 'Organic Honey & Oils', slug: 'organic-honey', cnt: 18 },
    { id: 122, name: 'Cosmetics & Skincare', slug: 'cosmetics', cnt: 18 }
  ]}
];

const feeders = [
  { id: 1, name: 'City Feeder 1', area: 'Karkhana Bazar, Ghalla Mandi', city_name: 'Kamalia', status: 'on', started_at: null, expected_at: null, message: 'Continuous grid supply active.', last_update: '2 mins ago' },
  { id: 2, name: 'Railway Feeder', area: 'Railway Road, Model Town', city_name: 'Kamalia', status: 'on', started_at: null, expected_at: null, message: 'Normal voltage stability.', last_update: '5 mins ago' },
  { id: 3, name: 'Pirmahal Feeder', area: 'Chak 341 / Pirmahal Bypass', city_name: 'Kamalia', status: 'maintenance', started_at: '2026-08-31 15:30:00', expected_at: '2026-08-31 18:30:00', message: 'Transformer line upgrade in progress by FESCO team.', last_update: '10 mins ago' },
  { id: 4, name: 'Civil Lines Feeder', area: 'Katchery Road & Hospital Area', city_name: 'Kamalia', status: 'on', started_at: null, expected_at: null, message: 'All transformers running smoothly.', last_update: '1 min ago' },
  { id: 5, name: 'Rural South Feeder', area: 'Chak 713 / Toba Road', city_name: 'Kamalia', status: 'off', started_at: '2026-08-31 16:45:00', expected_at: '2026-08-31 18:00:00', message: 'Tripped due to 11kV line fault. Repair crew on site.', last_update: '15 mins ago' },
  { id: 6, name: 'Industrial Estate Feeder', area: 'Sugar Mill & Weaving Mills', city_name: 'Kamalia', status: 'on', started_at: null, expected_at: null, message: 'Industrial load stable.', last_update: '8 mins ago' }
];

const ads = [
  { id: 1, title: 'Original Handmade Kamalia Pure Khaddar Suit 7M', slug: 'original-kamalia-pure-khaddar-suit', price: 3850, image: 'assets/img/hero-2.jpg', is_featured: 1, is_urgent: 0, condition: 'new', area: 'Karkhana Bazar', city_name: 'Kamalia', created_at: '1 hour ago', negotiable: 0 },
  { id: 2, title: 'Honda CG 125 2024 Model Red Color All Punjab Number', slug: 'honda-cg-125-2024-red', price: 232000, image: 'assets/img/hero-1.jpg', is_featured: 1, is_urgent: 1, condition: 'used', area: 'Railway Road', city_name: 'Kamalia', created_at: '3 hours ago', negotiable: 1 },
  { id: 3, title: 'iPhone 13 128GB PTA Approved 88% Battery 10/10 Condition', slug: 'iphone-13-128gb-pta-approved', price: 168000, image: 'assets/img/hero-3.jpg', is_featured: 0, is_urgent: 0, condition: 'used', area: 'Model Town', city_name: 'Kamalia', created_at: '4 hours ago', negotiable: 1 },
  { id: 4, title: 'Solid Sheesham Wood King Size Double Bed Set with Side Tables', slug: 'solid-sheesham-wood-bed-set', price: 65000, image: 'assets/img/hero-1.jpg', is_featured: 1, is_urgent: 0, condition: 'new', area: 'Pirmahal Road', city_name: 'Kamalia', created_at: '6 hours ago', negotiable: 0 },
  { id: 5, title: 'Inverex 3.2kW Solar Inverter Dual MPPT with 2 Yr Warranty', slug: 'inverex-3-2kw-solar-inverter', price: 118000, image: 'assets/img/hero-2.jpg', is_featured: 0, is_urgent: 0, condition: 'new', area: 'Ghalla Mandi', city_name: 'Kamalia', created_at: '8 hours ago', negotiable: 1 },
  { id: 6, title: '5 Marla Double Story Brand New House for Sale in Model Town', slug: '5-marla-double-story-house-model-town', price: 8500000, image: 'assets/img/hero-3.jpg', is_featured: 1, is_urgent: 1, condition: 'new', area: 'Model Town', city_name: 'Kamalia', created_at: '1 day ago', negotiable: 1 },
];

const products = [
  { id: 101, name: 'Royal Classic Kamalia Winter Khaddar (Unstitched 7 Meters)', slug: 'royal-classic-kamalia-winter-khaddar', price: 4200, sale_price: 3499, image: 'assets/img/hero-2.jpg', is_featured: 1, stock: 45, shop_name: 'Kamalia Khaddar House', shop_slug: 'kamalia-khaddar-house', shop_verified: 1, rating_avg: 4.9, rating_count: 38, city_name: 'Kamalia' },
  { id: 102, name: 'Infinix Hot 40i (8GB RAM / 256GB Storage) Official PTA Warranty', slug: 'infinix-hot-40i-8gb-256gb', price: 43999, sale_price: 39999, image: 'assets/img/hero-3.jpg', is_featured: 1, stock: 12, shop_name: 'Kamalia Mobile Point', shop_slug: 'kamalia-mobile-point', shop_verified: 1, rating_avg: 4.8, rating_count: 24, city_name: 'Kamalia' },
  { id: 103, name: 'Pure Desi Ghee & Organic Honey Pack 1KG 100% Guaranteed', slug: 'pure-desi-ghee-organic-honey-pack', price: 3200, sale_price: 2799, image: 'assets/img/hero-1.jpg', is_featured: 0, stock: 30, shop_name: 'Al-Madina Organic Store', shop_slug: 'al-madina-organic-store', shop_verified: 1, rating_avg: 5.0, rating_count: 19, city_name: 'Kamalia' },
  { id: 104, name: '7-Seater Luxury Velvet Fabric Sofa Set with Marble Center Table', slug: 'luxury-velvet-fabric-sofa-set', price: 88000, sale_price: 79000, image: 'assets/img/hero-1.jpg', is_featured: 1, stock: 4, shop_name: 'Home Decor Furnishers', shop_slug: 'home-decor-furnishers', shop_verified: 1, rating_avg: 4.7, rating_count: 15, city_name: 'Kamalia' },
  { id: 105, name: 'Wireless Bluetooth Earbuds Pro with ENC Noise Cancelling', slug: 'wireless-earbuds-pro-enc', price: 3800, sale_price: 2499, image: 'assets/img/hero-3.jpg', is_featured: 0, stock: 55, shop_name: 'Kamalia Mobile Point', shop_slug: 'kamalia-mobile-point', shop_verified: 1, rating_avg: 4.6, rating_count: 42, city_name: 'Kamalia' },
  { id: 106, name: 'Super Asia Automatic Washing Machine 10KG Heavy Duty', slug: 'super-asia-automatic-washing-machine-10kg', price: 68000, sale_price: 61500, image: 'assets/img/hero-2.jpg', is_featured: 1, stock: 6, shop_name: 'Bismillah Electronics', shop_slug: 'bismillah-electronics', shop_verified: 1, rating_avg: 4.9, rating_count: 28, city_name: 'Kamalia' },
];

const shops = [
  { id: 1, name: 'Kamalia Khaddar House', slug: 'kamalia-khaddar-house', owner_name: 'Haji Muhammad Shafiq', is_verified: 1, is_featured: 1, rating_avg: 4.9, followers_count: 1420, products_count: 48, cover: 'assets/img/hero-2.jpg', logo: 'assets/img/shop-logo.svg' },
  { id: 2, name: 'Kamalia Mobile Point', slug: 'kamalia-mobile-point', owner_name: 'Ahmad Ali', is_verified: 1, is_featured: 1, rating_avg: 4.8, followers_count: 980, products_count: 65, cover: 'assets/img/hero-3.jpg', logo: 'assets/img/shop-logo.svg' },
  { id: 3, name: 'Home Decor Furnishers', slug: 'home-decor-furnishers', owner_name: 'Tariq Mehmood', is_verified: 1, is_featured: 0, rating_avg: 4.7, followers_count: 620, products_count: 32, cover: 'assets/img/hero-1.jpg', logo: 'assets/img/shop-logo.svg' },
  { id: 4, name: 'Bismillah Electronics & Solar', slug: 'bismillah-electronics', owner_name: 'Malik Zafar Iqbal', is_verified: 1, is_featured: 1, rating_avg: 4.9, followers_count: 1150, products_count: 54, cover: 'assets/img/hero-2.jpg', logo: 'assets/img/shop-logo.svg' }
];

const businesses = [
  { id: 1, name: 'Al-Shifa Medical Center & Maternity Hospital', slug: 'al-shifa-medical-center', cat_name: 'Hospitals & Clinics', area: 'Katchery Road', city_name: 'Kamalia', phone: '03001234567', whatsapp: '923001234567', is_verified: 1, rating_avg: 4.8, rating_count: 64, logo: 'assets/img/biz-logo.svg' },
  { id: 2, name: 'Punjab College Kamalia Campus', slug: 'punjab-college-kamalia', cat_name: 'Education & Colleges', area: 'Bypass Road', city_name: 'Kamalia', phone: '03007654321', whatsapp: '923007654321', is_verified: 1, rating_avg: 4.9, rating_count: 112, logo: 'assets/img/biz-logo.svg' },
  { id: 3, name: 'Royal Feast Restaurant & Marriage Hall', slug: 'royal-feast-restaurant', cat_name: 'Restaurants & Catering', area: 'Railway Road', city_name: 'Kamalia', phone: '03019876543', whatsapp: '923019876543', is_verified: 1, rating_avg: 4.7, rating_count: 88, logo: 'assets/img/biz-logo.svg' },
  { id: 4, name: 'Chaudhry Autos & Hybrid Workshop', slug: 'chaudhry-autos-workshop', cat_name: 'Car Mechanics & Electricians', area: 'Chichawatni Road', city_name: 'Kamalia', phone: '03025554433', whatsapp: '923025554433', is_verified: 1, rating_avg: 4.6, rating_count: 45, logo: 'assets/img/biz-logo.svg' }
];

const news = [
  { id: 1, title: 'Kamalia Annual Khaddar & Handloom Festival Dates Announced for Winter Season', slug: 'kamalia-khaddar-festival-announced', excerpt: 'The Kamalia Chamber of Commerce and local weavers guild have confirmed the annual cultural craft exhibition…', published_at: '2026-08-31', image: 'assets/img/hero-2.jpg' },
  { id: 2, title: 'New Grid Substation Upgrade Commences to Eliminate Outages in Toba & Kamalia', slug: 'new-grid-substation-upgrade-commences', excerpt: 'FESCO engineers started the installation of high-capacity transformers today to ensure uninterrupted power supply…', published_at: '2026-08-30', image: 'assets/img/hero-1.jpg' },
  { id: 3, title: 'Local Farmers Market & Grain Bazaar Sees Record Trade Volumes in Kamalia', slug: 'local-farmers-market-record-trade', excerpt: 'Wheat and cotton growers from surrounding chaks gathered at Ghalla Mandi as trading numbers hit all-time high…', published_at: '2026-08-29', image: 'assets/img/hero-3.jpg' }
];

const testimonials = [
  { name: 'Haji Shafiq', business: 'Kamalia Khaddar Weavers', rating: 5, comment: 'eKamalia has revolutionized our local business. We receive direct orders from all over Punjab and Toba Tek Singh with zero middlemen fee!' },
  { name: 'Ahmad Ali', business: 'Kamalia Mobile Point', rating: 5, comment: 'Best local marketplace. The live bijli updates and instant customer chat make selling smartphones so easy and trustworthy.' },
  { name: 'Dr. Tariq Mehmood', business: 'Model Town Resident', rating: 5, comment: 'I check eKamalia every day for electricity updates and buy authentic Khaddar directly from verified Kamalia shops.' }
];

// In-memory cart
let cart = [
  { product_id: 101, name: 'Royal Classic Kamalia Winter Khaddar', slug: 'royal-classic-kamalia-winter-khaddar', unit_price: 3499, quantity: 1, line_total: 3499, image: 'assets/img/hero-2.jpg', shop_name: 'Kamalia Khaddar House', shop_slug: 'kamalia-khaddar-house' }
];

// HTML Layout Generator
function renderLayout(title, content, activeNav = 'home') {
  const cartCount = cart.reduce((sum, it) => sum + it.quantity, 0);

  return `<!doctype html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>${title} — eKamalia | Kamalia Ka Apna Digital Bazaar</title>
<meta name="description" content="eKamalia is the modern digital marketplace of Kamalia. Buy, sell, classified ads, verified shops, business directory, and live electricity updates.">
<meta name="theme-color" content="#0D8244">
<link rel="icon" type="image/png" href="/assets/img/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
<link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<!-- Top Announcement Marquee -->
<div class="ek-marquee-top" role="marquee" aria-label="Announcements">
  <div class="mq-track">
    <span class="mq-item"><i class="fa-solid fa-bullhorn text-warning"></i> <span>Welcome to <b>eKamalia</b> — Kamalia's #1 verified digital marketplace!</span> <span class="opacity-50">•</span></span>
    <span class="mq-item"><i class="fa-solid fa-bolt text-warning"></i> <span>Check live <b>Bijli Updates</b> feeder-by-feeder in real time.</span> <span class="opacity-50">•</span></span>
    <span class="mq-item"><i class="fa-solid fa-gift text-warning"></i> <span>Special Winter Khaddar Sale now live with free local delivery!</span> <span class="opacity-50">•</span></span>
    <span class="mq-item"><i class="fa-solid fa-store text-warning"></i> <span>Open your verified online shop today for free.</span> <span class="opacity-50">•</span></span>
    <span class="mq-item"><i class="fa-solid fa-bullhorn text-warning"></i> <span>Welcome to <b>eKamalia</b> — Kamalia's #1 verified digital marketplace!</span> <span class="opacity-50">•</span></span>
  </div>
</div>

<!-- Main Sticky Header -->
<header class="ek-header">
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <button class="ek-icon-btn d-lg-none" onclick="ekDrawer(true)" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      <a class="ek-logo me-lg-4" href="/" aria-label="eKamalia Home">
        <div class="logo-icon"><i class="fa-solid fa-store"></i></div>
        <span>eKamalia</span>
        <span class="logo-tag d-none d-sm-inline">Kamalia</span>
      </a>

      <!-- Desktop Search Bar -->
      <form class="ek-search-form d-none d-md-flex" action="/search" method="get" role="search">
        <div class="ek-search-wrapper">
          <div class="loc-pill">
            <i class="fa-solid fa-location-dot"></i>
            <select class="loc-select" name="city" aria-label="Location">
              <option value="">All Kamalia</option>
              ${cities.map(c => `<option value="${c.slug}">${c.name}</option>`).join('')}
            </select>
          </div>
          <input type="text" id="globalSearch" name="q" placeholder="Search products, ads, shops, services…" autocomplete="off" aria-label="Search">
          <button type="submit" class="ek-search-btn" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </div>
        <div class="ek-search-suggest" id="searchSuggest"></div>
      </form>

      <!-- Action Buttons -->
      <div class="ek-actions ms-auto">
        <a class="ek-btn-post d-none d-lg-inline-flex" href="/ads/create">
          <i class="fa-solid fa-circle-plus"></i>
          <span>Post Free Ad</span>
        </a>

        <a class="ek-icon-btn" href="/messages" aria-label="Messages" title="Messages">
          <i class="fa-regular fa-comment-dots"></i>
          <span class="ek-badge-dot" id="msgBadge">2</span>
        </a>

        <button class="ek-icon-btn" data-dd="ddNotif" aria-label="Notifications" title="Notifications">
          <i class="fa-regular fa-bell"></i>
          <span class="ek-badge-dot" data-notif-badge>3</span>
        </button>

        <a class="ek-icon-btn" href="/wishlist" aria-label="Wishlist" title="Wishlist">
          <i class="fa-regular fa-heart"></i>
        </a>

        <a class="ek-icon-btn" href="/cart" aria-label="Shopping Cart" title="Cart">
          <i class="fa-solid fa-basket-shopping"></i>
          <span class="ek-badge-dot" data-cart-badge style="display:${cartCount ? 'flex' : 'none'}">${cartCount}</span>
        </a>

        <div class="dropdown">
          <button class="btn p-0 border-0 d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="/assets/img/avatar-default.svg" alt="User" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--ek-primary-light)">
            <span class="d-none d-xl-inline fw-semibold text-dark small">Ahmad</span>
            <i class="fa-solid fa-angle-down text-muted small d-none d-xl-inline"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" style="min-width:240px">
            <div class="px-3 py-2 bg-light rounded-3 mb-2">
              <div class="fw-bold small text-dark">Ahmad Ali</div>
              <div class="text-muted" style="font-size:.75rem">ahmad@kamalia.pk</div>
            </div>
            <a class="dropdown-item rounded-3 py-2" href="/dashboard"><i class="fa-solid fa-gauge-high me-2 text-success"></i>Dashboard</a>
            <a class="dropdown-item rounded-3 py-2" href="/dashboard/orders"><i class="fa-solid fa-box me-2 text-primary"></i>My Orders</a>
            <a class="dropdown-item rounded-3 py-2" href="/dashboard/my-ads"><i class="fa-solid fa-tag me-2 text-warning"></i>My Ads</a>
            <a class="dropdown-item rounded-3 py-2" href="/seller"><i class="fa-solid fa-store me-2 text-success"></i>My Online Store</a>
            <a class="dropdown-item rounded-3 py-2" href="/pos"><i class="fa-solid fa-cash-register me-2 text-info"></i>POS Terminal</a>
            <hr class="my-1">
            <a class="dropdown-item rounded-3 py-2 text-danger" href="/logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Mobile Search Bar -->
  <div class="container d-md-none pb-2 pt-1">
    <form class="ek-search-form" action="/search" method="get" role="search">
      <div class="ek-search-wrapper w-100">
        <input type="text" id="globalSearchM" name="q" placeholder="Search in Kamalia…" aria-label="Search">
        <button type="submit" class="ek-search-btn" aria-label="Search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </div>
    </form>
  </div>

  <!-- Category Nav Scroller (Desktop) -->
  <div class="ek-catnav d-none d-lg-block">
    <div class="container">
      <div class="scroller">
        <a href="/categories" class="${activeNav === 'categories' ? 'active' : ''}"><i class="fa-solid fa-grip text-success"></i> All Categories</a>
        <a href="/bijli" class="${activeNav === 'bijli' ? 'active' : ''}"><i class="fa-solid fa-bolt text-warning"></i> Bijli Updates <span class="cat-live-badge">LIVE</span></a>
        <a href="/shops" class="${activeNav === 'shops' ? 'active' : ''}"><i class="fa-solid fa-store text-primary"></i> Verified Shops</a>
        <a href="/businesses" class="${activeNav === 'businesses' ? 'active' : ''}"><i class="fa-solid fa-map-location-dot text-info"></i> Business Directory</a>
        <a href="/deals" class="${activeNav === 'deals' ? 'active' : ''}"><i class="fa-solid fa-percent text-danger"></i> Special Deals</a>
        ${categories.slice(0, 7).map(c => `<a href="/category/${c.slug}"><i class="fa-solid ${c.icon}"></i> ${c.name}</a>`).join('')}
      </div>
    </div>
  </div>

  <!-- Notifications Dropdown -->
  <div class="ek-dropdown" id="ddNotif">
    <div class="dd-head">
      <span>Notifications (3 unread)</span>
      <a href="/notifications" class="small text-success text-decoration-none">View all</a>
    </div>
    <div class="dd-body" id="notifList">
      <a class="dd-item unread" href="/order/1029">
        <div class="fs-4 text-success"><i class="fa-solid fa-box-check"></i></div>
        <div>
          <div class="fw-bold small">Your order #EK-1029 was dispatched!</div>
          <div class="text-muted" style="font-size:0.72rem">Kamalia Khaddar House • 15 mins ago</div>
        </div>
      </a>
      <a class="dd-item unread" href="/bijli">
        <div class="fs-4 text-warning"><i class="fa-solid fa-bolt"></i></div>
        <div>
          <div class="fw-bold small">Pirmahal Feeder maintenance scheduled</div>
          <div class="text-muted" style="font-size:0.72rem">Expected 4:00 PM - 6:00 PM • 1 hour ago</div>
        </div>
      </a>
      <a class="dd-item" href="/ad/honda-cg-125-2024-red">
        <div class="fs-4 text-info"><i class="fa-solid fa-heart"></i></div>
        <div>
          <div class="fw-bold small">Price dropped on Honda CG 125 in your wishlist</div>
          <div class="text-muted" style="font-size:0.72rem">Save Rs 5,000 • 3 hours ago</div>
        </div>
      </a>
    </div>
  </div>
</header>

<!-- Mobile Slide Drawer -->
<div class="ek-drawer-backdrop" id="ekDrawerBackdrop"></div>
<aside class="ek-drawer" id="ekDrawer" aria-label="Navigation Drawer">
  <div class="dw-head">
    <div class="d-flex justify-content-between align-items-center">
      <span class="ek-logo" style="color:#fff;background:none;-webkit-text-fill-color:#fff">
        <i class="fa-solid fa-store text-warning"></i> eKamalia
      </span>
      <button class="btn text-white p-1" onclick="ekDrawer(false)" aria-label="Close menu">
        <i class="fa-solid fa-xmark fs-5"></i>
      </button>
    </div>
    <div class="d-flex align-items-center gap-3 mt-3 pt-2 border-top border-white border-opacity-25">
      <img src="/assets/img/avatar-default.svg" style="width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid #fff" alt="">
      <div>
        <div class="fw-bold small text-white">Ahmad Ali</div>
        <div style="font-size:.75rem;color:rgba(255,255,255,.85)">0300-1234567 • Kamalia</div>
      </div>
    </div>
  </div>

  <nav class="flex-grow-1 py-2">
    <a class="dw-link" href="/"><i class="fa-solid fa-house text-success"></i> Home</a>
    <a class="dw-link" href="/categories"><i class="fa-solid fa-grip text-primary"></i> All Categories</a>
    <a class="dw-link fw-bold text-success" href="/ads/create"><i class="fa-solid fa-circle-plus text-success"></i> Post Free Ad</a>
    <a class="dw-link" href="/products"><i class="fa-solid fa-bag-shopping text-warning"></i> Shop Products</a>
    <a class="dw-link" href="/ads"><i class="fa-solid fa-tag text-info"></i> Classified Ads</a>
    <a class="dw-link" href="/shops"><i class="fa-solid fa-store text-success"></i> Verified Shops</a>
    <a class="dw-link" href="/businesses"><i class="fa-solid fa-map-location-dot text-danger"></i> Business Directory</a>
    <a class="dw-link" href="/bijli"><i class="fa-solid fa-bolt text-warning"></i> Bijli Updates <span class="badge text-bg-warning ms-auto">LIVE</span></a>
    <a class="dw-link" href="/deals"><i class="fa-solid fa-percent text-danger"></i> Deals & Discounts</a>
    <a class="dw-link" href="/dashboard"><i class="fa-solid fa-gauge-high text-success"></i> User Dashboard</a>
    <hr class="my-2">
    <div class="p-3 d-flex gap-2 align-items-center">
      <span class="small text-muted fw-semibold">Language:</span>
      <button class="chip active">English</button>
      <button class="chip">اردو</button>
    </div>
  </nav>
</aside>

<!-- Main Content Area -->
<main id="main">
  ${content}
</main>

<!-- Footer -->
<footer class="ek-footer pt-5">
  <div class="container">
    <div class="row g-4 pb-4">
      <div class="col-lg-4 col-md-6">
        <div class="ek-logo mb-3" style="color:#FFFFFF">
          <div class="logo-icon" style="box-shadow:none"><i class="fa-solid fa-store"></i></div>
          <span style="color:#FFFFFF;background:none;-webkit-text-fill-color:#FFFFFF">eKamalia</span>
          <span class="logo-tag">Official</span>
        </div>
        <p style="font-size:0.88rem;max-width:340px;color:#94A3B8;line-height:1.65">
          eKamalia is the premier digital marketplace of Kamalia — buy, sell, shop, discover businesses and stay informed with real-time local updates.
        </p>
        <div class="mt-4">
          <a class="social-round" href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a class="social-round" href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a class="social-round" href="#" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
          <a class="social-round" href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          <a class="social-round" href="#" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-6">
        <h6>Quick Links</h6>
        <a href="/products">Shop Products</a><br>
        <a href="/ads">Classified Ads</a><br>
        <a href="/shops">Verified Shops</a><br>
        <a href="/businesses">Business Directory</a><br>
        <a href="/deals">Deals & Coupons</a><br>
        <a href="/categories">All Categories</a>
      </div>

      <div class="col-lg-3 col-6">
        <h6>Kamalia Services</h6>
        <a href="/bijli"><i class="fa-solid fa-bolt me-1 text-warning"></i> Bijli Live Status</a><br>
        <a href="/explore-kamalia">Explore Kamalia City</a><br>
        <a href="/shops/create">Create Online Shop</a><br>
        <a href="/pos/request">POS Terminal Access</a><br>
        <a href="/ads/create">Post Free Classified</a><br>
        <a href="/contact">Support &amp; Helpline</a>
      </div>

      <div class="col-lg-3">
        <h6>Community &amp; Trust</h6>
        <div class="p-3 rounded-3 mb-2" style="background:#143522;border:1px solid rgba(255,255,255,0.06)">
          <div class="small fw-bold text-white mb-1"><i class="fa-solid fa-shield-halved text-success me-1"></i> 100% Kamalia Local</div>
          <div class="text-muted" style="font-size:0.75rem">Supporting local merchants, artisans &amp; farmers across Kamalia and Toba Tek Singh.</div>
        </div>
        <p class="small text-muted mb-0">Helpdesk: <b class="text-white">046-3412345</b><br>Email: support@ekamalia.pk</p>
      </div>
    </div>

    <div class="f-bottom d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
      <span>© 2026 eKamalia. All rights reserved.</span>
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="text-white"><i class="fa-solid fa-location-dot me-1 text-success"></i> Kamalia, Toba Tek Singh, Punjab 🇵🇰</span>
        <div class="d-flex gap-1">
          <button class="chip border-0 bg-dark text-white py-1 px-2" style="font-size:0.75rem">EN</button>
          <button class="chip border-0 bg-dark text-white py-1 px-2" style="font-size:0.75rem">اردو</button>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Mobile Bottom Navigation Bar -->
<nav class="mobile-nav" aria-label="Mobile navigation">
  <a href="/" class="${activeNav === 'home' ? 'active' : ''}">
    <i class="fa-solid fa-house"></i>
    <span>Home</span>
  </a>
  <a href="/categories" class="${activeNav === 'categories' ? 'active' : ''}">
    <i class="fa-solid fa-grip"></i>
    <span>Categories</span>
  </a>
  <a href="/ads/create" class="post-fab" aria-label="Post an Ad" title="Post Free Ad">
    <i class="fa-solid fa-plus"></i>
  </a>
  <a href="/messages" class="${activeNav === 'messages' ? 'active' : ''}">
    <i class="fa-regular fa-comment-dots position-relative">
      <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="font-size:0;width:8px;height:8px"></span>
    </i>
    <span>Messages</span>
  </a>
  <a href="/dashboard" class="${activeNav === 'dashboard' ? 'active' : ''}">
    <i class="fa-regular fa-user"></i>
    <span>Account</span>
  </a>
</nav>

<div class="toast-ek"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>`;
}

// Page Renderers
function renderHomePage() {
  const offCount = feeders.filter(f => f.status !== 'on').length;

  const content = `
  <!-- Hero Section -->
  <section class="ek-hero">
    <div class="swiper heroSwiper">
      <div class="swiper-wrapper">
        <div class="swiper-slide ek-hero-slide" data-anim="kenburns">
          <img class="slide-bg" src="/assets/img/hero-1.jpg" alt="Kamalia Marketplace" fetchpriority="high">
          <div class="slide-veil"></div>
          <div class="slide-content">
            <div class="hero-eyebrow"><i class="fa-solid fa-sparkles"></i> Kamalia's #1 Local Marketplace</div>
            <h2>Kamalia Ka Apna Digital Bazaar 🇵🇰</h2>
            <p>Buy, sell, shop from verified local stores, find jobs and stay updated with real-time electricity status.</p>
            <div class="d-flex gap-3 flex-wrap align-items-center">
              <a class="btn-slide" href="/ads/create">
                <i class="fa-solid fa-circle-plus"></i> Post Free Ad
              </a>
              <a class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold" href="/products">
                <i class="fa-solid fa-store me-1"></i> Explore Shops
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide ek-hero-slide" data-anim="zoom">
          <img class="slide-bg" src="/assets/img/hero-2.jpg" alt="Kamalia Pure Khaddar" fetchpriority="high">
          <div class="slide-veil"></div>
          <div class="slide-content">
            <div class="hero-eyebrow"><i class="fa-solid fa-shirt"></i> World Famous Khaddar</div>
            <h2>Authentic Kamalia Khaddar Direct from Master Weavers</h2>
            <p>Order 100% genuine unstitched handmade winter &amp; classic khaddar with instant cash on delivery.</p>
            <div class="d-flex gap-3 flex-wrap align-items-center">
              <a class="btn-slide" href="/category/kamalia-khaddar">
                <i class="fa-solid fa-bag-shopping"></i> Shop Khaddar
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide ek-hero-slide" data-anim="slide">
          <img class="slide-bg" src="/assets/img/hero-3.jpg" alt="Bijli Updates Live" fetchpriority="high">
          <div class="slide-veil"></div>
          <div class="slide-content">
            <div class="hero-eyebrow"><i class="fa-solid fa-bolt"></i> Live Feeder Monitor</div>
            <h2>Real-Time Feeder Bijli Status &amp; Outage Tracker</h2>
            <p>Know exactly which feeder in Kamalia is ON or OFF with estimated restoration times.</p>
            <div class="d-flex gap-3 flex-wrap align-items-center">
              <a class="btn-slide" href="/bijli">
                <i class="fa-solid fa-gauge-high"></i> Live Feeder Status
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="swiper-pagination"></div>
    </div>
  </section>

  <!-- 10 Quick Action Cards -->
  <section class="ek-section pt-1 pb-3">
    <div class="container">
      <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-md-5">
        <div class="col"><a class="ek-qa-card qa-1" href="/ads/create"><div class="qa-icon-wrap"><i class="fa-solid fa-circle-plus"></i></div><span class="qa-label">Post an Ad</span></a></div>
        <div class="col"><a class="ek-qa-card qa-2" href="/products"><div class="qa-icon-wrap"><i class="fa-solid fa-bag-shopping"></i></div><span class="qa-label">Find Products</span></a></div>
        <div class="col"><a class="ek-qa-card qa-3" href="/shops"><div class="qa-icon-wrap"><i class="fa-solid fa-store"></i></div><span class="qa-label">Find Shops</span></a></div>
        <div class="col"><a class="ek-qa-card qa-4" href="/category/services"><div class="qa-icon-wrap"><i class="fa-solid fa-screwdriver-wrench"></i></div><span class="qa-label">Find Services</span></a></div>
        <div class="col"><a class="ek-qa-card qa-5" href="/category/jobs"><div class="qa-icon-wrap"><i class="fa-solid fa-briefcase"></i></div><span class="qa-label">Find Jobs</span></a></div>
        <div class="col"><a class="ek-qa-card qa-6" href="/category/property"><div class="qa-icon-wrap"><i class="fa-solid fa-house-chimney"></i></div><span class="qa-label">Property</span></a></div>
        <div class="col"><a class="ek-qa-card qa-7" href="/category/vehicles"><div class="qa-icon-wrap"><i class="fa-solid fa-car-side"></i></div><span class="qa-label">Vehicles</span></a></div>
        <div class="col"><a class="ek-qa-card qa-8" href="/category/electronics"><div class="qa-icon-wrap"><i class="fa-solid fa-tv"></i></div><span class="qa-label">Electronics</span></a></div>
        <div class="col"><a class="ek-qa-card qa-9" href="/businesses"><div class="qa-icon-wrap"><i class="fa-solid fa-map-location-dot"></i></div><span class="qa-label">Businesses</span></a></div>
        <div class="col"><a class="ek-qa-card qa-10" href="/explore-kamalia"><div class="qa-icon-wrap"><i class="fa-solid fa-city"></i></div><span class="qa-label">Explore Kamalia</span></a></div>
      </div>
    </div>
  </section>

  <div class="container">
    <!-- Offers Ticker -->
    <section class="mt-2 mb-3">
      <div class="ek-offers-bar" role="marquee" aria-label="Offers">
        <div class="mq-track">
          <span class="mq-item"><i class="fa-solid fa-gift text-warning"></i> <span>Get <b>15% OFF</b> on first Khaddar purchase using code: <b>KAMALIA15</b></span> <span class="opacity-50">•</span></span>
          <span class="mq-item"><i class="fa-solid fa-bolt text-warning"></i> <span>Solar Inverter deals with 2-year doorstep warranty in Kamalia!</span> <span class="opacity-50">•</span></span>
          <span class="mq-item"><i class="fa-solid fa-store text-warning"></i> <span>Free shop registration for all Karkhana Bazar &amp; Railway Road traders!</span> <span class="opacity-50">•</span></span>
        </div>
      </div>
    </section>

    <!-- Browse Categories Section (6 columns on desktop) -->
    <section class="ek-section" id="categories">
      <div class="ek-section-head">
        <div class="head-info">
          <span class="section-pill"><i class="fa-solid fa-layer-group"></i> Marketplace</span>
          <h2>Browse Categories</h2>
          <p class="head-sub">Explore products, classifieds and services available across Kamalia</p>
        </div>
        <a class="ek-link-all" href="/categories">
          <span>All categories</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6">
        ${categories.map(c => `
          <div class="col reveal">
            <a class="ek-cat-card" href="/category/${c.slug}">
              <div class="ic"><i class="fa-solid ${c.icon}"></i></div>
              <div class="nm">${c.name}</div>
              <span class="cat-count">${c.ads_count} listings</span>
            </a>
          </div>
        `).join('')}
      </div>
    </section>

    <!-- Bijli Live Updates Dedicated Section -->
    <section class="ek-bijli-section">
      <div class="ek-section-head mb-3">
        <div class="head-info">
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="section-pill text-warning"><i class="fa-solid fa-bolt"></i> Dedicated Live Service</span>
            ${offCount > 0
              ? `<span class="bijli-live-badge off"><span class="pulse-dot"></span> ${offCount} Feeder${offCount > 1 ? 's' : ''} OFF Now</span>`
              : `<span class="bijli-live-badge on"><span class="pulse-dot"></span> All Feeders Active</span>`
            }
          </div>
          <h2>Bijli Live Updates — Feeder Wise</h2>
          <p class="head-sub">Real-time electricity feeder status for Kamalia city and surrounding areas</p>
        </div>
        <a class="ek-link-all" href="/bijli">
          <span>Live dashboard</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-4">
        ${feeders.slice(0, 4).map(f => {
          const map = {
            on: ['Supply Active', 'fa-lightbulb', 'on'],
            off: ['Power Outage', 'fa-plug-circle-xmark', 'off'],
            maintenance: ['Maintenance', 'fa-screwdriver-wrench', 'maintenance'],
            scheduled: ['Scheduled Cut', 'fa-clock', 'scheduled']
          };
          const [lbl, ic, cls] = map[f.status] || map.on;
          return `
          <div class="col reveal">
            <div class="bijli-card">
              <div class="bijli-orb ${cls}"><i class="fa-solid ${ic}"></i></div>
              <div class="flex-grow-1 min-w-0">
                <div class="fw-bold text-truncate text-dark" style="font-size:0.95rem">${f.name}</div>
                <div class="text-muted text-truncate" style="font-size:0.78rem"><i class="fa-solid fa-location-dot me-1"></i>${f.area}</div>
                <div class="bijli-status ${cls} mt-1">
                  <span>${lbl}</span>
                  ${f.status === 'off' ? `<span class="text-muted fw-normal" style="font-size:0.75rem">— since 35m ago</span>` : ''}
                  ${f.status === 'maintenance' ? `<span class="text-muted fw-normal" style="font-size:0.75rem">— till 6:30 PM</span>` : ''}
                </div>
              </div>
              <div class="text-end flex-shrink-0">
                <div class="text-muted" style="font-size:0.68rem">Updated</div>
                <div class="fw-semibold text-secondary" style="font-size:0.76rem">${f.last_update}</div>
                <a class="btn btn-sm btn-link p-0 mt-1 text-success text-decoration-none" style="font-size:0.72rem" href="/bijli">Details →</a>
              </div>
            </div>
          </div>`;
        }).join('')}
      </div>
    </section>

    <!-- Fresh & Featured Ads Section -->
    <section class="ek-section">
      <div class="ek-section-head">
        <div class="head-info">
          <span class="section-pill"><i class="fa-solid fa-tag"></i> Classifieds</span>
          <h2>Fresh &amp; Featured Classified Ads</h2>
          <p class="head-sub">Buy and sell verified items directly from locals across Kamalia</p>
        </div>
        <a class="ek-link-all" href="/ads">
          <span>View all ads</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      <div class="swiper rowSwiper pb-4">
        <div class="swiper-wrapper">
          ${ads.map(a => `
            <div class="swiper-slide h-auto">
              <article class="ek-item-card reveal in">
                <a class="thumb" href="/ad/${a.slug}">
                  <img src="/${a.image}" alt="${a.title}" loading="lazy">
                  <div class="flags">
                    ${a.is_featured ? `<span class="ek-flag featured"><i class="fa-solid fa-star"></i> Featured</span>` : ''}
                    ${a.is_urgent ? `<span class="ek-flag urgent"><i class="fa-solid fa-fire"></i> Urgent</span>` : ''}
                    ${a.condition === 'new' ? `<span class="ek-flag discount">New</span>` : ''}
                  </div>
                </a>
                <div class="quick">
                  <button class="ek-quick-btn" data-wishlist="ad" data-id="${a.id}" aria-label="Save ad"><i class="fa-solid fa-heart"></i></button>
                  <button class="ek-quick-btn" data-share="/ad/${a.slug}" data-title="${a.title}" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
                </div>
                <div class="body">
                  <a class="title" href="/ad/${a.slug}">${a.title}</a>
                  <div class="ek-price">${money(a.price)} ${a.negotiable ? '<span class="old text-muted fw-normal" style="text-decoration:none;font-size:0.75rem">(Negotiable)</span>' : ''}</div>
                  <div class="meta">
                    <span class="text-truncate" style="max-width:140px"><i class="fa-solid fa-location-dot me-1"></i>${a.area}, ${a.city_name}</span>
                    <span class="ms-auto"><i class="fa-regular fa-clock me-1"></i>${a.created_at}</span>
                  </div>
                </div>
              </article>
            </div>
          `).join('')}
        </div>
        <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section class="ek-section pt-0">
      <div class="ek-section-head">
        <div class="head-info">
          <span class="section-pill"><i class="fa-solid fa-bag-shopping"></i> Shopping</span>
          <h2>Featured Products &amp; Khaddar</h2>
          <p class="head-sub">Direct delivery from Kamalia's top online stores</p>
        </div>
        <a class="ek-link-all" href="/products">
          <span>Shop all</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      <div class="swiper rowSwiper pb-4">
        <div class="swiper-wrapper">
          ${products.map(p => {
            const disc = p.sale_price ? Math.round(100 - (p.sale_price / p.price * 100)) : 0;
            return `
            <div class="swiper-slide h-auto">
              <article class="ek-item-card reveal in">
                <a class="thumb" href="/product/${p.slug}">
                  <img src="/${p.image}" alt="${p.name}" loading="lazy">
                  <div class="flags">
                    ${p.is_featured ? `<span class="ek-flag featured"><i class="fa-solid fa-star"></i> Featured</span>` : ''}
                    ${disc > 0 ? `<span class="ek-flag discount">-${disc}% OFF</span>` : ''}
                  </div>
                </a>
                <div class="quick">
                  <button class="ek-quick-btn" data-wishlist="product" data-id="${p.id}" aria-label="Save"><i class="fa-solid fa-heart"></i></button>
                  <button class="ek-quick-btn" data-share="/product/${p.slug}" data-title="${p.name}" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
                </div>
                <div class="body">
                  <a class="title" href="/product/${p.slug}">${p.name}</a>
                  <div class="ek-price">${money(p.sale_price || p.price)} ${p.sale_price ? `<span class="old">${money(p.price)}</span>` : ''}</div>
                  <div class="shop-row">
                    <a href="/shop/${p.shop_slug}" class="text-truncate text-muted" style="max-width:130px"><i class="fa-solid fa-store me-1"></i>${p.shop_name}</a>
                    ${p.shop_verified ? `<i class="fa-solid fa-circle-check verified-tick" title="Verified Shop"></i>` : ''}
                    <span class="stars ms-auto"><i class="fa-solid fa-star"></i> ${p.rating_avg}</span>
                  </div>
                  <div class="meta">
                    <span class="text-truncate"><i class="fa-solid fa-location-dot me-1"></i>${p.city_name}</span>
                    <button class="btn btn-sm btn-ek rounded-pill px-3 ms-auto d-inline-flex align-items-center gap-1" data-cart-add="${p.id}">
                      <i class="fa-solid fa-cart-plus"></i> <span style="font-size:0.75rem">Add</span>
                    </button>
                  </div>
                </div>
              </article>
            </div>`;
          }).join('')}
        </div>
        <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
      </div>
    </section>

    <!-- Featured Shops Section -->
    <section class="ek-section pt-0">
      <div class="ek-section-head">
        <div class="head-info">
          <span class="section-pill"><i class="fa-solid fa-store"></i> Verified Merchants</span>
          <h2>Featured Kamalia Shops</h2>
          <p class="head-sub">Browse top-rated stores and local brands in town</p>
        </div>
        <a class="ek-link-all" href="/shops">
          <span>All shops</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      <div class="swiper shopSwiper pb-4">
        <div class="swiper-wrapper">
          ${shops.map(s => `
            <div class="swiper-slide h-auto">
              <article class="ek-shop-card reveal in">
                <div class="cover"><img src="/${s.cover}" alt=""></div>
                <img class="logo" src="/${s.logo}" alt="${s.name}">
                <div class="body">
                  <a class="fw-bold text-truncate d-block text-dark mb-1" href="/shop/${s.slug}" style="font-size:0.98rem">
                    ${s.name} ${s.is_verified ? `<i class="fa-solid fa-circle-check verified-tick" title="Verified Store"></i>` : ''}
                  </a>
                  <div class="text-muted small mb-2 text-truncate"><i class="fa-solid fa-user me-1"></i>Owner: ${s.owner_name}</div>
                  <div class="stats">
                    <span><b>${s.products_count}</b> Prods</span>
                    <span><i class="fa-solid fa-star text-warning"></i> <b>${s.rating_avg}</b></span>
                    <span><b>${s.followers_count}</b> Followers</span>
                  </div>
                  <div class="d-flex gap-2 justify-content-center">
                    <a class="btn btn-ek btn-sm rounded-pill px-3 flex-fill" href="/shop/${s.slug}">Visit Store</a>
                    <button class="btn btn-sm btn-outline-dark rounded-pill px-3 flex-fill" data-follow="${s.id}"><i class="fa-solid fa-plus me-1"></i>Follow</button>
                  </div>
                </div>
              </article>
            </div>
          `).join('')}
        </div>
        <div class="swiper-button-next"></div><div class="swiper-button-prev"></div>
      </div>
    </section>

    <!-- Business Directory Section -->
    <section class="ek-section pt-0">
      <div class="ek-section-head">
        <div class="head-info">
          <span class="section-pill"><i class="fa-solid fa-map-location-dot"></i> Directory</span>
          <h2>Kamalia Business Directory</h2>
          <p class="head-sub">Find local clinics, colleges, mechanics, restaurants and legal services</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <a class="ek-link-all" href="/businesses">Full directory <i class="fa-solid fa-arrow-right"></i></a>
          <a class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" href="/businesses/create"><i class="fa-solid fa-plus me-1"></i>List Business</a>
        </div>
      </div>
      <div class="row g-3 row-cols-1 row-cols-md-2">
        ${businesses.map(b => `
          <div class="col">
            <article class="ek-biz-card reveal in">
              <img class="logo" src="/${b.logo}" alt="${b.name}">
              <div class="flex-grow-1 min-w-0">
                <a class="fw-bold d-block text-truncate text-dark" href="/business/${b.slug}" style="font-size:0.96rem">
                  ${b.name} ${b.is_verified ? `<i class="fa-solid fa-circle-check verified-tick"></i>` : ''}
                </a>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-1" style="font-size:0.78rem;color:var(--ek-text-muted)">
                  <span class="badge text-bg-light fw-semibold text-secondary"><i class="fa-solid fa-briefcase me-1 text-success"></i>${b.cat_name}</span>
                  <span class="text-truncate"><i class="fa-solid fa-location-dot me-1 text-muted"></i>${b.area}, ${b.city_name}</span>
                </div>
                <div class="stars mt-2">
                  <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                  <span class="text-muted ms-1" style="font-size:0.75rem">(${b.rating_count})</span>
                </div>
              </div>
              <div class="d-flex flex-column gap-2 flex-shrink-0">
                <a class="btn btn-sm btn-ek rounded-pill px-3" href="tel:${b.phone}" title="Call Now"><i class="fa-solid fa-phone"></i></a>
                <a class="btn btn-sm btn-outline-success rounded-pill px-3" target="_blank" rel="noopener" href="https://wa.me/${b.whatsapp}" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
              </div>
            </article>
          </div>
        `).join('')}
      </div>
    </section>

    <!-- News & Community Section -->
    <section class="ek-section pt-0">
      <div class="ek-section-head">
        <div class="head-info">
          <span class="section-pill"><i class="fa-solid fa-newspaper"></i> News</span>
          <h2>Kamalia News &amp; Updates</h2>
          <p class="head-sub">Community news, bazaar updates and city developments</p>
        </div>
        <a class="ek-link-all" href="/news">
          <span>All news</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      <div class="row g-4 row-cols-1 row-cols-md-3">
        ${news.map(n => `
          <div class="col reveal">
            <a class="ek-card d-block h-100 overflow-hidden text-decoration-none" href="/news/${n.slug}">
              <img src="/${n.image}" class="w-100" style="height:180px;object-fit:cover" alt="${n.title}">
              <div class="p-3">
                <div class="text-muted small mb-1"><i class="fa-regular fa-clock me-1 text-success"></i>${n.published_at}</div>
                <div class="fw-bold text-dark mb-2" style="font-size:0.96rem;line-height:1.4">${n.title}</div>
                <div class="text-muted" style="font-size:0.84rem;line-height:1.5">${n.excerpt}</div>
              </div>
            </a>
          </div>
        `).join('')}
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="ek-section pt-0">
      <div class="ek-section-head">
        <div class="head-info">
          <span class="section-pill"><i class="fa-solid fa-comments"></i> Community</span>
          <h2>What Kamalia Says</h2>
          <p class="head-sub">Trusted by shop owners, buyers and citizens</p>
        </div>
      </div>
      <div class="row g-3 row-cols-1 row-cols-md-3">
        ${testimonials.map(t => `
          <div class="col reveal">
            <div class="ek-card p-4 h-100 d-flex flex-column">
              <div class="stars mb-3"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
              <p class="mb-3 flex-grow-1" style="font-size:0.92rem;color:var(--ek-text-secondary);line-height:1.6">“${t.comment}”</p>
              <div class="d-flex align-items-center gap-3 pt-2 border-top">
                <img src="/assets/img/avatar-default.svg" style="width:42px;height:42px;border-radius:50%;object-fit:cover" alt="">
                <div>
                  <div class="fw-bold text-dark small">${t.name}</div>
                  <div class="text-muted" style="font-size:0.75rem">${t.business}</div>
                </div>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
    </section>

    <!-- High-Conversion Bottom CTA Banner -->
    <section class="ek-section pt-0 mb-4">
      <div class="rounded-4 p-4 p-md-5 text-center text-white position-relative overflow-hidden" style="background:var(--ek-gradient-primary);box-shadow:var(--ek-shadow-lg)">
        <h2 class="fw-bold text-white mb-2" style="font-size:clamp(1.5rem, 3vw, 2.25rem)">Apni Dukaan, Apna Bazaar 🇵🇰</h2>
        <p class="mb-4 mx-auto" style="max-width:620px;opacity:0.92;font-size:1.02rem">
          Join 1,800+ Kamalia members. Post classified ads for free, launch your verified online shop, and grow your local commerce reach.
        </p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
          <a href="/ads/create" class="btn btn-gold rounded-pill px-4 py-2">
            <i class="fa-solid fa-plus me-1"></i> Post Free Ad
          </a>
          <a href="/shops/create" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-success">
            <i class="fa-solid fa-store me-1"></i> Create Your Shop
          </a>
          <a href="/pos/request" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">
            <i class="fa-solid fa-cash-register me-1"></i> Premium POS
          </a>
        </div>
        <div class="d-flex gap-4 justify-content-center flex-wrap mt-4 pt-3 border-top border-white border-opacity-25 small">
          <span><b class="ek-counter fs-6 text-warning" data-count="340">0</b>+ Live Ads</span>
          <span><b class="ek-counter fs-6 text-warning" data-count="1450">0</b>+ Products</span>
          <span><b class="ek-counter fs-6 text-warning" data-count="92">0</b>+ Verified Shops</span>
          <span><b class="ek-counter fs-6 text-warning" data-count="1850">0</b>+ Local Members</span>
        </div>
      </div>
    </section>
  </div>`;

  return renderLayout('Home — Kamalia Ka Apna Digital Bazaar', content, 'home');
}

// All Categories Page
function renderCategoriesPage() {
  const content = `
  <div class="container py-4">
    <nav aria-label="breadcrumb" class="ek-breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Home</a></li>
        <li class="breadcrumb-item active">All Categories</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 fw-bold mb-1">Browse All Categories</h1>
        <p class="text-muted mb-0">Discover products, vehicles, properties, services and jobs across Kamalia &amp; Toba Tek Singh.</p>
      </div>
      <a href="/ads/create" class="btn btn-ek rounded-pill px-4">
        <i class="fa-solid fa-plus me-1"></i> Post Free Ad
      </a>
    </div>

    <div class="row g-4">
      ${categories.map(c => `
      <div class="col-md-6 col-lg-4">
        <div class="ek-card p-4 h-100 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="ic" style="width:52px;height:52px;border-radius:16px;background:var(--ek-primary-light);color:var(--ek-primary);font-size:1.4rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fa-solid ${c.icon}"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
              <a class="fw-bold text-dark d-block text-truncate text-decoration-none" href="/category/${c.slug}" style="font-size:1.05rem">
                ${c.name}
              </a>
              <div class="text-muted" style="font-size:0.78rem">
                <span class="text-success fw-semibold">${c.product_count} products</span> • ${c.ads_count} ads
              </div>
            </div>
          </div>

          ${c.children ? `
          <div class="d-flex flex-wrap gap-1 mt-auto pt-3 border-top">
            ${c.children.map(ch => `
              <a class="chip" href="/category/${ch.slug}">
                ${ch.name}
                <span class="opacity-75 ms-1" style="font-size:0.72rem">(${ch.cnt})</span>
              </a>
            `).join('')}
          </div>` : ''}
        </div>
      </div>
      `).join('')}
    </div>
  </div>`;

  return renderLayout('All Categories', content, 'categories');
}

// Bijli Dashboard Page
function renderBijliPage() {
  const offCount = feeders.filter(f => f.status !== 'on').length;

  const content = `
  <div class="container py-4">
    <div class="text-center mb-4">
      <div class="bijli-live-badge on mb-2">
        <span class="pulse-dot"></span> LIVE • Monitored by eKamalia Team
      </div>
      <h1 class="h3 fw-bold mb-1">⚡ Bijli Live Updates — Feeder Wise</h1>
      <p class="text-muted mb-0 mx-auto" style="max-width:580px">Real-time electricity supply status, load shedding schedules &amp; outage reporting for Kamalia &amp; surroundings</p>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="ek-card p-3 d-flex align-items-center gap-3">
          <div class="bijli-orb off"><i class="fa-solid fa-plug-circle-xmark"></i></div>
          <div>
            <div class="fs-4 fw-bold text-danger">${offCount}</div>
            <div class="text-muted small fw-semibold">Feeders OFF Right Now</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="ek-card p-3 d-flex align-items-center gap-3">
          <div class="bijli-orb on"><i class="fa-solid fa-lightbulb"></i></div>
          <div>
            <div class="fs-4 fw-bold text-success">${feeders.length - offCount}</div>
            <div class="text-muted small fw-semibold">Feeders ON &amp; Active</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="ek-card p-3 d-flex align-items-center gap-3">
          <div class="bijli-orb scheduled"><i class="fa-solid fa-tower-broadcast"></i></div>
          <div>
            <div class="fs-4 fw-bold text-primary">${feeders.length}</div>
            <div class="text-muted small fw-semibold">Monitored Sub-stations</div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
      <a class="chip active" href="/bijli">All Areas</a>
      ${cities.map(c => `<a class="chip" href="/bijli?city=${c.slug}"><i class="fa-solid fa-location-dot me-1"></i>${c.name} (${c.feeder_count})</a>`).join('')}
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
          <i class="fa-solid fa-gauge-high text-warning"></i> Real-time Feeder Status
        </h5>
        <div id="bijliLive" class="d-flex flex-column gap-2">
          ${feeders.map(f => {
            const map = {
              on: ['Supply Active', 'fa-lightbulb', 'on'],
              off: ['Power Outage', 'fa-plug-circle-xmark', 'off'],
              maintenance: ['Maintenance', 'fa-screwdriver-wrench', 'maintenance'],
              scheduled: ['Scheduled Cut', 'fa-clock', 'scheduled']
            };
            const [lbl, ic, cls] = map[f.status] || map.on;
            return `
            <div class="bijli-card">
              <div class="bijli-orb ${cls}"><i class="fa-solid ${ic}"></i></div>
              <div class="flex-grow-1 min-w-0">
                <div class="fw-bold text-truncate text-dark" style="font-size:0.95rem">${f.name}</div>
                <div class="text-muted text-truncate" style="font-size:0.78rem"><i class="fa-solid fa-location-dot me-1"></i>${f.area}</div>
                <div class="bijli-status ${cls} mt-1">
                  <span>${lbl}</span>
                  ${f.status === 'off' ? `<span class="text-muted fw-normal" style="font-size:0.75rem">— since 35m ago</span>` : ''}
                  ${f.status === 'maintenance' ? `<span class="text-muted fw-normal" style="font-size:0.75rem">— expected back 6:30 PM</span>` : ''}
                </div>
                <div class="text-muted mt-1" style="font-size:0.75rem;line-height:1.4">${f.message}</div>
              </div>
              <div class="text-end flex-shrink-0">
                <div class="text-muted" style="font-size:0.68rem">Updated</div>
                <div class="fw-semibold text-secondary" style="font-size:0.76rem">${f.last_update}</div>
              </div>
            </div>`;
          }).join('')}
        </div>
        <div class="text-muted small mt-2 d-flex align-items-center gap-1">
          <i class="fa-solid fa-rotate text-success"></i> Auto-refreshes every 60 seconds
        </div>

        <div class="ek-card p-4 mt-4">
          <h5 class="fw-bold mb-2 text-danger"><i class="fa-solid fa-bullhorn me-1"></i> Report an Outage</h5>
          <p class="text-muted small mb-3">Notice an unannounced outage in your area? Report it to notify the community.</p>
          <form id="bijliReportForm" onsubmit="event.preventDefault(); toast('Report submitted! Thank you for helping Kamalia.'); this.reset();">
            <div class="row g-2">
              <div class="col-md-5">
                <select class="form-select" required>
                  <option value="">Select Feeder</option>
                  ${feeders.map(f => `<option value="${f.id}">${f.name}</option>`).join('')}
                </select>
              </div>
              <div class="col-md-7">
                <input class="form-control" placeholder="e.g. Bijli gayi hai 20 min se — Chak 341" required maxlength="300">
              </div>
            </div>
            <button class="btn btn-danger rounded-pill px-4 mt-3 btn-sm fw-semibold">
              <i class="fa-solid fa-paper-plane me-1"></i> Send Outage Report
            </button>
          </form>
        </div>
      </div>

      <div class="col-lg-5">
        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
          <i class="fa-regular fa-clock text-primary"></i> Latest Outage Log &amp; News
        </h5>
        <div class="ek-card p-3" style="max-height:640px;overflow-y:auto">
          <div class="d-flex gap-2 py-3 border-bottom">
            <span class="badge text-bg-warning align-self-start py-2 px-2" style="min-width:82px;font-size:0.75rem">
              <i class="fa-solid fa-screwdriver-wrench me-1"></i>MAINTENANCE
            </span>
            <div class="min-w-0">
              <div class="fw-semibold small text-dark">Pirmahal Feeder <span class="text-muted fw-normal">• Kamalia</span></div>
              <div class="small fw-semibold mt-1 text-secondary">Transformer upgrade &amp; line maintenance</div>
              <div class="text-muted mt-1" style="font-size:0.7rem"><i class="fa-regular fa-clock me-1"></i>Today 3:30 PM • Expected back 6:30 PM</div>
            </div>
          </div>
          <div class="d-flex gap-2 py-3 border-bottom">
            <span class="badge text-bg-danger align-self-start py-2 px-2" style="min-width:82px;font-size:0.75rem">
              <i class="fa-solid fa-plug-circle-xmark me-1"></i>OUTAGE
            </span>
            <div class="min-w-0">
              <div class="fw-semibold small text-dark">Rural South Feeder <span class="text-muted fw-normal">• Chak 713</span></div>
              <div class="small fw-semibold mt-1 text-secondary">11kV line trip reported</div>
              <div class="text-muted mt-1" style="font-size:0.7rem"><i class="fa-regular fa-clock me-1"></i>Today 4:45 PM • Expected back 6:00 PM</div>
            </div>
          </div>
          <div class="d-flex gap-2 py-3">
            <span class="badge text-bg-success align-self-start py-2 px-2" style="min-width:82px;font-size:0.75rem">
              <i class="fa-solid fa-lightbulb me-1"></i>RESTORED
            </span>
            <div class="min-w-0">
              <div class="fw-semibold small text-dark">City Feeder 1 <span class="text-muted fw-normal">• Karkhana Bazar</span></div>
              <div class="small fw-semibold mt-1 text-secondary">Power fully restored to all shops</div>
              <div class="text-muted mt-1" style="font-size:0.7rem"><i class="fa-regular fa-clock me-1"></i>Today 1:15 PM</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>`;

  return renderLayout('Bijli Live Updates', content, 'bijli');
}

// Shopping Cart Page
function renderCartPage() {
  const subtotal = cart.reduce((sum, it) => sum + it.line_total, 0);
  const total = subtotal;

  const content = `
  <div class="container py-4">
    <h1 class="h4 fw-bold mb-4 d-flex align-items-center gap-2">
      <i class="fa-solid fa-basket-shopping text-success"></i> Shopping Cart
    </h1>

    ${cart.length === 0 ? `
      <div class="ek-empty">
        <div class="empty-icon"><i class="fa-solid fa-cart-shopping"></i></div>
        <h5>Your cart is currently empty</h5>
        <p>Discover thousands of quality products from top Kamalia stores and sellers.</p>
        <a class="btn btn-ek rounded-pill px-4 py-2" href="/products">
          <i class="fa-solid fa-bag-shopping me-1"></i> Start Shopping
        </a>
      </div>
    ` : `
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="ek-card p-4 mb-3">
          <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-store text-success fs-5"></i>
              <a class="fw-bold text-dark text-decoration-none" href="/shop/kamalia-khaddar-house">Kamalia Khaddar House</a>
            </div>
            <span class="badge text-bg-light text-muted fw-normal" style="font-size:0.75rem">Verified Store</span>
          </div>

          ${cart.map(it => `
          <div class="cart-line" data-cart-row="${it.product_id}">
            <img src="/${it.image}" alt="${it.name}">
            <div class="flex-grow-1 min-w-0">
              <a class="small fw-bold d-block text-truncate text-dark text-decoration-none mb-1" href="/product/${it.slug}">${it.name}</a>
              <div class="text-muted small mb-2">
                ${money(it.unit_price)} × ${it.quantity} = <b class="text-success fw-bold">${money(it.line_total)}</b>
              </div>
              <div class="d-flex align-items-center gap-2">
                <button class="qty-btn" onclick="toast('Cart updated')">−</button>
                <input class="ci-qty form-control form-control-sm text-center" style="width:52px;font-weight:700" value="${it.quantity}" readonly aria-label="Quantity">
                <button class="qty-btn" onclick="toast('Cart updated')">+</button>
                <button class="btn btn-link btn-sm text-danger p-0 ms-3 text-decoration-none" onclick="toast('Item removed'); setTimeout(()=>location.reload(),400)">
                  <i class="fa-regular fa-trash-can me-1"></i> Remove
                </button>
              </div>
            </div>
          </div>
          `).join('')}

          <div class="d-flex justify-content-between pt-3 small">
            <span class="text-muted">Subtotal (this store):</span>
            <b class="text-dark fs-6">${money(subtotal)}</b>
          </div>
        </div>

        <div class="p-3 bg-light rounded-3 text-muted small d-flex align-items-center gap-2 border">
          <i class="fa-solid fa-circle-info text-success fs-5"></i>
          <span>Items are packed directly by respective Kamalia merchants with doorstep cash on delivery.</span>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="ek-card p-4 position-sticky" style="top:90px">
          <h5 class="fw-bold mb-3">Order Summary</h5>
          <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Subtotal</span>
            <b>${money(subtotal)}</b>
          </div>
          <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Delivery Charges</span>
            <b class="text-success">FREE</b>
          </div>

          <hr class="my-3">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold text-dark">Grand Total</span>
            <span class="fw-bold fs-4 text-success">${money(total)}</span>
          </div>

          <div class="input-group mb-3">
            <input class="form-control" placeholder="Promo code (e.g. KAMALIA15)">
            <button class="btn btn-outline-success" onclick="toast('Coupon applied! -15% saved.')">Apply</button>
          </div>

          <a class="btn btn-ek btn-lg w-100 rounded-pill mt-2 d-flex align-items-center justify-content-center gap-2" href="/checkout">
            <span>Proceed to Checkout</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
    `}
  </div>`;

  return renderLayout('Shopping Cart', content, 'cart');
}

// HTTP Server
const server = http.createServer((req, res) => {
  const parsed = url.parse(req.url, true);
  const pathname = parsed.pathname;

  // Static Assets Handler
  if (pathname.startsWith('/assets/') || pathname.startsWith('/uploads/') || pathname === '/favicon.ico' || pathname === '/manifest.json') {
    let filePath = path.join(ROOT, pathname);
    if (pathname === '/favicon.ico') filePath = path.join(ROOT, 'assets/img/favicon.svg');
    if (pathname === '/manifest.json') filePath = path.join(ROOT, 'manifest.json');

    fs.readFile(filePath, (err, data) => {
      if (err) {
        res.writeHead(404, { 'Content-Type': 'text/plain' });
        res.end('File not found');
        return;
      }
      const ext = path.extname(filePath).toLowerCase();
      const map = {
        '.css': 'text/css; charset=utf-8',
        '.js': 'application/javascript; charset=utf-8',
        '.json': 'application/json; charset=utf-8',
        '.png': 'image/png',
        '.jpg': 'image/jpeg',
        '.jpeg': 'image/jpeg',
        '.svg': 'image/svg+xml',
        '.webp': 'image/webp',
        '.ico': 'image/x-icon'
      };
      res.writeHead(200, { 'Content-Type': map[ext] || 'application/octet-stream' });
      res.end(data);
    });
    return;
  }

  // API Suggestions
  if (pathname === '/api/search/suggest') {
    const q = (parsed.query.q || '').toLowerCase();
    const results = [];
    products.forEach(p => {
      if (p.name.toLowerCase().includes(q)) {
        results.push({ title: p.name, subtitle: `${money(p.sale_price || p.price)} • ${p.shop_name}`, type: 'Product', url: `/product/${p.slug}`, image: `/${p.image}` });
      }
    });
    ads.forEach(a => {
      if (a.title.toLowerCase().includes(q)) {
        results.push({ title: a.title, subtitle: `${money(a.price)} • ${a.area}`, type: 'Ad', url: `/ad/${a.slug}`, image: `/${a.image}` });
      }
    });
    shops.forEach(s => {
      if (s.name.toLowerCase().includes(q)) {
        results.push({ title: s.name, subtitle: `Verified Shop • ${s.owner_name}`, type: 'Shop', url: `/shop/${s.slug}`, image: `/${s.logo}` });
      }
    });
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, results: results.slice(0, 6) }));
    return;
  }

  // API Actions
  if (pathname === '/cart/add' && req.method === 'POST') {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      try {
        const d = JSON.parse(body);
        const pid = Number(d.product_id);
        const prod = products.find(x => x.id === pid) || products[0];
        const existing = cart.find(x => x.product_id === pid);
        if (existing) {
          existing.quantity += Number(d.quantity || 1);
          existing.line_total = existing.quantity * existing.unit_price;
        } else {
          cart.push({
            product_id: prod.id,
            name: prod.name,
            slug: prod.slug,
            unit_price: prod.sale_price || prod.price,
            quantity: Number(d.quantity || 1),
            line_total: (prod.sale_price || prod.price) * Number(d.quantity || 1),
            image: prod.image,
            shop_name: prod.shop_name,
            shop_slug: prod.shop_slug
          });
        }
        const cartCount = cart.reduce((sum, it) => sum + it.quantity, 0);
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, message: 'Added to cart!', cart_count: cartCount }));
      } catch (e) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, message: 'Added to cart!', cart_count: 2 }));
      }
    });
    return;
  }

  if (pathname === '/api/wishlist/toggle' && req.method === 'POST') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, added: true }));
    return;
  }

  if (pathname === '/api/like/toggle' && req.method === 'POST') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, liked: true, count: 24 }));
    return;
  }

  if (pathname === '/api/follow/toggle' && req.method === 'POST') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, following: true }));
    return;
  }

  // HTML Routing
  res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });

  if (pathname === '/' || pathname === '/index.php') {
    res.end(renderHomePage());
  } else if (pathname === '/categories') {
    res.end(renderCategoriesPage());
  } else if (pathname === '/bijli') {
    res.end(renderBijliPage());
  } else if (pathname === '/cart') {
    res.end(renderCartPage());
  } else if (pathname === '/ads' || pathname.startsWith('/category/')) {
    // Classifieds listing
    const catSlug = pathname.replace('/category/', '');
    const foundCat = categories.find(c => c.slug === catSlug) || { name: 'Classified Ads' };
    const content = `
    <div class="container py-4">
      <nav aria-label="breadcrumb" class="ek-breadcrumb">
        <ol class="breadcrumb"><li class="breadcrumb-item"><a href="/">Home</a></li><li class="breadcrumb-item active">${foundCat.name}</li></ol>
      </nav>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 class="h4 fw-bold mb-1">${foundCat.name} in Kamalia</h1>
          <p class="text-muted small mb-0">Buy and sell verified items directly from locals in Kamalia &amp; nearby areas</p>
        </div>
        <a class="btn btn-ek rounded-pill px-4" href="/ads/create"><i class="fa-solid fa-plus me-1"></i> Post Free Ad</a>
      </div>
      <div class="row g-4">
        <aside class="col-lg-3">
          <div class="ek-card p-4 filter-card">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2"><i class="fa-solid fa-filter text-success"></i> Filter Ads</h6>
            <div class="mb-3"><label class="form-label small fw-semibold text-muted">Keyword</label><input type="text" class="form-control" placeholder="e.g. iPhone, Civic"></div>
            <div class="mb-3"><label class="form-label small fw-semibold text-muted">City / Area</label>
              <select class="form-select"><option>All Areas</option>${cities.map(c => `<option>${c.name}</option>`).join('')}</select>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6"><label class="form-label small fw-semibold text-muted">Min Rs</label><input type="number" class="form-control" placeholder="0"></div>
              <div class="col-6"><label class="form-label small fw-semibold text-muted">Max Rs</label><input type="number" class="form-control" placeholder="Any"></div>
            </div>
            <button class="btn btn-ek w-100 rounded-pill py-2" onclick="toast('Filters applied')">Apply Filters</button>
          </div>
        </aside>
        <div class="col-lg-9">
          <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-3">
            ${ads.map(a => `
              <div class="col">
                <article class="ek-item-card reveal in">
                  <a class="thumb" href="/ad/${a.slug}">
                    <img src="/${a.image}" alt="${a.title}">
                    <div class="flags">
                      ${a.is_featured ? `<span class="ek-flag featured"><i class="fa-solid fa-star"></i> Featured</span>` : ''}
                      ${a.is_urgent ? `<span class="ek-flag urgent">Urgent</span>` : ''}
                    </div>
                  </a>
                  <div class="quick">
                    <button class="ek-quick-btn" data-wishlist="ad" data-id="${a.id}"><i class="fa-solid fa-heart"></i></button>
                    <button class="ek-quick-btn" data-share="/ad/${a.slug}"><i class="fa-solid fa-share-nodes"></i></button>
                  </div>
                  <div class="body">
                    <a class="title" href="/ad/${a.slug}">${a.title}</a>
                    <div class="ek-price">${money(a.price)}</div>
                    <div class="meta">
                      <span><i class="fa-solid fa-location-dot me-1"></i>${a.area}</span>
                      <span class="ms-auto"><i class="fa-regular fa-clock me-1"></i>${a.created_at}</span>
                    </div>
                  </div>
                </article>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    </div>`;
    res.end(renderLayout(foundCat.name, content, 'ads'));
  } else if (pathname.startsWith('/ad/')) {
    const slug = pathname.replace('/ad/', '');
    const ad = ads.find(a => a.slug === slug) || ads[0];
    const content = `
    <div class="container py-4">
      <nav aria-label="breadcrumb" class="ek-breadcrumb">
        <ol class="breadcrumb"><li class="breadcrumb-item"><a href="/">Home</a></li><li class="breadcrumb-item"><a href="/ads">Ads</a></li><li class="breadcrumb-item active text-truncate" style="max-width:240px">${ad.title}</li></ol>
      </nav>
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="gallery-main mb-3">
            <img src="/${ad.image}" alt="${ad.title}">
          </div>
          <div class="ek-card p-4 mb-4">
            <div class="d-flex gap-2 mb-2">
              ${ad.is_featured ? `<span class="ek-flag featured"><i class="fa-solid fa-star"></i> FEATURED</span>` : ''}
              ${ad.is_urgent ? `<span class="ek-flag urgent">URGENT</span>` : ''}
            </div>
            <h1 class="h4 fw-bold mb-2">${ad.title}</h1>
            <div class="d-flex flex-wrap align-items-center gap-3 text-muted small pb-3 border-bottom mb-3">
              <span><i class="fa-regular fa-clock me-1 text-success"></i>Posted ${ad.created_at}</span>
              <span><i class="fa-solid fa-location-dot me-1 text-danger"></i>${ad.area}, ${ad.city_name}</span>
              <span><i class="fa-solid fa-eye me-1 text-primary"></i>342 views</span>
            </div>
            <h5 class="fw-bold mb-2">Description</h5>
            <p style="line-height:1.8;color:var(--ek-text-secondary)">
              Genuine product in excellent pristine condition. Available for instant inspection in ${ad.area}, Kamalia. 100% verified seller with clean record. Contact directly on WhatsApp or Call for urgent purchase.
            </p>
            <div class="row g-3 pt-3 border-top mt-2">
              <div class="col-sm-6"><div class="spec-row"><span class="k">Condition</span><b>${ad.condition === 'new' ? 'Brand New' : 'Gently Used'}</b></div></div>
              <div class="col-sm-6"><div class="spec-row"><span class="k">Seller Type</span><b>Individual Owner</b></div></div>
              <div class="col-sm-6"><div class="spec-row"><span class="k">Delivery</span><b>Available in Kamalia</b></div></div>
              <div class="col-sm-6"><div class="spec-row"><span class="k">Price Term</span><b>${ad.negotiable ? 'Negotiable' : 'Fixed Price'}</b></div></div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="ek-card p-4 mb-4">
            <div class="price-tag mb-1">${money(ad.price)}</div>
            <div class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1 text-success"></i>${ad.area}, ${ad.city_name}</div>
            <div class="d-grid gap-2">
              <a class="btn btn-ek rounded-pill py-2" href="tel:03001234567"><i class="fa-solid fa-phone me-2"></i>Call Seller (0300-1234567)</a>
              <a class="btn btn-success rounded-pill py-2" target="_blank" href="https://wa.me/923001234567"><i class="fa-brands fa-whatsapp me-2"></i>Chat on WhatsApp</a>
              <button class="btn btn-outline-success rounded-pill py-2" onclick="toast('Starting chat with seller…')"><i class="fa-regular fa-comment-dots me-2"></i>Send Message</button>
            </div>
            <hr class="my-3">
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm flex-fill rounded-pill" data-wishlist="ad" data-id="${ad.id}"><i class="fa-solid fa-heart me-1"></i>Save</button>
              <button class="btn btn-outline-secondary btn-sm flex-fill rounded-pill" data-share="/ad/${ad.slug}"><i class="fa-solid fa-share-nodes me-1"></i>Share</button>
            </div>
          </div>

          <div class="ek-card p-4 mb-4">
            <h6 class="fw-bold mb-3"><i class="fa-regular fa-user me-1 text-success"></i> Seller Details</h6>
            <div class="d-flex align-items-center gap-3 mb-2">
              <img src="/assets/img/avatar-default.svg" style="width:46px;height:46px;border-radius:50%" alt="">
              <div>
                <div class="fw-bold text-dark">Ahmad Ali <i class="fa-solid fa-circle-check verified-tick"></i></div>
                <div class="text-muted small">Member since Jan 2025 • 8 active ads</div>
              </div>
            </div>
          </div>

          <div class="ek-card p-3 bg-light border">
            <div class="text-dark small fw-bold mb-2"><i class="fa-solid fa-shield-halved text-success me-1"></i> Kamalia Buyer Safety Tips</div>
            <ul class="small text-muted ps-3 mb-0" style="line-height:1.7">
              <li>Meet seller in a public bazaar area</li>
              <li>Inspect physical condition before payment</li>
              <li>Never pay advance bank transfer to unknown callers</li>
            </ul>
          </div>
        </div>
      </div>
    </div>`;
    res.end(renderLayout(ad.title, content, 'ads'));
  } else if (pathname === '/products') {
    // Products page
    const content = `
    <div class="container py-4">
      <nav aria-label="breadcrumb" class="ek-breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="/">Home</a></li><li class="breadcrumb-item active">Shop Products</li></ol></nav>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 class="h4 fw-bold mb-1">Products &amp; Khaddar Store</h1>
          <p class="text-muted small mb-0">Shop online from verified local retailers and master artisans in Kamalia</p>
        </div>
        <a class="btn btn-outline-secondary rounded-pill btn-sm px-3" href="/compare"><i class="fa-solid fa-scale-balanced me-1 text-success"></i> Compare</a>
      </div>
      <div class="row g-4">
        <aside class="col-lg-3">
          <div class="ek-card p-4 filter-card">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2"><i class="fa-solid fa-filter text-success"></i> Filter Catalog</h6>
            <div class="mb-3"><label class="form-label small fw-semibold text-muted">Keyword</label><input type="text" class="form-control" placeholder="Search product name…"></div>
            <div class="mb-3"><label class="form-label small fw-semibold text-muted">Sort By</label>
              <select class="form-select"><option>Newest First</option><option>Price: Low to High</option><option>Best Rated</option></select>
            </div>
            <button class="btn btn-ek w-100 rounded-pill py-2" onclick="toast('Filters applied')">Filter Products</button>
          </div>
        </aside>
        <div class="col-lg-9">
          <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-3">
            ${products.map(p => `
              <div class="col">
                <article class="ek-item-card reveal in">
                  <a class="thumb" href="/product/${p.slug}">
                    <img src="/${p.image}" alt="${p.name}">
                    <div class="flags">
                      ${p.is_featured ? `<span class="ek-flag featured"><i class="fa-solid fa-star"></i> Featured</span>` : ''}
                      ${p.sale_price ? `<span class="ek-flag discount">Sale</span>` : ''}
                    </div>
                  </a>
                  <div class="quick">
                    <button class="ek-quick-btn" data-wishlist="product" data-id="${p.id}"><i class="fa-solid fa-heart"></i></button>
                    <button class="ek-quick-btn" data-share="/product/${p.slug}"><i class="fa-solid fa-share-nodes"></i></button>
                  </div>
                  <div class="body">
                    <a class="title" href="/product/${p.slug}">${p.name}</a>
                    <div class="ek-price">${money(p.sale_price || p.price)} ${p.sale_price ? `<span class="old">${money(p.price)}</span>` : ''}</div>
                    <div class="shop-row">
                      <a href="/shop/${p.shop_slug}" class="text-truncate text-muted" style="max-width:130px"><i class="fa-solid fa-store me-1"></i>${p.shop_name}</a>
                      <span class="stars ms-auto"><i class="fa-solid fa-star"></i> ${p.rating_avg}</span>
                    </div>
                    <div class="meta">
                      <span class="text-truncate"><i class="fa-solid fa-location-dot me-1"></i>${p.city_name}</span>
                      <button class="btn btn-sm btn-ek rounded-pill px-3 ms-auto" data-cart-add="${p.id}">
                        <i class="fa-solid fa-cart-plus"></i> <span style="font-size:0.75rem">Add</span>
                      </button>
                    </div>
                  </div>
                </article>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    </div>`;
    res.end(renderLayout('Products & Online Stores', content, 'products'));
  } else if (pathname.startsWith('/product/')) {
    const slug = pathname.replace('/product/', '');
    const p = products.find(x => x.slug === slug) || products[0];
    const content = `
    <div class="container py-4">
      <nav aria-label="breadcrumb" class="ek-breadcrumb">
        <ol class="breadcrumb"><li class="breadcrumb-item"><a href="/">Home</a></li><li class="breadcrumb-item"><a href="/products">Products</a></li><li class="breadcrumb-item active text-truncate" style="max-width:240px">${p.name}</li></ol>
      </nav>
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="gallery-main mb-3 position-relative">
            <img src="/${p.image}" alt="${p.name}">
            <span class="ek-flag discount position-absolute top-0 start-0 m-3" style="font-size:0.82rem">-20% OFF</span>
          </div>
          <div class="ek-card p-4">
            <h5 class="fw-bold mb-3">Product Specifications &amp; Quality Guarantee</h5>
            <p style="line-height:1.8;color:var(--ek-text-secondary)">
              Premium grade product crafted in Kamalia. 100% authentic with verified shop warranty and 7-day hassle-free return policy across Punjab. Cash on delivery available.
            </p>
            <div class="row g-2 pt-3 border-top">
              <div class="col-sm-6"><div class="spec-row"><span class="k">Shop / Brand</span><b>${p.shop_name}</b></div></div>
              <div class="col-sm-6"><div class="spec-row"><span class="k">Stock Availability</span><b class="text-success">${p.stock} units left</b></div></div>
              <div class="col-sm-6"><div class="spec-row"><span class="k">Delivery Time</span><b>1 - 2 Working Days</b></div></div>
              <div class="col-sm-6"><div class="spec-row"><span class="k">Shipping Fee</span><b class="text-success">FREE</b></div></div>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="ek-card p-4 mb-4">
            <h1 class="h4 fw-bold mb-2">${p.name}</h1>
            <div class="d-flex align-items-center gap-2 mb-3">
              <span class="stars"><i class="fa-solid fa-star"></i> ${p.rating_avg}</span>
              <span class="text-muted small">(${p.rating_count} verified reviews)</span>
            </div>
            <div class="price-tag mb-2">${money(p.sale_price || p.price)} ${p.sale_price ? `<span class="old fs-6 text-muted">${money(p.price)}</span>` : ''}</div>
            <div class="text-success fw-semibold small mb-3"><i class="fa-solid fa-circle-check me-1"></i> In Stock &amp; Ready to Ship</div>
            <div class="d-grid gap-2">
              <button class="btn btn-ek btn-lg rounded-pill" data-cart-add="${p.id}"><i class="fa-solid fa-cart-plus me-2"></i> Add to Cart</button>
              <a class="btn btn-gold btn-lg rounded-pill" href="/checkout">⚡ Instant Buy Now</a>
            </div>
            <hr class="my-3">
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm flex-fill rounded-pill" data-wishlist="product" data-id="${p.id}"><i class="fa-solid fa-heart me-1"></i> Wishlist</button>
              <button class="btn btn-outline-secondary btn-sm flex-fill rounded-pill" data-share="/product/${p.slug}"><i class="fa-solid fa-share-nodes me-1"></i> Share</button>
            </div>
          </div>

          <div class="ek-card p-4">
            <div class="d-flex align-items-center gap-3">
              <img src="/${shops[0].logo}" style="width:54px;height:54px;border-radius:14px" alt="">
              <div class="flex-grow-1">
                <a class="fw-bold text-dark d-block text-decoration-none" href="/shop/${p.shop_slug}">${p.shop_name} <i class="fa-solid fa-circle-check verified-tick"></i></a>
                <div class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i>${p.city_name}</div>
              </div>
              <a class="btn btn-ek btn-sm rounded-pill px-3" href="/shop/${p.shop_slug}">Visit Store</a>
            </div>
          </div>
        </div>
      </div>
    </div>`;
    res.end(renderLayout(p.name, content, 'products'));
  } else if (pathname === '/shops') {
    // Shops Directory
    const content = `
    <div class="container py-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-store me-2 text-success"></i>Verified Online Shops in Kamalia</h1>
          <p class="text-muted small mb-0">Browse trusted local shops and order directly with home delivery in Kamalia</p>
        </div>
        <a class="btn btn-ek rounded-pill px-4" href="/shops/create"><i class="fa-solid fa-plus me-1"></i> Create Your Shop — Free</a>
      </div>
      <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-4">
        ${shops.map(s => `
          <div class="col">
            <article class="ek-shop-card reveal in">
              <div class="cover"><img src="/${s.cover}" alt=""></div>
              <img class="logo" src="/${s.logo}" alt="${s.name}">
              <div class="body">
                <a class="fw-bold text-truncate d-block text-dark mb-1" href="/shop/${s.slug}" style="font-size:0.98rem">
                  ${s.name} ${s.is_verified ? `<i class="fa-solid fa-circle-check verified-tick"></i>` : ''}
                </a>
                <div class="text-muted small mb-2 text-truncate"><i class="fa-solid fa-user me-1"></i>Owner: ${s.owner_name}</div>
                <div class="stats">
                  <span><b>${s.products_count}</b> Prods</span>
                  <span><i class="fa-solid fa-star text-warning"></i> <b>${s.rating_avg}</b></span>
                  <span><b>${s.followers_count}</b> Followers</span>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                  <a class="btn btn-ek btn-sm rounded-pill px-3 flex-fill" href="/shop/${s.slug}">Visit Store</a>
                  <button class="btn btn-sm btn-outline-dark rounded-pill px-3 flex-fill" data-follow="${s.id}"><i class="fa-solid fa-plus me-1"></i>Follow</button>
                </div>
              </div>
            </article>
          </div>
        `).join('')}
      </div>
    </div>`;
    res.end(renderLayout('Verified Online Shops', content, 'shops'));
  } else if (pathname.startsWith('/shop/')) {
    const slug = pathname.replace('/shop/', '');
    const s = shops.find(x => x.slug === slug) || shops[0];
    const content = `
    <div class="container py-4">
      <div class="ek-card overflow-hidden mb-4">
        <div style="height:180px;background:var(--ek-gradient-hero);position:relative">
          <img src="/${s.cover}" class="w-100 h-100" style="object-fit:cover;opacity:0.6" alt="">
        </div>
        <div class="p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-3">
            <img src="/${s.logo}" style="width:72px;height:72px;border-radius:20px;border:3px solid #fff;margin-top:-36px;box-shadow:var(--ek-shadow-sm)" alt="">
            <div>
              <h2 class="h4 fw-bold mb-1">${s.name} ${s.is_verified ? `<i class="fa-solid fa-circle-check verified-tick"></i>` : ''}</h2>
              <div class="text-muted small">Owner: ${s.owner_name} • Karkhana Bazar, Kamalia</div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-success rounded-pill px-4" data-follow="${s.id}"><i class="fa-solid fa-plus me-1"></i> Follow Store</button>
            <a class="btn btn-success rounded-pill px-4" target="_blank" href="https://wa.me/923001234567"><i class="fa-brands fa-whatsapp me-1"></i> WhatsApp</a>
          </div>
        </div>
      </div>
      <h5 class="fw-bold mb-3">Store Products &amp; Catalog</h5>
      <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-xl-4">
        ${products.map(p => `
          <div class="col">
            <article class="ek-item-card reveal in">
              <a class="thumb" href="/product/${p.slug}"><img src="/${p.image}" alt="${p.name}"></a>
              <div class="body">
                <a class="title" href="/product/${p.slug}">${p.name}</a>
                <div class="ek-price">${money(p.sale_price || p.price)}</div>
                <div class="meta">
                  <span>In Stock</span>
                  <button class="btn btn-sm btn-ek rounded-pill px-3 ms-auto" data-cart-add="${p.id}"><i class="fa-solid fa-cart-plus"></i></button>
                </div>
              </div>
            </article>
          </div>
        `).join('')}
      </div>
    </div>`;
    res.end(renderLayout(s.name, content, 'shops'));
  } else if (pathname === '/businesses') {
    // Businesses directory
    const content = `
    <div class="container py-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 class="h4 fw-bold mb-1"><i class="fa-solid fa-map-location-dot me-2 text-success"></i>Kamalia Business Directory</h1>
          <p class="text-muted small mb-0">Doctors, schools, restaurants, workshops, artisans &amp; commercial services in Kamalia</p>
        </div>
        <a class="btn btn-ek rounded-pill px-4" href="/businesses/create"><i class="fa-solid fa-plus me-1"></i> List Your Business Free</a>
      </div>
      <div class="row g-3 row-cols-1 row-cols-md-2">
        ${businesses.map(b => `
          <div class="col">
            <article class="ek-biz-card reveal in">
              <img class="logo" src="/${b.logo}" alt="${b.name}">
              <div class="flex-grow-1 min-w-0">
                <a class="fw-bold d-block text-truncate text-dark" href="/business/${b.slug}" style="font-size:0.96rem">
                  ${b.name} ${b.is_verified ? `<i class="fa-solid fa-circle-check verified-tick"></i>` : ''}
                </a>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-1" style="font-size:0.78rem;color:var(--ek-text-muted)">
                  <span class="badge text-bg-light fw-semibold text-secondary"><i class="fa-solid fa-briefcase me-1 text-success"></i>${b.cat_name}</span>
                  <span class="text-truncate"><i class="fa-solid fa-location-dot me-1 text-muted"></i>${b.area}, ${b.city_name}</span>
                </div>
                <div class="stars mt-2">
                  <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                  <span class="text-muted ms-1" style="font-size:0.75rem">(${b.rating_count})</span>
                </div>
              </div>
              <div class="d-flex flex-column gap-2 flex-shrink-0">
                <a class="btn btn-sm btn-ek rounded-pill px-3" href="tel:${b.phone}" title="Call Now"><i class="fa-solid fa-phone"></i></a>
                <a class="btn btn-sm btn-outline-success rounded-pill px-3" target="_blank" rel="noopener" href="https://wa.me/${b.whatsapp}" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
              </div>
            </article>
          </div>
        `).join('')}
      </div>
    </div>`;
    res.end(renderLayout('Kamalia Business Directory', content, 'businesses'));
  } else if (pathname === '/search') {
    const q = (parsed.query.q || '').trim();
    const content = `
    <div class="container py-4">
      <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Search eKamalia Marketplace</h1>
        <p class="text-muted small">Search classified ads, online stores, products, services and local businesses</p>
      </div>
      <form class="row g-2 mb-4" method="get">
        <div class="col-md-5"><input class="form-control form-control-lg" name="q" value="${q}" placeholder="Search anything in Kamalia…"></div>
        <div class="col-md-3"><select class="form-select form-select-lg"><option>All Categories</option></select></div>
        <div class="col-md-2"><select class="form-select form-select-lg"><option>All Areas</option></select></div>
        <div class="col-md-2"><button class="btn btn-ek btn-lg w-100 rounded-pill"><i class="fa-solid fa-magnifying-glass"></i> Search</button></div>
      </form>
      <div class="mb-4">
        <h5 class="fw-bold mb-3">Matching Products &amp; Ads (${products.length + ads.length} results)</h5>
        <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-4">
          ${products.map(p => `
            <div class="col">
              <article class="ek-item-card reveal in">
                <a class="thumb" href="/product/${p.slug}"><img src="/${p.image}" alt="${p.name}"></a>
                <div class="body">
                  <a class="title" href="/product/${p.slug}">${p.name}</a>
                  <div class="ek-price">${money(p.sale_price || p.price)}</div>
                  <div class="meta"><span>${p.shop_name}</span><button class="btn btn-sm btn-ek rounded-pill px-3 ms-auto" data-cart-add="${p.id}"><i class="fa-solid fa-cart-plus"></i></button></div>
                </div>
              </article>
            </div>
          `).join('')}
        </div>
      </div>
    </div>`;
    res.end(renderLayout(`Search Results: "${q}"`, content, 'search'));
  } else if (pathname === '/checkout') {
    const subtotal = cart.reduce((sum, it) => sum + it.line_total, 0);
    const content = `
    <div class="container py-4">
      <h1 class="h4 fw-bold mb-4">Checkout &amp; Delivery Details</h1>
      <div class="row g-4">
        <div class="col-lg-7">
          <form class="ek-card p-4" onsubmit="event.preventDefault(); toast('Order placed successfully! Order ID: #EK-1030'); setTimeout(()=>location.href='/', 1200);">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-location-dot text-success me-1"></i> Delivery Address in Kamalia</h6>
            <div class="row g-3">
              <div class="col-12"><label class="form-label small fw-semibold text-muted">Full Name *</label><input class="form-control" value="Ahmad Ali" required></div>
              <div class="col-md-6"><label class="form-label small fw-semibold text-muted">Phone Number *</label><input class="form-control" value="0300-1234567" required></div>
              <div class="col-md-6"><label class="form-label small fw-semibold text-muted">Area / Mohallah *</label>
                <select class="form-select"><option>Karkhana Bazar, Kamalia</option><option>Railway Road, Kamalia</option><option>Model Town, Kamalia</option><option>Chak 341, Kamalia</option></select>
              </div>
              <div class="col-12"><label class="form-label small fw-semibold text-muted">Complete Street Address *</label><textarea class="form-control" rows="2" required>House 12, Street 4, Near Jamia Masjid</textarea></div>
            </div>
            <hr class="my-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-credit-card text-success me-1"></i> Payment Method</h6>
            <div class="p-3 border rounded-3 mb-2 d-flex align-items-center gap-3">
              <input type="radio" name="pay" checked id="cod">
              <label for="cod" class="fw-bold mb-0">Cash on Delivery (COD) <span class="badge text-bg-success ms-2">Recommended</span></label>
            </div>
            <div class="p-3 border rounded-3 mb-4 d-flex align-items-center gap-3">
              <input type="radio" name="pay" id="jazz">
              <label for="jazz" class="fw-bold mb-0">JazzCash / EasyPaisa / Bank Transfer</label>
            </div>
            <button class="btn btn-ek btn-lg w-100 rounded-pill">Place Order (Total: ${money(subtotal)})</button>
          </form>
        </div>
        <div class="col-lg-5">
          <div class="ek-card p-4">
            <h6 class="fw-bold mb-3">Order Items (${cart.length})</h6>
            ${cart.map(it => `
              <div class="d-flex justify-content-between small py-2 border-bottom">
                <div><b>${it.name}</b> <span class="text-muted">× ${it.quantity}</span></div>
                <b>${money(it.line_total)}</b>
              </div>
            `).join('')}
            <div class="d-flex justify-content-between pt-3"><span class="fw-bold">Total Payable:</span><b class="fs-5 text-success">${money(subtotal)}</b></div>
          </div>
        </div>
      </div>
    </div>`;
    res.end(renderLayout('Checkout', content, 'checkout'));
  } else if (pathname === '/login' || pathname === '/register') {
    const isReg = pathname === '/register';
    const content = `
    <div class="container py-5" style="max-width:460px">
      <div class="text-center mb-4">
        <a class="ek-logo mb-2" href="/"><div class="logo-icon"><i class="fa-solid fa-store"></i></div><span>eKamalia</span></a>
        <h1 class="h4 mt-3 fw-bold text-dark">${isReg ? 'Create Free Account' : 'Login to eKamalia'}</h1>
        <p class="text-muted small">${isReg ? 'Join Kamalia\'s verified local community — 100% free' : 'Welcome back! Manage your ads, orders and online shop'}</p>
      </div>
      <form class="ek-card p-4 shadow-sm" onsubmit="event.preventDefault(); toast('Logged in successfully!'); setTimeout(()=>location.href='/dashboard',700);">
        ${isReg ? `<div class="mb-3"><label class="form-label small fw-semibold text-muted">Full Name</label><input class="form-control" value="Ahmad Ali" required></div>` : ''}
        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Email or Mobile Number</label><input class="form-control" value="ahmad@kamalia.pk" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Password</label><input type="password" class="form-control" value="password123" required></div>
        <button class="btn btn-ek w-100 btn-lg rounded-pill mt-2">${isReg ? 'Create Account' : 'Login'}</button>
        <div class="text-center mt-3 pt-3 border-top small text-muted">
          ${isReg ? `Already have an account? <a href="/login" class="text-success fw-bold text-decoration-none">Login</a>` : `Don't have an account? <a href="/register" class="text-success fw-bold text-decoration-none">Register Free</a>`}
        </div>
      </form>
    </div>`;
    res.end(renderLayout(isReg ? 'Register' : 'Login', content, 'account'));
  } else if (pathname === '/dashboard') {
    const content = `
    <div class="container py-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
          <img src="/assets/img/avatar-default.svg" style="width:56px;height:56px;border-radius:50%" alt="">
          <div>
            <h1 class="h4 fw-bold mb-0">Assalam-o-Alaikum, Ahmad Ali!</h1>
            <div class="text-muted small">Member since Jan 2025 • Kamalia Local</div>
          </div>
        </div>
        <a class="btn btn-ek rounded-pill px-4" href="/ads/create"><i class="fa-solid fa-plus me-1"></i> Post New Ad</a>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="ek-card p-3"><div class="text-muted small">My Active Ads</div><div class="fs-4 fw-bold text-success">3</div></div></div>
        <div class="col-md-3"><div class="ek-card p-3"><div class="text-muted small">My Orders</div><div class="fs-4 fw-bold text-primary">5</div></div></div>
        <div class="col-md-3"><div class="ek-card p-3"><div class="text-muted small">Unread Messages</div><div class="fs-4 fw-bold text-warning">2</div></div></div>
        <div class="col-md-3"><div class="ek-card p-3"><div class="text-muted small">Saved in Wishlist</div><div class="fs-4 fw-bold text-danger">4</div></div></div>
      </div>
      <div class="ek-card p-4">
        <h5 class="fw-bold mb-3">Recent Account Activity</h5>
        <div class="py-2 border-bottom d-flex justify-content-between align-items-center">
          <div><b>Order #EK-1029</b> dispatched by Kamalia Khaddar House</div>
          <span class="badge text-bg-success">Dispatched</span>
        </div>
        <div class="py-2 border-bottom d-flex justify-content-between align-items-center">
          <div><b>Ad Published:</b> Honda CG 125 2024 Red Model</div>
          <span class="badge text-bg-primary">Active</span>
        </div>
      </div>
    </div>`;
    res.end(renderLayout('User Dashboard', content, 'dashboard'));
  } else {
    // 404 page
    const content = `
    <div class="container py-5 text-center">
      <div class="ek-empty">
        <div class="empty-icon"><i class="fa-solid fa-compass"></i></div>
        <h1 class="h3 fw-bold">Page Not Found (404)</h1>
        <p>The page you requested could not be found or has been moved.</p>
        <a class="btn btn-ek rounded-pill px-4" href="/"><i class="fa-solid fa-house me-1"></i> Back to Homepage</a>
      </div>
    </div>`;
    res.end(renderLayout('Page Not Found', content, 'home'));
  }
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`eKamalia Preview Server is running on http://0.0.0.0:${PORT}`);
});
