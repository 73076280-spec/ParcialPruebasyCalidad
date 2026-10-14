# Resultados reales de pruebas

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

## Resultados finales

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
