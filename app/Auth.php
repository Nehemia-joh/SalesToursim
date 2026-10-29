<?php
/** Auth.php - session login/logout with sha256(salt . password). */
final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        // Keep signed-in sessions usable for a full working day. The browser
        // cookie itself still expires when the browser is closed.
        ini_set('session.gc_maxlifetime', '28800');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('SALESTOURISM');
        session_start();
    }

    /** Attempt login. Returns error string on failure, null on success. */
    public static function login(string $email, string $password): ?string
    {
        $email = trim(strtolower($email));
        if ($email === '' || $password === '') return 'Please enter both email and password.';

        $user = DB::one(
            'SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1',
            [$email]
        );
        if (!$user) return 'No active account found for that email.';

        $calc = hash('sha256', $user['salt'] . $password);
        if (!hash_equals($user['password_hash'], $calc)) return 'Incorrect password.';

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        return null;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        self::start();
        if (empty($_SESSION['user_id'])) return null;
        return DB::one('SELECT * FROM users WHERE id = ? AND active = 1', [$_SESSION['user_id']]);
    }

    public static function id(): int
    {
        $u = self::user();
        return $u ? (int)$u['id'] : 0;
    }

    public static function role(): string
    {
        $u = self::user();
        return $u ? $u['role'] : '';
    }

    public static function check(): void
    {
        if (!self::user()) redirect('page=login');
    }

    /** admin-only gate */
    public static function admin(): void
    {
        self::check();
        if (self::role() !== 'admin') {
            flash('err', 'Only the System Admin can do that.');
            redirect('page=dashboard');
        }
    }

    /** admin or manager */
    public static function manager(): void
    {
        self::check();
        if (!in_array(self::role(), ['admin', 'manager'], true)) {
            flash('err', 'Only managers can do that.');
            redirect('page=dashboard');
        }
    }

    /** change a user's password (re-salts) */
    public static function setPassword(int $userId, string $plain): void
    {
        $salt  = bin2hex(random_bytes(16));
        $hash  = hash('sha256', $salt . $plain);
        DB::run('UPDATE users SET salt = ?, password_hash = ? WHERE id = ?', [$salt, $hash, $userId]);
    }
}
