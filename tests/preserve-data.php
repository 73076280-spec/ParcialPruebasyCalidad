<?php
// Evidencia de conservación: solo lee; no guarda filas ni contraseñas en el informe.
require dirname(__DIR__) . '/app/bootstrap.php';
$pdo = db(); $result = [];
foreach (['news', 'categories', 'admins'] as $table) {
    $rows = $pdo->query("SELECT * FROM $table ORDER BY id")->fetchAll();
    $result[$table] = ['count' => count($rows), 'sha256' => hash('sha256', json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))];
}
foreach (['config.local.php', 'config.test.php'] as $file) $result['configuration'][$file] = hash_file('sha256', ROOT . '/config/' . $file);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
