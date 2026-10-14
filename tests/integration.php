<?php
require dirname(__DIR__) . '/app/bootstrap.php';
if (!is_file(ROOT.'/config/config.test.php')) exit("Falta config/config.test.php; use una base terminada en _test.\n");
$config=require ROOT.'/config/config.test.php';
if (!preg_match('/dbname=([a-zA-Z0-9_]+)(?:;|$)/',$config['dsn'],$match) || !str_ends_with($match[1],'_test')) exit("Seguridad: base de prueba debe terminar en _test.\n");
$blog=new Blog(db($config)); $pdo=$blog->pdo;
if (!str_ends_with((string)$pdo->query('SELECT DATABASE()')->fetchColumn(),'_test')) exit("Base no autorizada para pruebas.\n");
// No se borran tablas ni registros previos. Cada ejecución revierte sus datos con ROLLBACK.
$pdo->beginTransaction(); $passed=0;
function assertTest(string $name,bool $ok): void { global $passed; if (!$ok) throw new RuntimeException('FAIL '.$name); $passed++; echo 'PASS '.$name.PHP_EOL; }
try {
    $blog->query('INSERT INTO categories(name) VALUES(?)',['Prueba '.bin2hex(random_bytes(5))]); $cat=(int)$pdo->lastInsertId();
    $d=['title'=>'Título de integración '.bin2hex(random_bytes(5)),'summary'=>'Resumen de integración ficticio.','content'=>'Contenido de integración totalmente ficticio.','category_id'=>$cat,'image'=>null,'image_alt'=>'','status'=>'draft'];
    $blog->saveNews($d,null); $id=(int)$pdo->lastInsertId();
    assertTest('I01 crear y consultar borrador',$blog->news($id)['title']===$d['title']);
    assertTest('I02 ocultar detalle borrador',$blog->news($id,true)===false);
    assertTest('I03 ocultar listado borrador',$blog->listing('',$cat,1,true)['total']===0);
    $d['status']='published'; $blog->saveNews($d,$id); $published=$blog->news($id)['published_at'];
    assertTest('I04 publicar',$blog->news($id,true)!==false && $published!==null);
    $d['summary']='Resumen modificado y conservado.'; $blog->saveNews($d,$id);
    assertTest('I05 editar conserva fecha',$blog->news($id)['published_at']===$published && $blog->news($id)['summary']===$d['summary']);
    assertTest('I06 búsqueda título/categoría',$blog->listing($d['title'],$cat,1,true)['total']===1);
    assertTest('I07 inyección no amplía resultados',$blog->listing("' OR 1=1 --",$cat,1,true)['total']===0);
    assertTest('I08 comodín literal',$blog->listing('%',$cat,1,true)['total']===0);
    for($i=0;$i<7;$i++){ $d['title']='Noticia adicional '.$i; $blog->saveNews($d,null); }
    $page=$blog->listing('',$cat,2,true); assertTest('I09 paginar en SQL',$page['total']===8 && count($page['rows'])===2 && $page['pages']===2);
    assertTest('I10 orden estable descendente',$page['rows'][0]['id']>$page['rows'][1]['id']);
    $blocked=false; try{$blog->query('DELETE FROM categories WHERE id=?',[$cat]);}catch(PDOException $ex){$blocked=$ex->getCode()==='23000';}
    assertTest('I11 categoría con noticias protegida',$blocked);
    $d['status']='draft'; $blog->saveNews($d,$id); assertTest('I12 retirar publicación',$blog->news($id,true)===false);
    $blog->query('DELETE FROM news WHERE id=?',[$id]); assertTest('I13 eliminar noticia',$blog->news($id)===false);
    $blog->query('DELETE FROM news WHERE category_id=?',[$cat]); $blog->query('DELETE FROM categories WHERE id=?',[$cat]);
    assertTest('I14 eliminar categoría vacía',!$blog->query('SELECT id FROM categories WHERE id=?',[$cat])->fetch());
    echo "$passed aprobaciones, 0 fallos; ROLLBACK\n";
} finally { $pdo->rollBack(); }
