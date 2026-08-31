<?php
/**
 * eKamalia — default seeder (runs during installation).
 * Inserted: settings, cities (Kamalia primary + surroundings), areas, categories,
 * packages, pages, marquee, sliders, homepage sections, banks, feeders (Bijli).
 */
declare(strict_types=1);

function ek_seed(PDO $pdo): void
{
    $ins = function (string $sql, array $p = []) use ($pdo) {
        $pdo->prepare($sql)->execute($p);
        return (int)$pdo->lastInsertId();
    };
    $now = date('Y-m-d H:i:s');

    /* ---------- settings ---------- */
    $settings = [
        'site_name' => 'eKamalia', 'site_tagline' => 'Kamalia Ka Apna Digital Bazaar',
        'site_email' => 'hello@ekamalia.com', 'site_phone' => '0300-0000000', 'whatsapp_number' => '923000000000',
        'site_address' => 'Main Bazaar, Kamalia, District Toba Tek Singh, Punjab, Pakistan',
        'currency' => 'PKR', 'currency_symbol' => 'Rs ', 'timezone' => 'Asia/Karachi',
        'primary_color' => '#0B7A3E', 'footer_text' => 'eKamalia — Buy • Sell • Shop • Discover. Kamalia ka apna digital bazaar.',
        'social_fb' => 'https://facebook.com/ekamalia', 'social_ig' => '', 'social_x' => '', 'social_yt' => '',
        'seo_title' => 'eKamalia — Kamalia Ka Apna Digital Bazaar | Classifieds, Shops & Business Directory',
        'seo_description' => 'Pakistan\'s modern local marketplace for Kamalia & Toba Tek Singh. Post free ads, shop online, find local businesses, check bijli updates and more.',
        'seo_keywords' => 'kamalia, ekamalia, olx kamalia, kamalia bazaar, toba tek singh, kamalia bijli, load shedding kamalia, online shopping pakistan',
        'smtp_host' => '', 'smtp_port' => '587', 'smtp_user' => 'hello@ekamalia.com', 'smtp_pass' => '',
        'smtp_encryption' => 'tls', 'smtp_from_name' => 'eKamalia', 'smtp_from_email' => 'hello@ekamalia.com',
        'otp_enabled' => '1', 'otp_expiry_minutes' => '10',
        'ads_require_approval' => '0', 'products_require_approval' => '1', 'shops_require_approval' => '1',
        'reviews_require_approval' => '0', 'comments_enabled' => '1', 'chat_enabled' => '1', 'pos_enabled' => '1',
        'free_ads_limit' => '10', 'per_page' => '20', 'max_upload_mb' => '5', 'commission_percent' => '0',
        'maintenance_mode' => '0', 'maintenance_message' => 'eKamalia is being upgraded. Hum jald wapas aa rahe hain!',
        'demo_mode' => '0', 'payment_instructions' => 'Transfer the exact amount to any bank account below, then upload your receipt with the transaction ID. Verification is done within a few hours.',
        'app_theme_color' => '#0B7A3E', 'delivery_default_fee' => '150', 'cod_enabled' => '1', 'bank_transfer_enabled' => '1',
        'login_otp_required' => '0', 'reg_otp_required' => '1',
    ];
    foreach ($settings as $k => $v) {
        $pdo->prepare('INSERT INTO settings (`key`,`value`) VALUES (?,?)')->execute([$k, $v]);
    }

    /* ---------- cities: Kamalia featured + surrounding ---------- */
    $cities = [
        ['Kamalia', 'kamalia', 'Toba Tek Singh', 'Punjab', 1, 0],
        ['Pirmahal', 'pirmahal', 'Toba Tek Singh', 'Punjab', 0, 1],
        ['Toba Tek Singh', 'toba-tek-singh', 'Toba Tek Singh', 'Punjab', 0, 2],
        ['Chichawatni', 'chichawatni', 'Sahiwal', 'Punjab', 0, 3],
        ['Sahiwal', 'sahiwal', 'Sahiwal', 'Punjab', 0, 4],
        ['Burewala', 'burewala', 'Vehari', 'Punjab', 0, 5],
        ['Vehari', 'vehari', 'Vehari', 'Punjab', 0, 6],
        ['Gojra', 'gojra', 'Toba Tek Singh', 'Punjab', 0, 7],
        ['Arifwala', 'arifwala', 'Pakpattan', 'Punjab', 0, 8],
        ['Haroonabad', 'haroonabad', 'Bahawalnagar', 'Punjab', 0, 9],
        ['Pakpattan', 'pakpattan', 'Pakpattan', 'Punjab', 0, 10],
        ['Faisalabad', 'faisalabad', 'Faisalabad', 'Punjab', 0, 11],
        ['Multan', 'multan', 'Multan', 'Punjab', 0, 12],
        ['Lahore', 'lahore', 'Lahore', 'Punjab', 0, 13],
        ['Islamabad', 'islamabad', 'Islamabad', 'Islamabad', 0, 14],
        ['Karachi', 'karachi', 'Karachi', 'Sindh', 0, 15],
        ['Rawalpindi', 'rawalpindi', 'Rawalpindi', 'Punjab', 0, 16],
        ['Peshawar', 'peshawar', 'Peshawar', 'KPK', 0, 17],
        ['Quetta', 'quetta', 'Quetta', 'Balochistan', 0, 18],
    ];
    $cityId = [];
    foreach ($cities as $c) {
        $cityId[$c[0]] = $ins('INSERT INTO cities (name,slug,district,province,is_primary,sort_order) VALUES (?,?,?,?,?,?)',
            [$c[0], $c[1], $c[2], $c[3], $c[4], $c[5]]);
    }
    $areas = [
        'Kamalia' => ['Main Bazaar','Karkhana Bazar','Railway Road','Kotwali Road','Pirmahal Road','Garh Maharaja Road','Sugar Mills Area','Nur Shah Colony','Gulberg Colony','Mohallah Sadiqabad','Chak No. 331/GB','Chak No. 332/GB','Chak No. 341/GB','Chak No. 342/GB','Chak No. 343/GB','Chak No. 344/GB','Chak No. 345/GB','Chak No. 354/GB','Kurkundi Road','Jandanwala'],
        'Pirmahal' => ['Main Bazaar','Railway Road','Sabzi Mandi','Chak No. 262/RB','Chak No. 274/GB'],
        'Toba Tek Singh' => ['Main Bazaar','Civil Lines','Ghanta Ghar','Jhang Road','Gojra Road'],
        'Chichawatni' => ['Main Bazaar','Forestry Road','Sahiwal Road','Kassowal'],
        'Gojra' => ['Main Bazaar','Railway Road','Faisalabad Road'],
        'Sahiwal' => ['Main Bazaar','High Street','Faruq Town','Montgomery Road'],
        'Burewala' => ['Main Bazaar','Gronwala Road','Chak 51/GB'],
        'Vehari' => ['Main Bazaar','Satellite Town','Multan Road'],
    ];
    foreach ($areas as $city => $list) {
        foreach ($list as $a) $ins('INSERT INTO areas (city_id,name) VALUES (?,?)', [$cityId[$city], $a]);
    }

    /* ---------- categories (classifieds + products) ---------- */
    $cats = [
        ['Mobiles', 'fa-mobile-screen-button', 1], ['Electronics', 'fa-tv', 1], ['Vehicles', 'fa-car-side', 1],
        ['Property', 'fa-house-chimney', 1], ['Jobs', 'fa-briefcase', 1], ['Services', 'fa-screwdriver-wrench', 1],
        ['Furniture', 'fa-couch', 1], ['Home & Garden', 'fa-seedling', 0], ['Fashion', 'fa-shirt', 1],
        ['Beauty', 'fa-spa', 0], ['Food', 'fa-utensils', 1], ['Grocery', 'fa-basket-shopping', 1],
        ['Construction', 'fa-trowel-bricks', 0], ['Agriculture', 'fa-wheat-awn', 1], ['Animals', 'fa-cow', 1],
        ['Books', 'fa-book', 0], ['Education', 'fa-graduation-cap', 0], ['Sports', 'fa-futbol', 0],
        ['Kids', 'fa-baby', 0], ['Business & Industrial', 'fa-industry', 0], ['Other', 'fa-circle-dot', 0],
    ];
    $catId = [];
    foreach ($cats as $i => $c) {
        $catId[$c[0]] = $ins('INSERT INTO categories (name,slug,icon,type,is_featured,sort_order) VALUES (?,?,?,?,?,?)',
            [$c[0], slugify_s($c[0]), $c[1], 'both', $c[2], $i]);
    }
    $subs = [
        'Mobiles' => ['Mobile Phones','Tablets','Accessories','Smart Watches'],
        'Electronics' => ['TV & Audio','AC & Cooling','Refrigerators','Washing Machines','Cameras','Computers & Laptops'],
        'Vehicles' => ['Cars','Motorcycles & Bikes','Rickshaws','Trucks & Buses','Spare Parts','Bicycles'],
        'Property' => ['Plots for Sale','Houses for Sale','Houses for Rent','Shops & Commercial','Farm Land'],
        'Jobs' => ['Government Jobs','Private Jobs','Part Time','Freelance','Domestic Staff'],
        'Services' => ['Electricians','Plumbers','AC Technicians','Masons & Labour','Tailors','Photography','Transport'],
        'Furniture' => ['Sofas & Beds','Office Furniture','Outdoor Furniture','Home Decor'],
        'Fashion' => ['Men','Women','Kids','Footwear','Jewellery'],
        'Food' => ['Restaurants','Home Made','Bakery','Sweets'],
        'Grocery' => ['Attas & Grains','Spices','Dairy','General Store'],
        'Agriculture' => ['Tractors & Trolleys','Seeds & Fertilizer','Tube Wells','Harvesting'],
        'Animals' => ['Cows & Buffaloes','Goats & Sheep','Poultry','Birds','Pets'],
    ];
    foreach ($subs as $parent => $list) {
        foreach ($list as $i => $s) $ins('INSERT INTO categories (parent_id,name,slug,type,sort_order) VALUES (?,?,?,?,?)',
            [$catId[$parent], $s, slugify_s($parent . ' ' . $s), 'both', $i]);
    }

    /* ---------- business directory categories ---------- */
    $bizCats = ['Restaurants','Hotels','Clinics','Doctors','Pharmacies','Schools','Colleges','Academies','Mechanics','Electricians','Plumbers','Construction','Hardware','Garments','Mobile Shops','Computer Shops','Grocery Stores','Beauty Salons','Barbers','Travel Agencies','Real Estate','Car Dealers','Workshops','Freelancers','Other Services'];
    foreach ($bizCats as $i => $b) {
        $ins('INSERT INTO categories (name,slug,icon,type,is_featured,sort_order) VALUES (?,?,?,?,1,?)',
            [$b, slugify_s('biz ' . $b), 'fa-store', 'business', $i]);
    }

    /* ---------- packages ---------- */
    $pkgs = [
        ['Free', 0, 365, 10, 30, 0, 1, 0, 0, '10 ads • 1 shop • 30 products'],
        ['Basic', 500, 90, 30, 150, 2, 1, 0, 1, '30 ads • 2 featured • 150 products • coupons'],
        ['Premium', 1500, 180, 100, 500, 10, 1, 0, 1, '100 ads • 10 featured • 500 products • priority support'],
        ['Business', 4000, 365, 0, 5000, 30, 1, 1, 1, 'Unlimited ads • 30 featured • POS included'],
        ['Enterprise', 10000, 365, 0, 0, 100, 1, 1, 1, 'Everything unlimited • POS • premium support'],
    ];
    foreach ($pkgs as $i => $p) {
        $ins('INSERT INTO packages (name,slug,price,duration_days,ad_limit,product_limit,featured_ads,can_shop,can_coupons,can_pos,features,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            [$p[0], slugify_s($p[0]), $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[8], $p[7], $p[9], $i]);
    }
    $posPkgs = [
        ['POS Starter', 0, 365, 1, 500, '1 user • 500 products • full sales & inventory'],
        ['POS Business', 3000, 365, 5, 5000, '5 users • reports • purchases • returns'],
        ['POS Enterprise', 8000, 365, 20, 0, 'Unlimited users & products • priority support'],
    ];
    foreach ($posPkgs as $p) {
        $ins('INSERT INTO pos_packages (name,price,duration_days,max_users,max_products,features) VALUES (?,?,?,?,?,?)', $p);
    }

    /* ---------- CMS pages ---------- */
    $pages = [
        ['About Us', 'about-us', "<h2>Kamalia Ka Apna Digital Bazaar</h2><p>eKamalia is the modern digital marketplace built specially for <strong>Kamalia city</strong> and surrounding areas of District Toba Tek Singh — Pirmahal, Chichawatni, Gojra, Sahiwal, Burewala, Vehari and beyond. Buy, sell, shop, discover local businesses, follow feeder-wise bijli updates — all in one place.</p><p>Our mission is simple: give the people and businesses of Kamalia the same world-class digital shopping experience that big cities enjoy — in Urdu-friendly, mobile-first design.</p>"],
        ['Contact Us', 'contact-us', '<p>Questions, suggestions or partnership ideas? We would love to hear from you. Use the contact form on this page, email us at hello@ekamalia.com, or message us on WhatsApp.</p><p><strong>Office:</strong> Main Bazaar, Kamalia, District Toba Tek Singh, Punjab, Pakistan</p>'],
        ['FAQs', 'faqs', "<h4>How do I post a free ad?</h4><p>Create a free account, click <strong>Post Ad</strong>, add photos, price and details — done. Your ad goes live after a quick review.</p><h4>How do I open a shop?</h4><p>Register as a seller, submit your shop for approval, and start listing products once approved.</p><h4>What payment methods are supported?</h4><p>Cash on Delivery and manual Bank Transfer with receipt upload (verified by our team). Online gateways like JazzCash/Easypaisa are coming soon.</p><h4>What are Bijli Updates?</h4><p>eKamalia publishes feeder-wise electricity on/off news for Kamalia and nearby areas, updated by our team — so you always know when the light is coming back.</p>"],
        ['Terms & Conditions', 'terms', '<p>Welcome to eKamalia. By using this platform you agree to post only lawful content, trade honestly, and respect other users. Prohibited: illegal items, weapons, drugs, wildlife, counterfeit products, misleading listings, harassment. eKamalia is a marketplace venue; it does not own the items listed. Always inspect goods and use safe payment practices. We may remove content and suspend accounts that violate these terms.</p>'],
        ['Privacy Policy', 'privacy', '<p>We respect your privacy. We collect only the information needed to run the marketplace (name, contact, listings, orders) and never sell your personal data. Payment proofs and chats stay private. You may request account deletion at any time by contacting hello@ekamalia.com.</p>'],
        ['Refund Policy', 'refund-policy', '<p>If an item is not as described, damaged or not delivered, open a refund request from your order page within 3 days of delivery. Sellers should resolve fairly; eKamalia moderation decides disputes where needed. Approved refunds are returned via bank transfer or agreed method.</p>'],
        ['Seller Policy', 'seller-policy', '<p>Sellers must list real, in-stock products with honest prices, keep orders updated, and deliver within the promised time. Fake orders, review abuse or repeated complaints can lead to suspension. Verified shops get priority placement.</p>'],
        ['Buyer Policy', 'buyer-policy', '<p>Buyers should provide accurate delivery details and pay on time. Order cancellations are allowed before dispatch. Misuse (fraudulent claims, abusive behavior) can lead to account restrictions.</p>'],
        ['Shipping Policy', 'shipping-policy', '<p>Delivery time and fee are set by each seller and shown at checkout. Local Kamalia delivery is often same-day. For out-of-city orders, sellers use courier services — tracking updates appear on your order timeline.</p>'],
        ['Cookie Policy', 'cookie-policy', '<p>eKamalia uses essential cookies to keep you logged in and remember your cart, language and recently viewed items. We do not use advertising trackers.</p>'],
    ];
    foreach ($pages as $p) {
        $ins('INSERT INTO pages (title,slug,content,status,created_at) VALUES (?,?,?,"active",?)', [$p[0], $p[1], $p[2], $now]);
    }

    /* ---------- marquee ---------- */
    $marquees = [
        ['announcement', 'Welcome to eKamalia — Kamalia Ka Apna Digital Bazaar', '/'],
        ['announcement', 'Buy • Sell • Shop • Discover Kamalia', '/products'],
        ['offer', 'Free Shop Registration for Kamalia Businesses — Limited Time', '/shops/create'],
        ['offer', 'Premium POS Available for Businesses — Apply Today', '/pos/request'],
        ['announcement', 'Feeder-wise Bijli Updates now live for Kamalia & Toba Tek Singh', '/bijli'],
        ['offer', 'Featured Ads Available — Promote Your Listing Now', '/ads/create'],
    ];
    foreach ($marquees as $i => $m) {
        $ins('INSERT INTO marquees (type,text,url,sort_order) VALUES (?,?,?,?)', [$m[0], $m[1], $m[2], $i]);
    }

    /* ---------- sliders (editable in Admin > Sliders) ---------- */
    $sliders = [
        ['Kamalia Ka Apna Digital Bazaar', 'Post free ads, shop from local stores and discover Kamalia — all in one place.', 'assets/img/hero-1.jpg', 'Explore Now', '/products', 'fade'],
        ['Apni Dukaan, Online!', 'Register your shop free and sell to customers across Kamalia & nearby cities.', 'assets/img/hero-2.jpg', 'Create Your Shop', '/shops/create', 'zoom'],
        ['Premium POS for Businesses', 'Billing, inventory, purchases & reports — professional POS made for Pakistani shops.', 'assets/img/hero-3.jpg', 'Request POS Access', '/pos/request', 'slide'],
    ];
    foreach ($sliders as $i => $s) {
        $ins('INSERT INTO sliders (title,subtitle,image,button_text,button_url,animation,sort_order) VALUES (?,?,?,?,?,?,?)',
            [$s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $i]);
    }

    /* ---------- homepage sections ---------- */
    $sections = [
        ['hero_slider', 'Hero Slider'], ['quick_actions', 'Quick Actions'], ['categories', 'Browse Categories'],
        ['featured_ads', 'Fresh & Featured Ads'], ['featured_products', 'Featured Products'], ['featured_shops', 'Featured Shops'],
        ['bijli_updates', 'Bijli Updates (Electricity News)'], ['business_directory', 'Kamalia Business Directory'],
        ['deals', 'Deals & Offers'], ['marquee_offers', 'Offers Marquee'], ['news', 'Kamalia News & Updates'],
        ['testimonials', 'What People Say'], ['cta', 'Join eKamalia Today'],
    ];
    foreach ($sections as $i => $s) {
        $ins('INSERT INTO homepage_sections (section_key,title,is_enabled,sort_order) VALUES (?,?,1,?)', [$s[0], $s[1], $i]);
    }

    /* ---------- default bank account (editable in Admin) ---------- */
    $ins('INSERT INTO bank_accounts (bank_name,account_title,account_number,iban,branch,instructions,sort_order) VALUES (?,?,?,?,?,?,1)',
        ['Meezan Bank (sample — replace in Admin)', 'eKamalia (Pvt) Ltd', '0123 0102 4570 1234 56', 'PK36MEZN0001230102457012', 'Kamalia Branch', 'After transfer, upload your receipt from the order page.']);

    /* ---------- Bijli feeders (Kamalia + surrounding) ---------- */
    $feeders = [
        ['Kamalia City Feeder-1', 'Kamalia', 'Main Bazaar, Karkhana Bazar, Kotwali Road'],
        ['Kamalia City Feeder-2', 'Kamalia', 'Railway Road, Nur Shah Colony, Gulberg Colony'],
        ['Kamalia Karkhana Feeder', 'Kamalia', 'Sugar Mills Area, Industrial Road'],
        ['Kamalia Rural Feeder-341', 'Kamalia', 'Chak No. 341/GB to 345/GB, Jandanwala'],
        ['Pirmahal City Feeder', 'Pirmahal', 'Pirmahal Main Bazaar & nearby chaks'],
        ['Toba Tek Singh City Feeder', 'Toba Tek Singh', 'TTS Main Bazaar, Civil Lines'],
        ['Chichawatni City Feeder', 'Chichawatni', 'Chichawatni Main Bazaar'],
        ['Gojra City Feeder', 'Gojra', 'Gojra Main Bazaar'],
    ];
    foreach ($feeders as $i => $f) {
        $fid = $ins('INSERT INTO feeders (name,city_id,area,sort_order) VALUES (?,?,?,?)', [$f[0], $cityId[$f[1]], $f[2], $i]);
        $ins('INSERT INTO feeder_updates (feeder_id,status,title,message,started_at,is_resolved,resolved_at,created_by) VALUES (?,?,?,?,?,1,?,NULL)',
            [$fid, 'on', 'Supply Normal', 'Bijli supply is running normally on this feeder.', date('Y-m-d H:i:s', time() - 86400), date('Y-m-d H:i:s', time() - 86000)]);
    }
}

/** ASCII slug helper usable before app helpers exist */
function slugify_s(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = strtolower(trim($text, '-'));
    return preg_replace('~-+~', '-', $text) ?: 'item';
}
