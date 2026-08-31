<?php
declare(strict_types=1);

namespace App\Controllers;

class PageController extends Controller
{
    public function show(array $params): void
    {
        $page = q1('SELECT * FROM pages WHERE slug=? AND status="active"', [$params['slug']]);
        if (!$page) not_found();
        $this->view('pages/show', ['page' => $page,
            'seo' => ['title' => ($page['seo_title'] ?: $page['title']) . ' — ' . setting('site_name', 'eKamalia'),
                      'description' => $page['seo_description'] ?: mb_substr(strip_tags((string)$page['content']), 0, 160)]]);
    }

    /* ---------------- news ---------------- */
    public function news(): void
    {
        $news = qa('SELECT n.*, u.name AS author FROM news n LEFT JOIN users u ON u.id=n.user_id WHERE n.status="published" ORDER BY n.published_at DESC LIMIT 30');
        $this->view('news/index', ['news' => $news, 'seo' => ['title' => 'Kamalia News & Local Updates — eKamalia']]);
    }

    public function newsShow(array $params): void
    {
        $n = q1('SELECT n.*, u.name AS author FROM news n LEFT JOIN users u ON u.id=n.user_id WHERE n.slug=? AND n.status="published"', [$params['slug']]);
        if (!$n) not_found();
        q('UPDATE news SET views=views+1 WHERE id=?', [$n['id']]);
        $related = qa('SELECT title,slug,image,published_at FROM news WHERE status="published" AND id<>? ORDER BY published_at DESC LIMIT 4', [$n['id']]);
        $this->view('news/show', ['n' => $n, 'related' => $related,
            'seo' => ['title' => $n['title'] . ' — eKamalia News', 'description' => mb_substr(strip_tags((string)$n['excerpt']), 0, 160),
                      'og_image' => $n['image'] ? upload_url($n['image']) : asset('img/og-image.jpg')]]);
    }

    /* ---------------- contact ---------------- */
    public function contact(): void
    {
        $this->view('pages/contact', ['seo' => ['title' => 'Contact Us — eKamalia Kamalia']]);
    }

    public function contactSubmit(): void
    {
        $name = str_input('name', '', 120);
        $email = strtolower(str_input('email', '', 190));
        $msg = str_input('message', '', 5000);
        if (mb_strlen($name) < 2 || mb_strlen($msg) < 10) { stash_old(); flash('danger', 'Please fill your name and a proper message.'); back('/contact'); }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { stash_old(); flash('danger', 'Please enter a valid email.'); back('/contact'); }
        if (rate_limited('contact', client_ip(), 5, 3600)) { flash('danger', 'Too many messages. Please wait.'); back('/contact'); }
        q('INSERT INTO contact_messages (name,email,phone,subject,message,created_at) VALUES (?,?,?,?,?,?)',
            [$name, $email, preg_replace('/\D/', '', str_input('phone', '', 20)) ?: null, str_input('subject', 'General', 190), $msg, now()]);
        rate_hit('contact', client_ip());
        notify_admins('New contact message', $name . ': ' . mb_substr($msg, 0, 100), 'admin', '/admin/inbox');
        send_app_mail($email, 'We received your message — eKamalia', "Assalam-o-Alaikum $name,\n\nShukriya for contacting eKamalia. Our team will reply within 24 hours, In Sha Allah.", '/contact');
        clear_old();
        flash('success', 'Message sent! We will get back to you soon.');
        redirect('/contact');
    }

    /* ---------------- sitemap ---------------- */
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');
        $urls = [url('/'), url('/products'), url('/ads'), url('/shops'), url('/businesses'), url('/bijli'), url('/news'), url('/contact')];
        foreach (qa('SELECT slug FROM categories WHERE parent_id IS NULL AND status="active"') as $c) $urls[] = url('/category/' . $c['slug']);
        foreach (qa('SELECT slug, updated_at, created_at FROM products WHERE status="published" AND deleted_at IS NULL ORDER BY id DESC LIMIT 2000') as $r) $urls[] = url('/product/' . $r['slug']);
        foreach (qa('SELECT slug, created_at FROM ads WHERE status="active" AND deleted_at IS NULL ORDER BY id DESC LIMIT 2000') as $r) $urls[] = url('/ad/' . $r['slug']);
        foreach (qa('SELECT slug FROM shops WHERE status="approved" AND deleted_at IS NULL') as $r) $urls[] = url('/shop/' . $r['slug']);
        foreach (qa('SELECT slug FROM businesses WHERE status="approved"') as $r) $urls[] = url('/business/' . $r['slug']);
        foreach (qa('SELECT slug FROM pages WHERE status="active"') as $r) $urls[] = url('/page/' . $r['slug']);
        foreach (qa('SELECT slug FROM news WHERE status="published"') as $r) $urls[] = url('/news/' . $r['slug']);
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach (array_unique($urls) as $u) {
            echo '<url><loc>' . e($u) . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . "\n";
        }
        echo '</urlset>';
        exit;
    }
}
