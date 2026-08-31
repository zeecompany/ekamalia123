-- ============================================================
-- eKamalia Database Schema (MySQL 5.7+/8.x, utf8mb4)
-- Installed automatically by /install
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------- users & security ----------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(20) NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  avatar VARCHAR(255) NULL,
  dob DATE NULL,
  city_id INT UNSIGNED NULL,
  status ENUM('active','pending','suspended','banned') NOT NULL DEFAULT 'active',
  email_verified_at DATETIME NULL,
  phone_verified_at DATETIME NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  last_activity DATETIME NULL,
  last_login_at DATETIME NULL,
  last_login_ip VARCHAR(45) NULL,
  profile_public TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY users_email_unique (email),
  KEY users_status_idx (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS otp_verifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identifier VARCHAR(190) NOT NULL,
  code_hash VARCHAR(255) NOT NULL,
  purpose VARCHAR(40) NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY otp_ident_idx (identifier, purpose)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bucket VARCHAR(60) NOT NULL,
  identifier VARCHAR(190) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY rate_idx (bucket, identifier, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  ip VARCHAR(45) NOT NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY lh_user_idx (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  target_type VARCHAR(60) NULL,
  target_id INT UNSIGNED NULL,
  details VARCHAR(500) NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY audit_created_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- locations ----------
CREATE TABLE IF NOT EXISTS cities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  district VARCHAR(120) NULL,
  province VARCHAR(80) NOT NULL DEFAULT 'Punjab',
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY cities_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS areas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  city_id INT UNSIGNED NOT NULL,
  name VARCHAR(140) NOT NULL,
  KEY areas_city_idx (city_id),
  CONSTRAINT fk_areas_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- categories & brands ----------
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id INT UNSIGNED NULL,
  name VARCHAR(140) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  icon VARCHAR(60) NULL,
  image VARCHAR(255) NULL,
  type ENUM('both','ad','product','business') NOT NULL DEFAULT 'both',
  description VARCHAR(500) NULL,
  seo_title VARCHAR(190) NULL,
  seo_description VARCHAR(300) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY cats_slug_unique (slug),
  KEY cats_parent_idx (parent_id),
  CONSTRAINT fk_cats_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS brands (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  logo VARCHAR(255) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  UNIQUE KEY brands_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- packages ----------
CREATE TABLE IF NOT EXISTS packages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  duration_days INT NOT NULL DEFAULT 30,
  ad_limit INT NOT NULL DEFAULT 5,
  product_limit INT NOT NULL DEFAULT 50,
  featured_ads INT NOT NULL DEFAULT 0,
  can_shop TINYINT(1) NOT NULL DEFAULT 1,
  can_pos TINYINT(1) NOT NULL DEFAULT 0,
  can_coupons TINYINT(1) NOT NULL DEFAULT 0,
  features TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- shops ----------
CREATE TABLE IF NOT EXISTS shops (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  owner_name VARCHAR(140) NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(20) NULL,
  whatsapp VARCHAR(20) NULL,
  cnic VARCHAR(20) NULL,
  address VARCHAR(255) NULL,
  city_id INT UNSIGNED NULL,
  area VARCHAR(140) NULL,
  description TEXT NULL,
  logo VARCHAR(255) NULL,
  cover VARCHAR(255) NULL,
  category_id INT UNSIGNED NULL,
  business_hours VARCHAR(190) NULL,
  delivery_available TINYINT(1) NOT NULL DEFAULT 1,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  bank_info TEXT NULL,
  package_id INT UNSIGNED NULL,
  package_expires_at DATETIME NULL,
  status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  status_note VARCHAR(300) NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  followers_count INT UNSIGNED NOT NULL DEFAULT 0,
  rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY shops_slug_unique (slug),
  KEY shops_user_idx (user_id),
  KEY shops_status_idx (status),
  CONSTRAINT fk_shops_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shop_followers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY sf_unique (shop_id, user_id),
  CONSTRAINT fk_sf_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- ads (classifieds) ----------
CREATE TABLE IF NOT EXISTS ads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  description TEXT NULL,
  category_id INT UNSIGNED NULL,
  subcategory_id INT UNSIGNED NULL,
  `condition` ENUM('new','used','refurbished') NOT NULL DEFAULT 'used',
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  negotiable TINYINT(1) NOT NULL DEFAULT 0,
  city_id INT UNSIGNED NULL,
  area VARCHAR(140) NULL,
  phone VARCHAR(20) NULL,
  whatsapp VARCHAR(20) NULL,
  seller_type ENUM('individual','dealer','business') NOT NULL DEFAULT 'individual',
  delivery_available TINYINT(1) NOT NULL DEFAULT 0,
  video_url VARCHAR(255) NULL,
  tags VARCHAR(255) NULL,
  status ENUM('pending','active','paused','sold','rejected','expired') NOT NULL DEFAULT 'pending',
  status_note VARCHAR(300) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  featured_until DATETIME NULL,
  is_urgent TINYINT(1) NOT NULL DEFAULT 0,
  urgent_until DATETIME NULL,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  likes_count INT UNSIGNED NOT NULL DEFAULT 0,
  comments_count INT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY ads_slug_unique (slug),
  KEY ads_status_idx (status, created_at),
  KEY ads_cat_idx (category_id),
  KEY ads_city_idx (city_id),
  KEY ads_user_idx (user_id),
  CONSTRAINT fk_ads_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ad_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  KEY ai_ad_idx (ad_id),
  CONSTRAINT fk_ai_ad FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- products (marketplace) ----------
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  sku VARCHAR(60) NULL,
  barcode VARCHAR(60) NULL,
  category_id INT UNSIGNED NULL,
  subcategory_id INT UNSIGNED NULL,
  brand_id INT UNSIGNED NULL,
  description TEXT NULL,
  short_description VARCHAR(300) NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  sale_price DECIMAL(12,2) NULL,
  cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  min_stock INT NOT NULL DEFAULT 3,
  unit VARCHAR(30) NOT NULL DEFAULT 'pcs',
  video_url VARCHAR(255) NULL,
  weight VARCHAR(30) NULL,
  dimensions VARCHAR(60) NULL,
  warranty VARCHAR(190) NULL,
  return_policy VARCHAR(190) NULL,
  shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  delivery_time VARCHAR(90) NULL,
  tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  tags VARCHAR(255) NULL,
  status ENUM('draft','pending','published','out_of_stock','hidden','archived') NOT NULL DEFAULT 'pending',
  status_note VARCHAR(300) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  likes_count INT UNSIGNED NOT NULL DEFAULT 0,
  rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY prod_slug_unique (slug),
  KEY prod_shop_idx (shop_id),
  KEY prod_status_idx (status, created_at),
  KEY prod_cat_idx (category_id),
  KEY prod_barcode_idx (barcode),
  CONSTRAINT fk_prod_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  KEY pi_prod_idx (product_id),
  CONSTRAINT fk_pi_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_variants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  value VARCHAR(120) NOT NULL,
  price_adjustment DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  KEY pv_prod_idx (product_id),
  CONSTRAINT fk_pv_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- cart / wishlist / social ----------
CREATE TABLE IF NOT EXISTS cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY ci_unique (user_id, product_id),
  CONSTRAINT fk_ci_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wishlists (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  item_type ENUM('product','ad') NOT NULL DEFAULT 'product',
  item_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY wl_unique (user_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  item_type ENUM('product','ad','comment','business') NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY likes_unique (user_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  item_type ENUM('product','ad','business','news') NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NULL,
  body TEXT NOT NULL,
  status ENUM('visible','hidden','deleted') NOT NULL DEFAULT 'visible',
  likes_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY cmt_item_idx (item_type, item_id),
  CONSTRAINT fk_cmt_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  item_type ENUM('product','shop','business') NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  order_id INT UNSIGNED NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  title VARCHAR(190) NULL,
  body TEXT NULL,
  seller_reply TEXT NULL,
  replied_at DATETIME NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  is_verified_purchase TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY rev_item_idx (item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT UNSIGNED NULL,
  item_type ENUM('ad','product','shop','user','comment','business','message') NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  reason VARCHAR(60) NOT NULL,
  details VARCHAR(500) NULL,
  status ENUM('open','reviewed','resolved','dismissed') NOT NULL DEFAULT 'open',
  admin_note VARCHAR(300) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY rpt_status_idx (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_searches (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  params TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- addresses ----------
CREATE TABLE IF NOT EXISTS addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  label VARCHAR(60) NULL,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address VARCHAR(255) NOT NULL,
  city_id INT UNSIGNED NULL,
  area VARCHAR(140) NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY addr_user_idx (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- orders ----------
CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(30) NOT NULL,
  group_number VARCHAR(30) NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  shop_id INT UNSIGNED NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  coupon_code VARCHAR(60) NULL,
  shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod',
  payment_status ENUM('pending','submitted','verified','rejected','refunded') NOT NULL DEFAULT 'pending',
  status ENUM('pending','confirmed','processing','packed','dispatched','out_for_delivery','delivered','completed','cancelled','returned','refund_requested','refunded') NOT NULL DEFAULT 'pending',
  delivery_method ENUM('seller_delivery','pickup','third_party') NOT NULL DEFAULT 'seller_delivery',
  ship_name VARCHAR(120) NULL,
  ship_phone VARCHAR(20) NULL,
  ship_address VARCHAR(255) NULL,
  ship_city VARCHAR(120) NULL,
  ship_area VARCHAR(140) NULL,
  note VARCHAR(500) NULL,
  estimated_delivery VARCHAR(90) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY orders_number_unique (order_number),
  KEY orders_group_idx (group_number),
  KEY orders_user_idx (user_id),
  KEY orders_shop_idx (shop_id),
  KEY orders_status_idx (status),
  CONSTRAINT fk_ord_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  image VARCHAR(255) NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  quantity INT NOT NULL DEFAULT 1,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  KEY oi_order_idx (order_id),
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_status_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL,
  note VARCHAR(300) NULL,
  changed_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY osh_order_idx (order_id),
  CONSTRAINT fk_osh_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- payments & banks ----------
CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  method VARCHAR(40) NOT NULL DEFAULT 'bank_transfer',
  bank_account_id INT UNSIGNED NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  reference_no VARCHAR(60) NULL,
  transaction_id VARCHAR(90) NULL,
  proof_image VARCHAR(255) NULL,
  status ENUM('pending','submitted','verified','rejected','refunded') NOT NULL DEFAULT 'pending',
  verified_by INT UNSIGNED NULL,
  verified_at DATETIME NULL,
  note VARCHAR(300) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY pay_order_idx (order_id),
  KEY pay_status_idx (status),
  CONSTRAINT fk_pay_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bank_accounts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bank_name VARCHAR(140) NOT NULL,
  account_title VARCHAR(140) NOT NULL,
  account_number VARCHAR(60) NOT NULL,
  iban VARCHAR(40) NULL,
  branch VARCHAR(140) NULL,
  instructions VARCHAR(500) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- coupons ----------
CREATE TABLE IF NOT EXISTS coupons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NULL,
  code VARCHAR(40) NOT NULL,
  type ENUM('percent','fixed','free_delivery') NOT NULL DEFAULT 'percent',
  value DECIMAL(12,2) NOT NULL DEFAULT 0,
  min_order DECIMAL(12,2) NOT NULL DEFAULT 0,
  max_discount DECIMAL(12,2) NULL,
  max_uses INT NOT NULL DEFAULT 0,
  used_count INT UNSIGNED NOT NULL DEFAULT 0,
  starts_at DATETIME NULL,
  expires_at DATETIME NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY coupon_code_unique (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_usage (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  coupon_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  order_id INT UNSIGNED NULL,
  used_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY cu_coupon_idx (coupon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- chat ----------
CREATE TABLE IF NOT EXISTS message_threads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT UNSIGNED NOT NULL,
  seller_id INT UNSIGNED NOT NULL,
  shop_id INT UNSIGNED NULL,
  ad_id INT UNSIGNED NULL,
  product_id INT UNSIGNED NULL,
  last_message VARCHAR(255) NULL,
  last_message_at DATETIME NULL,
  buyer_unread INT UNSIGNED NOT NULL DEFAULT 0,
  seller_unread INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('active','archived') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY mt_buyer_idx (buyer_id),
  KEY mt_seller_idx (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  thread_id INT UNSIGNED NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  type ENUM('text','image','product','order') NOT NULL DEFAULT 'text',
  body TEXT NULL,
  image VARCHAR(255) NULL,
  ref_id INT UNSIGNED NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY msg_thread_idx (thread_id, created_at),
  CONSTRAINT fk_msg_thread FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blocked_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  blocked_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY bu_unique (user_id, blocked_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- notifications ----------
CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  body VARCHAR(500) NULL,
  type VARCHAR(40) NOT NULL DEFAULT 'general',
  url VARCHAR(255) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ntf_user_idx (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- business directory ----------
CREATE TABLE IF NOT EXISTS businesses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  category_id INT UNSIGNED NULL,
  description TEXT NULL,
  logo VARCHAR(255) NULL,
  cover VARCHAR(255) NULL,
  phone VARCHAR(20) NULL,
  whatsapp VARCHAR(20) NULL,
  email VARCHAR(190) NULL,
  website VARCHAR(190) NULL,
  address VARCHAR(255) NULL,
  city_id INT UNSIGNED NULL,
  area VARCHAR(140) NULL,
  opening_hours VARCHAR(190) NULL,
  facebook VARCHAR(190) NULL,
  instagram VARCHAR(190) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY biz_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  KEY bp_biz_idx (business_id),
  CONSTRAINT fk_bp_biz FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- content: sliders, marquee, ads, pages, news ----------
CREATE TABLE IF NOT EXISTS sliders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NULL,
  subtitle VARCHAR(300) NULL,
  image VARCHAR(255) NOT NULL,
  mobile_image VARCHAR(255) NULL,
  button_text VARCHAR(80) NULL,
  button_url VARCHAR(255) NULL,
  bg_color VARCHAR(20) NULL,
  animation VARCHAR(40) NOT NULL DEFAULT 'fade',
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marquees (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('announcement','offer') NOT NULL DEFAULT 'announcement',
  text VARCHAR(255) NOT NULL,
  url VARCHAR(255) NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS advertisements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  image VARCHAR(255) NOT NULL,
  url VARCHAR(255) NULL,
  position ENUM('banner','sidebar','category','in_content') NOT NULL DEFAULT 'banner',
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  priority INT NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  clicks INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  content LONGTEXT NULL,
  featured_image VARCHAR(255) NULL,
  seo_title VARCHAR(190) NULL,
  seo_description VARCHAR(300) NULL,
  show_in_footer TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY pages_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  excerpt VARCHAR(400) NULL,
  content LONGTEXT NULL,
  image VARCHAR(255) NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  views INT UNSIGNED NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  UNIQUE KEY news_slug_unique (slug),
  FULLTEXT KEY news_ft (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  photo VARCHAR(255) NULL,
  comment VARCHAR(500) NOT NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  business VARCHAR(160) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homepage_sections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(60) NOT NULL,
  title VARCHAR(190) NULL,
  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY hs_key_unique (section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(20) NULL,
  subject VARCHAR(190) NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  reply_text TEXT NULL,
  replied_at DATETIME NULL,
  status ENUM('new','read','archived') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- POS ----------
CREATE TABLE IF NOT EXISTS pos_packages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  duration_days INT NOT NULL DEFAULT 365,
  max_users INT NOT NULL DEFAULT 3,
  max_products INT NOT NULL DEFAULT 1000,
  features VARCHAR(500) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  shop_id INT UNSIGNED NULL,
  business_name VARCHAR(160) NULL,
  package_id INT UNSIGNED NULL,
  note VARCHAR(500) NULL,
  status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  expires_at DATETIME NULL,
  admin_note VARCHAR(300) NULL,
  decided_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY posr_user_idx (user_id),
  KEY posr_status_idx (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  shop_id INT UNSIGNED NULL,
  pos_request_id INT UNSIGNED NULL,
  role ENUM('owner','manager','cashier','inventory','sales') NOT NULL DEFAULT 'cashier',
  permissions TEXT NULL,
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY posu_user_idx (user_id),
  KEY posu_shop_idx (shop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_customers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  name VARCHAR(140) NOT NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(190) NULL,
  address VARCHAR(255) NULL,
  note VARCHAR(300) NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_purchases DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY pc_shop_idx (shop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_suppliers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  name VARCHAR(140) NOT NULL,
  company VARCHAR(160) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(190) NULL,
  address VARCHAR(255) NULL,
  payment_terms VARCHAR(190) NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ps_shop_idx (shop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_sales (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  invoice_no VARCHAR(30) NOT NULL,
  type ENUM('sale','quotation') NOT NULL DEFAULT 'sale',
  status ENUM('completed','held','returned','partially_returned','cancelled') NOT NULL DEFAULT 'completed',
  customer_id INT UNSIGNED NULL,
  cashier_id INT UNSIGNED NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  change_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(40) NOT NULL DEFAULT 'cash',
  payment_detail VARCHAR(300) NULL,
  note VARCHAR(300) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY poss_invoice_unique (invoice_no),
  KEY poss_shop_idx (shop_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_sale_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sale_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  quantity INT NOT NULL DEFAULT 1,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  returned_qty INT NOT NULL DEFAULT 0,
  KEY psi_sale_idx (sale_id),
  CONSTRAINT fk_psi_sale FOREIGN KEY (sale_id) REFERENCES pos_sales(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_returns (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  sale_id INT UNSIGNED NOT NULL,
  return_no VARCHAR(30) NOT NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  refund_method VARCHAR(40) NOT NULL DEFAULT 'cash',
  reason VARCHAR(300) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY psr_no_unique (return_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_return_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  return_id INT UNSIGNED NOT NULL,
  sale_item_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  quantity INT NOT NULL DEFAULT 1,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_pri_return FOREIGN KEY (return_id) REFERENCES pos_returns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_purchases (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED NULL,
  invoice_no VARCHAR(60) NULL,
  purchase_date DATE NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('draft','received','partially_received','completed','cancelled') NOT NULL DEFAULT 'completed',
  note VARCHAR(300) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY psp_shop_idx (shop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_purchase_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  name VARCHAR(190) NOT NULL,
  cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  quantity INT NOT NULL DEFAULT 1,
  received_qty INT NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_ppi_purchase FOREIGN KEY (purchase_id) REFERENCES pos_purchases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  category VARCHAR(80) NOT NULL DEFAULT 'general',
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  note VARCHAR(300) NULL,
  expense_date DATE NOT NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY pse_shop_idx (shop_id, expense_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_movements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  type VARCHAR(40) NOT NULL,
  quantity INT NOT NULL,
  previous_stock INT NOT NULL DEFAULT 0,
  new_stock INT NOT NULL DEFAULT 0,
  reason VARCHAR(190) NULL,
  reference_type VARCHAR(40) NULL,
  reference_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY im_prod_idx (product_id, created_at),
  KEY im_shop_idx (shop_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- eKamalia Bijli Updates (feeder-wise electricity news) ----------
CREATE TABLE IF NOT EXISTS feeders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  city_id INT UNSIGNED NOT NULL,
  area VARCHAR(190) NULL,
  description VARCHAR(400) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY feeder_city_idx (city_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feeder_updates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  feeder_id INT UNSIGNED NOT NULL,
  status ENUM('on','off','maintenance','scheduled') NOT NULL DEFAULT 'off',
  title VARCHAR(190) NULL,
  message VARCHAR(500) NULL,
  started_at DATETIME NOT NULL,
  expected_at DATETIME NULL,
  is_resolved TINYINT(1) NOT NULL DEFAULT 0,
  resolved_at DATETIME NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY fu_feeder_idx (feeder_id, created_at),
  CONSTRAINT fk_fu_feeder FOREIGN KEY (feeder_id) REFERENCES feeders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feeder_reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  feeder_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  message VARCHAR(400) NOT NULL,
  status ENUM('open','reviewed','resolved') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY fr_feeder_idx (feeder_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- misc ----------
CREATE TABLE IF NOT EXISTS search_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  query VARCHAR(140) NOT NULL,
  results_count INT UNSIGNED NOT NULL DEFAULT 0,
  user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY sl_query_idx (query)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('ad_featured','ad_urgent','product_featured','shop_featured','package_upgrade') NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  package_id INT UNSIGNED NULL,
  note VARCHAR(300) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(300) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  to_email VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NULL,
  status ENUM('sent','failed') NOT NULL DEFAULT 'sent',
  error VARCHAR(300) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

/* -------- settings (key-value store) -------- */
CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(120) NOT NULL,
  `value` TEXT NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
