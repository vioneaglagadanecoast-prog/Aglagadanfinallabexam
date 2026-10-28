<?php
declare(strict_types=1);

final class Security
{
    public static function startSession(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        session_name($config['app']['session_name'] ?? 'sis_session');
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_start();

        $timeout = (int)($config['app']['session_idle_timeout'] ?? 1800);
        if (isset($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > $timeout) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['last_activity'] = time();
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
    }

    public static function csrfToken(): string
    {
        return (string)($_SESSION['_csrf'] ?? '');
    }

    public static function verifyCsrf(): void
    {
        $sent = (string)($_POST['_csrf'] ?? '');
        $stored = (string)($_SESSION['_csrf'] ?? '');
        if ($sent === '' || $stored === '' || !hash_equals($stored, $sent)) {
            http_response_code(419);
            exit('Your session or form token expired. Please go back, refresh, and try again.');
        }
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ?r=login');
            exit;
        }
    }

    public static function redirect(string $route): never
    {
        // Internal route strings may include a query suffix, e.g. students/view&id=12.
        $parts = explode('&', $route, 2);
        $url = '?r=' . rawurlencode($parts[0]);
        if (isset($parts[1])) {
            $url .= '&' . $parts[1];
        }
        header('Location: ' . $url);
        exit;
    }
}
