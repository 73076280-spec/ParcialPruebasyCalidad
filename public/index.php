<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
// Cookies HttpOnly y SameSite; Secure se activa en HTTPS. La sesión se regenera al autenticar.
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
header("Content-Security-Policy: default-src 'self'; img-src 'self'; style-src 'self'; script-src 'self'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");
header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: same-origin');
try {
    if (!is_file(ROOT . '/config/config.local.php')) throw new RuntimeException('Configuración pendiente');
    $GLOBALS['config'] = require ROOT . '/config/config.local.php';
    (new Controller(new Blog(db())))->run();
} catch (Throwable $exception) {
    error_log($exception->getMessage()); http_response_code(503);
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Servicio no disponible</title><h1>Servicio no disponible</h1><p>Revise la configuración y la conexión a la base de datos según el README.</p></html>';
}
