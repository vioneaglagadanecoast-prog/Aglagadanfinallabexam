<?php
declare(strict_types=1);

// Run from CLI only: php scripts/create_admin.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}
$config = require dirname(__DIR__) . '/app/config.php';
require dirname(__DIR__) . '/core/Database.php';
$db = Database::connection($config);

$username = 'admin';
$password = 'admin123';
$stmt = $db->prepare('SELECT user_id FROM users WHERE username=?');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    fwrite(STDOUT, "Admin account already exists; no changes made.\n");
    exit(0);
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
$stmt->execute([$username, $hash, 'admin']);
fwrite(STDOUT, "Created admin / admin123. Change this password before using real student data.\n");
