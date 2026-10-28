<?php
declare(strict_types=1);

final class AuthController
{
    public function __construct(private PDO $db, private array $config) {}

    public function login(): void
    {
        if (!empty($_SESSION['user_id'])) Security::redirect('dashboard');
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $stmt = $this->db->prepare('SELECT user_id, username, password_hash, role FROM users WHERE username=? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash']) && $user['role'] === 'admin') {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                Logger::write($this->config, 'info', 'auth.login_success', ['user_id' => (int)$user['user_id']]);
                Security::redirect('dashboard');
            }
            Logger::write($this->config, 'warning', 'auth.login_failed', ['username' => substr($username, 0, 80)]);
            $error = 'Invalid username or password.';
        }
        require dirname(__DIR__) . '/views/auth/login.php';
    }

    public function logout(): void
    {
        Security::verifyCsrf();
        Logger::write($this->config, 'info', 'auth.logout', ['user_id' => (int)($_SESSION['user_id'] ?? 0)]);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], '', (bool)$p['secure'], (bool)$p['httponly']);
        }
        session_destroy();
        Security::redirect('login');
    }
}
