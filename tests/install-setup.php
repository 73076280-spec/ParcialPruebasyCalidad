<?php
// Inicializa únicamente una base vacía y exclusiva de instalación.
if(PHP_SAPI!=='cli')exit(1);
$target=$argv[1]??'';
$name=$argv[2]??'actualidad_install_test';
if(!preg_match('/^actualidad_install[0-9]*_test$/',$name))exit(1);
if(!is_dir($target.'/public'))exit("Destino no válido\n");
$pdo=new PDO('mysql:host=127.0.0.1;port=3307;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS $name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE $name");
if($pdo->query('SHOW TABLES')->fetch()){fwrite(STDERR,"La base ya contiene tablas. No se modifica.\n");exit(1);}
$pdo->exec(file_get_contents($target.'/database/schema.sql'));
$pdo->exec(file_get_contents($target.'/database/demo.sql'));
$config=['dsn'=>"mysql:host=127.0.0.1;port=3307;dbname=$name;charset=utf8mb4",'user'=>'root','password'=>'','base_url'=>''];
file_put_contents($target.'/config/config.local.php',"<?php\nreturn ".var_export($config,true).";\n");
// DSN deliberadamente inseguro para demostrar que integración se niega antes de conectar.
$config['dsn']='mysql:host=127.0.0.1;port=3307;dbname=actualidad_guard_dev;charset=utf8mb4';
file_put_contents($target.'/config/config.test.php',"<?php\nreturn ".var_export($config,true).";\n");
echo "SQL importado en $name; configuración local creada.\n";
