# Empaqueta exclusivamente una lista permitida; nunca incluye configuraciones locales ni storage.
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$archiveFolder = Join-Path $projectRoot 'delivery'
New-Item -ItemType Directory -Force -Path $archiveFolder | Out-Null
$zipTarget = Join-Path $archiveFolder 'actualidad-huancayo.zip'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$allowedFiles = @('README.md','.gitignore','.htaccess','config/config.example.php')
foreach ($folder in @('app','database','docs','scripts','tests','public/assets')) {
    $allowedFiles += Get-ChildItem -LiteralPath (Join-Path $projectRoot $folder) -File -Recurse | ForEach-Object { $_.FullName.Substring($projectRoot.Length + 1).Replace('\','/') }
}
$allowedFiles += @('public/index.php','public/uploads/.htaccess','public/uploads/.gitkeep','storage/.gitkeep')
$allowedFiles = $allowedFiles | Sort-Object -Unique
$manifest = Join-Path $projectRoot 'docs/evidencias/manifest.txt'
$allowedFiles += 'docs/evidencias/manifest.txt'
$allowedFiles = $allowedFiles | Sort-Object -Unique
[IO.File]::WriteAllLines($manifest, $allowedFiles, [Text.UTF8Encoding]::new($false))
$stream = [IO.File]::Open($zipTarget,[IO.FileMode]::Create)
$zip = [IO.Compression.ZipArchive]::new($stream,[IO.Compression.ZipArchiveMode]::Create)
try {
    foreach ($relative in $allowedFiles) {
        [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip,(Join-Path $projectRoot $relative),('actualidad-huancayo/' + $relative),[IO.Compression.CompressionLevel]::Optimal) | Out-Null
    }
} finally { $zip.Dispose(); $stream.Dispose() }
$check = [IO.Compression.ZipFile]::OpenRead($zipTarget)
try {
    $unexpected = $check.Entries | Where-Object { $_.FullName -match '(config\.local|config\.test|http-config|edge-profile|mysql/|sess_|cookies|\.log$)' }
    if ($unexpected) { throw 'El ZIP contiene archivos excluidos.' }
    if ($check.Entries.Count -ne $allowedFiles.Count) { throw 'Cantidad de entradas incorrecta.' }
    Write-Output ('ZIP verificado: ' + $zipTarget + '; ' + $check.Entries.Count + ' archivos; sin configuraciones locales ni datos temporales.')
} finally { $check.Dispose() }
Get-FileHash -LiteralPath $zipTarget -Algorithm SHA256
