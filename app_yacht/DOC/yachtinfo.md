## Observations

- The flow described in this document appears to be based on a previous or legacy version of the code. In the current code, AJAX handling for 'createTemplate' is done in `bootstrap.php` via `handleCreateTemplate()`, which uses `YachtInfoService` to extract yacht data if `yachtUrl` is provided, and then invokes `RenderEngine::createTemplate()`.
- No handler was found for the AJAX action 'extract_yacht_info' mentioned in `interfaz.js`. This could be a pending endpoint or not implemented in the current repository.
- The actual extraction in `YachtInfoService` includes domain validation, caching, and specific parsers for sites like charterworld.com, yachtcharterfleet.com, and burgessyachts.com, using wp_remote_get and DOMXPath.
- The frontend in `interfaz.js` attempts a preview on blur of #yachtUrl, but without a backend handler, this would not work as described.

# Yacht URL Data Extraction Flow

This document summarizes how the application retrieves yacht information from a provided URL and renders it in templates.

## Key Components

- **template/php/template-data.php** – helpers `buildYachtInfoArray()` and `buildCalcSnippetArray()` structure yacht details and calculation snippets for templates.
- **shared/js/classes/TemplateManager.js** – collects form data, including the yacht URL, and sends it to the backend via `createTemplate` using `fetch`.
- **modules/template/js/template.js** – UI logic that gathers user inputs, initializes `TemplateManager`, and triggers template creation.
- **modules/template/templates/default-template.php** – default template that renders yacht and cost data using arrays from `template-data.php`.
- **modules/template/php/load-template.php** – AJAX handler `handle_create_template()`; validates request, scrapes yacht data with `extraerInformacionYate()`, calculates costs, and loads the selected template.
- **modules/template/php/calculate-template.php** – delegates cost calculations to `calculate()` and returns structured results.
- **modules/calc/js/interfaz.js** – UI helpers for the calculator; on `#yachtUrl` blur, sends an AJAX request to `extract_yacht_info` to preview yacht info (handler not found in repo).
- **modules/yachtinfo/yacht-info-service.php** – service class used by the modern bootstrap flow to scrape yacht information with domain validation, caching, and specialized parsers.
- **core/bootstrap.php** – registers services and handles the `createTemplate` AJAX action, invoking `YachtInfoService` and `RenderEngine`.
- **core/yacht-functions.php** – enqueues scripts and registers AJAX actions for template creation and calculation.

## Frontend Flow

1. The user enters a yacht URL in the calculator form and clicks **Create Template**.
2. `TemplateManager.collectFormData()` builds `FormData` with the URL, selected template, season texts, currency, fees, and toggles.
3. `TemplateManager.createTemplate()` sends this `FormData` to `admin-ajax.php` with action `createTemplate`, then replaces `#result` with the returned HTML and enables copying.
4. `template.js` initializes `TemplateManager`, manages dynamic form groups, restores saved data, and propagates changes (e.g., toggling One Day Charter).
5. `interfaz.js` includes a separate blur event on `#yachtUrl` that attempts to fetch preview data via `extract_yacht_info`.

## Backend Flow

1. `handle_create_template()` in `load-template.php` verifies nonce and permissions, sanitizes inputs, and calls `extraerInformacionYate()` when a yacht URL is supplied.
2. `extraerInformacionYate()` retrieves the page with cURL and uses DOMXPath to extract name, length, type, builder, year built, crew, cabins, guest capacity, cabin configuration, image URL, and the source URL.
3. Cost parameters and flags are packaged into `$data` and passed to `calcularResultadosTemplate()` (in `calculate-template.php`), which delegates to the calculator service.
4. The template file (e.g., `default-template.php`) is included with `$templateData` containing calculation results, season texts, yacht info, and hide-element flags.
5. `default-template.php` builds arrays using `buildYachtInfoArray()` and `buildCalcSnippetArray()` and outputs a styled HTML snippet displaying yacht details, rates, extras, totals, and gratuity suggestions.
6. `TemplateManager` receives the HTML, injects it into `#result`, and exposes a copy-to-clipboard function.

## Data Mapping

- Yacht fields: `yachtName`, `length`, `type`, `builder`, `yearBuilt`, `crew`, `cabins`, `guest`, `cabinConfiguration`, `imageUrl`, `yachtUrl`.
- Cost snippet fields: low/high season labels and costs, structured block with calculated totals and flags like `enableExpenses`.
- Frontend flags and inputs control optional sections (VAT, APA, relocation, security, extras, mixed seasons, one-day charter).

## Observations

- The blur-based preview in `interfaz.js` calls `extract_yacht_info`, but no corresponding PHP handler exists in the repository; this endpoint may be planned or handled elsewhere.
- `load-template.php` implements its own scraping via `extraerInformacionYate`, while the modern `YachtInfoService` provides a more modular approach; duplication suggests legacy vs. new architecture.

## Plan para la Creación del Módulo app_yacht\modules\yachtinfo

Este módulo reutilizará el método de extracción de datos del yate existente (de `YachtInfoService`), pero en lugar de crear un template personalizado para email, generará un contenedor HTML con los datos del yate y una miniatura. Esto offload la carga de extracción del módulo template, que solo usará el arreglo preparado globalmente.

### Pasos de Implementación:

1. **Implementación Frontend Estática:**
   - Crear la interfaz estática para el contenedor HTML que muestre los datos del yate (nombre, longitud, tipo, constructor, año, etc.) y una miniatura.
   - Asegurar que el diseño respete el estilo general de la app.
   - Iterar hasta que el diseño cumpla con las expectativas y coherente con el de la app ya implementado.

2. **Configuración del Envío de Payload:**
   - Configurar el envío de datos desde el frontend (e.g., URL del yate) vía AJAX o fetch al backend del nuevo módulo.

3. **Implementación Backend:**
   - Usar `YachtInfoService` para extraer y procesar los datos del yate.
   - Generar el HTML del contenedor con los datos extraídos y la miniatura.
   - Guardar un arreglo global con los datos del yate para uso en otros módulos.

4. **Integración y Pruebas:**
   - Imprimir los datos en el contenedor HTML.
   - Asegurar que el arreglo global esté disponible para módulos como template, que lo usarán directamente sin extraer datos nuevamente.
   - Probar la reutilización de código y la offload de responsabilidades.

