<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cart;
use App\Services\Mailer;

class AuthController extends Controller
{
    /* ---------------- register ---------------- */
    public function showRegister(): void
    {
        if (auth()) redirect('/dashboard');
        $this->view('auth/register', ['seo' => ['title' => t('auth.register') . ' — eKamalia']]);
    }

    public function register(): void
    {
        $name = str_input('name', '', 120);
        $email = strtolower(str_input('email', '', 190));
        $phone = str_input('phone', '', 20);
        $pass = (string)($_POST['password'] ?? '');
        $pass2 = (string)($_POST['password_confirmation'] ?? '');
        $cityId = int_input('city_id') ?: null;

        if ($name === '' || mb_strlen($name) < 3) { stash_old(); flash('danger', 'Please enter your full name.'); back('/register'); }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { stash_old(); flash('danger', 'Please enter a valid email address.'); back('/register'); }
        if ($phone && !preg_match('/^(0|92)?3\d{9}$/', preg_replace('/\D/', '', $phone))) { stash_old(); flash('danger', 'Enter a valid Pakistani mobile number (03XX-XXXXXXX).'); back('/register'); }
        if (strlen($pass) < 8) { stash_old(); flash('danger', 'Password must be at least 8 characters.'); back('/register'); }
        if ($pass !== $pass2) { stash_old(); flash('danger', 'Passwords do not match.'); back('/register'); }
        if (q1('SELECT id FROM users WHERE email=?', [$email])) { stash_old(); flash('danger', 'This email is already registered. Please login.'); back('/register'); }
        if (rate_limited('register', client_ip(), 8, 3600)) { flash('danger', 'Too many registrations from this device. Try later.'); back('/register'); }

        $status = 'active';
        q('INSERT INTO users (name,email,phone,password,city_id,status,created_at) VALUES (?,?,?,?,?,?,?)',
            [$name, $email, preg_replace('/\D/', '', $phone) ?: null, password_hash($pass, PASSWORD_DEFAULT), $cityId, $status, now()]);
        $userId = last_id();

        send_app_mail($email, 'Welcome to eKamalia! 🎉', "Assalam-o-Alaikum $name!\n\nYour eKamalia account is ready. Post free ads, follow shops, and enjoy shopping from Kamalia's own digital bazaar.", '/dashboard', 'Go to Dashboard');

        // OTP verification flow
        if (setting('otp_enabled') === '1' && setting('reg_otp_required') === '1') {
            $sent = self::sendOtp($email, 'register');
            $_SESSION['pending_user'] = $userId;
            if ($sent) { flash('info', 'We sent a 6-digit verification code to your email. Please verify to continue.'); redirect('/verify-otp'); }
            flash('warning', 'Account created! (Email OTP is not configured yet — you can login directly.)');
        } else {
            q('UPDATE users SET email_verified_at=? WHERE id=?', [now(), $userId]);
            flash('success', 'Welcome to eKamalia! Your account is ready.');
        }
        rate_hit('register', client_ip());
        $_SESSION['user_id'] = $userId;
        session_regenerate_id(true);
        Cart::merge($userId);
        redirect('/dashboard');
    }

    /* ---------------- verify OTP ---------------- */
    public function showVerifyOtp(): void
    {
        if (empty($_SESSION['otp_ctx'])) redirect('/login');
        $this->view('auth/verify-otp', ['ctx' => $_SESSION['otp_ctx']]);
    }

    public function verifyOtp(): void
    {
        $ctx = $_SESSION['otp_ctx'] ?? null;
        if (!$ctx) redirect('/login');
        $code = preg_replace('/\D/', '', str_input('code'));
        if (rate_limited('otp_verify', $ctx['identifier'], 8, 600)) { flash('danger', 'Too many attempts. Please wait 10 minutes.'); back('/verify-otp'); }
        $row = q1('SELECT * FROM otp_verifications WHERE identifier=? AND purpose=? AND used_at IS NULL AND expires_at > ? ORDER BY id DESC LIMIT 1', [$ctx['identifier'], $ctx['purpose'], now()]);
        if (!$row || !password_verify($code, $row['code_hash'])) {
            if ($row) q('UPDATE otp_verifications SET attempts=attempts+1 WHERE id=?', [$row['id']]);
            rate_hit('otp_verify', $ctx['identifier']);
            flash('danger', 'Invalid or expired code. Please try again.');
            back('/verify-otp');
        }
        if ((int)$row['attempts'] >= 5) { flash('danger', 'Too many wrong attempts. Request a new code.'); back('/verify-otp'); }
        q('UPDATE otp_verifications SET used_at=? WHERE id=?', [now(), $row['id']]);
        rate_clear('otp_verify', $ctx['identifier']);
        $purpose = $ctx['purpose'];

        if ($purpose === 'register') {
            $uid = (int)($_SESSION['pending_user'] ?? 0);
            if ($uid) { q('UPDATE users SET email_verified_at=? WHERE id=?', [now(), $uid]); $_SESSION['user_id'] = $uid; session_regenerate_id(true); Cart::merge($uid); }
            unset($_SESSION['pending_user'], $_SESSION['otp_ctx']);
            flash('success', 'Email verified — welcome to eKamalia!');
            redirect('/dashboard');
        }
        if ($purpose === 'login') {
            $u = q1('SELECT * FROM users WHERE email=? AND status="active"', [$ctx['identifier']]);
            unset($_SESSION['otp_ctx']);
            if ($u && $u['status'] === 'active') { self::loginUser($u); }
            flash('danger', 'Account not found or suspended.'); redirect('/login');
        }
        if ($purpose === 'reset') {
            $_SESSION['reset_verified'] = $ctx['identifier'];
            unset($_SESSION['otp_ctx']);
            redirect('/reset-password');
        }
        redirect('/login');
    }

    public function resendOtp(): void
    {
        $ctx = $_SESSION['otp_ctx'] ?? null;
        if (!$ctx) redirect('/login');
        if (rate_limited('otp_send', $ctx['identifier'], 3, 300)) { flash('danger', 'Please wait a minute before requesting another code.'); back('/verify-otp'); }
        $ok = self::sendOtp($ctx['identifier'], $ctx['purpose']);
        flash($ok ? 'info' : 'danger', $ok ? 'New code sent to your email.' : 'Could not send email — please contact support.');
        back('/verify-otp');
    }

    /* ---------------- login ---------------- */
    public function showLogin(): void
    {
        if (auth()) redirect('/dashboard');
        $this->view('auth/login', ['seo' => ['title' => t('auth.login') . ' — eKamalia']]);
    }

    public function login(): void
    {
        $email = strtolower(str_input('email', '', 190));
        $pass = (string)($_POST['password'] ?? '');
        $key = $email . '|' . client_ip();
        if (rate_limited('login', $key, 6, 900)) {
            flash('danger', 'Too many failed attempts. Please wait 15 minutes or reset your password.');
            back('/login');
        }
        $u = q1('SELECT * FROM users WHERE email=? AND deleted_at IS NULL', [$email]);
        if (!$u || !password_verify($pass, $u['password'])) {
            rate_hit('login', $key);
            flash('danger', 'Invalid email or password.');
            back('/login');
        }
        if (!in_array($u['status'], ['active', 'pending'], true)) {
            flash('danger', 'Your account is ' . $u['status'] . '. Please contact support.');
            back('/login');
        }
        rate_clear('login', $key);

        if (setting('login_otp_required') === '1' && setting('otp_enabled') === '1') {
            if (self::sendOtp($email, 'login')) { $_SESSION['otp_ctx'] = ['identifier' => $email, 'purpose' => 'login']; flash('info', 'Enter the 6-digit code we emailed you.'); redirect('/verify-otp'); }
        }
        self::loginUser($u);
    }

    private static function loginUser(array $u): never
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        q('UPDATE users SET last_login_at=?, last_login_ip=?, last_activity=? WHERE id=?', [now(), client_ip(), now(), $u['id']]);
        q('INSERT INTO login_history (user_id,ip,user_agent,created_at) VALUES (?,?,?,?)', [$u['id'], client_ip(), user_agent(), now()]);
        Cart::merge((int)$u['id']);
        $next = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);
        flash('success', 'Welcome back, ' . explode(' ', $u['name'])[0] . '!');
        redirect($next ?: ($u['role'] === 'admin' ? '/admin' : '/dashboard'));
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        session_regenerate_id(true);
        flash('success', 'Logged out successfully. Khuda Hafiz!');
        redirect('/');
    }

    /* ---------------- forgot / reset password ---------------- */
    public function showForgot(): void { $this->view('auth/forgot'); }

    public function forgot(): void
    {
        $email = strtolower(str_input('email', '', 190));
        if (rate_limited('forgot', $email . '|' . client_ip(), 3, 900)) { flash('danger', 'Too many reset requests. Try again in 15 minutes.'); back('/forgot-password'); }
        $u = q1('SELECT id FROM users WHERE email=? AND deleted_at IS NULL', [$email]);
        // do not reveal whether the email exists
        if ($u) {
            $sent = self::sendOtp($email, 'reset');
            if ($sent) { $_SESSION['otp_ctx'] = ['identifier' => $email, 'purpose' => 'reset']; }
        }
        flash('info', 'If that email exists, a reset code has been sent.');
        redirect($u && !empty($_SESSION['otp_ctx']) ? '/verify-otp' : '/forgot-password');
    }

    public function showReset(): void
    {
        if (empty($_SESSION['reset_verified'])) redirect('/forgot-password');
        $this->view('auth/reset');
    }

    public function reset(): void
    {
        $email = $_SESSION['reset_verified'] ?? '';
        if (!$email) redirect('/forgot-password');
        $pass = (string)($_POST['password'] ?? '');
        $pass2 = (string)($_POST['password_confirmation'] ?? '');
        if (strlen($pass) < 8) { flash('danger', 'Password must be at least 8 characters.'); back('/reset-password'); }
        if ($pass !== $pass2) { flash('danger', 'Passwords do not match.'); back('/reset-password'); }
        q('UPDATE users SET password=? WHERE email=?', [password_hash($pass, PASSWORD_DEFAULT), $email]);
        unset($_SESSION['reset_verified']);
        send_app_mail($email, 'Your eKamalia password was changed', 'Your password was just changed successfully. If this was not you, contact support immediately.', '/contact');
        flash('success', 'Password updated. Please login with your new password.');
        redirect('/login');
    }

    /* ---------------- OTP sender (shared) ---------------- */
    public static function sendOtp(string $identifier, string $purpose): bool
    {
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = (int)setting('otp_expiry_minutes', 10);
        q('INSERT INTO otp_verifications (identifier,code_hash,purpose,expires_at,created_at) VALUES (?,?,?,?,?)',
            [$identifier, password_hash($code, PASSWORD_DEFAULT), $purpose, date('Y-m-d H:i:s', time() + $expiry * 60), now()]);
        rate_hit('otp_send', $identifier);
        $site = setting('site_name', 'eKamalia');
        $html = '<p>Your verification code is:</p><div style="font-size:34px;font-weight:800;letter-spacing:10px;background:#f0f7f2;border-radius:14px;padding:14px;text-align:center;color:#0B7A3E">' . e($code) . '</div>'
              . '<p class="small">This code expires in ' . $expiry . ' minutes. Never share this code with anyone.</p>';
        return Mailer::send($identifier, "$site Verification Code: $code", Mailer::template('Email Verification', $html));
    }
}
