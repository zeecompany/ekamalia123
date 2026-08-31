<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * eKamalia Bijli Updates — feeder-wise electricity news for
 * Kamalia (Toba Tek Singh) and surrounding cities.
 */
class BijliController extends Controller
{
    public static function feederStatuses(?int $cityId = null, ?int $feederId = null): array
    {
        $conds = ['f.is_active=1']; $params = [];
        if ($cityId) { $conds[] = 'f.city_id=?'; $params[] = $cityId; }
        if ($feederId) { $conds[] = 'f.id=?'; $params[] = $feederId; }
        return qa('SELECT f.*, c.name AS city_name, c.slug AS city_slug,
              (SELECT fu.status FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS status,
              (SELECT fu.title FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS last_title,
              (SELECT fu.message FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS message,
              (SELECT fu.started_at FROM feeder_updates fu WHERE fu.feeder_id=f.id AND fu.is_resolved=0 ORDER BY fu.created_at DESC LIMIT 1) AS started_at,
              (SELECT fu.expected_at FROM feeder_updates fu WHERE fu.feeder_id=f.id AND fu.is_resolved=0 ORDER BY fu.created_at DESC LIMIT 1) AS expected_at,
              (SELECT fu.created_at FROM feeder_updates fu WHERE fu.feeder_id=f.id ORDER BY fu.created_at DESC LIMIT 1) AS last_update
            FROM feeders f JOIN cities c ON c.id=f.city_id
            WHERE ' . implode(' AND ', $conds) . ' ORDER BY c.is_primary DESC, f.sort_order', $params);
    }

    public static function recentUpdates(int $limit = 30, ?int $feederId = null): array
    {
        $where = $feederId ? 'WHERE fu.feeder_id=' . (int)$feederId : '';
        return qa("SELECT fu.*, f.name AS feeder_name, c.name AS city_name, u.name AS updated_by
                   FROM feeder_updates fu JOIN feeders f ON f.id=fu.feeder_id JOIN cities c ON c.id=f.city_id
                   LEFT JOIN users u ON u.id=fu.created_by $where ORDER BY fu.created_at DESC LIMIT " . (int)$limit);
    }

    public function index(): void
    {
        $citySlug = str_input('city', '', 140);
        $feederId = int_input('feeder') ?: null;
        $cityId = null;
        $activeCity = null;
        if ($citySlug) {
            $activeCity = q1('SELECT * FROM cities WHERE slug=?', [$citySlug]);
            if ($activeCity) $cityId = (int)$activeCity['id'];
        }
        $feeders = self::feederStatuses($cityId, $feederId);
        $updates = self::recentUpdates(40, $feederId);
        $cities = qa('SELECT c.*, (SELECT COUNT(*) FROM feeders f WHERE f.city_id=c.id AND f.is_active=1) AS feeder_count
                      FROM cities c WHERE c.is_active=1 AND EXISTS (SELECT 1 FROM feeders f WHERE f.city_id=c.id) ORDER BY c.is_primary DESC, c.sort_order');
        $offCount = count(array_filter($feeders, fn($f) => $f['status'] === 'off'));
        $this->view('bijli/index', [
            'feeders' => $feeders, 'updates' => $updates, 'cities' => $cities, 'activeCity' => $activeCity,
            'offCount' => $offCount, 'reportModal' => false,
            'seo' => [
                'title' => 'Kamalia Bijli Updates — Feeder Wise Load Shedding News (Toba Tek Singh) | eKamalia',
                'description' => 'Live feeder-wise electricity on/off updates for Kamalia City, Karkhana, Rural 341, Pirmahal, Toba Tek Singh, Chichawatni & Gojra feeders.',
            ],
        ]);
    }

    /* user outage report */
    public function report(): void
    {
        $me = require_login();
        $feederId = int_input('feeder_id');
        $msg = str_input('message', '', 400);
        $f = q1('SELECT * FROM feeders WHERE id=? AND is_active=1', [$feederId]);
        if (!$f || mb_strlen($msg) < 5) json_fail('Please select a feeder and write a short message.');
        if (rate_limited('bijli_report', (string)$me['id'], 5, 3600)) json_fail('Too many reports. Please wait.');
        q('INSERT INTO feeder_reports (feeder_id,user_id,message,created_at) VALUES (?,?,?,?)', [$feederId, $me['id'], $msg, now()]);
        rate_hit('bijli_report', (string)$me['id']);
        notify_admins('Bijli outage reported', $f['name'] . ': ' . $msg, 'moderation', '/admin/bijli');
        json_ok(['message' => 'Shukriya! Your report helps neighbors stay informed.']);
    }

    public function reportsList(): void
    {
        $feederId = int_input('feeder') ?: null;
        $where = $feederId ? 'WHERE fr.feeder_id=' . (int)$feederId : '';
        $rows = qa("SELECT fr.*, f.name AS feeder_name, u.name AS user_name FROM feeder_reports fr
                    JOIN feeders f ON f.id=fr.feeder_id LEFT JOIN users u ON u.id=fr.user_id
                    $where ORDER BY fr.created_at DESC LIMIT 30");
        json_ok(['reports' => $rows]);
    }
}
