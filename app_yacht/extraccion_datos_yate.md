# Flujo de extracción de datos de yate y renderizado con `default-template`

Este documento describe paso a paso cómo la aplicación obtiene la información de un yate a partir de una URL y la muestra mediante la plantilla por defecto. El proceso cubre desde la captura de la URL en la interfaz hasta la inserción del HTML final en el frontend.

## 1. Captura de la URL y disparo del evento
- El formulario del módulo de cálculo contiene un campo para la URL del yate (`#yachtUrl`) y el botón **Create Template** (`#createTemplateButton`). Ambos elementos están en `calculator.php`【F:app_yacht/modules/calc/calculator.php†L172-L180】.
- Un script de `template.js` habilita o deshabilita el botón según si la URL es válida y agrega un listener para invocar `templateManager.createTemplate()` al hacer clic【F:app_yacht/modules/template/js/template.js†L270-L276】【F:app_yacht/modules/template/js/template.js†L385-L387】.

## 2. Recolección y envío de datos desde el frontend
- `TemplateManager.collectFormData()` reúne los campos del formulario y añade la URL del yate al objeto `FormData`【F:app_yacht/shared/js/classes/TemplateManager.js†L52-L65】.
- `TemplateManager.createTemplate()` valida la información y realiza una petición `fetch` `POST` a la URL AJAX definida, enviando el `FormData` construido【F:app_yacht/shared/js/classes/TemplateManager.js†L212-L260】.

## 3. Procesamiento en el backend y scraping de la información del yate
- `handle_create_template()` recibe la solicitud AJAX, verifica nonce y permisos, y obtiene la URL del yate de `$_POST['yachtUrl']`【F:app_yacht/modules/template/php/load-template.php†L43-L70】.
- La función `extraerInformacionYate()` realiza el scraping: usa cURL para descargar el HTML, lo procesa con `DOMXPath` y extrae nombre, eslora, tipo, constructor, año de construcción, tripulación, cabinas, huéspedes, configuración de cabinas e imagen principal. Retorna estos datos en un arreglo asociativo【F:app_yacht/modules/template/php/load-template.php†L179-L255】.

## 4. Preparación de datos y renderizado de la plantilla
- Con los resultados de cálculo y la información del yate, el backend compone `$templateData` y carga `default-template.php` para generar el HTML final【F:app_yacht/modules/template/php/load-template.php†L146-L174】.
- `default-template.php` transforma la información del yate mediante `buildYachtInfoArray()` para estructurarla y luego imprime los campos clave como eslora, tipo, constructor, año, tripulación, cabinas, huéspedes y configuración de cabinas【F:app_yacht/modules/template/templates/default-template.php†L29-L72】【F:app_yacht/modules/template/templates/default-template.php†L125-L138】.

## 5. Inserción del HTML en el frontend
- Tras recibir la respuesta JSON exitosa, `TemplateManager.createTemplate()` reemplaza el contenido del contenedor `#result` con el HTML generado, mostrando la plantilla con los datos del yate en pantalla【F:app_yacht/shared/js/classes/TemplateManager.js†L266-L270】.

Este flujo asegura que, al ingresar una URL válida y presionar **Create Template**, la aplicación extrae la información del yate, la combina con los cálculos y la muestra mediante la plantilla por defecto en el frontend.

# Guía para Generar un Contenedor de Información de Yate en el Frontend

Esta guía describe paso a paso cómo agregar un contenedor minimalista al inicio del formulario en el frontend, utilizando `app_yacht\modules\yachtinfo\yacht-info-service.php` para extraer y presentar la información del yate de manera horizontal. El contenedor ocupará el ancho total del formulario, incluirá una imagen miniatura del yate a la izquierda y la información extraída a la derecha.

## Paso 1: Modificar el Formulario en calculator.php
- Abre `app_yacht\modules\calc\calculator.php`.
- Agrega un div contenedor al inicio del formulario con ID `yacht-info-container` para mostrar la información extraída.
- Asegúrate de que ocupe el ancho total (clase `row` o similar).

## Paso 2: Actualizar la Lógica de Extracción en yacht-info-service.php
- Verifica y ajusta `yacht-info-service.php` para que extraiga todos los datos necesarios (nombre, eslora, tipo, etc.) y los retorne en un formato JSON accesible.

## Paso 3: Implementar Lógica en JavaScript para Cargar Datos
- En `TemplateManager.js` o un script similar, agrega una función que se dispare al ingresar la URL en `#yachtUrl` (usando evento `input` o `change`).
- Realiza una llamada AJAX a `yacht-info-service.php` pasando la URL.
- Al recibir los datos, renderiza el contenedor con la imagen miniatura a la izquierda y la info en formato horizontal a la derecha.

## Paso 4: Estilizar el Contenedor
- Agrega CSS en un archivo compartido para que el contenedor sea minimalista: flexbox horizontal, imagen con tamaño fijo (e.g., 100x100px), y texto alineado a la derecha.

Una vez implementado este frontend, confirma si está correcto antes de proceder a la recolección y almacenamiento de datos en la app.
