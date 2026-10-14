<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$email = $argv[1] ?? 'admin@actualidad.test';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) exit("Correo inválido.\n");
echo "Escriba una contraseña de 12 a 72 bytes (entrada visible en esta consola): ";
$password = rtrim((string)fgets(STDIN), "\r\n");
if (strlen($password) < 12 || strlen($password) > 72) exit("Longitud de contraseña inválida.\n");
try {
    (new Blog(db()))->query('INSERT INTO admins(email,password_hash) VALUES(?,?)', [$email, password_hash($password, PASSWORD_DEFAULT)]);
    echo "Administrador creado: $email. No se imprime ni guarda la contraseña en texto.\n";
} catch (PDOException $ex) { fwrite(STDERR, "No se pudo crear: revise conexión o correo ya existente.\n"); exit(1); }
