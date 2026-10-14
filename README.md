# Actualidad Huancayo

Producto Académico N.º 2 · Pruebas y Calidad de Software · Universidad Continental.
Autor: **Ramos Ore Dennis Jhordan**. Noticias originales ficticias para demostración.

Blog PHP 8.2 sin framework: área pública, búsqueda por título, categorías, paginación de seis noticias, detalle; panel protegido con noticias, categorías y resumen. Recursos locales, sin compilación.

## Instalación en Windows/XAMPP

1. Instale XAMPP con PHP 8.2 y MariaDB/MySQL. Inicie **MySQL** en su panel. El servidor integrado de PHP permite trabajar directamente en `D:\Parcial`, sin copiar a htdocs. No exponga el servidor integrado a Internet.
2. En PowerShell:

```powershell
Set-Location D:\Parcial
Copy-Item config\config.example.php config\config.local.php
notepad config\config.local.php
```

3. Configure host, puerto, usuario y contraseña locales. La base debe ser `actualidad_huancayo`; para el servidor PHP mantenga `base_url` vacío. No comparta `config.local.php`.
4. Cree la base e importe SQL desde phpMyAdmin: **Nueva** → `actualidad_huancayo` → cotejamiento `utf8mb4_unicode_ci` → **Importar**, primero `database/schema.sql`, después `database/demo.sql`. Los scripts no borran tablas. No importe dos veces sobre la misma base.
5. Alternativa de importación desde la consola interactiva (la contraseña se solicita, no se incluye en comandos):

```powershell
& C:\xampp\mysql\bin\mysql.exe -u root -p
```

```sql
CREATE DATABASE actualidad_huancayo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE actualidad_huancayo;
SOURCE D:/Parcial/database/schema.sql;
SOURCE D:/Parcial/database/demo.sql;
EXIT;
```

6. Cree el administrador:

```powershell
& C:\xampp\php\php.exe scripts\create-admin.php admin@actualidad.test
```

Introduzca una contraseña propia de 12 a 72 bytes. Para una demostración local reproducible puede usar `DemoHuancayo2026!`; es una clave pública de ejemplo, debe cambiarse para otro uso. Se solicita por stdin y se almacena únicamente su hash. La entrada es visible en la consola. El script no reemplaza usuarios existentes.

7. Ejecute:

```powershell
& C:\xampp\php\php.exe -d session.save_path=D:\Parcial\storage -d upload_max_filesize=3M -d post_max_size=4M -S 127.0.0.1:8080 -t public
```

Inicio: `http://127.0.0.1:8080/index.php`. Panel: `http://127.0.0.1:8080/index.php?r=login`. Detenga con Ctrl+C. El límite de aplicación es 2 MiB; el límite PHP se deja ligeramente mayor para poder mostrar el error del campo.

## Alternativa Apache

Copie el proyecto completo a `C:\xampp\htdocs\actualidad-huancayo`, configure `base_url` como `/actualidad-huancayo/public` y acceda a `http://localhost/actualidad-huancayo/public/index.php`. Nunca utilice la raíz del proyecto como raíz pública de un alojamiento; configure un VirtualHost con DocumentRoot apuntando exclusivamente a `public`. Las configuraciones, scripts y SQL deben quedar fuera de la raíz pública. No es necesario modificar Apache para usar la opción PHP anterior. `public/uploads/.htaccess` bloquea ejecución en Apache; en el servidor integrado solo se admiten extensiones raster y nombres aleatorios.

## Pruebas reproducibles

```powershell
& C:\xampp\php\php.exe tests\unit.php
rg --files -g '*.php' -g '!storage/**' | ForEach-Object { & C:\xampp\php\php.exe -l $_ }
```

Para integración, cree una base **separada** `actualidad_huancayo_test`, importe solamente `schema.sql`, copie el ejemplo a `config/config.test.php` y ajuste el DSN. Ejecute `php tests/integration.php`. Verifica el nombre de base real y usa transacción con rollback; no contiene DROP ni elimina datos previos. Los contadores AUTO_INCREMENT pueden avanzar aunque haya rollback.

Los flujos HTTP y rendimiento requieren una instancia de aplicación dedicada con base `actualidad_http_test`. **No cambie la configuración de una aplicación en uso.** Prepare una copia del proyecto en otra carpeta para repetir esos flujos, configure dicha copia y lance su servidor en otro puerto. `scripts/setup-test.php 3306` crea exclusivamente esas dos bases `_test` en el MySQL local con root sin contraseña; para otras credenciales use phpMyAdmin, schema/demo y el script de administrador. El helper no sirve como instalación de desarrollo ni cambia `config.local.php`; escribe la configuración HTTP en `storage/http-config.php`. En la copia dedicada, cópiela a `config/config.local.php` si no existe otra configuración que conservar.

```powershell
php tests\integration.php
php tests\http.php http://127.0.0.1:8080
php tests\performance.php http://127.0.0.1:8080
node tests\browser.mjs http://127.0.0.1:8080
```

La instalación desde ZIP también dispone de un arnés opcional para el entorno de esta entrega: `powershell -NoProfile -File tests/install.ps1 -ReviewNumber 1`. Requiere el ZIP, Apache/PHP en `C:\xampp`, MySQL temporal en 3307 y puerto 8081 libre; crea una carpeta y base de instalación nuevas, comprueba bloqueo de scripts y detiene únicamente su Apache. Para repetir, use otro ReviewNumber: se niega a sobrescribir la copia o base anterior. En un entorno que restrinja el cierre de procesos, debe disponer de permisos para limpiar el Apache que inicia este arnés. La instalación habitual se hace con los pasos iniciales, sin necesitar este helper.

HTTP requiere curl; imágenes requieren fileinfo; textos requieren mbstring. El navegador opcional requiere Node y Edge en su ruta estándar de Windows; usa un perfil exclusivo en `storage`, no el perfil personal. Las pruebas HTTP limpian solo los registros que crean. Rendimiento agrega 1.000 noticias ficticias en una categoría única y elimina solamente esa categoría y sus noticias al terminar. No ejecute pruebas simultáneamente sobre la misma base HTTP.

## Reglas y errores habituales

- Categorías con noticias: eliminación bloqueada por clave foránea; reasigne primero las noticias. No existe borrado en cascada.
- Borrador: oculto del inicio y del detalle público. Publicar asigna fecha; editar publicado conserva fecha; volver a borrador retira publicación y limpia la fecha.
- Imágenes: JPEG/PNG/WebP auténticos, máximo 2 MiB, texto alternativo obligatorio. No se admiten SVG ni PHP. Al reemplazar o eliminar noticias se conservan imágenes antiguas para evitar borrado accidental; una limpieza manual posterior debe verificar referencias en la BD.
- Error 503: revise MySQL iniciado, puerto, DSN, credenciales y tablas importadas; los detalles solo van al registro del servidor.
- `could not find driver`: habilite pdo_mysql en XAMPP. `undefined function mb_strlen`: habilite mbstring.
- CSRF 403: recargue el formulario; un login cambia el token y hace caducar formularios anteriores.
- Imagen no recibida: revise `upload_max_filesize`, `post_max_size`, `file_uploads` y permisos de `public/uploads`. El navegador no conserva archivos elegidos después de un error.
- Puerto 8080 ocupado: cambie a 8081 y use ese puerto en las URLs de pruebas.
- No hay resultados: limpie filtros. Las noticias de demostración son ficticias y hay un borrador oculto.

## Entrega

Documentación en `docs/`: informe, plan con 24 casos, matriz, resultados, participantes y guion. Informe y plan también en PDF y HTML imprimible. Puede actualizar esos formatos con `node scripts/export-docs.mjs` (requiere Edge en Windows), o abrir los HTML y usar Imprimir → Guardar como PDF. Para DOCX, abra el HTML con Word o LibreOffice y guarde como DOCX; ese formato no se generó en esta entrega. La exportación automática revisa texto, ausencia de desbordamiento, cantidad de casos y estructura PDF; las vistas iniciales se revisaron visualmente, sin revisión visual página por página de los PDF.

Evidencias reales en `docs/evidencias`. ZIP en `delivery/actualidad-huancayo.zip`. Para regenerarlo: `powershell -NoProfile -File scripts/package.ps1`; utiliza una lista permitida y comprueba exclusiones. Configuraciones locales, bases temporales, perfiles del navegador y sesiones están excluidos. Consulte los resultados antes de afirmar que se ha verificado todo. Los materiales de semanas 3 y 4 no fueron proporcionados: deben contrastarse antes de presentar.
