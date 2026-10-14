# Requiere MariaDB temporal en 3307 y XAMPP; nunca modifica sus configuraciones.
param([ValidateRange(1,9999)][int]$ReviewNumber = 1)
$ErrorActionPreference = 'Stop'
$taskRoot = Split-Path -Parent $PSScriptRoot
$installFolder = Join-Path $taskRoot ('storage/install-review' + $ReviewNumber)
$databaseName = 'actualidad_install' + $ReviewNumber + '_test'
if (Test-Path -LiteralPath $installFolder) { throw 'La copia de revisión ya existe. Use una carpeta nueva para no sobrescribir.' }
Expand-Archive -LiteralPath (Join-Path $taskRoot 'delivery/actualidad-huancayo.zip') -DestinationPath $installFolder
$copyRoot = Join-Path $installFolder 'actualidad-huancayo'
$lines = [Collections.Generic.List[string]]::new()
& C:\xampp\php\php.exe (Join-Path $PSScriptRoot 'install-setup.php') $copyRoot $databaseName
if ($LASTEXITCODE -ne 0) { throw 'No se pudo inicializar la instalación.' }
$lines.Add('PASS A01 ZIP extraído sin config.local.php previo; esquema/demo importados en ' + $databaseName + '.')
'DemoHuancayo2026!' | & C:\xampp\php\php.exe (Join-Path $copyRoot 'scripts/create-admin.php') admin@actualidad.test
if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear administrador.' }
$lines.Add('PASS A02 Administrador creado con el script CLI desde la copia extraída.')
$guard = & C:\xampp\php\php.exe (Join-Path $copyRoot 'tests/integration.php')
if (($guard -join '') -notmatch 'base de prueba debe terminar en _test') { throw 'Guarda de desarrollo no funcionó.' }
$lines.Add('PASS A03 Integración rechazó DSN actualidad_guard_dev antes de conectar o modificar datos.')
$documentRoot = (Join-Path $copyRoot 'public').Replace('\','/')
$tempRoot = (Join-Path $taskRoot 'storage').Replace('\','/')
$configuration = @"
ServerRoot "C:/xampp/apache"
Listen 127.0.0.1:8081
ServerName 127.0.0.1:8081
PidFile "$tempRoot/apache-review.pid"
ErrorLog "$tempRoot/apache-review.log"
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule mime_module modules/mod_mime.so
LoadModule dir_module modules/mod_dir.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadFile "C:/xampp/php/php8ts.dll"
LoadFile "C:/xampp/php/libpq.dll"
LoadFile "C:/xampp/php/libsqlite3.dll"
LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
PHPIniDir "C:/xampp/php"
TypesConfig "C:/xampp/apache/conf/mime.types"
DocumentRoot "$documentRoot"
DirectoryIndex index.php
<Directory />
Require all denied
AllowOverride None
</Directory>
<Directory "$documentRoot">
Require all granted
AllowOverride All
Options -Indexes -ExecCGI
</Directory>
<FilesMatch "\.php$">
SetHandler application/x-httpd-php
</FilesMatch>
php_admin_value session.save_path "$tempRoot"
php_admin_value upload_tmp_dir "$tempRoot"
php_admin_value error_log "$tempRoot/apache-php.log"
php_admin_value upload_max_filesize 3M
php_admin_value post_max_size 4M
"@
$confFile = Join-Path $taskRoot 'storage/apache-review.conf'
[IO.File]::WriteAllText($confFile,$configuration,[Text.UTF8Encoding]::new($false))
& C:\xampp\apache\bin\httpd.exe -t -f $confFile
if ($LASTEXITCODE -ne 0) { throw 'Apache no acepta configuración.' }
$ownedApache = Start-Process -FilePath C:\xampp\apache\bin\httpd.exe -ArgumentList @('-f',$confFile) -WindowStyle Hidden -PassThru
function Status([string]$url) {
    try { return (Invoke-WebRequest -UseBasicParsing -Uri $url).StatusCode }
    catch { if ($_.Exception.Response) { return [int]$_.Exception.Response.StatusCode }; throw }
}
try {
    $ready = $false
    for ($attempt=0; $attempt -lt 40; $attempt++) {
        try { if ((Status 'http://127.0.0.1:8081/index.php') -eq 200) { $ready=$true; break } } catch {}
        Start-Sleep -Milliseconds 250
    }
    if (!$ready) { throw 'Apache no respondió.' }
    $page = Invoke-WebRequest -UseBasicParsing -Uri 'http://127.0.0.1:8081/index.php'
    if ($page.Content -notmatch '8 noticias encontradas') { throw 'Inicio Apache sin demo.' }
    $lines.Add('PASS A04 Inicio desde ZIP en Apache: 200, ocho publicaciones ficticias.')
    foreach ($relative in @('config/config.local.php','database/schema.sql','scripts/create-admin.php','../config/config.local.php')) {
        $statusCode = Status ('http://127.0.0.1:8081/' + $relative)
        if ($statusCode -notin @(403,404)) { throw ('Ruta privada accesible: ' + $relative) }
    }
    $lines.Add('PASS A05 Configuración, SQL y script CLI fuera de raíz pública: solicitudes 403/404.')
    $scriptProbe = Join-Path $documentRoot 'uploads/execution-probe.php'
    [IO.File]::WriteAllText($scriptProbe,'<?php echo "SHOULD_NOT_EXECUTE";',[Text.UTF8Encoding]::new($false))
    if ((Status 'http://127.0.0.1:8081/uploads/execution-probe.php') -ne 403) { throw 'Apache no bloqueó el script de prueba.' }
    $lines.Add('PASS A06 .htaccess uploads denegó archivo PHP de prueba con 403.')
    $session = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
    $loginPage = Invoke-WebRequest -UseBasicParsing -Uri 'http://127.0.0.1:8081/index.php?r=login' -WebSession $session
    $token = [regex]::Match($loginPage.Content,'name="_token" value="([a-f0-9]+)"').Groups[1].Value
    $panel = Invoke-WebRequest -UseBasicParsing -Uri 'http://127.0.0.1:8081/index.php?r=login' -Method Post -WebSession $session -Body @{_token=$token;email='admin@actualidad.test';password='DemoHuancayo2026!'}
    if ($panel.Content -notmatch 'Resumen del blog') { throw 'Login Apache no completado.' }
    $lines.Add('PASS A07 Login del administrador creado y panel funcionando en Apache.')
    & C:\xampp\apache\bin\httpd.exe -v | ForEach-Object { $lines.Add($_) }
    $lines | Tee-Object -FilePath (Join-Path $taskRoot 'docs/evidencias/install.txt')
} finally {
    & taskkill.exe /PID $ownedApache.Id /T /F | Out-Null
}
