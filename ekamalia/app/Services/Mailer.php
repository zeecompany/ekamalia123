<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Minimal SMTP mailer (STARTTLS + AUTH LOGIN) — no external libs required.
 * Falls back to PHP mail() when SMTP is not configured. Failures are logged, never fatal.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $html, ?string $textAlt = null): bool
    {
        $host = \setting('smtp_host');
        $fromEmail = \setting('smtp_from_email', 'hello@ekamalia.com');
        $fromName = \setting('smtp_from_name', 'eKamalia');
        if (!$host) {
            $ok = self::phpMail($to, $subject, $html, $fromEmail, $fromName);
            self::log($to, $subject, $ok ? 'sent' : 'failed', 'no-smtp/php-mail');
            return $ok;
        }
        try {
            $port = (int)\setting('smtp_port', 587);
            $enc = \setting('smtp_encryption', 'tls'); // tls | ssl | none
            $timeout = 12;
            $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
            $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
            $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
            if (!$fp) throw new \RuntimeException("SMTP connect failed: $errstr");
            stream_set_timeout($fp, $timeout);

            self::read($fp);                                  // banner
            self::cmd($fp, 'EHLO ekamalia.local');
            if ($enc === 'tls') {
                self::cmd($fp, 'STARTTLS');
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('STARTTLS failed');
                }
                self::cmd($fp, 'EHLO ekamalia.local');
            }
            $user = \setting('smtp_user'); $pass = \setting('smtp_pass');
            if ($user) {
                self::cmd($fp, 'AUTH LOGIN');
                self::cmd($fp, base64_encode($user));
                self::cmd($fp, base64_encode($pass));
            }
            self::cmd($fp, "MAIL FROM:<$fromEmail>");
            self::cmd($fp, "RCPT TO:<$to>");
            self::cmd($fp, 'DATA');
            $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromEmail>\r\n"
                     . "To: <$to>\r\n"
                     . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
                     . "MIME-Version: 1.0\r\n"
                     . "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body = $headers . self::wrap($html) . "\r\n.";
            fwrite($fp, $body . "\r\n");
            $resp = fread($fp, 512);
            fclose($fp);
            $ok = str_starts_with((string)$resp, '250');
            self::log($to, $subject, $ok ? 'sent' : 'failed', substr((string)$resp, 0, 120));
            return $ok;
        } catch (\Throwable $e) {
            self::log($to, $subject, 'failed', $e->getMessage());
            return false;
        }
    }

    private static function phpMail(string $to, string $subject, string $html, string $fromEmail, string $fromName): bool
    {
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: $fromName <$fromEmail>\r\n";
        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers);
        return (bool)$ok;
    }

    private static function read($fp): string
    {
        $data = '';
        while ($line = fgets($fp, 515)) { $data .= $line; if (isset($line[3]) && $line[3] === ' ') break; }
        return $data;
    }
    private static function cmd($fp, string $cmd): string
    {
        fwrite($fp, $cmd . "\r\n");
        return self::read($fp);
    }
    private static function wrap(string $html): string
    {
        return wordwrap($html, 900, "\r\n", true);
    }
    private static function log(string $to, string $subject, string $status, string $err = ''): void
    {
        try {
            \q('INSERT INTO email_logs (to_email,subject,status,error,created_at) VALUES (?,?,?,?,?)', [$to, $subject, $status, $err, \now()]);
        } catch (\Throwable $t) { error_log('[EK Mail log] ' . $t->getMessage()); }
    }

    /** branded HTML wrapper */
    public static function template(string $title, string $body, string $ctaUrl = '', string $ctaText = 'Open eKamalia'): string
    {
        $name = \setting('site_name', 'eKamalia');
        $cta = $ctaUrl ? '<div style="text-align:center;margin:28px 0 8px"><a href="' . \e($ctaUrl) . '" style="background:linear-gradient(90deg,#0B7A3E,#14915a);color:#fff;text-decoration:none;padding:12px 34px;border-radius:999px;font-weight:600;display:inline-block">' . \e($ctaText) . '</a></div>' : '';
        return '<!doctype html><html><body style="margin:0;background:#f2f7f4;font-family:Segoe UI,Arial,sans-serif;padding:24px">'
            . '<div style="max-width:560px;margin:auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.08)">'
            . '<div style="background:linear-gradient(100deg,#0B7A3E,#14915a);padding:22px 28px;color:#fff;font-size:22px;font-weight:800">🛍️ ' . \e($name) . '</div>'
            . '<div style="padding:28px;color:#1c2b24;font-size:15px;line-height:1.7"><h2 style="margin:0 0 14px;font-size:19px">' . \e($title) . '</h2>'
            . $body . $cta . '</div>'
            . '<div style="padding:16px 28px;background:#f4f8f6;color:#7c8b83;font-size:12px">This is an automated message from ' . \e($name) . ' — Kamalia Ka Apna Digital Bazaar.<br>Kamalia, District Toba Tek Singh, Punjab, Pakistan.</div>'
            . '</div></body></html>';
    }
}
