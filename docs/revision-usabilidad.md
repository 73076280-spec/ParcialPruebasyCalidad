# Revisión de CRUD, validación y presentación

Autor: Ramos Ore Dennis Jhordan · Universidad Continental · Pruebas y Calidad de Software.

La revisión parte del proyecto existente. Se mantienen rutas, MVC, PDO, sesiones, CSRF, publicación/borradores y datos. No cambia contraseñas ni configuración local. Se amplió únicamente el tipo de news.content a MEDIUMTEXT, porque 20.000 caracteres de cuatro bytes no cabían en TEXT. La migración no altera filas; se comprobó el máximo con integración y las huellas de datos/credenciales coinciden antes/después.

## Medidas vinculadas a necesidades

| Necesidad | Medida aplicada | Verificación y límite |
|---|---|---|
| Visual: leer texto y reconocer estados | Paleta sobria, contraste de texto, «Error:» explícito, borrador/publicado por texto, instrucciones visibles y alt de imágenes | Ocho muestras de contraste entre 7,04:1 y 12,66:1; no auditoría de todos los estados. |
| Visual: ampliar contenido | Unidades rem, cajas flexibles, salto de línea, formularios sin ancho fijo mínimo | Fuente raíz al 200% en formulario y viewport/layout equivalente a zoom de página en 11 pantallas. Ctrl+ y lector de pantalla pendientes. |
| Motora: usar teclado o tocar controles | Tab/Shift+Tab en orden DOM, foco visible, enlace de salto, acciones de mínimo 44px y botones que se envuelven | Recorridos en 12 pantallas a 390/1440; cancelación y enlaces de errores activados con Enter. No prueba con hardware adaptado. |
| Cognitiva: entender qué hacer | Crear/Guardar cambios/Cancelar claramente separados, reglas por campo, resumen de errores enlazado, conservación de valores y mensajes específicos | HTTP al crear/editar y pantallas de error. No prueba con usuarios participantes. |
| Auditiva: acceder a información sin sonido | Toda información es textual/visual; el blog no incorpora audio ni video informativo | Revisión del contenido y DOM; subtítulos/transcripciones no aplican a esta versión. Si se añaden medios, deben revisarse. |

No se añadió un panel decorativo de accesibilidad. No se afirma conformidad total con WCAG. Las muestras de contraste se calculan a partir de los estilos efectivos de Edge; no sustituyen revisión completa con lector de pantalla o pruebas humanas.

## Cambios concretos

- app/support.php: reglas compartidas, normalización de espacios Unicode, validación de categorías y rechazo de entradas estructuradas.
- Controller.php: valida creando y editando, normaliza solo al guardar, mensajes específicos y consulta previa de noticias asociadas antes de confirmar categoría.
- Views/errors.php: resumen accesible con enlaces; app.js lo enfoca. Instrucciones y errores enlazados con aria-describedby, aria-invalid solo cuando corresponde.
- Formularios y listados: mínimos/máximos coherentes, valores actuales al editar, selección inválida preservada para corregir y acciones con nombre contextual.
- CSS: tipografía de sistema, color sobrio, bordes discretos, sin sombras/animaciones, columnas y botones adaptables. Confirmar/Cancelar siguen funcionando sin JavaScript.
- Esquema/migración: MEDIUMTEXT conserva la regla de 20.000 caracteres Unicode.
- Pruebas: se extienden los scripts existentes, sin framework. Copia QA separada, registro de preservación y tabla de particiones/límites generada desde unitarias.

## Evidencia real y límites

La revisión obtuvo 56 comprobaciones unitarias, 15 de integración, 52 HTTP y 226 de navegador aprobadas. La revisión del navegador cubre 68 variantes de pantalla: 48 pantallas normales (12×4 anchos), ocho de errores, una ampliación de texto y 11 con layout equivalente al zoom 200%. Son comprobaciones DOM/teclado y capturas emuladas; no un teléfono real.

Fuente raíz duplicada: prueba de ampliación de texto efectiva, porque el tamaño calculado de los controles se duplicó. Layout equivalente: ancho CSS 720 y escala de salida 2, para representar 1440 píxeles a 200%; no se accionó el control de zoom nativo del navegador. Se mantienen pendientes Ctrl+ manual, teléfono físico, lector de pantalla y contraste de todos los estados.

Referencias oficiales: [W3C WAI: ampliación de texto](https://www.w3.org/WAI/WCAG22/Understanding/resize-text.html), [DevTools Protocol: emulación](https://chromedevtools.github.io/devtools-protocol/tot/Emulation/). Las referencias guían la revisión; no constituyen certificación.
