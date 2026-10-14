<?php
declare(strict_types=1);
define('ROOT', dirname(__DIR__));
date_default_timezone_set('America/Lima');
require_once ROOT . '/app/support.php';
require_once ROOT . '/app/Models/Blog.php';
require_once ROOT . '/app/Controllers/Controller.php';
function db(?array $config = null): PDO {
    $config ??= require ROOT . '/config/config.local.php';
    $pdo = new PDO($config['dsn'], $config['user'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    // Lima UTC-5: TIMESTAMP de la BD y fechas PHP se presentan en el mismo huso.
    $pdo->exec("SET time_zone = '-05:00'");
    return $pdo;
}
