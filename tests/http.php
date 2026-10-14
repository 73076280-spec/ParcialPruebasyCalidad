<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$config=require ROOT.'/config/config.local.php';
if (!preg_match('/dbname=actualidad_http_test(?:;|$)/',$config['dsn'])) exit("Use únicamente actualidad_http_test para estos flujos.\n");
$blog=new Blog(db($config)); $base=$argv[1]??'http://127.0.0.1:8080'; $cookie=ROOT.'/storage/http-cookies.txt'; $passed=0; $created=[];
function request(string $route,array $data=[],bool $post=false): array { global $base,$cookie; $c=curl_init($base.'/index.php?'.$route); curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_COOKIEFILE=>$cookie,CURLOPT_FOLLOWLOCATION=>false]); if($post){curl_setopt($c,CURLOPT_POST,true);curl_setopt($c,CURLOPT_POSTFIELDS,$data);} $body=curl_exec($c); if($body===false)throw new RuntimeException(curl_error($c)); $code=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);return [$code,$body]; }
function token(string $body): string { preg_match('/name="_token" value="([a-f0-9]+)"/',$body,$m);return $m[1]??''; }
function ok(string $name,bool $condition): void { global $passed; if(!$condition)throw new RuntimeException('FAIL '.$name);$passed++;echo 'PASS '.$name.PHP_EOL; }
function sid(): string { global $cookie; $text=file_get_contents($cookie);preg_match('/PHPSESSID\s+(\S+)/',$text,$m);return $m[1]??''; }
try {
    [$code,$body]=request('r=home');ok('H01 inicio público',$code===200 && substr_count($body,'class="card"')===6);
    [$code,$body]=request('r=home&page=2');ok('H02 segunda página',$code===200 && substr_count($body,'class="card"')===2);
    [$code,$body]=request('r=home&q=nada_imposible');ok('H03 sin resultados',str_contains($body,'No hay noticias'));
    ok('H04 detalle publicado',request('r=detail&id=1')[0]===200);ok('H05 borrador inaccesible',request('r=detail&id=9')[0]===404);
    foreach(['dashboard','news','news-edit','news-delete','categories','category-edit','category-delete','logout'] as $r)ok('H06 autorización '.$r,request('r='.$r.'&id=1')[0]===302);
    [$code,$body]=request('r=login');$csrf=token($body);$before=sid();
    ok('H07 login CSRF ausente',request('r=login',['email'=>'admin@actualidad.test','password'=>'DemoHuancayo2026!'],true)[0]===403);
    [$code,$body]=request('r=login',['_token'=>$csrf,'email'=>'admin@actualidad.test','password'=>'incorrecta'],true);ok('H08 credenciales incorrectas',$code===200 && str_contains($body,'Correo o contraseña incorrectos.'));
    ok('H09 login correcto',request('r=login',['_token'=>$csrf,'email'=>'admin@actualidad.test','password'=>'DemoHuancayo2026!'],true)[0]===302);
    ok('H10 regenerar sesión',$before!==sid());[$code,$body]=request('r=dashboard');$csrf=token($body);ok('H11 panel protegido',$code===200 && str_contains($body,'Resumen del blog'));
    $d=['_token'=>$csrf,'title'=>'Corto','summary'=>'breve','content'=>'breve','category_id'=>1,'image_alt'=>'','status'=>'draft'];
    [$code,$body]=request('r=news-edit',$d,true);ok('H12 validación y conservación',$code===200 && str_contains($body,'value="Corto"') && str_contains($body,'Debe tener entre 10 y 300'));
    ok('H13 CSRF modificar',request('r=news-edit',['title'=>'Intento sin token'],true)[0]===403);
    $title='Prueba HTTP '.bin2hex(random_bytes(4));$d=['_token'=>$csrf,'title'=>$title,'summary'=>'Resumen ficticio de prueba HTTP.','content'=>'<script>alert("xss")</script> Texto ficticio y seguro.','category_id'=>1,'image_alt'=>'','status'=>'draft'];
    ok('H14 crear borrador',request('r=news-edit',$d,true)[0]===302);$id=(int)$blog->query('SELECT id FROM news WHERE title=?',[$title])->fetchColumn();$created[]=$id;
    ok('H15 borrador creado oculto',request('r=detail&id='.$id)[0]===404);
    $d['status']='published';ok('H16 publicar',request('r=news-edit&id='.$id,$d,true)[0]===302);
    [$code,$body]=request('r=detail&id='.$id);ok('H17 XSS escapado',$code===200 && str_contains($body,'&lt;script&gt;') && !str_contains($body,'<script>alert'));
    $tmp=ROOT.'/storage/http-image.png';file_put_contents($tmp,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    $d['image']=new CURLFile($tmp,'image/png','foto.png');
    ok('H18a alt requerido',str_contains(request('r=news-edit&id='.$id,$d,true)[1],'requiere un texto alternativo'));
    $d['image_alt']='Punto claro de prueba';ok('H18 carga PNG auténtica',request('r=news-edit&id='.$id,$d,true)[0]===302);
    $image=$blog->news($id)['image'];ok('H19 nombre aleatorio y archivo',preg_match('/^[a-f0-9]{32}\.png$/',$image) && is_file(ROOT.'/public/uploads/'.$image));
    $fake=ROOT.'/storage/http-fake.jpg';file_put_contents($fake,'<?php echo "danger";');$d['image']=new CURLFile($fake,'image/jpeg','fake.jpg');
    [$code,$body]=request('r=news-edit&id='.$id,$d,true);ok('H20 rechazo imagen camuflada',$code===200 && str_contains($body,'auténtica'));
    $d['image']=new CURLFile($tmp,'image/png','foto.php');ok('H20a extensión ejecutable rechazada',str_contains(request('r=news-edit&id='.$id,$d,true)[1],'auténtica'));
    file_put_contents($tmp,str_repeat('x',2097153));$d['image']=new CURLFile($tmp,'image/png','grande.png');ok('H20b tamaño excesivo rechazado',str_contains(request('r=news-edit&id='.$id,$d,true)[1],'máximo 2 MiB'));
    [$code,$body]=request('r=news-delete&id='.$id);ok('H21 GET confirma sin borrar',$code===200 && str_contains($body,'Confirmar eliminación') && $blog->news($id)!==false);
    ok('H22 eliminar sin CSRF',request('r=news-delete&id='.$id,[],true)[0]===403);
    ok('H23 eliminar POST',request('r=news-delete&id='.$id,['_token'=>$csrf],true)[0]===302 && $blog->news($id)===false);
    [$code,$body]=request('r=category-delete&id=1',['_token'=>$csrf],true);ok('H24 categoría referenciada',$code===400 && str_contains($body,'Reasigne'));
    $name='Categoría HTTP '.bin2hex(random_bytes(4));ok('H25 crear categoría',request('r=category-edit',['_token'=>$csrf,'name'=>$name],true)[0]===302);
    $cat=(int)$blog->query('SELECT id FROM categories WHERE name=?',[$name])->fetchColumn();
    try {
        ok('H26 duplicado categoría',str_contains(request('r=category-edit',['_token'=>$csrf,'name'=>$name],true)[1],'Ya existe'));
        ok('H27 editar categoría',request('r=category-edit&id='.$cat,['_token'=>$csrf,'name'=>$name.' editada'],true)[0]===302);
        ok('H28 eliminar categoría vacía',request('r=category-delete&id='.$cat,['_token'=>$csrf],true)[0]===302);
    }finally{$blog->query('DELETE FROM categories WHERE id=?',[$cat]);}
    $name='Filtros HTTP '.bin2hex(random_bytes(4));$blog->query('INSERT INTO categories(name) VALUES(?)',[$name]);$filterCat=(int)$blog->pdo->lastInsertId();
    try{
        for($i=0;$i<7;$i++)$blog->saveNews(['title'=>'FiltroPrueba común '.$i,'summary'=>'Resumen ficticio para filtros HTTP.','content'=>'Contenido ficticio para filtros y paginación.','category_id'=>$filterCat,'image'=>null,'image_alt'=>'','status'=>'published'],null);
        [$code,$body]=request('r=home&q=FiltroPrueba&category='.$filterCat);ok('H32 filtros combinados y enlace persistente',$code===200 && substr_count($body,'class="card"')===6 && str_contains($body,'q=FiltroPrueba&amp;category='.$filterCat.'&amp;page=2'));
        [$code,$body]=request('r=home&q=FiltroPrueba&category='.$filterCat.'&page=2');ok('H33 filtros segunda página',$code===200 && substr_count($body,'class="card"')===1);
        [$code,$body]=request('r=home&category='.$filterCat.'&page=9999');ok('H34 página fuera de rango',str_contains($body,'Página 2 de 2'));
    }finally{$blog->query('DELETE FROM news WHERE category_id=?',[$filterCat]);$blog->query('DELETE FROM categories WHERE id=?',[$filterCat]);}
    ok('H29 logout GET rechazado',request('r=logout')[0]===405);ok('H30 logout POST',request('r=logout',['_token'=>$csrf],true)[0]===302);ok('H31 sesión cerrada',request('r=dashboard')[0]===302);
    echo "$passed aprobaciones, 0 fallos\n";
}finally{
    foreach($created as $id){$blog->query('DELETE FROM news WHERE id=?',[$id]);}
    foreach([$tmp??null,$fake??null,isset($image)?ROOT.'/public/uploads/'.$image:null,$cookie] as $path)if($path && is_file($path))unlink($path);
}
