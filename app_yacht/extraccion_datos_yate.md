# Flujo de extracción de datos de yate y renderizado con `default-template`

Este documento describe paso a paso cómo la aplicación obtiene la información de un yate a partir de una URL y la muestra mediante la plantilla por defecto. El proceso cubre desde la captura de la URL en la interfaz hasta la inserción del HTML final en el frontend.

## 1. Captura de la URL y disparo del evento
- El formulario del módulo de cálculo incluye un campo específico para la URL del yate (`#yachtUrl`) y el botón **Create Template** (`#createTemplateButton`). El campo está definido como `type="url"` con un `pattern` que obliga a iniciar con `http` o `https`, evitando entradas sin protocolo【F:app_yacht/modules/calc/calculator.php†L172-L176】.
- `template.js` valida esa entrada en tiempo real: habilita o deshabilita el botón según una expresión regular con protocolo y registra un listener para invocar `templateManager.createTemplate()` al hacer clic【F:app_yacht/modules/template/js/template.js†L270-L276】【F:app_yacht/modules/template/js/template.js†L385-L387】.

## 2. Recolección y envío de datos desde el frontend
- `TemplateManager.collectFormData()` reúne los campos del formulario y añade la URL del yate al objeto `FormData`【F:app_yacht/shared/js/classes/TemplateManager.js†L52-L65】.
- `TemplateManager.createTemplate()` valida la información y realiza una petición `fetch` `POST` a la URL AJAX definida, enviando el `FormData` construido【F:app_yacht/shared/js/classes/TemplateManager.js†L212-L260】.

## 3. Procesamiento en el backend y scraping de la información del yate
- `AppYachtBootstrap::handleCreateTemplate()` atiende la solicitud AJAX: obtiene el servicio de renderizado, recupera la URL del yate de `$_POST` y delega la extracción de datos al módulo `YachtInfoService`【F:app_yacht/core/bootstrap.php†L187-L201】.
- `YachtInfoService::extractYachtInfo()` valida la URL, comprueba dominios permitidos, utiliza caché y ejecuta el scraping para devolver un arreglo con los datos principales del yate【F:app_yacht/modules/yachtinfo/yacht-info-service.php†L36-L70】.

## 4. Preparación de datos y renderizado de la plantilla
- `RenderEngine::createTemplate()` combina los datos del formulario con la información del yate recibida para generar el contenido HTML y de texto de la plantilla seleccionada【F:app_yacht/modules/render/render-engine.php†L128-L160】.
- `default-template.php` utiliza `buildYachtInfoArray()` para estructurar la información del yate y luego imprime campos como eslora, tipo, constructor, año, tripulación, cabinas y huéspedes【F:app_yacht/modules/template/templates/default-template.php†L29-L72】【F:app_yacht/modules/template/templates/default-template.php†L136-L138】.

## 5. Inserción del HTML en el frontend
- Tras recibir la respuesta JSON exitosa, `TemplateManager.createTemplate()` reemplaza el contenido del contenedor `#result` con el HTML generado, mostrando la plantilla con los datos del yate en pantalla【F:app_yacht/shared/js/classes/TemplateManager.js†L266-L270】.

Este flujo asegura que, al ingresar una URL válida y presionar **Create Template**, la aplicación extrae la información del yate, la combina con los cálculos y la muestra mediante la plantilla por defecto en el frontend.
