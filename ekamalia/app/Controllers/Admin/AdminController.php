<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        require_admin();
        $kpi = [
            'users' => (int)qv('SELECT COUNT(*) FROM users WHERE role="user"'),
            'active_users' => (int)qv('SELECT COUNT(*) FROM users WHERE status="active" AND role="user"'),
            'shops' => (int)qv('SELECT COUNT(*) FROM shops WHERE status="approved"'),
            'pending_shops' => (int)qv('SELECT COUNT(*) FROM shops WHERE status="pending"'),
            'products' => (int)qv('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL'),
            'pending_products' => (int)qv('SELECT COUNT(*) FROM products WHERE status="pending"'),
            'ads' => (int)qv('SELECT COUNT(*) FROM ads WHERE deleted_at IS NULL'),
            'orders' => (int)qv('SELECT COUNT(*) FROM orders'),
            'revenue' => (float)qv('SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN ("delivered","completed")'),
            'pending_payments' => (int)qv('SELECT COUNT(*) FROM payments WHERE status="submitted"'),
            'pos_users' => (int)qv('SELECT COUNT(*) FROM pos_users WHERE status="active"'),
            'pending_pos' => (int)qv('SELECT COUNT(*) FROM pos_requests WHERE status="pending"'),
            'reports' => (int)qv('SELECT COUNT(*) FROM reports WHERE status="open"'),
            'pending_reviews' => (int)qv('SELECT COUNT(*) FROM reviews WHERE status="pending"'),
            'open_comments' => (int)qv('SELECT COUNT(*) FROM comments WHERE status="visible"'),
            'messages' => (int)qv('SELECT COUNT(*) FROM contact_messages WHERE status="new"'),
        ];
        $revenue14 = qa('SELECT DATE(created_at) d, COALESCE(SUM(total),0) t, COUNT(*) c FROM orders WHERE created_at>=DATE_SUB(NOW(), INTERVAL 13 DAY) GROUP BY DATE(created_at) ORDER BY d');
        $users14 = qa('SELECT DATE(created_at) d, COUNT(*) c FROM users WHERE created_at>=DATE_SUB(NOW(), INTERVAL 13 DAY) GROUP BY DATE(created_at) ORDER BY d');
        $topCats = qa('SELECT c.name, (SELECT COUNT(*) FROM ads a WHERE a.category_id=c.id)+(SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS cnt FROM categories c WHERE c.parent_id IS NULL ORDER BY cnt DESC LIMIT 7');
        $topShops = qa('SELECT s.name, COALESCE(SUM(o.total),0) revenue, COUNT(o.id) orders FROM shops s LEFT JOIN orders o ON o.shop_id=s.id AND o.status IN ("delivered","completed") WHERE s.status="approved" GROUP BY s.id ORDER BY revenue DESC LIMIT 6');
        $topCities = qa('SELECT COALESCE(NULLIF(ship_city,""),"Kamalia") city, COUNT(*) c FROM orders GROUP BY city ORDER BY c DESC LIMIT 6');
        $recent = qa('SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 8');
        $feedersOff = (int)qv('SELECT COUNT(DISTINCT feeder_id) FROM feeder_updates fu JOIN feeders f ON f.id=fu.feeder_id WHERE fu.status="off" AND fu.created_at >= (SELECT MAX(fu2.created_at) FROM feeder_updates fu2 WHERE fu2.feeder_id=fu.feeder_id)');
        $this->view('admin/dashboard', ['kpi' => $kpi, 'revenue14' => $revenue14, 'users14' => $users14, 'topCats' => $topCats, 'topShops' => $topShops, 'topCities' => $topCities, 'recent' => $recent, 'feedersOff' => $feedersOff,
            'seo' => ['title' => 'Admin Dashboard — eKamalia']], 'layouts/admin');
    }

    /* ---------------- audit logs ---------------- */
    public function auditLogs(): void
    {
        require_admin();
        $action = str_input('q', '', 80);
        $conds = ['1=1']; $params = [];
        if ($action) { $conds[] = '(a.action LIKE ? OR a.details LIKE ?)'; $params[] = "%$action%"; $params[] = "%$action%"; }
        $logs = qa('SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id WHERE ' . implode(' AND ', $conds) . ' ORDER BY a.created_at DESC LIMIT 300', $params);
        $this->view('admin/audit', ['logs' => $logs, 'q' => $action, 'seo' => ['title' => 'Audit Logs — Admin']], 'layouts/admin');
    }

    /* ---------------- system health ---------------- */
    public function health(): void
    {
        require_admin();
        $info = [
            'php' => PHP_VERSION, 'php_ok' => version_compare(PHP_VERSION, '7.4', '>='),
            'mysql' => db()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'db_ok' => true,
            'disk_free' => @disk_free_space(EK_ROOT) ? round(@disk_free_space(EK_ROOT) / 1048576 / 1024, 2) . ' GB' : 'unknown',
            'uploads_writable' => is_writable(EK_ROOT . '/uploads'),
            'storage_writable' => is_writable(EK_ROOT . '/storage/logs'),
            'gd' => extension_loaded('gd'), 'curl' => extension_loaded('curl'), 'mbstring' => extension_loaded('mbstring'),
            'smtp_set' => setting('smtp_host') !== null && setting('smtp_host') !== '',
            'otp_enabled' => setting('otp_enabled') === '1',
            'maintenance' => setting('maintenance_mode') === '1',
            'version' => config('app.version', '1.0.0'),
            'installed' => is_file(EK_ROOT . '/storage/install.lock'),
            'env' => config('app.env', 'production'),
            'logs' => is_file(EK_ROOT . '/storage/logs/app.log') ? round(filesize(EK_ROOT . '/storage/logs/app.log') / 1024, 1) . ' KB' : '0 KB',
            'email_failures' => (int)qv('SELECT COUNT(*) FROM email_logs WHERE status="failed"'),
        ];
        $this->view('admin/health', ['info' => $info, 'seo' => ['title' => 'System Health — Admin']], 'layouts/admin');
    }

    /* ---------------- backups ---------------- */
    public function backups(): void
    {
        require_admin();
        if (is_post() && str_input('action') === 'create') {
            $this->createBackup();
        }
        $files = [];
        foreach (glob(EK_ROOT . '/storage/backups/*.sql.gz') ?: [] as $f) {
            $files[] = ['name' => basename($f), 'size' => round(filesize($f) / 1024, 1) . ' KB', 'date' => date('d M Y H:i', filemtime($f))];
        }
        usort($files, fn($a, $b) => strcmp($b['date'], $a['date']));
        $this->view('admin/backups', ['files' => $files, 'seo' => ['title' => 'Backups — Admin']], 'layouts/admin');
    }

    private function createBackup(): void
    {
        require_admin();
        try {
            $tables = array_column(qa('SHOW TABLES'), 0);
            $sql = "-- eKamalia backup " . date('c') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n";
            foreach ($tables as $t) {
                $create = q1("SHOW CREATE TABLE `$t`");
                $sql .= "DROP TABLE IF EXISTS `$t`;\n" . $create['Create Table'] . ";\n";
                $st = db()->query("SELECT * FROM `$t`");
                while ($row = $st->fetch(\PDO::FETCH_ASSOC)) {
                    $vals = array_map(fn($v) => $v === null ? 'NULL' : db()->quote((string)$v), array_values($row));
                    $cols = '`' . implode('`,`', array_keys($row)) . '`';
                    $sql .= "INSERT INTO `$t` ($cols) VALUES (" . implode(',', $vals) . ");\n";
                }
            }
            $sql .= "SET FOREIGN_KEY_CHECKS=1;";
            $path = EK_ROOT . '/storage/backups/backup_' . date('Ymd_His') . '.sql.gz';
            @file_put_contents($path, gzencode($sql, 6));
            audit_log('backup.created', 'system', 0, basename($path));
            flash('success', 'Backup created: ' . basename($path));
        } catch (\Throwable $e) {
            flash('danger', 'Backup failed: ' . $e->getMessage());
        }
        back('/admin/backups');
    }

    public function backupDownload(array $params): void
    {
        require_admin();
        $name = basename($params['name']);
        $path = realpath(EK_ROOT . '/storage/backups/' . $name);
        if (!$path || !str_starts_with($path, realpath(EK_ROOT . '/storage/backups')) || !is_file($path)) not_found();
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function backupDelete(array $params): void
    {
        require_admin();
        $name = basename($params['name']);
        $path = realpath(EK_ROOT . '/storage/backups/' . $name);
        if ($path && str_starts_with($path, realpath(EK_ROOT . '/storage/backups'))) {
            @unlink($path);
            audit_log('backup.deleted', 'system', 0, $name);
            flash('success', 'Backup deleted.');
        }
        back('/admin/backups');
    }
}
