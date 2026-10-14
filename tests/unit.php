<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$passed = 0; $failed = 0;
function check(string $name, bool $ok): void { global $passed,$failed; $ok ? $passed++ : $failed++; echo ($ok ? 'PASS ' : 'FAIL ') . $name . PHP_EOL; }
$valid = ['title' => 'Título válido', 'summary' => 'Resumen suficientemente largo.', 'content' => 'Contenido de una noticia para realizar pruebas.', 'category_id' => '1', 'status' => 'draft', 'image_alt' => ''];
check('U01 noticia válida sin imagen', validateNews($valid) === []);
foreach ([4 => false, 5 => true, 160 => true, 161 => false] as $length => $expected) { $d=$valid; $d['title']=str_repeat('á',$length); check("U02 título $length", !isset(validateNews($d)['title']) === $expected); }
foreach ([9 => false, 10 => true, 300 => true, 301 => false] as $length => $expected) { $d=$valid; $d['summary']=str_repeat('s',$length); check("U03 resumen $length", !isset(validateNews($d)['summary']) === $expected); }
foreach ([19 => false, 20 => true, 20000 => true, 20001 => false] as $length => $expected) { $d=$valid; $d['content']=str_repeat('c',$length); check("U04 contenido $length", !isset(validateNews($d)['content']) === $expected); }
$d=$valid; $d['status']='invalid'; $d['category_id']='0'; check('U05 estado/categoría inválidos', count(validateNews($d)) === 2);
check('U06 alt obligatorio con imagen', isset(validateNews($valid,true)['image_alt']));
$d=$valid; $d['image_alt']=str_repeat('a',180); check('U07 alt límite 180', validateNews($d,true)===[]); $d['image_alt'].='a'; check('U08 alt 181',isset(validateNews($d,true)['image_alt']));
check('U09 escape XSS', e('<script>"&') === '&lt;script&gt;&quot;&amp;');
$_SESSION=[]; $token=csrf(); check('U10 token aleatorio y estable', strlen($token)===64 && csrf()===$token); check('U11 CSRF correcto', validCsrf($token)); check('U12 CSRF incorrecto/ausente', !validCsrf('bad') && !validCsrf(null));
check('U13 sin imagen', validateImage(['error'=>UPLOAD_ERR_NO_FILE])===null);
check('U14 error de transferencia', validateImage(['error'=>UPLOAD_ERR_PARTIAL])!==null);
check('U15 tamaño excesivo', validateImage(['error'=>0,'size'=>2097153])!==null);
$tmp=ROOT.'/storage/unit-image.tmp'; file_put_contents($tmp,'<?php echo 1;');
check('U16 PHP camuflado', validateImage(['error'=>0,'size'=>15,'name'=>'foto.jpg','tmp_name'=>$tmp])!==null);
file_put_contents($tmp,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
check('U17 PNG real',validateImage(['error'=>0,'size'=>filesize($tmp),'name'=>'foto.png','tmp_name'=>$tmp])===null);
check('U18 extensión prohibida',validateImage(['error'=>0,'size'=>filesize($tmp),'name'=>'foto.php','tmp_name'=>$tmp])!==null);
$png=file_get_contents($tmp);file_put_contents($tmp,$png.str_repeat("\0",2097152-strlen($png)));clearstatcache(true,$tmp);
check('U20 imagen exactamente 2 MiB',validateImage(['error'=>0,'size'=>filesize($tmp),'name'=>'foto.png','tmp_name'=>$tmp])===null); unlink($tmp);
$hash=password_hash('ClavePrueba2026!',PASSWORD_DEFAULT); check('U19 contraseña hash',password_verify('ClavePrueba2026!',$hash) && !password_verify('otra',$hash));
echo "$passed aprobaciones, $failed fallos\n"; exit($failed ? 1 : 0);
