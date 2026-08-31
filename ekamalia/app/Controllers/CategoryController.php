<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Categories overview — all categories with children and live counts.
 */
final class CategoryController extends Controller
{
    public function all(): void
    {
        $parents = qa("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order, name");
        foreach ($parents as &$c) {
            $c['children'] = qa("SELECT c.*,
                (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.status='published' AND p.deleted_at IS NULL) AS product_count,
                (SELECT COUNT(*) FROM ads a WHERE a.category_id=c.id AND a.status='active' AND a.deleted_at IS NULL) AS ad_count
                FROM categories c WHERE c.parent_id=? AND c.status = 'active' ORDER BY c.sort_order, c.name", [$c['id']]);
            $c['product_count'] = (int)qv("SELECT COUNT(*) FROM products WHERE category_id=? AND status='published' AND deleted_at IS NULL", [$c['id']]);
            $c['ad_count'] = (int)qv("SELECT COUNT(*) FROM ads WHERE category_id=? AND status='active' AND deleted_at IS NULL", [$c['id']]);
        }
        unset($c);
        $this->view('categories/all', ['parents' => $parents, 'seo' => [
            'title' => 'All Categories — eKamalia',
            'description' => 'Browse every category on eKamalia — products and classified ads across Kamalia and Toba Tek Singh.',
        ]]);
    }
}
