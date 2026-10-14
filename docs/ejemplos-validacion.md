# Ejemplos ejecutados de datos válidos, inválidos y límites

Reglas reales de support.php. mb_strlen cuenta caracteres Unicode; se excluyen espacios exteriores. Estas filas reutilizan las pruebas unitarias; no son casos adicionales del plan ni pruebas de navegador. Un nombre válido sintácticamente todavía debe ser único y la categoría de una noticia debe existir, comprobados por HTTP.

| Comprobación | Campo | Regla | Dato de prueba | Esperado | Resultado real | Estado |
|---|---|---|---|---|---|---|
| Límite title 4 | title | 5 a 160 caracteres sin espacios exteriores | á repetida 4 veces | Rechazar | Error del campo verificado | Aprobado |
| Límite title 5 | title | 5 a 160 caracteres sin espacios exteriores | á repetida 5 veces | Aceptar | Aceptado por el validador | Aprobado |
| Límite title 160 | title | 5 a 160 caracteres sin espacios exteriores | á repetida 160 veces | Aceptar | Aceptado por el validador | Aprobado |
| Límite title 161 | title | 5 a 160 caracteres sin espacios exteriores | á repetida 161 veces | Rechazar | Error del campo verificado | Aprobado |
| Vacío title vacío | title | Obligatorio; no solo espacios | vacío | Rechazar | Error del campo verificado | Aprobado |
| Vacío title solo espacios | title | Obligatorio; no solo espacios | solo espacios | Rechazar | Error del campo verificado | Aprobado |
| Vacío title espacios Unicode | title | Obligatorio; no solo espacios | espacios Unicode | Rechazar | Error del campo verificado | Aprobado |
| Límite summary 9 | summary | 10 a 300 caracteres sin espacios exteriores | á repetida 9 veces | Rechazar | Error del campo verificado | Aprobado |
| Límite summary 10 | summary | 10 a 300 caracteres sin espacios exteriores | á repetida 10 veces | Aceptar | Aceptado por el validador | Aprobado |
| Límite summary 300 | summary | 10 a 300 caracteres sin espacios exteriores | á repetida 300 veces | Aceptar | Aceptado por el validador | Aprobado |
| Límite summary 301 | summary | 10 a 300 caracteres sin espacios exteriores | á repetida 301 veces | Rechazar | Error del campo verificado | Aprobado |
| Vacío summary vacío | summary | Obligatorio; no solo espacios | vacío | Rechazar | Error del campo verificado | Aprobado |
| Vacío summary solo espacios | summary | Obligatorio; no solo espacios | solo espacios | Rechazar | Error del campo verificado | Aprobado |
| Vacío summary espacios Unicode | summary | Obligatorio; no solo espacios | espacios Unicode | Rechazar | Error del campo verificado | Aprobado |
| Límite content 19 | content | 20 a 20000 caracteres sin espacios exteriores | á repetida 19 veces | Rechazar | Error del campo verificado | Aprobado |
| Límite content 20 | content | 20 a 20000 caracteres sin espacios exteriores | á repetida 20 veces | Aceptar | Aceptado por el validador | Aprobado |
| Límite content 20000 | content | 20 a 20000 caracteres sin espacios exteriores | á repetida 20000 veces | Aceptar | Aceptado por el validador | Aprobado |
| Límite content 20001 | content | 20 a 20000 caracteres sin espacios exteriores | á repetida 20001 veces | Rechazar | Error del campo verificado | Aprobado |
| Vacío content vacío | content | Obligatorio; no solo espacios | vacío | Rechazar | Error del campo verificado | Aprobado |
| Vacío content solo espacios | content | Obligatorio; no solo espacios | solo espacios | Rechazar | Error del campo verificado | Aprobado |
| Vacío content espacios Unicode | content | Obligatorio; no solo espacios | espacios Unicode | Rechazar | Error del campo verificado | Aprobado |
| Categoría nombre 0 | name | 2 a 60 caracteres, sin espacios exteriores | Cadena vacía | Rechazar | Error del campo verificado | Aprobado |
| Categoría nombre 1 | name | 2 a 60 caracteres, sin espacios exteriores | n repetida 1 veces | Rechazar | Error del campo verificado | Aprobado |
| Categoría nombre 2 | name | 2 a 60 caracteres, sin espacios exteriores | n repetida 2 veces | Aceptar | Aceptado por el validador | Aprobado |
| Categoría nombre 60 | name | 2 a 60 caracteres, sin espacios exteriores | n repetida 60 veces | Aceptar | Aceptado por el validador | Aprobado |
| Categoría nombre 61 | name | 2 a 60 caracteres, sin espacios exteriores | n repetida 61 veces | Rechazar | Error del campo verificado | Aprobado |
| Nombre solo espacios ASCII | name | No solo espacios | Espacios ASCII | Rechazar | Error del campo verificado | Aprobado |
| Nombre solo espacios Unicode | name | No solo espacios | Espacios Unicode | Rechazar | Error del campo verificado | Aprobado |
| Estado draft | status | Solo draft o published | draft | Aceptar | Aceptado por el validador | Aprobado |
| Estado published | status | Solo draft o published | published | Aceptar | Aceptado por el validador | Aprobado |
| Estado invalid | status | Solo draft o published | invalid | Rechazar | Error del campo verificado | Aprobado |
| Estado vacío | status | Solo draft o published | Vacío | Rechazar | Error del campo verificado | Aprobado |
| Categoría identificador 1 | category_id | Entero positivo; existencia comprobada por HTTP | 1 | Aceptar formato | Formato aceptado; existencia requiere BD | Aprobado |
| Categoría identificador 0 | category_id | Entero positivo; existencia comprobada por HTTP | 0 | Rechazar | Error del campo verificado | Aprobado |
| Categoría identificador abc | category_id | Entero positivo; existencia comprobada por HTTP | abc | Rechazar | Error del campo verificado | Aprobado |
| U06 alt obligatorio con imagen | image_alt | 1 a 180 si hay imagen | Vacío con imagen | Rechazar | Error del campo verificado | Aprobado |
| Alt mínimo 1 | image_alt | 1 a 180 si hay imagen | a | Aceptar | Aceptado por el validador | Aprobado |
| U07 alt límite 180 | image_alt | 1 a 180 si hay imagen | a repetida 180 veces | Aceptar | Aceptado por el validador | Aprobado |
| U08 alt 181 | image_alt | 1 a 180 si hay imagen | a repetida 181 veces | Rechazar | Error del campo verificado | Aprobado |
| U13 sin imagen | image | Opcional | Sin archivo | Aceptar | Aceptado por el validador | Aprobado |
| Imagen cero bytes | image | Archivo recibido no vacío | 0 bytes | Rechazar | Error de tamaño verificado | Aprobado |
| U15 tamaño excesivo | image | Máximo 2.097.152 bytes | 2.097.153 bytes | Rechazar | Error de tamaño verificado | Aprobado |
| U16 PHP camuflado | image | Extensión y MIME raster reales | PHP renombrado foto.jpg | Rechazar | Error de tipo verificado | Aprobado |
| U20 imagen exactamente 2 MiB | image | Máximo 2.097.152 bytes | PNG real rellenado hasta 2.097.152 bytes | Aceptar | Aceptado por el validador | Aprobado |
| Imagen máximo menos 1 byte | image | Máximo 2.097.152 bytes | PNG real rellenado hasta 2.097.151 bytes | Aceptar | Aceptado por el validador | Aprobado |

Evidencia: docs/evidencias/revision-2/unit.txt. Comando: php tests/unit.php --examples. Revisión de servidor al crear/editar: H35–H42 en http.txt. Existencia/estado, duplicados e imágenes HTTP están descritos en el plan. Variantes no ejecutadas: visualización JPEG/WebP y teléfono físico.
