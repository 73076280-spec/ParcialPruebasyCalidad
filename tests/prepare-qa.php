<?php
// Copia aislada para probar sin reconfigurar la aplicación ni tocar datos del autor.
require dirname(__DIR__) . '/app/bootstrap.php';
$stamp = date('YmdHis');
$target = ROOT . '/storage/qa-' . $stamp;
$name = 'actualidad_revision_' . $stamp . '_test';
$source = require ROOT . '/config/config.test.php';
if (!preg_match('/dbname=([a-zA-Z0-9_]+)(?:;|$)/', $source['dsn'], $match) || !str_ends_with($match[1], '_test')) exit("Solo configuración de pruebas.\n");
$serverDsn = preg_replace('/dbname=[a-zA-Z0-9_]+;?/', '', $source['dsn']);
$server = new PDO($serverDsn, $source['user'], $source['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$server->exec("CREATE DATABASE $name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$source['dsn'] = preg_replace('/dbname=[a-zA-Z0-9_]+/', 'dbname=' . $name, $source['dsn']);
$source['base_url'] = '';
$pdo = db($source);
$pdo->exec(file_get_contents(ROOT . '/database/schema.sql'));
$pdo->exec(file_get_contents(ROOT . '/database/demo.sql'));
(new Blog($pdo))->query('INSERT INTO admins(email,password_hash) VALUES(?,?)', ['admin@actualidad.test', password_hash('DemoHuancayo2026!', PASSWORD_DEFAULT)]);
foreach (['app', 'public', 'database', 'tests', 'scripts'] as $folder) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT . '/' . $folder, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(ROOT) + 1));
        // No se copian imágenes del autor; solo metadatos de la carpeta de subidas.
        if (str_starts_with($relative, 'public/uploads/') && !in_array(basename($relative), ['.htaccess', '.gitkeep'], true)) continue;
        $destination = $target . '/' . $relative;
        if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0777, true);
        copy($file->getPathname(), $destination);
    }
}
foreach (['config', 'storage', 'docs/evidencias'] as $folder) mkdir($target . '/' . $folder, 0777, true);
foreach (['config.local.php', 'config.test.php'] as $file) file_put_contents($target . '/config/' . $file, "<?php\nreturn " . var_export($source, true) . ";\n");
file_put_contents(ROOT . '/storage/qa-path.txt', $target);
echo "Copia QA: $target\nBase exclusiva: $name\n";
