<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class CatalogController extends Controller
{
    /* ---------------- products moderation ---------------- */
    public function products(): void
    {
        require_admin();
        $status = str_input('status', '');
        $q = str_input('q', '', 100);
        $conds = ['p.deleted_at IS NULL']; $params = [];
        if (in_array($status, ['draft', 'pending', 'published', 'hidden', 'archived', 'out_of_stock'], true)) { $conds[] = 'p.status=?'; $params[] = $status; }
        if ($q) { $conds[] = '(p.name LIKE ? OR p.sku=?)'; $params[] = "%$q%"; $params[] = $q; }
        $perPage = 30; $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM products p WHERE ' . implode(' AND ', $conds), $params);
        $products = qa('SELECT p.*, s.name AS shop_name, c.name AS cat_name FROM products p LEFT JOIN shops s ON s.id=p.shop_id LEFT JOIN categories c ON c.id=p.category_id
                        WHERE ' . implode(' AND ', $conds) . " ORDER BY FIELD(p.status,'pending','published'), p.created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);
        $this->view('admin/products', ['products' => $products, 'status' => $status, 'q' => $q, 'total' => $total, 'perPage' => $perPage, 'seo' => ['title' => 'Products — Admin']], 'layouts/admin');
    }

    public function productUpdate(array $params): void
    {
        require_admin();
        $p = q1('SELECT * FROM products WHERE id=?', [(int)$params['id']]);
        if (!$p) not_found();
        $action = str_input('action');
        $note = str_input('note', '', 300);
        switch ($action) {
            case 'approve':
                q('UPDATE products SET status="published", status_note=NULL WHERE id=?', [$p['id']]);
                $seller = qv('SELECT user_id FROM shops WHERE id=?', [$p['shop_id']]);
                if ($seller) notify((int)$seller, 'Product approved ✅', $p['name'] . ' is now live.', 'moderation', '/seller/products');
                $msg = 'Product approved';
                break;
            case 'reject':
                q('UPDATE products SET status="hidden", status_note=? WHERE id=?', [$note ?: 'Does not meet guidelines', $p['id']]);
                $seller = qv('SELECT user_id FROM shops WHERE id=?', [$p['shop_id']]);
                if ($seller) notify((int)$seller, 'Product rejected', $p['name'] . ': ' . ($note ?: 'Review our guidelines.'), 'moderation');
                $msg = 'Product rejected';
                break;
            case 'feature': q('UPDATE products SET is_featured=1 WHERE id=?', [$p['id']]); $msg = 'Featured'; break;
            case 'unfeature': q('UPDATE products SET is_featured=0 WHERE id=?', [$p['id']]); $msg = 'Unfeatured'; break;
            case 'hide': q('UPDATE products SET status="hidden" WHERE id=?', [$p['id']]); $msg = 'Hidden'; break;
            case 'restore': q('UPDATE products SET status="published", status_note=NULL WHERE id=?', [$p['id']]); $msg = 'Restored'; break;
            case 'delete':
                q('UPDATE products SET deleted_at=?, status="archived" WHERE id=?', [now(), $p['id']]);
                $msg = 'Product deleted';
                break;
            default: json_fail('Unknown action');
        }
        audit_log('product.' . $action, 'product', (int)$p['id'], $p['name']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/products');
    }

    /* ---------------- ads moderation ---------------- */
    public function ads(): void
    {
        require_admin();
        $status = str_input('status', '');
        $q = str_input('q', '', 100);
        $conds = ['a.deleted_at IS NULL']; $params = [];
        if (in_array($status, ['pending', 'active', 'paused', 'sold', 'rejected', 'expired'], true)) { $conds[] = 'a.status=?'; $params[] = $status; }
        if ($q) { $conds[] = 'a.title LIKE ?'; $params[] = "%$q%"; }
        $perPage = 30; $page = max(1, int_input('page', 1));
        $total = (int)qv('SELECT COUNT(*) FROM ads a WHERE ' . implode(' AND ', $conds), $params);
        $ads = qa('SELECT a.*, c.name AS city_name, u.name AS user_name, (SELECT image FROM ad_images ai WHERE ai.ad_id=a.id LIMIT 1) AS image
                   FROM ads a LEFT JOIN cities c ON c.id=a.city_id JOIN users u ON u.id=a.user_id
                   WHERE ' . implode(' AND ', $conds) . " ORDER BY FIELD(a.status,'pending','active'), a.created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);
        $this->view('admin/ads', ['ads' => $ads, 'status' => $status, 'q' => $q, 'total' => $total, 'perPage' => $perPage, 'seo' => ['title' => 'Classified Ads — Admin']], 'layouts/admin');
    }

    public function adUpdate(array $params): void
    {
        require_admin();
        $a = q1('SELECT * FROM ads WHERE id=?', [(int)$params['id']]);
        if (!$a) not_found();
        $action = str_input('action');
        $note = str_input('note', '', 300);
        $days = max(1, int_input('days', 7));
        switch ($action) {
            case 'approve': q('UPDATE ads SET status="active", status_note=NULL, expires_at=? WHERE id=?', [date('Y-m-d H:i:s', time() + 86400 * 30), $a['id']]);
                notify((int)$a['user_id'], 'Ad approved ✅', $a['title'] . ' is now live.', 'moderation', '/dashboard/my-ads'); $msg = 'Ad approved'; break;
            case 'reject': q('UPDATE ads SET status="rejected", status_note=? WHERE id=?', [$note ?: 'Does not meet guidelines', $a['id']]);
                notify((int)$a['user_id'], 'Ad rejected', $a['title'] . ': ' . ($note ?: 'Review our posting rules.'), 'moderation'); $msg = 'Ad rejected'; break;
            case 'feature': q('UPDATE ads SET is_featured=1, featured_until=? WHERE id=?', [date('Y-m-d H:i:s', time() + $days * 86400), $a['id']]); $msg = "Featured $days days"; break;
            case 'urgent': q('UPDATE ads SET is_urgent=1, urgent_until=? WHERE id=?', [date('Y-m-d H:i:s', time() + $days * 86400), $a['id']]); $msg = "Urgent $days days"; break;
            case 'unfeature': q('UPDATE ads SET is_featured=0 WHERE id=?', [$a['id']]); $msg = 'Unfeatured'; break;
            case 'unurgent': q('UPDATE ads SET is_urgent=0 WHERE id=?', [$a['id']]); $msg = 'Urgent removed'; break;
            case 'pause': q('UPDATE ads SET status="paused" WHERE id=?', [$a['id']]); $msg = 'Ad paused'; break;
            case 'delete':
                q('UPDATE ads SET deleted_at=? WHERE id=?', [now(), $a['id']]);
                notify((int)$a['user_id'], 'Ad removed', $a['title'] . ' was removed by moderation.', 'moderation');
                $msg = 'Ad deleted';
                break;
            default: json_fail('Unknown action');
        }
        audit_log('ad.' . $action, 'ad', (int)$a['id'], $a['title']);
        if (is_ajax()) json_ok(['message' => $msg]);
        flash('success', $msg);
        back('/admin/ads');
    }

    /* ---------------- categories ---------------- */
    public function categories(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            $name = str_input('name', '', 140);
            if (mb_strlen($name) < 2) { flash('danger', 'Category name required.'); back('/admin/categories'); }
            $parentId = int_input('parent_id') ?: null;
            try { $icon_img = upload_image('image', 'categories', 3); } catch (\RuntimeException $e) { $icon_img = null; }
            if ($id) {
                q('UPDATE categories SET name=?, slug=COALESCE(NULLIF(?,""),slug), icon=?, type=?, parent_id=?, description=?, is_featured=?, sort_order=?, status=? WHERE id=?',
                    [$name, str_input('slug', '', 160), str_input('icon', 'fa-tag', 60) ?: 'fa-tag', str_input('type', 'both'), $parentId, str_input('description', '', 500), input('is_featured') ? 1 : 0, int_input('sort_order'), input('status') === 'inactive' ? 'inactive' : 'active', $id]);
            } else {
                q('INSERT INTO categories (name,slug,icon,image,type,parent_id,description,is_featured,sort_order,status) VALUES (?,?,?,?,?,?,?,?,?,?)',
                    [$name, unique_slug('categories', $name), str_input('icon', 'fa-tag', 60) ?: 'fa-tag', $icon_img, str_input('type', 'both'), $parentId, str_input('description', '', 500), input('is_featured') ? 1 : 0, int_input('sort_order'), 'active']);
            }
            audit_log('category.saved', 'category', $id, $name);
            flash('success', 'Category saved.');
            back('/admin/categories');
        }
        $type = str_input('type', '');
        $conds = ['1=1']; $params = [];
        if (in_array($type, ['both', 'ad', 'product', 'business'], true)) { $conds[] = 'type=?'; $params[] = $type; }
        $cats = qa('SELECT c.*, p.name AS parent_name, (SELECT COUNT(*) FROM ads a WHERE a.category_id=c.id) AS ads_count, (SELECT COUNT(*) FROM products pp WHERE pp.category_id=c.id) AS products_count
                    FROM categories c LEFT JOIN categories p ON p.id=c.parent_id WHERE ' . implode(' AND ', $conds) . ' ORDER BY c.type, c.sort_order, c.id', $params);
        $parents = qa('SELECT id,name,type FROM categories WHERE parent_id IS NULL ORDER BY name');
        $this->view('admin/categories', ['cats' => $cats, 'parents' => $parents, 'type' => $type, 'seo' => ['title' => 'Categories & Brands — Admin']], 'layouts/admin');
    }

    public function categoryDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM categories WHERE id=?', [(int)$params['id']]);
        audit_log('category.deleted', 'category', (int)$params['id']);
        flash('success', 'Category deleted.');
        back('/admin/categories');
    }

    /* ---------------- brands ---------------- */
    public function brands(): void
    {
        require_admin();
        if (is_post()) {
            $id = int_input('id');
            try { $logo = upload_image('logo', 'categories', 3); } catch (\RuntimeException $e) { $logo = null; }
            if ($id) q('UPDATE brands SET name=?, logo=COALESCE(?,logo), status=? WHERE id=?', [str_input('name', '', 140), $logo, input('status') === 'inactive' ? 'inactive' : 'active', $id]);
            else q('INSERT INTO brands (name,slug,logo,status) VALUES (?,?,?,?)', [str_input('name', '', 140), unique_slug('brands', str_input('name', 'brand')), $logo, 'active']);
            flash('success', 'Brand saved.');
            back('/admin/categories?tab=brands');
        }
        back('/admin/categories?tab=brands');
    }

    public function brandDelete(array $params): void
    {
        require_admin();
        q('DELETE FROM brands WHERE id=?', [(int)$params['id']]);
        flash('success', 'Brand deleted.');
        back('/admin/categories?tab=brands');
    }
}
