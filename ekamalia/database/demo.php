<?php
/**
 * eKamalia — optional DEMO seeder.
 * Creates clearly-marked sample shops/products/ads/businesses so the platform
 * can be previewed before real content arrives. Disable by deleting demo data from Admin.
 */
declare(strict_types=1);

function ek_seed_demo(PDO $pdo): void
{
    $ins = function (string $sql, array $p = []) use ($pdo) {
        $pdo->prepare($sql)->execute($p);
        return (int)$pdo->lastInsertId();
    };
    $one = function (string $sql, array $p = []) use ($pdo) {
        $st = $pdo->prepare($sql); $st->execute($p); $r = $st->fetch(); return $r === false ? null : $r;
    };
    $now = date('Y-m-d H:i:s');
    $hash = password_hash('Demo@1234', PASSWORD_DEFAULT);

    $city = fn(string $n) => (int)($one('SELECT id FROM cities WHERE slug=?', [slugify_s($n)])['id'] ?? 1);
    $cat  = fn(string $n) => (int)($one('SELECT id FROM categories WHERE slug=?', [slugify_s($n)])['id'] ?? 1);
    $sub  = fn(string $n) => (int)($one('SELECT id FROM categories WHERE slug=?', [slugify_s($n)])['id'] ?? 1);

    $pdo->prepare('UPDATE settings SET `value`="1" WHERE `key`="demo_mode"')->execute();

    /* demo users */
    $seller1 = $ins('INSERT INTO users (name,email,phone,password,role,status,email_verified_at,created_at) VALUES ("Ahmad Ali (Demo)","seller@demo.ekamalia.pk","03001234567",?,"user","active",?,?)', [$hash, $now, $now]);
    $seller2 = $ins('INSERT INTO users (name,email,phone,password,role,status,email_verified_at,created_at) VALUES ("Fatima Home Store (Demo)","seller2@demo.ekamalia.pk","03007654321",?,"user","active",?,?)', [$hash, $now, $now]);
    $buyer   = $ins('INSERT INTO users (name,email,phone,password,role,status,email_verified_at,created_at) VALUES ("Bilal Buyer (Demo)","buyer@demo.ekamalia.pk","03011112222",?,"user","active",?,?)', [$hash, $now, $now]);

    /* demo shops */
    $shop1 = $ins('INSERT INTO shops (user_id,name,slug,owner_name,email,phone,whatsapp,address,city_id,area,description,logo,cover,category_id,status,is_verified,is_featured,business_hours,delivery_available,delivery_fee,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
        $seller1, 'Kamalia Mobile Point (Demo)', 'kamalia-mobile-point', 'Ahmad Ali', 'seller@demo.ekamalia.pk', '03001234567', '923001234567',
        'Karkhana Bazar, Kamalia', $city('Kamalia'), 'Karkhana Bazar',
        'Demo shop — All brand new & used mobile phones with warranty. PTA approved. Home delivery in Kamalia.',
        'demo/shop1-logo.png', 'demo/shop1-cover.jpg', $cat('mobile shops'), 'approved', 1, 1, '10:00 AM - 10:00 PM', 1, 100, $now,
    ]);
    $shop2 = $ins('INSERT INTO shops (user_id,name,slug,owner_name,email,phone,whatsapp,address,city_id,area,description,logo,cover,category_id,status,is_featured,business_hours,delivery_available,delivery_fee,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
        $seller2, 'Home Decor Furnishers (Demo)', 'home-decor-furnishers', 'Fatima Noor', 'seller2@demo.ekamalia.pk', '03007654321', '923007654321',
        'Pirmahal Road, Kamalia', $city('Kamalia'), 'Pirmahal Road',
        'Demo shop — Quality furniture & home decor at honest prices. Sofa sets, beds, dining tables & more.',
        'demo/shop2-logo.png', 'demo/shop2-cover.jpg', $cat('garments') ?: $cat('furniture'), 'approved', 1, '10:00 AM - 9:00 PM', 1, 200, $now,
    ]);
    $ins('INSERT INTO pos_users (user_id,shop_id,role,permissions,status) VALUES (?,?,?,?,?)', [$seller1, $shop1, 'owner', '[]', 'active']);
    $ins('INSERT INTO pos_requests (user_id,shop_id,business_name,status,decided_at,created_at) VALUES (?,?,?,?,?,?)', [$seller1, $shop1, 'Kamalia Mobile Point', 'approved', $now, $now]);

    /* demo products */
    $products = [
        [$shop1, 'Infinix Hot 40i 8/256GB (Demo)', $cat('mobiles'), $sub('mobile phones'), 42999, 39999, 12, 'demo/p-mobile.jpg', 'Brand new packed Infinix Hot 40i, official warranty. Cash on delivery available in Kamalia.', 1],
        [$shop1, 'Samsung Galaxy A15 (Demo)', $cat('mobiles'), $sub('mobile phones'), 54999, null, 8, 'demo/p-mobile.jpg', 'Samsung Galaxy A15 6/128GB — PTA approved, 1 year official warranty.', 1],
        [$shop1, 'Wireless Earbuds Pro (Demo)', $cat('mobiles'), $sub('accessories'), 3500, 2499, 30, 'demo/p-earbuds.jpg', 'ENC calling, 30hr battery, touch controls. 6 months shop warranty.', 0],
        [$shop2, '7-Seater Fabric Sofa Set (Demo)', $cat('furniture'), $sub('sofas & beds'), 85000, 79500, 5, 'demo/p-sofa.jpg', 'Premium quality 7-seater sofa set with center table. Free delivery in Kamalia.', 1],
        [$shop2, 'Sheesham Wood King Bed (Demo)', $cat('furniture'), $sub('sofas & beds'), 62000, null, 3, 'demo/p-bed.jpg', 'Solid sheesham wood bed with side tables. Custom sizes available.', 0],
        [$shop2, 'Study Table with Bookshelf (Demo)', $cat('furniture'), $sub('office furniture'), 18500, 16500, 10, 'demo/p-table.jpg', 'Wooden study table with attached bookshelf — perfect for students.', 0],
    ];
    foreach ($products as $i => $p) {
        $pid = $ins('INSERT INTO products (shop_id,name,slug,sku,category_id,subcategory_id,description,short_description,price,sale_price,cost_price,stock,min_stock,unit,status,is_featured,views,shipping_fee,delivery_time,warranty,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $p[0], $p[1], slugify_s($p[1]), 'SKU-' . (1000 + $i), $p[2], $p[3], $p[8], mb_substr($p[8], 0, 140),
            $p[4], $p[5], (int)round($p[4] * 0.8), $p[6], 3, 'pcs', 'published', $p[9], rand(20, 200), 150, '1-2 days', 'Shop warranty', $now,
        ]);
        $ins('INSERT INTO product_images (product_id,image,sort_order) VALUES (?,?,0)', [$pid, $p[7]]);
        $ins('INSERT INTO inventory_movements (shop_id,product_id,type,quantity,previous_stock,new_stock,reason,user_id,created_at) VALUES (?,?,?, 0,?,?,0,?,?,?)', [$p[0], $pid, 'opening', 0, $p[6], $seller1, $now, $now]);
    }

    /* demo ads */
    $ads = [
        ['Toyota Corolla GLi 2019 (Demo)', $cat('vehicles'), $sub('cars'), 3850000, 'used', 'Well maintained Corolla GLi automatic, first owner, complete file, fresh paintwork not needed.', 'demo/a-car.jpg', 1, 1],
        ['Honda CD 125 Dream (Demo)', $cat('vehicles'), $sub('motorcycles & bikes'), 234000, 'used', '2023 model, only 8,000 km driven, complete documents, engine perfect.', 'demo/a-bike.jpg', 1, 0],
        ['10 Marla Plot Gulberg Colony Kamalia (Demo)', $cat('property'), $sub('plots for sale'), 2750000, 'new', 'Corner plot in developed colony, electricity, sui gas & paved street. Registry ready.', 'demo/a-plot.jpg', 0, 0],
        ['5 Room House for Rent — Railway Road (Demo)', $cat('property'), $sub('houses for rent'), 45000, 'new', 'Spacious double story portion, separate meter, near Government High School.', 'demo/a-house.jpg', 0, 0],
        ['Sahiwal Cow 3rd Lactation (Demo)', $cat('animals'), $sub('cows & buffaloes'), 265000, 'used', 'Healthy Sahiwal cow, 8 litres daily, vaccinated, price slightly negotiable.', 'demo/a-cow.jpg', 0, 1],
        ['HP Core i5 Laptop 8/512 (Demo)', $cat('electronics'), $sub('computers & laptops'), 98000, 'used', 'Like new condition with charger and bag. Ideal for students & freelancers.', 'demo/a-laptop.jpg', 0, 0],
    ];
    foreach ($ads as $i => $a) {
        $aid = $ins('INSERT INTO ads (user_id,title,slug,description,category_id,subcategory_id,`condition`,price,negotiable,city_id,area,phone,whatsapp,seller_type,status,is_featured,is_urgent,views,expires_at,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $seller1, $a[0], slugify_s($a[0]), $a[5], $a[1], $a[2], $a[4], $a[3], 1, $city('Kamalia'), 'Main Bazaar', '03001234567', '923001234567',
            'individual', 'active', $a[7], $a[8], rand(10, 300), date('Y-m-d H:i:s', time() + 86400 * 30), $now,
        ]);
        $ins('INSERT INTO ad_images (ad_id,image,sort_order) VALUES (?,?,0)', [$aid, $a[6]]);
    }

    /* demo businesses */
    $biz = [
        ['Al-Madina Restaurant (Demo)', 'restaurants', 'Best desi cuisine in Kamalia — karahi, BBQ, biryani and fast food. Family hall available.', 'Main Bazaar, Kamalia', 1],
        ['Kamalia Medical Store (Demo)', 'pharmacies', 'Genuine medicines, 24/7 open. Free home delivery within city on orders above Rs 1000.', 'Kotwali Road, Kamalia', 0],
        ['The Educators School Kamalia (Demo)', 'schools', 'Quality education from Playgroup to Matric. Experienced staff, science & computer labs.', 'Garh Maharaja Road, Kamalia', 1],
    ];
    foreach ($biz as $b) {
        $ins('INSERT INTO businesses (user_id,name,slug,category_id,description,logo,cover,phone,whatsapp,address,city_id,opening_hours,status,is_featured,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $seller2, $b[0], slugify_s($b[0]), $cat($b[1]), $b[2], 'demo/biz-logo.png', 'demo/biz-cover.jpg', '03009998887', '923009998887', $b[3], $city('Kamalia'), '9:00 AM - 11:00 PM', 'approved', $b[4], $now,
        ]);
    }

    /* demo coupons */
    $ins('INSERT INTO coupons (shop_id,code,type,value,min_order,max_uses,expires_at,status) VALUES (?,?,?,?,?,?,?,?)', [null, 'WELCOME10', 'percent', 10, 1000, 1000, date('Y-m-d H:i:s', time() + 86400 * 30), 'active']);
    $ins('INSERT INTO coupons (shop_id,code,type,value,min_order,max_uses,expires_at,status) VALUES (?,?,?,?,?,?,?,?)', [$shop1, 'MOBILE500', 'fixed', 500, 20000, 100, date('Y-m-d H:i:s', time() + 86400 * 15), 'active']);

    /* demo testimonials */
    $testi = [
        ['Muhammad Usman', 'Shop keeper from Kamalia who now receives orders from WhatsApp and eKamalia both. Recommended!', 5, 'Kamalia Mobile Point'],
        ['Ayesha Bibi', 'Ghar bethay order kiya, agle din delivery mil gayi. Kamalia ke liye bohat achi platform hai.', 5, ''],
        ['Rana Tanveer', 'Bijli updates feature is genius — I check feeder timing before starting my workshop machines.', 4, 'Tanveer Workshop'],
    ];
    foreach ($testi as $i => $tt) $ins('INSERT INTO testimonials (name,comment,rating,business,sort_order) VALUES (?,?,?,?,?)', [$tt[0], $tt[1], $tt[2], $tt[3], $i]);

    /* demo news */
    $ins('INSERT INTO news (user_id,title,slug,excerpt,content,status,published_at) VALUES (?,?,?,?,?,?,?)', [
        null, 'eKamalia Launches Feeder-Wise Bijli Updates for Kamalia', 'ekamalia-launches-bijli-updates',
        'Now Kamalia residents can check electricity on/off status feeder by feeder — in real time.',
        '<p>eKamalia has launched a dedicated <strong>Bijli Updates</strong> section where residents of Kamalia and surrounding areas can check feeder-wise electricity status in real time.</p><p>The team updates feeders like Kamalia City Feeder-1, Feeder-2, Karkhana Feeder and Rural Feeder-341 whenever load shedding starts or supply returns. Users can also report outages to help neighbors.</p><p>Visit the <a href="/bijli">Bijli Updates</a> page or bookmark it on your phone.</p>',
        'published', $now,
    ]);

    /* demo feeder activity */
    $fd = fn(string $n) => (int)($one('SELECT id FROM feeders WHERE name LIKE ?', ["%$n%"])['id'] ?? 1);
    $ins('INSERT INTO feeder_updates (feeder_id,status,title,message,started_at,expected_at,is_resolved,created_by) VALUES (?,?,?,?,?,?,0,NULL)', [
        $fd('Feeder-1'), 'off', 'Load Shedding in progress', 'Feeder-1 par load shedding shuru — expected restoration soon.', date('Y-m-d H:i:s', time() - 1500), date('Y-m-d H:i:s', time() + 1800),
    ]);
    $ins('INSERT INTO feeder_updates (feeder_id,status,title,message,started_at,expected_at,is_resolved,created_by) VALUES (?,?,?,?,?,?,0,NULL)', [
        $fd('Karkhana'), 'maintenance', 'Scheduled Maintenance', 'Maintenance work at Karkhana Feeder. Supply may flicker.', date('Y-m-d H:i:s', time() - 3600), date('Y-m-d H:i:s', time() + 3600),
    ]);
}
