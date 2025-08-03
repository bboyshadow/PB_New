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
5. `TemplateManager` receives the HTML, injects it into `#result`, and exposes a copy-to-clipboard function.

## Data Mapping

- Yacht fields: `yachtName`, `length`, `type`, `builder`, `yearBuilt`, `crew`, `cabins`, `guest`, `cabinConfiguration`, `imageUrl`, `yachtUrl`.
- Cost snippet fields: low/high season labels and costs, structured block with calculated totals and flags like `enableExpenses`.
- Frontend flags and inputs control optional sections (VAT, APA, relocation, security, extras, mixed seasons, one-day charter).

## Observaciones a tomar en cuenta

- El flujo descrito en este documento parece basarse en una versión anterior o legacy del código. En el código actual, el manejo de AJAX para `createTemplate` se realiza en `bootstrap.php` mediante `handleCreateTemplate()`, que utiliza `YachtInfoService` para extraer datos del yate si se proporciona `yachtUrl`, y luego invoca `RenderEngine::createTemplate()`.
- No se encontró un handler para la acción AJAX `extract_yacht_info` mencionada en `interfaz.js`.
- La extracción real en `YachtInfoService` incluye validación de dominio, caché, y parsers específicos para sitios como charterworld.com, yachtcharterfleet.com y burgessyachts.com, usando `wp_remote_get` y `DOMXPath`.
- El frontend en `interfaz.js` intenta una vista previa al perder foco en `#yachtUrl`, pero sin handler backend, esto no funcionaría como se describe.

