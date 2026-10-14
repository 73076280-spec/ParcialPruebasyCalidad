# Guion de exposición

Autor: Ramos Ore Dennis Jhordan. Duración sugerida: 8 a 10 minutos, estimación.

1. **Problema y objetivo (1 minuto).** «Actualidad Huancayo permite publicar noticias sin editar directamente archivos. Las noticias de ejemplo son ficticias. El proyecto demuestra tanto desarrollo como planificación y ejecución de pruebas».
2. **Vista pública (1 minuto).** Abrir inicio, buscar por título, filtrar categoría y explicar las seis noticias por página. Abrir un detalle. Consultar `?r=detail&id=9` para mostrar que el borrador de demostración responde 404; comprobar antes que ese ID sigue siendo el borrador en la instalación.
3. **Panel (2 minutos).** Iniciar sesión con el administrador creado. Mostrar cantidades. Crear una noticia como borrador, intentar un resumen muy corto y observar la conservación de textos. Completar y publicar. Editar; demostrar confirmación y cancelación antes de eliminar. No eliminar noticias de ejemplo durante la explicación.
4. **Categorías (1 minuto).** Crear una categoría vacía. Explicar que una categoría con noticias no puede borrarse: la base de datos bloquea la pérdida de información.
5. **Archivos (2 minutos).** `public/index.php` recibe la petición y abre la sesión. `Controller.php` verifica ruta, autorización, método y CSRF. `Blog.php` consulta PDO. Las vistas generan HTML escapado. `support.php` valida longitudes e imágenes. `schema.sql` define las relaciones. El archivo local de configuración queda fuera del ZIP.
6. **Pruebas (2 minutos).** Ejecutar `php tests/unit.php` y, con una base separada, `php tests/integration.php`. Explicar que 24 casos de sistema no son lo mismo que las comprobaciones automatizadas. Mostrar resultados y evidencias reales. Mostrar una partición válida/inválida y el límite 160/161 del título. No presentar una revisión parcial de teclado como certificación de accesibilidad.
7. **Cierre (1 minuto).** Señalar las verificaciones pendientes, los materiales de semanas 3 y 4 por contrastar y la ubicación de ZIP, SQL, informe y plan. Distinguir la medición local de la velocidad de un servidor con usuarios simultáneos.

Preguntas previsibles: ¿por qué MVC? Separa peticiones, datos y presentación. ¿Por qué PDO? Enlaza datos en consultas preparadas. ¿Por qué hash? Evita almacenar la contraseña legible; `password_verify` la comprueba. ¿Por qué POST y CSRF? POST evita modificar con enlaces y CSRF comprueba que el formulario procede de la sesión. ¿Por qué texto plano? Se escapa HTML para evitar que contenido de una noticia ejecute scripts.
