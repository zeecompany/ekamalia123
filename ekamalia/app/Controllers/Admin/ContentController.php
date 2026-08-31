<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;

/** Content & marketing: sliders, marquee, ads, pages, news, testimonials, homepage, businesses, bijli */
class ContentController extends Controller
{
    /* ================= sliders ================= */
    public function sliders(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            try {
                $img = upload_image('image', 'sliders', 6);
                $imgM = upload_image('mobile_image', 'sliders', 6);
            } catch (\RuntimeException $e) { flash('danger', $e->getMessage()); back('/admin/sliders'); }
            $data = [str_input('title', '', 190) ?: null, str_input('subtitle', '', 300) ?: null,
                     filter_var(str_input('button_url'), FILTER_VALIDATE_URL) ?: str_input('button_url', '', 255) ?: null,
                     str_input('button_text', '', 80) ?: null, str_input('animation', 'fade', 40),
                     str_input('start_date') ?: null, str_input('end_date') ?: null,
                     input('status') === 'inactive' ? 'inactive' : 'active', int_input('sort_order')];
            if ($id) {
                q('UPDATE sliders SET title=?,subtitle=?,button_url=?,button_text=?,animation=?,start_date=?,end_date=?,status=?,sort_order=? WHERE id=?', array_merge($data, [$id]));
                if ($img) q('UPDATE sliders SET image=? WHERE id=?', [$img, $id]);
                if ($imgM) q('UPDATE sliders SET mobile_image=? WHERE id=?', [$imgM, $id]);
            } else {
                if (!$img) { flash('danger', 'Slide image is required.'); back('/admin/sliders'); }
                q('INSERT INTO sliders (title,subtitle,button_url,button_text,animation,start_date,end_date,status,sort_order,image) VALUES (?,?,?,?,?,?,?,?,?,?)', array_merge($data, [$img]));
            }
            audit_log('slider.saved', 'slider', $id);
            flash('success', 'Slide saved.');
            back('/admin/sliders');
        }
        $sliders = qa('SELECT * FROM sliders ORDER BY sort_order, id');
        $this->view('admin/sliders', ['sliders' => $sliders, 'seo' => ['title' => 'Hero Sliders — Admin']], 'layouts/admin');
    }

    public function sliderDelete(array $params): void
    {
        require_admin();
        $s = q1('SELECT * FROM sliders WHERE id=?', [(int)$params['id']]);
        if ($s) { delete_upload($s['image']); delete_upload($s['mobile_image']); q('DELETE FROM sliders WHERE id=?', [$s['id']]); }
        flash('success', 'Slide deleted.');
        back('/admin/sliders');
    }

    /* ================= marquee ================= */
    public function marquees(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            $data = [str_input('type', 'announcement') === 'offer' ? 'offer' : 'announcement', str_input('text', '', 255),
                     str_input('url', '', 255) ?: null, str_input('start_date') ?: null, str_input('end_date') ?: null,
                     input('status') === 'inactive' ? 'inactive' : 'active', int_input('sort_order')];
            if ($id) { array_push($data, $id); q('UPDATE marquees SET type=?,text=?,url=?,start_date=?,end_date=?,status=?,sort_order=? WHERE id=?', $data); }
            else q('INSERT INTO marquees (type,text,url,start_date,end_date,status,sort_order) VALUES (?,?,?,?,?,?,?)', $data);
            audit_log('marquee.saved', 'marquee', $id);
            flash('success', 'Marquee message saved.');
            back('/admin/marquee');
        }
        $marquees = qa('SELECT * FROM marquees ORDER BY sort_order, id');
        $this->view('admin/marquee', ['marquees' => $marquees, 'seo' => ['title' => 'Marquee Messages — Admin']], 'layouts/admin');
    }

    public function marqueeDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM marquees WHERE id=?', [(int)$params['id']]);
        flash('success', 'Message deleted.');
        back('/admin/marquee');
    }

    /* ================= advertisements ================= */
    public function advertisements(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            try { $img = upload_image('image', 'banners', 6); } catch (\RuntimeException $e) { $img = null; }
            $data = [str_input('title', '', 190), filter_var(str_input('url'), FILTER_VALIDATE_URL) ?: null,
                     in_array(str_input('position'), ['banner', 'sidebar', 'category', 'in_content'], true) ? str_input('position') : 'banner',
                     str_input('start_date') ?: null, str_input('end_date') ?: null,
                     input('status') === 'inactive' ? 'inactive' : 'active', int_input('priority')];
            if ($id) {
                q('UPDATE advertisements SET title=?,url=?,position=?,start_date=?,end_date=?,status=?,priority=? WHERE id=?', array_merge($data, [$id]));
                if ($img) q('UPDATE advertisements SET image=? WHERE id=?', [$img, $id]);
            } else {
                if (!$img) { flash('danger', 'Ad image is required.'); back('/admin/advertisements'); }
                q('INSERT INTO advertisements (title,url,position,start_date,end_date,status,priority,image) VALUES (?,?,?,?,?,?,?,?)', array_merge($data, [$img]));
            }
            audit_log('advertisement.saved', 'advertisement', $id);
            flash('success', 'Advertisement saved.');
            back('/admin/advertisements');
        }
        $ads = qa('SELECT * FROM advertisements ORDER BY priority DESC, id DESC');
        $this->view('admin/advertisements', ['ads' => $ads, 'seo' => ['title' => 'Advertisements — Admin']], 'layouts/admin');
    }

    public function advertisementDelete(array $params): void
    {
        require_admin();
        $a = q1('SELECT * FROM advertisements WHERE id=?', [(int)$params['id']]);
        if ($a) { delete_upload($a['image']); q('DELETE FROM advertisements WHERE id=?', [$a['id']]); }
        flash('success', 'Advertisement deleted.');
        back('/admin/advertisements');
    }

    /* ================= CMS pages ================= */
    public function pages(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            $title = str_input('title', '', 190);
            if (mb_strlen($title) < 2) { flash('danger', 'Page title required.'); back('/admin/pages'); }
            try { $img = upload_image('featured_image', 'banners', 5); } catch (\RuntimeException $e) { $img = null; }
            $data = [$title, str_input('content', '', 200000), $img, str_input('seo_title', '', 190) ?: null, str_input('seo_description', '', 300) ?: null,
                     input('show_in_footer') ? 1 : 0, input('status') === 'inactive' ? 'inactive' : 'active'];
            if ($id) {
                if ($title !== qv('SELECT title FROM pages WHERE id=?', [$id])) {
                    q('UPDATE pages SET slug=? WHERE id=?', [unique_slug('pages', $title), $id]);
                }
                q('UPDATE pages SET title=?,content=?,featured_image=COALESCE(?,featured_image),seo_title=?,seo_description=?,show_in_footer=?,status=? WHERE id=?', array_merge($data, [$id]));
            } else {
                q('INSERT INTO pages (title,slug,content,featured_image,seo_title,seo_description,show_in_footer,status) VALUES (?,?,?,?,?,?,?,?)',
                    array_merge([$title, unique_slug('pages', $title)], $data));
            }
            audit_log('page.saved', 'page', $id, $title);
            flash('success', 'Page saved.');
            back('/admin/pages');
        }
        $pages = qa('SELECT * FROM pages ORDER BY id');
        $this->view('admin/pages', ['pages' => $pages, 'seo' => ['title' => 'CMS Pages — Admin']], 'layouts/admin');
    }

    public function pageEdit(array $params): void
    {
        require_admin();
        $page = q1('SELECT * FROM pages WHERE id=?', [(int)$params['id']]);
        if (!$page) not_found();
        $pages = qa('SELECT * FROM pages ORDER BY id');
        $this->view('admin/pages', ['pages' => $pages, 'editPage' => $page, 'seo' => ['title' => 'Edit Page — Admin']], 'layouts/admin');
    }

    public function pageDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM pages WHERE id=?', [(int)$params['id']]);
        flash('success', 'Page deleted.');
        back('/admin/pages');
    }

    /* ================= news ================= */
    public function news(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            $title = str_input('title', '', 190);
            try { $img = upload_image('image', 'news', 5); } catch (\RuntimeException $e) { $img = null; flash('danger', $e->getMessage()); back('/admin/news'); }
            $data = [$title, str_input('excerpt', '', 400), str_input('content', '', 100000), $img, input('status') === 'draft' ? 'draft' : 'published'];
            if ($id) {
                q('UPDATE news SET title=?,excerpt=?,content=?,image=COALESCE(?,image),status=?, slug=IF(?="",slug,slug) WHERE id=?', array_merge($data, [str_input('slug', '', 210), $id]));
            } else {
                q('INSERT INTO news (user_id,title,slug,excerpt,content,image,status,published_at) VALUES (?,?,?,?,?,?,?,?)',
                    array_merge([user_id(), $title, unique_slug('news', $title)], $data, [now()]));
            }
            audit_log('news.saved', 'news', $id, $title);
            flash('success', 'News saved.');
            back('/admin/news');
        }
        $news = qa('SELECT * FROM news ORDER BY id DESC LIMIT 100');
        $this->view('admin/news', ['news' => $news, 'seo' => ['title' => 'News & Updates — Admin']], 'layouts/admin');
    }

    public function newsDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM news WHERE id=?', [(int)$params['id']]);
        flash('success', 'News deleted.');
        back('/admin/news');
    }

    /* ================= testimonials ================= */
    public function testimonials(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            try { $photo = upload_image('photo', 'avatars', 3, [200, 200]); } catch (\RuntimeException $e) { $photo = null; }
            $data = [str_input('name', '', 120), str_input('comment', '', 500), max(1, min(5, int_input('rating', 5))),
                     str_input('business', '', 160) ?: null, input('status') === 'inactive' ? 'inactive' : 'active', int_input('sort_order')];
            if ($id) {
                q('UPDATE testimonials SET name=?,comment=?,rating=?,business=?,status=?,sort_order=? WHERE id=?', array_merge($data, [$id]));
                if ($photo) q('UPDATE testimonials SET photo=? WHERE id=?', [$photo, $id]);
            } else {
                q('INSERT INTO testimonials (name,comment,rating,business,status,sort_order,photo) VALUES (?,?,?,?,?,?,?)', array_merge($data, [$photo]));
            }
            flash('success', 'Testimonial saved.');
            back('/admin/testimonials');
        }
        $items = qa('SELECT * FROM testimonials ORDER BY sort_order, id');
        $this->view('admin/testimonials', ['items' => $items, 'seo' => ['title' => 'Testimonials — Admin']], 'layouts/admin');
    }

    public function testimonialDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM testimonials WHERE id=?', [(int)$params['id']]);
        flash('success', 'Testimonial deleted.');
        back('/admin/testimonials');
    }

    /* ================= homepage builder ================= */
    public function homepage(): void
    {
        require_admin();
        if (is_post()) {
            foreach ((array)($_POST['sections'] ?? []) as $id => $vals) {
                q('UPDATE homepage_sections SET is_enabled=?, sort_order=? WHERE id=?',
                    [!empty($vals['enabled']) ? 1 : 0, (int)($vals['sort'] ?? 0), (int)$id]);
            }
            audit_log('homepage.updated');
            flash('success', 'Homepage sections updated.');
            back('/admin/homepage');
        }
        $sections = qa('SELECT * FROM homepage_sections ORDER BY sort_order');
        $this->view('admin/homepage', ['sections' => $sections, 'seo' => ['title' => 'Homepage Builder — Admin']], 'layouts/admin');
    }

    /* ================= businesses ================= */
    public function businesses(): void
    {
        require_admin();
        $status = str_input('status', '');
        $conds = ['b.deleted_at IS NULL']; $params = [];
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) { $conds[] = 'b.status=?'; $params[] = $status; }
        $businesses = qa('SELECT b.*, c.name AS cat_name, ct.name AS city_name FROM businesses b LEFT JOIN categories c ON c.id=b.category_id LEFT JOIN cities ct ON ct.id=b.city_id
                          WHERE ' . implode(' AND ', $conds) . ' ORDER BY FIELD(b.status,"pending","approved","rejected"), b.created_at DESC LIMIT 200', $params);
        $this->view('admin/businesses', ['businesses' => $businesses, 'status' => $status, 'seo' => ['title' => 'Business Directory — Admin']], 'layouts/admin');
    }

    public function businessUpdate(array $params): void
    {
        require_admin();
        $b = q1('SELECT * FROM businesses WHERE id=?', [(int)$params['id']]);
        if (!$b) not_found();
        $action = str_input('action');
        switch ($action) {
            case 'approve':
                q('UPDATE businesses SET status="approved" WHERE id=?', [$b['id']]);
                if ($b['user_id']) notify((int)$b['user_id'], 'Business approved ✅', $b['name'] . ' is now listed in the directory.', 'moderation');
                $msg = 'Business approved'; break;
            case 'reject': q('UPDATE businesses SET status="rejected" WHERE id=?', [$b['id']]); $msg = 'Business rejected'; break;
            case 'verify': q('UPDATE businesses SET is_verified=1 WHERE id=?', [$b['id']]); $msg = 'Business verified'; break;
            case 'feature': q('UPDATE businesses SET is_featured=1 WHERE id=?', [$b['id']]); $msg = 'Business featured'; break;
            case 'delete': q('UPDATE businesses SET deleted_at=? WHERE id=?', [now(), $b['id']]); $msg = 'Business deleted'; break;
            default: json_fail('Unknown action');
        }
        audit_log('business.' . $action, 'business', (int)$b['id'], $b['name']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/businesses');
    }

    /* ================= BIJLI (feeders & updates) ================= */
    public function bijli(): void
    {
        require_admin();
        $feeders = qa('SELECT f.*, c.name AS city_name,
              (SELECT fu.status FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS status,
              (SELECT fu.created_at FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS last_update,
              (SELECT COUNT(*) FROM feeder_reports fr WHERE fr.feeder_id=f.id AND fr.status="open") AS open_reports
            FROM feeders f JOIN cities c ON c.id=f.city_id ORDER BY c.is_primary DESC, f.sort_order');
        $updates = qa('SELECT fu.*, f.name AS feeder_name, u.name AS by_name FROM feeder_updates fu JOIN feeders f ON f.id=fu.feeder_id LEFT JOIN users u ON u.id=fu.created_by ORDER BY fu.created_at DESC LIMIT 50');
        $reports = qa('SELECT fr.*, f.name AS feeder_name, u.name AS user_name FROM feeder_reports fr JOIN feeders f ON f.id=fr.feeder_id LEFT JOIN users u ON u.id=fr.user_id ORDER BY FIELD(fr.status,"open","reviewed","resolved"), fr.created_at DESC LIMIT 50');
        $cities = qa('SELECT * FROM cities WHERE is_active=1 ORDER BY is_primary DESC, sort_order');
        $this->view('admin/bijli', ['feeders' => $feeders, 'updates' => $updates, 'reports' => $reports, 'cities' => $cities, 'seo' => ['title' => 'Bijli Updates Manager — Admin']], 'layouts/admin');
    }

    public function feederSave(): void
    {
        require_admin();
        $id = int_input('id');
        $name = str_input('name', '', 160);
        if (mb_strlen($name) < 3) { flash('danger', 'Feeder name required.'); back('/admin/bijli'); }
        if ($id) q('UPDATE feeders SET name=?, city_id=?, area=?, description=?, sort_order=?, is_active=? WHERE id=?',
            [$name, int_input('city_id'), str_input('area', '', 190), str_input('description', '', 400), int_input('sort_order'), input('is_active') ? 1 : 0, $id]);
        else {
            q('INSERT INTO feeders (name,city_id,area,description,sort_order,is_active) VALUES (?,?,?,?,?,?)',
                [$name, int_input('city_id'), str_input('area', '', 190), str_input('description', '', 400), int_input('sort_order'), 1]);
            $fid = last_id();
            q('INSERT INTO feeder_updates (feeder_id,status,title,message,started_at,is_resolved,resolved_at,created_by) VALUES (?,?,?,?,?,1,?,?)',
                [$fid, 'on', 'Added to system', 'Feeder added. Supply status: normal.', now(), now(), user_id()]);
        }
        audit_log('feeder.saved', 'feeder', $id, $name);
        flash('success', 'Feeder saved.');
        back('/admin/bijli');
    }

    public function feederDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM feeders WHERE id=?', [(int)$params['id']]);
        audit_log('feeder.deleted', 'feeder', (int)$params['id']);
        flash('success', 'Feeder deleted.');
        back('/admin/bijli');
    }

    public function feederUpdatePost(): void
    {
        require_admin();
        $feederId = int_input('feeder_id');
        $f = q1('SELECT * FROM feeders WHERE id=?', [$feederId]);
        if (!$f) not_found();
        $status = str_input('status', 'off');
        if (!in_array($status, ['on', 'off', 'maintenance', 'scheduled'], true)) $status = 'off';
        $startedAt = str_input('started_at') ?: now();
        // resolve any previous unresolved update for this feeder
        q('UPDATE feeder_updates SET is_resolved=1, resolved_at=? WHERE feeder_id=? AND is_resolved=0', [now(), $feederId]);
        $titleMap = ['on' => 'Supply Restored', 'off' => 'Power Shutdown', 'maintenance' => 'Maintenance Work', 'scheduled' => 'Scheduled Shutdown'];
        q('INSERT INTO feeder_updates (feeder_id,status,title,message,started_at,expected_at,is_resolved,created_by,created_at) VALUES (?,?,?,?,?,?,0,?,?)',
            [$feederId, $status, str_input('title', $titleMap[$status], 190), str_input('message', '', 500) ?: null, $startedAt, str_input('expected_at') ?: null, user_id(), now()]);
        // notify users who reported this feeder recently
        $reporters = qa('SELECT DISTINCT user_id FROM feeder_reports WHERE feeder_id=? AND user_id IS NOT NULL AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)', [$feederId]);
        foreach ($reporters as $rep) notify((int)$rep['user_id'], 'Bijli Update — ' . $f['name'], $titleMap[$status] . ': ' . (str_input('message', '', 120) ?: 'Status changed to ' . strtoupper($status)), 'general', '/bijli?feeder=' . $feederId);
        audit_log('feeder.update', 'feeder', $feederId, $f['name'] . ' → ' . $status);
        if (is_ajax()) json_ok(['message' => $f['name'] . ' updated to ' . strtoupper($status)]);
        flash('success', $f['name'] . ' status updated (' . strtoupper($status) . ').');
        back('/admin/bijli');
    }

    public function feederReportUpdate(array $params): void
    {
        require_admin();
        $status = str_input('status', 'reviewed');
        if (!in_array($status, ['open', 'reviewed', 'resolved'], true)) $status = 'reviewed';
        q('UPDATE feeder_reports SET status=? WHERE id=?', [$status, (int)$params['id']]);
        if (is_ajax()) json_ok(['message' => 'Report ' . $status]);
        flash('success', 'Report marked ' . $status . '.');
        back('/admin/bijli');
    }
}
