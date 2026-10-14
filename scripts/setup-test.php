<?php
// Herramienta local: crea solamente bases de pruebas terminadas en _test, sin DROP.
require dirname(__DIR__) . '/app/bootstrap.php';
$port=(int)($argv[1] ?? 3307);
$server=new PDO("mysql:host=127.0.0.1;port=$port;charset=utf8mb4",'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
foreach(['actualidad_huancayo_test','actualidad_http_test'] as $name){
    $server->exec("CREATE DATABASE IF NOT EXISTS $name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $config=['dsn'=>"mysql:host=127.0.0.1;port=$port;dbname=$name;charset=utf8mb4",'user'=>'root','password'=>'','base_url'=>''];
    $pdo=db($config);
    if (!$pdo->query("SHOW TABLES LIKE 'news'")->fetch()) {
        $pdo->exec(file_get_contents(ROOT.'/database/schema.sql'));
        if ($name==='actualidad_http_test') {
            $pdo->exec(file_get_contents(ROOT.'/database/demo.sql'));
            (new Blog($pdo))->query('INSERT INTO admins(email,password_hash) VALUES(?,?)',['admin@actualidad.test',password_hash('DemoHuancayo2026!',PASSWORD_DEFAULT)]);
        }
    }
    $path=$name==='actualidad_huancayo_test' ? '/config/config.test.php' : '/storage/http-config.php';
    file_put_contents(ROOT.$path,"<?php\nreturn ".var_export($config,true).";\n");
}
echo "Bases _test preparadas sin borrar registros existentes.\n";
