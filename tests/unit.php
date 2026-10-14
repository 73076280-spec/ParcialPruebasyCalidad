<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$passed = 0; $failed = 0; $examples = [];
function check(string $name, bool $ok, array $example = []): void {
    global $passed, $failed, $examples;
    $ok ? $passed++ : $failed++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . PHP_EOL;
    if ($example) $examples[] = [$name, ...$example, $ok ? 'Aprobado' : 'Falló'];
}
$valid = ['title' => 'Título válido', 'summary' => 'Resumen suficientemente largo.', 'content' => 'Contenido de una noticia para realizar pruebas.', 'category_id' => '1', 'status' => 'draft', 'image_alt' => ''];
check('U01 noticia válida sin imagen', validateNews($valid) === []);
foreach (NEWS_TEXT_RULES as $field => [$min, $max]) {
    foreach ([$min-1 => false, $min => true, $max => true, $max+1 => false] as $length => $expected) {
        $d=$valid; $d[$field]=str_repeat('á',$length);
        check("Límite $field $length", !isset(validateNews($d)[$field]) === $expected, [$field, "$min a $max caracteres sin espacios exteriores", "á repetida $length veces", $expected ? 'Aceptar' : 'Rechazar', $expected ? 'Aceptado por el validador' : 'Error del campo verificado']);
    }
    foreach (['vacío' => '', 'solo espacios' => '     ', 'espacios Unicode' => "\u{00A0}\u{2003}"] as $label => $value) {
        $d=$valid; $d[$field]=$value;
        check("Vacío $field $label", isset(validateNews($d)[$field]), [$field, 'Obligatorio; no solo espacios', $label, 'Rechazar', 'Error del campo verificado']);
    }
}
foreach ([0 => false,1 => false,2 => true,60 => true,61 => false] as $length => $expected) {
    check("Categoría nombre $length", (validateCategory(['name'=>str_repeat('n',$length)])===[])===$expected, ['name', '2 a 60 caracteres, sin espacios exteriores', $length === 0 ? 'Cadena vacía' : "n repetida $length veces", $expected ? 'Aceptar' : 'Rechazar', $expected ? 'Aceptado por el validador' : 'Error del campo verificado']);
}
foreach (['ASCII'=>'   ', 'Unicode'=>"\u{00A0}\u{2003}"] as $label => $spaces) check('Nombre solo espacios '.$label, isset(validateCategory(['name'=>$spaces])['name']), ['name', 'No solo espacios', 'Espacios '.$label, 'Rechazar', 'Error del campo verificado']);
check('Normalización al guardar', normalizeText("\u{00A0} Cultura \u{2003}")==='Cultura');
check('Entrada estructurada inválida', isset(validateNews($valid+['unused'=>''])['unused'])===false && isset(validateNews(array_replace($valid,['title'=>['x']]))['title']));
foreach (['draft'=>true,'published'=>true,'invalid'=>false,''=>false] as $value=>$expected) {
    $d=$valid; $d['status']=$value;
    check('Estado '.($value?:'vacío'), !isset(validateNews($d)['status'])===$expected,['status','Solo draft o published',$value?:'Vacío',$expected?'Aceptar':'Rechazar',$expected?'Aceptado por el validador':'Error del campo verificado']);
}
foreach (['1'=>true,'0'=>false,'abc'=>false] as $value=>$expected) {
    $d=$valid; $d['category_id']=(string)$value;
    check('Categoría identificador '.$value, !isset(validateNews($d)['category_id'])===$expected,['category_id','Entero positivo; existencia comprobada por HTTP',(string)$value,$expected?'Aceptar formato':'Rechazar',$expected?'Formato aceptado; existencia requiere BD':'Error del campo verificado']);
}
check('U06 alt obligatorio con imagen', isset(validateNews($valid,true)['image_alt']), ['image_alt','1 a 180 si hay imagen','Vacío con imagen','Rechazar','Error del campo verificado']);
$d=$valid; $d['image_alt']='a'; check('Alt mínimo 1',validateNews($d,true)===[],['image_alt','1 a 180 si hay imagen','a','Aceptar','Aceptado por el validador']);
$d['image_alt']=str_repeat('a',180); check('U07 alt límite 180', validateNews($d,true)===[],['image_alt','1 a 180 si hay imagen','a repetida 180 veces','Aceptar','Aceptado por el validador']); $d['image_alt'].='a'; check('U08 alt 181',isset(validateNews($d,true)['image_alt']),['image_alt','1 a 180 si hay imagen','a repetida 181 veces','Rechazar','Error del campo verificado']);
check('U09 escape XSS', e('<script>"&') === '&lt;script&gt;&quot;&amp;');
$_SESSION=[]; $token=csrf(); check('U10 token aleatorio y estable', strlen($token)===64 && csrf()===$token); check('U11 CSRF correcto', validCsrf($token)); check('U12 CSRF incorrecto/ausente', !validCsrf('bad') && !validCsrf(null));
check('U13 sin imagen', validateImage(['error'=>UPLOAD_ERR_NO_FILE])===null,['image','Opcional','Sin archivo','Aceptar','Aceptado por el validador']);
check('U14 error de transferencia', validateImage(['error'=>UPLOAD_ERR_PARTIAL])!==null);
check('Imagen cero bytes',validateImage(['error'=>0,'size'=>0])!==null,['image','Archivo recibido no vacío','0 bytes','Rechazar','Error de tamaño verificado']);
check('U15 tamaño excesivo', validateImage(['error'=>0,'size'=>2097153])!==null,['image','Máximo 2.097.152 bytes','2.097.153 bytes','Rechazar','Error de tamaño verificado']);
$tmp=ROOT.'/storage/unit-image.tmp'; file_put_contents($tmp,'<?php echo 1;');
check('U16 PHP camuflado', validateImage(['error'=>0,'size'=>15,'name'=>'foto.jpg','tmp_name'=>$tmp])!==null,['image','Extensión y MIME raster reales','PHP renombrado foto.jpg','Rechazar','Error de tipo verificado']);
file_put_contents($tmp,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
check('U17 PNG real',validateImage(['error'=>0,'size'=>filesize($tmp),'name'=>'foto.png','tmp_name'=>$tmp])===null);
check('U18 extensión prohibida',validateImage(['error'=>0,'size'=>filesize($tmp),'name'=>'foto.php','tmp_name'=>$tmp])!==null);
$png=file_get_contents($tmp);file_put_contents($tmp,$png.str_repeat("\0",2097152-strlen($png)));clearstatcache(true,$tmp);
check('U20 imagen exactamente 2 MiB',validateImage(['error'=>0,'size'=>filesize($tmp),'name'=>'foto.png','tmp_name'=>$tmp])===null,['image','Máximo 2.097.152 bytes','PNG real rellenado hasta 2.097.152 bytes','Aceptar','Aceptado por el validador']);
$full=file_get_contents($tmp);file_put_contents($tmp,substr($full,0,-1));clearstatcache(true,$tmp);
check('Imagen máximo menos 1 byte',validateImage(['error'=>0,'size'=>filesize($tmp),'name'=>'foto.png','tmp_name'=>$tmp])===null,['image','Máximo 2.097.152 bytes','PNG real rellenado hasta 2.097.151 bytes','Aceptar','Aceptado por el validador']);unlink($tmp);
$hash=password_hash('ClavePrueba2026!',PASSWORD_DEFAULT); check('U19 contraseña hash',password_verify('ClavePrueba2026!',$hash) && !password_verify('otra',$hash));
if (in_array('--examples', $argv, true)) {
    $md = "# Ejemplos ejecutados de datos válidos, inválidos y límites\n\nReglas reales de support.php. mb_strlen cuenta caracteres Unicode; se excluyen espacios exteriores. Estas filas reutilizan las pruebas unitarias; no son casos adicionales del plan ni pruebas de navegador. Un nombre válido sintácticamente todavía debe ser único y la categoría de una noticia debe existir, comprobados por HTTP.\n\n";
    $md .= "| Comprobación | Campo | Regla | Dato de prueba | Esperado | Resultado real | Estado |\n|---|---|---|---|---|---|---|\n";
    foreach ($examples as $row) $md .= '| '.implode(' | ', array_map(fn($v)=>str_replace('|','/', $v),$row))." |\n";
    $md .= "\nEvidencia: docs/evidencias/revision-2/unit.txt. Comando: php tests/unit.php --examples. Revisión de servidor al crear/editar: H35–H42 en http.txt. Existencia/estado, duplicados e imágenes HTTP están descritos en el plan. Variantes no ejecutadas: visualización JPEG/WebP y teléfono físico.\n";
    file_put_contents(ROOT.'/docs/ejemplos-validacion.md',$md);
}
echo "$passed aprobaciones, $failed fallos\n"; exit($failed ? 1 : 0);
