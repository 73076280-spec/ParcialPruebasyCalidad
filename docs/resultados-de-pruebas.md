# Resultados reales de pruebas

## Revisión 2: CRUD, validación, usabilidad y móvil

Autor: Ramos Ore Dennis Jhordan. Revisión: 13 de octubre de 2026 (America/Lima). Directorio D:\Parcial. Se revisó el proyecto existente y su Git sin publicar ni subir cambios. No había AGENTS.md en el proyecto.

La base y credenciales de desarrollo se conservaron. tests/prepare-qa.php creó una copia en storage/qa-20261013200403 y la base nueva actualidad_revision_20261013200403_test. El servidor de pruebas sirvió solo su public en 127.0.0.1:8082. No se reemplazó config.local.php/config.test.php del autor. La ampliación de content a MEDIUMTEXT conservó todos los registros.

| Comprobación actual | Resultado real | Evidencia en docs/evidencias/revision-2 |
|---|---|---|
| Sintaxis PHP | Sin errores en todos los PHP del proyecto examinados | syntax.txt |
| Unitarias y tabla de ejemplos | 56 aprobaciones, 0 fallos | unit.txt y docs/ejemplos-validacion.md |
| Integración MariaDB | 15 aprobaciones, 0 fallos, ROLLBACK; máximo Unicode íntegro | integration.txt |
| HTTP | 52 aprobaciones, 0 fallos; crear/editar inválidos, conservar, cancelar, CRUD/seguridad | http.txt |
| Edge/CDP | 226 comprobaciones aprobadas, 0 fallos | navegador.txt, pantallas.json, contraste.json y 12 PNG |
| Instalación ZIP/Apache | Siete aprobaciones, 0 fallos en la copia del ZIP de esta revisión | install.txt |
| Conservación del proyecto | Huellas de datos y configuraciones idénticas antes/después | datos-antes.json y datos-despues.json |
| Rendimiento local | Mediana 6,457 ms, P95 31,011 ms, máximo 31,175 ms; P95 <500 ms alcanzado | performance.txt |

356 aserciones actuales, aparte de sintaxis/rendimiento. Plan: 25 casos; 24 Aprobado y CP21 No ejecutado. Avance 96%, aprobación de ejecutados 100%. Cobertura de requisitos diseñada y con algún caso ejecutado 100%; no significa evaluación exhaustiva de RNF05. No se midió cobertura de código.

### Comandos de la revisión

```powershell
php tests\preserve-data.php
php scripts\migrate-content.php
php tests\unit.php --examples
php tests\prepare-qa.php
$qaPath = (Get-Content storage\qa-path.txt -Raw).Trim()
php (Join-Path $qaPath 'scripts\migrate-content.php')
php -d session.save_path="$qaPath\storage" -d upload_tmp_dir="$qaPath\storage" -d upload_max_filesize=3M -d post_max_size=4M -S 127.0.0.1:8082 -t "$qaPath\public"
php (Join-Path $qaPath 'tests\integration.php')
php (Join-Path $qaPath 'tests\http.php') http://127.0.0.1:8082
node tests\browser.mjs http://127.0.0.1:8082
php (Join-Path $qaPath 'tests\performance.php') http://127.0.0.1:8082
php tests\preserve-data.php
rg --files -g '*.php' -g '!storage/**' | ForEach-Object { php -l $_ }
node scripts\export-docs.mjs
powershell -NoProfile -File scripts\package.ps1
powershell -NoProfile -File tests\install.ps1 -ReviewNumber 5
```

Estos comandos se ejecutan en consolas separadas para mantener el servidor mientras se prueba. No se cambian credenciales ni se importan fixtures en desarrollo. Los scripts HTTP/performance limpian solo sus filas creadas en la base QA; integración revierte su transacción.

### Cobertura del navegador y revisión visual

Se probaron 12 pantallas en cuatro anchos CSS: 360, 390, 768 y 1440 (48 variantes). Se añadieron ocho estados de error, un formulario con fuente raíz al 200% y 11 variantes con viewport/layout equivalente al zoom 200%: 68 layouts en total. Todos pasaron ausencia de desbordamiento, límites de controles y superposición, etiquetas/descripciones y un único h1 principal.

En 390/1440 se recorrieron todas las rutas con Tab/Shift+Tab y se comprobaron foco visible y orden DOM. Enter activó salto, Cancelar y enlaces de errores. Los formularios inválidos conservaron valores y el resumen recibió foco. Las capturas se revisaron visualmente: se conserva paleta formal y no se observan campos/botones superpuestos. Son tamaños de navegador emulados, no un teléfono físico.

Fuente raíz duplicada: el tamaño calculado del control se duplicó. Layout equivalente al zoom: 720 píxeles CSS con escala de salida 2 para 1440; no se pulsó Ctrl+ nativo del navegador. No se usa pinch/scroll para aparentar una prueba de reflow. Las ocho muestras de contraste dieron ratios de 7,04:1 a 12,66:1, según estilos efectivos. No constituyen auditoría completa de foco, hover y todas las imágenes.

### Conservación y cambios de datos

Antes/después: nueve noticias, tres categorías y un administrador, con huellas completas iguales. SHA256 de config.local.php y config.test.php también coincide. No se cambiaron credenciales, títulos, textos, fechas ni categorías del autor. Solo cambió la capacidad del tipo content de TEXT a MEDIUMTEXT; I15 verificó 20.000 caracteres de cuatro bytes sin truncamiento. Las pruebas se hicieron en una base nueva, separada.

### Rendimiento actual

Se reutilizó el escenario: 1.000 noticias ficticias adicionales en una categoría propia, página 100, un calentamiento y 30 solicitudes secuenciales, un usuario. P95 por rango más próximo. Objetivo local <500 ms para respuesta sencilla sin red externa; resultado 31,011 ms. No equivale a carga concurrente o red móvil. Los resultados iniciales de la primera versión se conservan abajo como históricos.

### Pendientes reales

CP21 conserva estado No ejecutado para la revisión manual completa: lector de pantalla, Ctrl+ nativo 200%, zoom intermedio y contraste de todos los estados. También quedan teléfono físico/Wi-Fi, otros navegadores, usuarios con necesidades específicas, imágenes JPEG/WebP visuales, carga concurrente y revisión de PDFs página por página. No hay audio/video informativo, por lo que subtítulos/transcripciones no aplican a esta versión. Deben contrastarse los materiales de semanas 3 y 4.

HTML/PDF y ZIP se regeneran con los scripts existentes; registro de exportación y vistas iniciales en revision-2. La lista de archivos se revisa en manifest.txt, excluyendo configuraciones locales, bases, cookies y perfiles. La instalación desde el ZIP usa Apache propio en 8081, config temporal y base exclusiva actualidad_install5_test, sin modificar XAMPP.

## Registro histórico de la primera versión

El siguiente registro corresponde al desarrollo inicial. Sus cantidades y pendientes describen esa versión; las cifras actuales son las de la revisión anterior. Se conservan los registros originales, no se presentan como nuevas ejecuciones.

Autor: Ramos Ore Dennis Jhordan. Ejecución: 13 de octubre de 2026 (America/Lima). No es fecha de entrega. Directorio de trabajo: D:\Parcial.

## Entorno comprobado

Windows; PHP CLI 8.2.12, PDO MySQL/SQLite, mbstring, fileinfo y curl disponibles. MariaDB 10.4.32 de XAMPP; Node v26.7.0; Edge 155.0.4283.45. La carpeta estaba vacía y no contenía AGENTS.md ni código previo. MySQL de XAMPP no escuchaba en 3306. No se modificaron otros proyectos, configuraciones del sistema ni datos de XAMPP.

Se inicializó una instancia temporal dentro de storage/mysql, escuchando exclusivamente en 127.0.0.1:3307. El helper creó actualidad_huancayo_test (integración) y actualidad_http_test (HTTP/navegador). La configuración local de esta ejecución apunta a la base HTTP de prueba y está excluida del ZIP. El receptor debe crear su configuración y base de desarrollo según README.

## Comandos ejecutados

```powershell
php -v
php -m
& C:\xampp\mysql\bin\mysql.exe --version
& C:\xampp\mysql\bin\mysql.exe -h 127.0.0.1 -u root -e 'SELECT VERSION();'
```

Último comando inicial: error 2002, conexión rechazada porque MySQL no estaba iniciado. Se continuó con instancia aislada:

```powershell
& C:\xampp\mysql\bin\mysql_install_db.exe --datadir=D:\Parcial\storage\mysql --port=3307
& C:\xampp\mysql\bin\mysqld.exe --defaults-file=D:\Parcial\storage\mysql\my.ini --datadir=D:\Parcial\storage\mysql --port=3307 --bind-address=127.0.0.1 --console
php scripts\setup-test.php 3307
Copy-Item storage\http-config.php config\config.local.php
php -d session.save_path=D:\Parcial\storage -d upload_max_filesize=3M -d post_max_size=4M -S 127.0.0.1:8080 -t public
php tests\unit.php
php tests\integration.php
php tests\http.php
node tests\browser.mjs
php tests\performance.php
rg --files -g '*.php' -g '!storage/**' | ForEach-Object { php -l $_ }
```

El Copy-Item se realizó sobre un proyecto nuevo sin configuración previa, exclusivamente para QA. No debe repetirse sobre una instalación de desarrollo en uso.

## Resultados iniciales (históricos)

| Comprobación | Resultado real | Evidencia |
|---|---|---|
| Sintaxis PHP | Sin errores en todos los PHP incluidos, y configuraciones locales de prueba | evidencias/syntax.txt |
| Unitarias | 29 aprobaciones, 0 fallos | evidencias/unit.txt |
| Integración MariaDB | 14 aprobaciones, 0 fallos; ROLLBACK | evidencias/integration.txt |
| HTTP con cookies y multipart | 44 aprobaciones, 0 fallos | evidencias/http.txt |
| Edge escritorio/celular | 11 aprobaciones, 0 fallos | evidencias/navegador.txt y seis PNG |
| Instalación desde ZIP y Apache | 7 aprobaciones, 0 fallos | evidencias/install.txt |
| Rendimiento local | Objetivo P95 <500 ms alcanzado | evidencias/performance.txt |

Total final de aserciones funcionales automatizadas: 105. No incluye sintaxis ni rendimiento. El plan tiene 24 casos, 23 aprobados y CP21 No ejecutado. Avance 95,83%; aprobación de ejecutados 100%; cobertura de requisitos diseñada 100% y ejecutada 100% (18/18 con algún caso ejecutado). No se midió cobertura de líneas o ramas. La revisión completa de RNF05 sigue pendiente aunque tenga casos parciales aprobados.

La ejecución inicial del arnés Edge no completó Page.enable. Se corrigió el mantenimiento del proceso Node y se lanzó Edge headless con perfil exclusivo y --no-sandbox para esta QA local. La ejecución final completó las 11 comprobaciones. Esta opción se usa solamente con contenidos del proyecto local; no se modificó el perfil personal ni las opciones del Edge habitual. No se registró como defecto del blog.

## Revisión visual

Se abrieron y revisaron capturas reales de inicio en escritorio 1440×1000 y celular emulado 390×844, así como errores del formulario móvil. El DOM verificó ausencia de desbordamiento, una columna móvil, etiquetas de login, salto por teclado, asociación de errores y foco en primer campo inválido. La captura del escritorio conserva el contorno de foco del main después de utilizar el enlace de salto: es evidencia del foco visible, no un error de borde.

Las otras capturas corresponden a login, formulario y resumen. El resumen contiene 9 noticias totales, 8 publicadas y 3 categorías. No se utilizó un teléfono físico ni lector de pantalla. No se afirmó conformidad WCAG.

## Rendimiento reproducible

`tests/performance.php` exige la base actualidad_http_test, crea una categoría única con 1.000 noticias ficticias adicionales, consulta página 100 (seis tarjetas), realiza un calentamiento y 30 solicitudes curl secuenciales. Mide tiempo HTTP con hrtime, ordena observaciones y calcula P95 como observación ceil(30×0,95)=29. Limpia solamente sus noticias y categoría.

Objetivo propuesto: P95 local <500 ms, presupuesto razonable para una interacción sencilla sin red externa en un blog académico. No es un requisito contractual ni un resultado de los materiales del curso. Resultado medido: mediana 9,946 ms, P95 35,225 ms y máximo 41,897 ms. Un usuario, servidor PHP integrado y BD local; no permite concluir rendimiento de producción, concurrencia ni comportamiento con imágenes grandes.

## Instalación desde ZIP en Apache

Se extrajo el ZIP en una carpeta nueva dentro de storage, se importaron schema.sql/demo.sql en una base exclusiva de instalación y se creó el administrador ejecutando scripts/create-admin.php desde la copia extraída. Apache 2.4.58 se inició con un archivo de configuración propio en storage/apache-review.conf, puerto 8081 y DocumentRoot apuntando a public, sin modificar configuraciones/servicios XAMPP. Se verificaron inicio, login y resumen; rutas de config, SQL y scripts respondieron 403/404 y un PHP de prueba en uploads fue denegado con 403. Un DSN con nombre de desarrollo fue rechazado por integración antes de conectar.

Comando: `powershell -NoProfile -File tests/install.ps1 -ReviewNumber 4`. Siete comprobaciones A01–A07 aprobadas; evidencia install.txt. ReviewNumber permite carpetas y bases nuevas, evitando sobrescrituras. El primer intento no sirvió por lectura de .htaccess de directorios antecesores; se corrigió la configuración temporal con AllowOverride None en Directory / y se repitió. La limpieza del proceso Apache temporal necesitó ejecución fuera del sandbox; se verificó su línea de comandos y se terminaron exclusivamente los procesos iniciados para esta prueba. No se instaló servicio Windows ni se modificó Apache del sistema.

## Formatos y ZIP

Se generaron informe y plan en HTML y PDF mediante `node scripts/export-docs.mjs`. El exportador comprobó documento no vacío, ausencia de desbordamiento horizontal, 24 secciones de casos en el plan y cabecera/estructura de páginas PDF. Las vistas iniciales HTML se revisaron visualmente; se separó la portada del informe tras esa revisión. No se realizó inspección visual página por página de los PDF. Las cantidades y tamaños finales están en evidencias/exportacion.txt. Python y Pandoc no están instalados; no se generó DOCX. El README explica exportación alternativa con Word/LibreOffice.

`powershell -NoProfile -File scripts/package.ps1` generó y revisó el ZIP mediante una lista explícita de archivos. Excluye config.local.php, config.test.php, storage de ejecución, imágenes temporales y perfiles. La lista se registra en evidencias/manifest.txt. Los servicios temporales de QA se detuvieron al finalizar; para instalar y arrancar, use el README.

## Verificaciones pendientes

- CP21: teclado en todas las rutas, lector de pantalla, contraste sistemático y zoom.
- Variantes complementarias: visualización JPEG/WebP, otros navegadores, dispositivos físicos, carga concurrente y condiciones de red.
- Contrastar materiales de semanas 3 y 4 no proporcionados y revisar interpretación de rúbrica con el autor.

No se inventaron capturas, defectos, cobertura de código ni aprobación de escenarios pendientes. Los registros de servidor, cookies, perfiles y bases temporales no forman parte de la entrega.
