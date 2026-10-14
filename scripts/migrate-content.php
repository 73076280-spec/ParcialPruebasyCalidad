<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$pdo = db();
$type = $pdo->query("SHOW COLUMNS FROM news LIKE 'content'")->fetch()['Type'] ?? '';
if ($type === 'mediumtext') { echo "La capacidad del contenido ya está actualizada.\n"; exit; }
if ($type !== 'text') { fwrite(STDERR, "Tipo de contenido no esperado; no se cambia.\n"); exit(1); }
$pdo->exec(file_get_contents(ROOT . '/database/migrations/002-content-capacity.sql'));
echo "Contenido ampliado a MEDIUMTEXT; se conservan registros y credenciales.\n";
