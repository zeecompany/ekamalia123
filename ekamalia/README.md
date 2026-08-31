# eKamalia — Kamalia Ka Apna Digital Bazaar 🇵🇰

**eKamalia** (ekamalia.com) is a complete, production-ready Pakistani marketplace platform built for **Kamalia, District Toba Tek Singh, Punjab** — classified ads, a multi-shop product marketplace, a business directory, premium POS billing, and feeder-wise **Bijli Updates** (electricity on/off news) in one platform.

- **Stack:** PHP 8.x (no framework, PDO prepared statements everywhere) + MySQL/MariaDB (utf8mb4), Bootstrap 5, vanilla JS + Fetch, Chart.js, Font Awesome 6, Swiper 11
- **Languages:** English + اردو (full RTL stylesheet + language switcher)
- **PWA-ready:** manifest, offline page, service worker (static shell caching)
- **Design:** original green & gold brand system — not a copy of OLX/Daraz

---

## Modules

| Area | What's included |
|---|---|
| **Homepage** | Admin hero slider, announcement + offers marquee, quick actions, category cards, featured products/ads/shops, business directory preview, live Bijli strip, deals, news, testimonials, newsletter/contact |
| **Classified ads** | Full CRUD, images, featured/urgent promotions (admin-approved), pause / resume / mark-sold / renew, city + category filters, inquiries via chat |
| **Marketplace** | Products with full specs (SKU, barcode, variants, warranty, returns, shipping, tax), moderation workflow draft → pending → published → out of stock → hidden → archived, compare, recently viewed |
| **Shops** | Public shop pages, free shop registration with admin approval, verification badges, featured shops, packages with limits |
| **Seller dashboard** | Products, shop orders (12-status lifecycle), coupons, reviews with replies, analytics (charts), shop settings, POS entry |
| **Buyer dashboard** | Orders + public tracking, wishlist, following, saved searches, addresses, activity (comments/reviews), notifications, profile, security (login history) |
| **Cart & checkout** | Multi-shop cart split into per-shop sub-orders, coupons, per-shop delivery fees, COD + bank transfer (reference + receipt upload + admin verification) |
| **Orders** | Group orders `EK-xxxxxx` → sub-orders `EK-xxxxxx-01…`, status timeline, buyer cancel/received/refund-request, automatic restock on cancel/return, notifications both sides |
| **Chat** | WhatsApp-style buyer↔seller/shop threads, polling, images, product references, read receipts, block & report |
| **Reviews & social** | Verified-purchase reviews, seller replies, like/comment/share (nested replies), report + moderation queue |
| **Premium POS** | Request → admin approval; terminal (barcode/search/hold-resume/quotations), split payments, invoices A4 + 80mm thermal, returns, purchases & suppliers, customers with ledgers, expenses, stock movements, staff roles (Owner/Manager/Cashier/Inventory/Sales), reports + CSV export |
| **Bijli Updates** ⚡ | Feeder-wise electricity status for **Kamalia & Toba Tek Singh** — admin posts shutdown/restore updates, citizens report outages, auto notifications to reporters |
| **Admin panel** (at `/admin`) | KPIs + charts, users/shops/catalog/orders/payments moderation, businesses, categories & brands, reviews/comments/reports, contact inbox with email reply, sliders/marquee/advertisements (with CTR), CMS pages, news, testimonials, homepage builder, packages, POS approvals, bank accounts, Bijli manager, settings (SMTP, OTP, features, commerce, SEO, maintenance), audit logs, system health, database backups (gzip download) |
| **Platform** | OTP email verification (optional), rate limiting, CSRF everywhere, soft deletes, audit log, SEO slugs + sitemap.xml + OG tags, error pages, reduced-motion support, responsive 320–1920px + mobile bottom nav |

---

## Security model

- All SQL uses **PDO prepared statements**; all output is escaped.
- **CSRF tokens** on every form and AJAX write; secure session cookies.
- Passwords hashed (bcrypt); OTP codes hashed, single-use, rate-limited.
- DB credentials live **only** in `config/config.php` (written by the installer). `app/`, `config/`, `database/`, `storage/`, `resources/`, `routes/` are blocked by `.htaccess`.
- Uploads directory blocks PHP execution.
- The installer **locks itself** (`storage/install.lock`) after success. To reinstall, an admin with file-manager access must delete that file — there is no web way back in.
- No errors are shown to visitors (logged to `storage/logs/` instead).
- Admin creates their own password at install time — there is **no default/hard-coded admin password**.
- No fake orders, customers or payment confirmations are ever seeded into a production install. The optional demo data set is clearly labelled `(Demo)` and can be deleted from the admin panel.

---

## Documentation

- **[INSTALL.md](INSTALL.md)** — step-by-step installation guide (shared hosting & local), including the 5-step web installer, SMTP setup, and troubleshooting.
- `routes/web.php` — the full route map (~200 routes).
- `database/schema.sql` / `database/seed.php` — normalized schema (60+ tables, FKs, indexes) and the default seed (settings, cities, categories, packages, pages, feeders).

## Project structure

```
ekamalia/
├── index.php              # front controller
├── router.php             # PHP built-in server router (dev only)
├── .htaccess              # Apache rewrite + security rules
├── app/
│   ├── init.php           # bootstrap: config, session, autoload
│   ├── helpers.php        # global helpers (db, auth, csrf, upload, mail…)
│   ├── router.php         # tiny regex router
│   ├── Controllers/       # feature controllers (+ Admin/ sub-namespace)
│   └── Services/          # Cart, Mailer
├── assets/                # css / js / img (original branding)
├── config/config.php      # created by installer — DO NOT COMMIT/PUBLISH
├── database/              # schema.sql, seed.php, demo.php (optional)
├── install/index.php      # 5-step web installer (self-locking)
├── resources/
│   ├── lang/              # en.php, ur.php
│   └── views/             # layouts, partials, pages (plain PHP templates)
├── routes/web.php         # all routes
├── storage/               # logs, cache, backups, install.lock
└── uploads/               # user media (no PHP executes here)
```

## Local development

```bash
# requires PHP 8.1+ with pdo_mysql, gd, mbstring, curl
mysql -u root -e "CREATE DATABASE ekamalia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

php -S localhost:8080 router.php
# open http://localhost:8080/install and follow the wizard
```

On Apache/Hostinger just upload the folder to `public_html` — `.htaccess` handles everything (no `router.php` needed).

---

Made with ❤️ for Kamalia — *Kamalia Ka Apna Digital Bazaar.*
