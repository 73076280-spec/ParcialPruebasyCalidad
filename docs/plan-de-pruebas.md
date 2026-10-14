# Plan de pruebas: Actualidad Huancayo

**Universidad Continental · Pruebas y Calidad de Software · Producto Académico N.º 2**

**Autor:** Ramos Ore Dennis Jhordan. Versión 1.1 · revisión del proyecto existente, registro de ejecución: 13 de octubre de 2026 (America/Lima).

## 1. Identificación y descripción

Blog de noticias autoadministrable en PHP 8.2, MySQL/MariaDB y PDO. El visitante consulta noticias publicadas y el administrador mantiene noticias y categorías. Es una actividad individual; la referencia a equipo en la consigna es contradictoria y se documenta en participantes.md. Los contenidos de demostración son originales y ficticios.

## 2. ¿Qué factores son importantes al diseñar un plan de pruebas de software para una aplicación web?

El primer factor es comprender qué necesitan los usuarios y transformar esas necesidades en requisitos observables. «Mostrar noticias» resulta insuficiente: hay que precisar si incluye borradores, cómo se ordenan los resultados, cuántos aparecen por página y qué sucede cuando no existen coincidencias. Cada requisito debe enlazarse con casos y resultados para que la evaluación tenga evidencia y no dependa de impresiones.

El segundo factor es el riesgo. Una noticia con un acento incorrecto afecta la presentación, mientras que un borrador visible o una eliminación sin autorización afecta confidencialidad e integridad. El esfuerzo debe concentrarse primero en autenticación, autorización, consultas, archivos subidos y modificaciones de datos. La severidad describe el impacto y la prioridad determina el orden de atención; no siempre coinciden.

También importan la arquitectura, las dependencias y el entorno: versión PHP, extensiones, motor SQL, límites de subida, sesiones, servidor web y navegadores. Una prueba que funciona en SQLite no demuestra compatibilidad con MariaDB; por eso la integración usa el motor real. Una prueba destructiva debe contar con una base separada y una estrategia de limpieza limitada a los datos que crea.

Los datos deben representar clases válidas, inválidas y límites. Se incluyen caracteres españoles, entradas vacías, títulos de 160 y 161 caracteres, búsquedas con comodines, archivos camuflados y estados borrador/publicado. Las transiciones permiten comprobar no solo valores individuales sino cómo cambia el acceso al publicar o retirar una noticia.

La aplicación web añade factores de interacción: teclado, errores comprensibles, conservación de datos, tamaños de pantalla, foco y etiquetas. Las comprobaciones automáticas ayudan a detectar reglas repetibles, pero una revisión visual y un lector de pantalla siguen siendo necesarios para evaluar la experiencia. No se debe confundir presencia de atributos ARIA con accesibilidad completa.

Finalmente, se necesitan tiempo, responsabilidades, criterios de aceptación y trazabilidad de resultados. El plan debe distinguir lo previsto de lo ejecutado, registrar herramientas y comandos, y definir cuándo detener las pruebas si el entorno no es confiable. Rendimiento requiere un escenario y objetivo explícitos: una medición local secuencial no permite concluir capacidad ante usuarios concurrentes. Los materiales de semanas 3 y 4 no se proporcionaron y deben contrastarse antes de entregar.

## 3. Objetivos

Verificar publicación y consulta correctas; impedir acceso o modificaciones no autorizadas; evitar pérdida accidental de noticias; validar datos e imágenes; comprobar mensajes, interacción y adaptación; producir evidencias reproducibles y reconocer verificaciones pendientes.

## 4. Requisitos

| ID | Requisito verificable |
|---|---|
| RF01 | Inicio muestra solo publicadas, orden descendente por fecha e ID. |
| RF02 | Detalle publicado disponible; borrador o inexistente responde 404. |
| RF03 | Búsqueda literal por título y filtro de categoría combinables; mensaje sin resultados. |
| RF04 | Seis noticias por página; enlaces conservan filtros; página fuera de rango se ajusta. |
| RF05 | Login con hash, regeneración de sesión y logout por POST. |
| RF06 | Todas las rutas administrativas requieren sesión autorizada. |
| RF07 | Resumen muestra noticias totales, publicadas y categorías. |
| RF08 | Crear, consultar, editar y eliminar noticias; publicación y fechas coherentes; confirmación. |
| RF09 | Crear, editar, listar y eliminar categorías vacías; duplicados y eliminación con noticias bloqueados. |
| RF10 | Validaciones al crear y editar, espacios Unicode rechazados, reglas visibles, errores por campo y valores conservados. |
| RNF01 | PDO preparado; las entradas no modifican la estructura SQL. |
| RNF02 | Modificaciones solo POST y con token CSRF válido. |
| RNF03 | HTML escapado y contenido de noticia tratado como texto. |
| RNF04 | Imagen real JPEG/PNG/WebP, hasta 2 MiB, nombre aleatorio, alt y ejecución bloqueada. |
| RNF05 | Etiquetas, teclado, salto, foco, errores accesibles, ampliación 200% y controles táctiles; evaluación parcial sin certificación. |
| RNF06 | Diseño sobrio en español, naranja/beige/blanco/café, adaptable a 360/390/768/1440 y estados de error. |
| RNF07 | LIMIT/OFFSET en SQL, imágenes diferidas en listados y escenario local de rendimiento. |
| RNF08 | Credenciales locales excluidas; instalación reproducible; pruebas con base separada. |

## 5. Alcance y exclusiones

Incluye ambos roles, base de datos, validaciones, seguridad solicitada, presentación, teclado, instalación desde ZIP en Apache y rendimiento local. Excluye comentarios, registro público, múltiples roles, recuperación de contraseña, editor HTML, correo, pagos, SEO avanzado y despliegue de producción. No incluye auditoría exhaustiva de accesibilidad, pruebas de penetración ni carga concurrente. El receptor debe repetir la instalación en su equipo.

## 6. Riesgos y prioridades

| Riesgo | Impacto | Prioridad | Mitigación y casos |
|---|---|---|---|
| Acceso a panel o borrador | Alto | P1 | Autorización en servidor, CP03/06/07. |
| Pérdida de noticias | Alto | P1 | Confirmación y FK RESTRICT, CP11/13. |
| CSRF, SQL o XSS | Alto | P1 | Tokens, PDO, escape, CP14/16/17. |
| Archivo ejecutable | Alto | P1 | MIME/extensión/nombre/Apache, CP18–20/24. |
| Datos inválidos o mensajes confusos | Medio | P2 | Límites y conservación, CP12/15. |
| Barreras visuales o de interacción | Medio | P2 | CP21/22 y revisión humana. |
| Pruebas sobre datos de desarrollo | Alto | P1 | Guardas _test, rollback y copia dedicada, CP24. |
| Lista lenta al crecer | Medio | P2 | Índices y paginación SQL, CP23. |

## 7. Estrategia

Unitarias sin dependencias: funciones de validación, escape, CSRF e imágenes, ejecutadas por PHP CLI. Integración: modelo con MariaDB real, transacción y rollback. Sistema: solicitudes HTTP con curl y cookie de sesión sobre una instancia dedicada. Navegador: Edge headless vía CDP, capturas y comprobaciones DOM en dos resoluciones. Revisión manual: apariencia de capturas y recorrido humano pendiente. Automatizar reglas deterministas; reservar juicio de usabilidad, lector de pantalla y coherencia editorial a revisión humana. Ninguna comprobación es un sustituto de todas las capas.

## 8. Técnicas de diseño

- **Particiones de equivalencia:** login correcto/incorrecto; categoría existente/inexistente; imagen raster auténtica/archivo PHP camuflado; noticia publicada/borrador; búsqueda con/sin coincidencias.
- **Valores límite:** título 4/5/160/161; resumen 9/10/300/301; contenido 19/20/20.000/20.001; alt 180/181; imagen 2.097.152/2.097.153 bytes; página primera/última/excesiva.
- **Transiciones de estado:** anónimo → autenticado → sesión cerrada; borrador → publicado → edición publicada → borrador; categoría vacía → con noticias → intento de eliminación bloqueado.

## 9. Entorno, herramientas y datos

Windows, PHP 8.2.12, MariaDB 10.4.32, PDO MySQL, mbstring, fileinfo, curl; Node v26.7.0 y Edge 155.0.4283.45 para la revisión realizada. Servidor PHP en 127.0.0.1:8080; instancia MariaDB temporal en 3307 con datos en storage/mysql, sin modificar XAMPP. Integración: actualidad_huancayo_test; HTTP: actualidad_http_test. Datos iniciales: tres categorías, ocho publicaciones y un borrador ficticios. Copia QA nueva en storage/qa-20261013200403 con base actualidad_revision_20261013200403_test, sin alterar configuraciones ni datos de desarrollo. Cuenta de pruebas admin@actualidad.test; clave pública de demostración DemoHuancayo2026!, solo para entorno local. Producción/desarrollo se instalan por separado según README.

## 10. Responsabilidades

Ramos Ore Dennis Jhordan es el único responsable académico de revisar requisitos, explicar código, repetir pruebas en su instalación, contrastar materiales del curso y preparar la entrega. La documentación registra la asistencia de herramientas para desarrollo y comprobaciones; no se inventan integrantes ni revisores.

## 11. Estimación de esfuerzo

| Actividad | Horas estimadas |
|---|---|
| Requisitos y riesgos | 2 |
| Entorno y datos separados | 2 |
| Diseño de 25 casos y matriz | 3 |
| Automatización de reglas e integración | 4 |
| Flujos de sistema y navegador | 3 |
| Revisión manual, accesibilidad e instalación | 3 |
| Correcciones y regresión | 3 |
| Documentación y empaquetado | 2 |
| Total | 22 |

Estimación del trabajo de pruebas, no medición real ni duración del desarrollo. Supone un autor, equipo Windows con XAMPP, conocimientos básicos PHP, ausencia de servicios externos y disponibilidad del receptor para revisar manualmente. Reservar margen adicional si falla la instalación.

## 12. Cronograma relativo

Jornada 1: requisitos/riesgos/entorno (4 h). Jornada 2: casos y automatización inicial (5 h). Jornada 3: completar automatización y sistema (5 h). Jornada 4: manual e instalación, primeras correcciones (4 h). Jornada 5: regresión, documentación y entrega (4 h). No se inventa fecha de entrega. Ordenar P1 antes de P2 y ajustar según defectos.

## 13. Criterios

Entrada: fuentes completas, PHP/extensiones disponibles, bases separadas, esquema importado y requisitos identificados. Salida: todos los P1 ejecutados y aprobados, sin defectos críticos/altos abiertos, trazabilidad y evidencias; pendientes P2 justificados y aceptados para una entrega provisional. Los P1 están aprobados; permanece CP21 P2 pendiente de revisión manual y aceptación por el autor. Suspensión: uso accidental de base de desarrollo, entorno inaccesible, datos inconsistentes o defecto que impida continuar con seguridad. Reanudación: corregir causa, restaurar exclusivamente datos de prueba, comprobar conexión y repetir casos afectados.

## 14. Gestión de defectos

Registrar DEF-001 y sucesivos con requisito/caso, pasos, datos, esperado/real, entorno, evidencia, severidad, prioridad, responsable y estado. Estados: nuevo → analizado → corregido → pendiente de reprueba → cerrado; reabrir si persiste. Severidad crítica: pérdida general o ejecución de código; alta: acceso indebido/CRUD inutilizable; media: filtro incorrecto o barrera de interacción; baja: detalle visual. P1: bloquea entrega o riesgo de datos; P2: afecta uso normal; P3: mejora menor. No inventar defectos: no se detectaron fallos funcionales en las comprobaciones finales; el problema de conexión inicial de Edge fue del arnés de QA y se corrigió para esta revisión.

## 15. Métricas

Avance = casos ejecutados / casos planificados × 100. Aprobación = casos aprobados / casos ejecutados × 100. Cobertura de requisitos diseñada = requisitos con al menos un caso / requisitos totales × 100. Cobertura ejecutada = requisitos con al menos un caso ejecutado / requisitos totales × 100. Un requisito parcialmente ejercitado no se considera certificado por esa cifra. Cobertura de código mide líneas/ramas ejercitadas mediante instrumentación; no se ha medido.

En esta revisión: 25 casos planificados, 24 aprobados y uno No ejecutado (CP21). Avance 24/25 = 96%; aprobación 24/24 = 100%; cobertura diseñada 18/18 = 100%; cobertura ejecutada 18/18 = 100%. Las comprobaciones automatizadas actuales son 56 unitarias, 15 de integración, 52 HTTP, 226 de navegador y siete de instalación Apache: 356 aserciones. Son particiones y variantes del plan, no 356 casos. Rendimiento y sintaxis se registran aparte. La cobertura mediante algún caso no certifica la accesibilidad completa.

## 16. Evidencias y entregables

Código/SQL/configuración de ejemplo, README, plan y 25 casos, matriz, informe, guion, participantes, registros textuales, capturas reales, versiones imprimibles y ZIP. Los registros están en docs/evidencias; comandos y limitaciones en resultados-de-pruebas.md. Cada ejecución futura debe actualizar resultado real, estado y evidencia sin sobrescribir evidencia histórica sin identificación.

## 17. Casos de prueba

Estados finales corresponden a los escenarios delimitados aquí. «Aprobado» no implica cobertura de variantes no enumeradas. Evidencias de esta revisión en docs/evidencias/revision-2 (unit.txt, integration.txt, http.txt, navegador.txt, pantallas.json, contraste.json y capturas). Los registros de la primera versión se conservan en evidencias, fuera de revision-2. La tabla ejemplos-validacion.md reutiliza las pruebas unitarias y contiene datos y resultados reales.

### CP01 — Inicio y orden

- Requisitos: RF01. Objetivo: mostrar seis publicadas en orden descendente. Prioridad: P1.
- Precondiciones: SQL demo importado, visitante anónimo. Datos: ocho publicaciones y un borrador.
- Pasos: solicitar inicio; contar tarjetas; comparar orden por published_at e ID; consultar página 2.
- Esperado: seis tarjetas en primera página y dos en segunda, ninguna de borrador, orden descendente estable.
- Real: H01/H02 e I03/I10 verificaron tamaño, exclusión y desempate descendente; captura muestra fechas descendentes.
- Estado: Aprobado. Evidencia: http.txt, integration.txt e inicio-1440.png.

### CP02 — Detalle público

- Requisitos: RF02. Objetivo: consultar una noticia publicada. Prioridad: P1.
- Precondiciones: publicación demo ID 1. Datos: id=1.
- Pasos: GET r=detail&id=1; comprobar respuesta y campos.
- Esperado: 200, título, resumen, contenido y fecha.
- Real: H04 respondió 200; estructura revisada en detail.php e integración I04.
- Estado: Aprobado. Evidencia: http.txt e integration.txt.

### CP03 — Borrador privado

- Requisitos: RF01, RF02. Objetivo: impedir descubrimiento y acceso directo a borradores. Prioridad: P1.
- Precondiciones: borrador demo ID 9 y borrador creado durante prueba. Datos: id=9, ID temporal.
- Pasos: buscar/listar; abrir detalle por ID; publicar y volver a borrador.
- Esperado: borrador ausente del listado y 404 en detalle; al publicar disponible; al retirar vuelve a ocultarse.
- Real: H05/H15 e I02/I03/I04/I12 verificaron estas transiciones.
- Estado: Aprobado. Evidencia: http.txt e integration.txt.

### CP04 — Búsqueda y categorías

- Requisitos: RF03. Objetivo: combinar búsqueda por título y categoría sin interpretar comodines del usuario. Prioridad: P2.
- Precondiciones: datos temporales de filtros. Datos: FiltroPrueba, categoría temporal; símbolo %.
- Pasos: buscar título y categoría; enviar %; contrastar cantidades.
- Esperado: solo coincidencias de la categoría; % se busca literalmente.
- Real: H32/H33 e I06/I08 aprobaron.
- Estado: Aprobado. Evidencia: http.txt e integration.txt.

### CP05 — Sin coincidencias

- Requisitos: RF03. Objetivo: informar una búsqueda vacía. Prioridad: P2.
- Precondiciones: blog con datos demo. Datos: nada_imposible.
- Pasos: GET home con q indicado.
- Esperado: 200, cero resultados y sugerencia de otra búsqueda.
- Real: H03 encontró «No hay noticias».
- Estado: Aprobado. Evidencia: http.txt.

### CP06 — Login, contraseña y sesión

- Requisitos: RF05. Objetivo: rechazar credenciales falsas y regenerar sesión al ingresar. Prioridad: P1.
- Precondiciones: administrador de prueba con hash; navegador/curl con cookies. Datos: correo demo, clave incorrecta y clave demo.
- Pasos: enviar clave incorrecta; comprobar mensaje; enviar correcta con token; comparar cookie antes/después.
- Esperado: mensaje genérico al fallar; redirect al panel al acertar; ID distinto; password_verify valida el hash.
- Real: H08/H09/H10 y U19 aprobaron; «Login navegador» aprobó en el arnés actualizado.
- Estado: Aprobado. Evidencia: http.txt, unit.txt y navegador.txt.

### CP07 — Autorización administrativa

- Requisitos: RF06. Objetivo: proteger todas las rutas del panel. Prioridad: P1.
- Precondiciones: sin sesión. Datos: dashboard, news, news-edit, news-delete, categories, category-edit, category-delete, logout.
- Pasos: solicitar cada ruta con id=1 cuando corresponda.
- Esperado: redirección al login sin consultar ni modificar información administrativa.
- Real: las ocho variantes H06 devolvieron 302; se revisó la guarda común antes del despacho.
- Estado: Aprobado. Evidencia: http.txt y Controller.php.

### CP08 — Cerrar sesión

- Requisitos: RF05, RNF02. Objetivo: cerrar con POST y bloquear acceso posterior. Prioridad: P1.
- Precondiciones: login correcto, token actual. Datos: GET y POST logout.
- Pasos: intentar GET; enviar POST con token; solicitar panel.
- Esperado: GET 405, POST 302, panel vuelve al login.
- Real: H29/H30/H31 aprobaron.
- Estado: Aprobado. Evidencia: http.txt.

### CP09 — Resumen administrativo

- Requisitos: RF07. Objetivo: mostrar cantidades tomadas de BD. Prioridad: P2.
- Precondiciones: sesión y SQL demo. Datos: nueve noticias, ocho publicadas, tres categorías.
- Pasos: abrir panel; comparar las tres tarjetas con los datos iniciales.
- Esperado: cantidades 9, 8 y 3.
- Real: H11 y «Login navegador» abrieron panel; captura panel-768.png muestra 9/8/3, revisada visualmente.
- Estado: Aprobado. Evidencia: panel-768.png, http.txt y navegador.txt.

### CP10 — Crear, editar y publicar

- Requisitos: RF08. Objetivo: CRUD de noticia y fechas de publicación. Prioridad: P1.
- Precondiciones: sesión, categoría válida. Datos: título único, resumen/contenido válidos, draft/published.
- Pasos: crear borrador; consultar; editar y publicar; editar otra vez; volver a borrador.
- Esperado: valores guardados; publicar asigna fecha; edición publicada conserva fecha; retirar limpia fecha y oculta.
- Real: H14/H16 e I01/I04/I05/I12 aprobaron.
- Estado: Aprobado. Evidencia: http.txt e integration.txt.

### CP11 — Confirmación y eliminación de noticia

- Requisitos: RF08, RNF02. Objetivo: evitar borrado por GET. Prioridad: P1.
- Precondiciones: noticia temporal, sesión/token. Datos: ID temporal.
- Pasos: GET news-delete; comprobar que existe; enviar POST con token; consultar BD.
- Esperado: GET presenta confirmación sin borrar; POST elimina solo el ID elegido.
- Real: H21/H23/H39 e I13 aprobaron; cancelar devuelve al listado y conserva la noticia. El navegador activó Cancelar con Enter en las cuatro anchuras sin enviar eliminación.
- Estado: Aprobado. Evidencia: http.txt e integration.txt.

### CP12 — Categorías y duplicados

- Requisitos: RF09, RF10. Objetivo: crear, editar y eliminar categoría vacía; rechazar duplicado. Prioridad: P1.
- Precondiciones: sesión/token. Datos: nombre temporal único y repetido.
- Pasos: crear; repetir nombre; editar; eliminar vacía.
- Esperado: altas/edición válidas; error de duplicado; eliminación sin pérdida de noticias.
- Real: H25/H26/H27/H28/H40/H41/H42 e I14 aprobaron; cancelar conserva la categoría y el nombre inválido no modifica el registro.
- Estado: Aprobado. Evidencia: http.txt e integration.txt.

### CP13 — Categoría con noticias

- Requisitos: RF09. Objetivo: preservar noticias al intentar eliminar categoría usada. Prioridad: P1.
- Precondiciones: categoría 1 y noticias relacionadas. Datos: id=1.
- Pasos: POST category-delete con token; comprobar respuesta y FK.
- Esperado: rechazo y explicación de reasignar; registros conservados.
- Real: H24 devolvió 400 y mensaje; I11 verificó restricción SQLSTATE 23000.
- Estado: Aprobado. Evidencia: http.txt e integration.txt.

### CP14 — CSRF y métodos

- Requisitos: RNF02. Objetivo: rechazar modificaciones sin token. Prioridad: P1.
- Precondiciones: cookie anónima y después sesión válida. Datos: login, news-edit y news-delete sin token; token erróneo.
- Pasos: POST sin token en rutas indicadas; probar validCsrf con valor falso; GET logout.
- Esperado: 403 para POST sin token/falso; 405 para GET logout; ningún cambio.
- Real: H07/H13/H22/H29 y U11/U12 aprobaron; controles comunes revisados para categorías.
- Estado: Aprobado. Evidencia: http.txt, unit.txt y Controller.php.

### CP15 — Límites y conservación

- Requisitos: RF10, RNF05. Objetivo: comprobar límites de textos, categoría/estado y errores por campo. Prioridad: P1.
- Precondiciones: sesión, formulario nuevo. Datos: título 4/5/160/161; resumen 9/10/300/301; contenido 19/20/20.000/20.001; categoría 0, estado invalid; formulario breve.
- Pasos: ejecutar particiones unitarias; enviar formulario breve por HTTP/navegador; observar valores y errores.
- Esperado: aceptar límites inclusivos, rechazar exteriores; conservar textos y relacionar errores con campos.
- Real: Las filas «Límite», «Vacío», «Estado» y «Categoría» de unit.txt aprobaron. H12/H35–H38/H40/H42 verificaron validación al crear/editar incluso sin navegador. I15 conservó íntegros 20.000 caracteres de cuatro bytes. El navegador verificó resumen, enlaces de error, foco y textos conservados en cuatro anchos.
- Estado: Aprobado. Evidencia: unit.txt, http.txt, navegador.txt y errores-news-360.png.

### CP16 — Inyección SQL

- Requisitos: RNF01, RF03. Objetivo: tratar entrada de búsqueda como dato. Prioridad: P1.
- Precondiciones: categoría temporal de integración. Datos: ' OR 1=1 --.
- Pasos: buscar cadena de ataque; comprobar que no devuelve todas las noticias; revisar consultas preparadas.
- Esperado: cero coincidencias en categoría de prueba, sin error SQL ni ampliación de selección.
- Real: I07 aprobó; Blog utiliza prepare y parámetros enlazados.
- Estado: Aprobado. Evidencia: integration.txt y Blog.php.

### CP17 — XSS almacenado

- Requisitos: RNF03, RF08. Objetivo: presentar contenido HTML como texto. Prioridad: P1.
- Precondiciones: sesión, noticia temporal. Datos: <script>alert("xss")</script> en contenido.
- Pasos: guardar y publicar; consultar HTML público; buscar etiquetas escapadas y ausencia de script inyectado.
- Esperado: &lt;script&gt; en respuesta; ninguna etiqueta ejecutable del contenido.
- Real: H17 y U09 aprobaron; no se automatizó un detector de alertas, se verificó la respuesta escapada.
- Estado: Aprobado. Evidencia: http.txt y unit.txt.

### CP18 — Imagen y texto alternativo

- Requisitos: RNF04, RNF05. Objetivo: cargar PNG real y exigir descripción. Prioridad: P1.
- Precondiciones: sesión, noticia temporal. Datos: PNG de 1×1, alt vacío y «Punto claro de prueba»; alt 180/181.
- Pasos: subir sin alt; repetir con alt; revisar nombre y archivo; probar límites unitarios.
- Esperado: error sin alt; archivo válido con nombre hexadecimal aleatorio; límite 180 inclusivo.
- Real: H18a/H18/H19 y U06–U08/U17 aprobaron.
- Estado: Aprobado. Evidencia: http.txt y unit.txt. JPEG/WebP visuales siguen pendientes como variantes adicionales.

### CP19 — Imagen falsa o ejecutable

- Requisitos: RNF04. Objetivo: rechazar contenido camuflado y extensión ejecutable. Prioridad: P1.
- Precondiciones: sesión, noticia temporal. Datos: PHP con nombre fake.jpg y PNG auténtico con nombre foto.php.
- Pasos: subir ambas variantes; comprobar error y ausencia de guardado.
- Esperado: rechazo de ambas por MIME/extensión; ningún archivo ejecutable creado.
- Real: H20/H20a y U16/U18 aprobaron. La prohibición de ejecución efectiva en Apache se comprobó en CP24.
- Estado: Aprobado. Evidencia: http.txt y unit.txt.

### CP20 — Tamaño de imagen

- Requisitos: RNF04. Objetivo: comprobar límite 2 MiB. Prioridad: P1.
- Precondiciones: función de validación disponible y sesión HTTP. Datos: PNG auténtico rellenado hasta 2.097.152 bytes; archivo de 2.097.153 bytes.
- Pasos: validar límite exacto; enviar archivo excesivo con límites PHP mayores.
- Esperado: límite exacto aceptado por validador; exceso rechazado por tamaño con mensaje de campo.
- Real: U20 y H20b aprobaron; U15 rechazó exceso y «Imagen máximo menos 1 byte» aprobó. También se rechazaron cero bytes. No se verificó calidad visual de PNG rellenado.
- Estado: Aprobado. Evidencia: unit.txt y http.txt.

### CP21 — Accesibilidad manual completa

- Requisitos: RNF05. Objetivo: completar la revisión con lector de pantalla, zoom nativo y todos los estados de contraste. Prioridad: P2.
- Precondiciones: navegador y lector de pantalla (por ejemplo NVDA), cuenta de prueba. Datos: todas las rutas y estados.
- Pasos: recorrer por teclado, escuchar etiquetas/errores, ampliar con Ctrl+ hasta 200%, medir contraste de foco/hover/errores y contenido con imágenes.
- Esperado: lectura comprensible, sin trampas de foco ni pérdida de funcionalidad; documentar barreras reales.
- Real: se recorrieron con Tab/Shift+Tab las 12 pantallas a 390/1440, se activó salto/Cancelar/enlaces de error con Enter y se midieron ocho muestras de contraste. CP25 registra ampliación automatizada. No se ejecutó lector de pantalla, zoom nativo manual ni auditoría de todos los estados.
- Estado: No ejecutado. Evidencia parcial: revision-2/navegador.txt, contraste.json y capturas. Evidencia manual completa pendiente; no se afirma conformidad WCAG.

### CP22 — Diseño y funcionamiento en tamaños móviles

- Requisitos: RNF06. Objetivo: revisar todas las pantallas y mensajes en 360, 390, 768 y 1440 píxeles. Prioridad: P2.
- Precondiciones: Edge con copia QA y SQL demo. Datos: inicio, detalle, login, panel, listados, crear/editar noticia/categoría y confirmaciones.
- Pasos: abrir 12 pantallas en cada ancho; enviar formularios inválidos de noticias/categorías; comprobar límites de cajas, superposiciones, controles, etiquetas y títulos; revisar capturas.
- Esperado: sin desbordamiento horizontal ni controles superpuestos/cortados; campos y acciones utilizables y textos comprensibles.
- Real: 48 pantallas normales y ocho de error verificadas con DOM y capturas. Los diseños, etiquetas y semántica aprobaron; la paleta sobria se revisó visualmente. No se utilizó teléfono físico.
- Estado: Aprobado. Evidencia: revision-2/navegador.txt, pantallas.json y 12 PNG; el caso cubre tamaños de navegador emulados, no todos los dispositivos.

### CP23 — Paginación y rendimiento local

- Requisitos: RNF07, RF04. Objetivo: conservar filtros, limitar filas y medir respuesta con volumen moderado. Prioridad: P2.
- Precondiciones: base HTTP dedicada, servidor PHP local; no otras pruebas simultáneas. Datos: siete noticias para filtros y 1.000 adicionales para rendimiento, página 100.
- Pasos: recorrer dos páginas con filtros; solicitar página excesiva; ejecutar un calentamiento y 30 HTTP secuenciales; calcular P95 por rango más próximo.
- Esperado: 6/1 tarjetas, filtros conservados, página ajustada; consulta usa LIMIT/OFFSET; P95 local <500 ms como presupuesto educativo para interacción sencilla sin red externa.
- Real: H32/H33/H34 e I09 aprobaron. Mediana 6,457 ms, P95 31,011 ms, máximo 31,175 ms. Objetivo alcanzado en este escenario.
- Estado: Aprobado. Evidencia: http.txt, integration.txt y performance.txt. Sin conclusión sobre concurrencia o producción.

### CP24 — Instalación, aislamiento y Apache

- Requisitos: RNF08, RNF04. Objetivo: instalar desde ZIP y comprobar exclusiones y bloqueo de ejecución de subidas con Apache. Prioridad: P1.
- Precondiciones: instalación nueva XAMPP, ZIP, credenciales locales propias; copia dedicada de pruebas.
- Datos: BD actualidad_huancayo, BD *_test, archivo de prueba no ejecutable en uploads.
- Pasos: inspeccionar ZIP; instalar según README; crear administrador; recorrer blog en Apache; verificar que HTTP no accede a configuración y no ejecuta archivo con extensión PHP; intentar iniciar integración con DSN no _test en copia dedicada y comprobar negativa sin modificar datos.
- Esperado: instalación reproducible, solo public como raíz, configuraciones excluidas, acceso a scripts/credenciales imposible, pruebas rechazan desarrollo.
- Real: A01–A07 aprobaron instalación en una copia extraída, importación SQL, creación CLI del administrador, rechazo de DSN de desarrollo, inicio/login en Apache, rutas privadas 403/404 y ejecución PHP en uploads denegada con 403. Apache 2.4.58 y base exclusiva actualidad_install5_test.
- Estado: Aprobado. Evidencia: install.txt, manifest.txt y resultados-de-pruebas.md. Configuración Apache temporal dentro de storage, sin modificar XAMPP.
### CP25 — Ampliación de contenido y controles táctiles

- Requisitos: RNF05, RNF06. Objetivo: verificar adaptación con contenido al 200% y tamaño útil de controles. Prioridad: P2.
- Precondiciones: Edge/CDP, copia QA y sesión. Datos: fuente raíz al 200% en formulario de noticia; viewport CSS 720 con salida escalada a 1440 para 11 pantallas; controles incluidos en las otras anchuras.
- Pasos: duplicar fuente raíz y comparar tamaño calculado del campo título; comprobar ausencia de desbordamiento; repetir layouts a escala 2; revisar altura y superposición de controles; activar Cancelar y enlaces de error con Enter.
- Esperado: fuente de controles duplicada, campos/acciones dentro del viewport y sin superposición; altura mínima 44px para controles examinados.
- Real: el tamaño calculado se duplicó; 11 variantes de layout equivalente al zoom 200% aprobaron, junto con el formulario con texto duplicado. Las capturas muestran campos y acciones completos. Se verificaron controles principales, navegación, enlaces de tarjetas, paginación y errores.
- Estado: Aprobado en el alcance automatizado. Evidencia: revision-2/navegador.txt, pantallas.json, texto-200-formulario.png y zoom-200-formulario.png. No se accionó Ctrl+ nativo ni se usó un teléfono; esa revisión humana permanece en CP21.
