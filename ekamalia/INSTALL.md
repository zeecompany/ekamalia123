# eKamalia — Installation Guide

This guide covers installing eKamalia on **shared hosting (Hostinger/cPanel)** and on a **local machine**. The whole process takes about 5 minutes.

> ⚠️ The installer is **self-locking**: after a successful install, `storage/install.lock` is created and `/install` refuses to run again. Reinstalling requires deleting that file from your hosting file manager — a deliberate security measure.

---

## 1. Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.1+ (8.2/8.4 recommended) with `pdo_mysql`, `gd`, `mbstring`, `curl`, `openssl` |
| Database | MySQL 5.7+ / MariaDB 10.4+ |
| Web server | Apache (`.htaccess` included) or Nginx |
| Disk | Writable `storage/`, `uploads/`, `config/` folders |

The installer's **Step 1 (Requirements)** checks all of this for you automatically.

---

## 2. Shared hosting (Hostinger / cPanel)

### 2.1 Upload the files
1. Upload the `ekamalia` folder contents into **`public_html/`** (via File Manager → Upload ZIP → Extract, or FTP).
2. Make sure the **dotfile** `.htaccess` was extracted too (hidden files are easy to miss).
3. Keep the folder structure exactly as shipped — `app/`, `config/`, `storage/`, `uploads/` etc. sit beside `index.php`.

### 2.2 Create the database
1. In hPanel/cPanel open **MySQL Databases**.
2. Create a database, e.g. `u107154643_kml`.
3. Create a user (e.g. `u107154643_kml1`) with a strong password and **assign it to the database with ALL PRIVILEGES**.
4. Note the host — on Hostinger it is `localhost`; on some hosts it is a hostname like `mysql.hostinger.com` or `SERVERIP`.

> The installer ships with the project owner's production database values **pre-filled in Step 2**. If you are deploying for the owner, simply click *Test Connection*. Otherwise replace them with your own.

### 2.3 Run the web installer
Open **`https://yourdomain.com/install`** and follow the 5 steps:

| Step | What happens | What to do |
|---|---|---|
| **1. Requirements** | Checks PHP version, extensions, folder permissions | All rows must be green ✔ |
| **2. Database** | Writes `config/config.php` | Enter host / DB name / user / password → **Test Connection** |
| **3. Migrate & Seed** | Creates 60+ tables and seeds defaults | Click **Run** — seeds settings, 19 cities (Kamalia first), 100+ categories, packages, CMS pages, sliders, marquees, homepage sections and **8 Kamalia/TTS electricity feeders** |
| **4. Admin account** | Creates **your** administrator | Enter your name, email and choose a password (min 8 chars, hashed). *This is the only admin login — there is no default password.* |
| **5. Finish** | Locks the installer | Delete the DEMO checkbox state if you don't want sample data → **Finish Installation** |

**Optional demo data:** ticking *Install demo data* in Step 4 adds clearly-marked sample shops/products/ads (login `seller@demo.ekamalia.pk` / `Demo@1234`) so you can preview the platform. For a **production** launch leave it unticked — eKamalia never seeds fake orders, customers or payment confirmations.

After Step 5 you are redirected to the homepage. Log in at `/login` with your admin account and open **`/admin`**.

### 2.4 If `/install` won't run / 500 errors
- Verify `.htaccess` exists in `public_html` (enable *Show hidden files*).
- Set folder permissions: `storage/`, `uploads/`, `config/` → **755** (files 644).
- On some hosts the document root must point to the project folder itself (where `index.php` is), not a parent.

---

## 3. Local installation (any OS)

```bash
# 1) create a database
mysql -u root -e "CREATE DATABASE ekamalia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 2) start the dev server from the project folder (uses router.php)
php -S localhost:8080 router.php
```

Open `http://localhost:8080/install` → same 5 steps as above (host `localhost`, user `root`).
The bundled `router.php` only exists for PHP's built-in server — **Apache does not need it** (delete it in production if you like).

---

## 4. Post-install checklist (recommended order)

1. **SMTP** — Admin → Settings → *Email/SMTP* tab: enter your SMTP host/port/user/pass (e.g. `smtp.hostinger.com`, port `465`, SSL, user `hello@ekamalia.com`) → **Send Test Email**. OTP verification and all transactional mail depend on this.
2. **OTP (optional)** — Settings → *OTP & Security*: enable OTP on registration/login if desired.
3. **Feature switches** — Settings → *Features*: ad/product/shop/review approvals, comments, chat, POS, COD, bank transfer.
4. **Bank accounts** — Admin → Bank Accounts: add the account(s) buyers will transfer to (shown on checkout + payment page).
5. **Content** — Hero Sliders, Marquee, Homepage Builder, CMS Pages, Testimonials.
6. **Bijli feeders** — Admin → Bijli Updates Manager: verify the seeded feeders match your local grid areas; post the first status update.
7. **Categories/brands** — adjust the seeded catalog taxonomy to your market.
8. **Cron/backup** — Admin → Backups: create your first `.sql.gz` backup and download it.

---

## 5. Production hardening

- Serve over **HTTPS** (uncomment the redirect block in `.htaccess`).
- Make sure `config/config.php` is never web-accessible (the shipped `.htaccess` blocks `/config`; on Nginx add an equivalent deny rule for `app|config|database|storage|resources|routes`).
- Keep `display_errors` off (default) — errors are logged to `storage/logs/`.
- Take a backup before major upgrades (Admin → Backups).
- Remove the optional `router.php` and `/database/demo.php` on a hardened production box if unused.
- Set `storage/` outside web access if your host allows it — otherwise the shipped block rule protects it.

---

## 6. Reinstalling / resetting

1. Open your hosting **File Manager** and delete **`storage/install.lock`**.
2. (Optional) drop/recreate the database if you want a clean slate.
3. Visit `/install` again.

Everything else stays in place — the wizard will simply run through the 5 steps again.

---

## 7. Troubleshooting

| Symptom | Fix |
|---|---|
| Blank page / 500 | Check `storage/logs/` for the real error; almost always a permission issue on `storage/` or `config/` |
| "Could not write config/config.php" | `chmod 755 config/` (and 644 the file after install) |
| Emails not sending | SMTP not configured or wrong port/encryption — use the *Send Test Email* button; failures appear in `email_logs` |
| Images not uploading | `uploads/` must be writable; check the MB limit in Settings → Features |
| All pages 404 on Nginx | Add the rewrite: `try_files $uri $uri/ /index.php?$query_string;` |
| OTP emails delayed | Normal mail queueing on shared hosts — or switch OTP off in Settings |

---

*Invoice-free, ad-free, yours — Kamalia Ka Apna Digital Bazaar.* ⚡
