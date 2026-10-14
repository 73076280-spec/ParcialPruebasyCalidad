<?php
require dirname(__DIR__).'/app/bootstrap.php';
$config=require ROOT.'/config/config.local.php';
if (!preg_match('/dbname=(actualidad_http_test|actualidad_revision_[0-9]+_test)(?:;|$)/',$config['dsn'])) exit("Solo una base de QA.\n");
$blog=new Blog(db($config));$base=$argv[1]??'http://127.0.0.1:8080';
$blog->query('INSERT INTO categories(name) VALUES(?)',['Carga ficticia '.bin2hex(random_bytes(4))]);$cat=(int)$blog->pdo->lastInsertId();
try{
    $blog->pdo->beginTransaction();
    for($i=0;$i<1000;$i++)$blog->saveNews(['title'=>'Carga ficticia '.$i,'summary'=>'Resumen ficticio para medir paginación.','content'=>str_repeat('Contenido ficticio de prueba. ',20),'category_id'=>$cat,'image'=>null,'image_alt'=>'','status'=>'published'],null);
    $blog->pdo->commit();$times=[];
    for($i=0;$i<31;$i++){
        $c=curl_init($base.'/index.php?r=home&category='.$cat.'&page=100');curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
        $start=hrtime(true);$body=curl_exec($c);$ms=(hrtime(true)-$start)/1e6;
        if(curl_getinfo($c,CURLINFO_HTTP_CODE)!==200 || substr_count($body,'class="card"')!==6)throw new RuntimeException('Respuesta inesperada');
        curl_close($c);if($i>0)$times[]=$ms;
    }
    sort($times);$p95=$times[(int)ceil(count($times)*0.95)-1];
    echo 'Escenario: 1000 noticias ficticias adicionales, página 100, 1 usuario, 1 calentamiento + 30 solicitudes HTTP secuenciales.'.PHP_EOL;
    printf("Mediana: %.3f ms; P95: %.3f ms; máximo: %.3f ms. Objetivo local P95 < 500 ms: %s\n",($times[14]+$times[15])/2,$p95,max($times),$p95<500?'APROBADO':'NO ALCANZADO');
    echo 'No representa concurrencia ni red de producción.'.PHP_EOL;
}finally{if($blog->pdo->inTransaction())$blog->pdo->rollBack();$blog->query('DELETE FROM news WHERE category_id=?',[$cat]);$blog->query('DELETE FROM categories WHERE id=?',[$cat]);}
