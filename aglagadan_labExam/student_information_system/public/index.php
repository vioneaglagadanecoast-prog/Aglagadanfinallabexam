<?php
declare(strict_types=1);

$configPath = dirname(__DIR__) . '/app/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('Configuration is missing. Copy app/config.example.php to app/config.php and configure the database.');
}
$config = require $configPath;

require dirname(__DIR__) . '/core/Database.php';
require dirname(__DIR__) . '/core/Logger.php';
require dirname(__DIR__) . '/core/Security.php';
require dirname(__DIR__) . '/core/Model.php';
require dirname(__DIR__) . '/models/GenericModel.php';
require dirname(__DIR__) . '/controllers/AuthController.php';
require dirname(__DIR__) . '/controllers/AppController.php';

Security::startSession($config);

try {
    $db = Database::connection($config);
    $auth = new AuthController($db, $config);
    $app = new AppController($db, $config);
    $route = trim((string)($_GET['r'] ?? 'dashboard'), '/');

    if ($route === 'login') { $auth->login(); exit; }
    if ($route === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') { $auth->logout(); exit; }

    if ($route === 'dashboard' || $route === '') { $app->dashboard(); exit; }
    if ($route === 'students/view') { $app->viewStudent(); exit; }

    if (preg_match('~^(departments|courses|students|academics)$~', $route, $m)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $app->save($m[1]); exit; }
        $app->listing($m[1]); exit;
    }
    if (preg_match('~^(departments|courses|students|academics)/form$~', $route, $m)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $app->save($m[1]); exit; }
        $app->form($m[1]); exit;
    }
    if (preg_match('~^(departments|courses|students|academics)/delete$~', $route, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $app->delete($m[1]); exit;
    }

    http_response_code(404);
    echo 'Page not found.';
} catch (Throwable $e) {
    Logger::write($config, 'error', 'application.exception', ['type'=>get_class($e),'message'=>$e->getMessage()]);
    http_response_code(500);
    if (!empty($config['app']['debug'])) {
        echo '<h1>Application error</h1><pre>' . Security::e($e->getMessage()) . '</pre>';
    } else {
        echo 'Something went wrong. Please try again later.';
    }
}
