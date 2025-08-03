# Guía Paso a Paso para Integrar Nuevas Funcionalidades en Calculate y Mostrarlas en Default Template

Esta guía paso a paso está diseñada para evitar errores comunes al agregar nuevas funcionalidades a `calculate.php` (cálculos backend) y mostrarlas en `default-template.php` (frontend), basada en lecciones aprendidas de la integración de promociones. Sigue estos pasos estrictamente para integrar datos como descuentos, promociones o nuevas tasas sin problemas. El flujo es: Frontend (TemplateManager.js) → Backend (load-template.php → calculate-template.php → calculate.php) → Template (default-template.php).

## Paso 1: Recolección de Datos en el Frontend (TemplateManager.js)
- **Archivo**: `shared/js/classes/TemplateManager.js`.
- **Función**: `collectFormData()`.
- **Acciones clave para nuevas funcionalidades**:
  - Identifica los inputs nuevos (ej. checkboxes para activar la funcionalidad, campos numéricos para valores).
  - Recolecta datos condicionalmente: Si un flag está activo (ej. promotionActive), incluye valores relacionados (ej. promotionNights).
  - Agrega a FormData: Usa `formData.append('newField', value)`; para arrays, itera y append multiple.
  - **Evitar errores**: Valida que los datos sean completos antes de enviar (ej. si flag activo pero valor faltante, alerta al usuario). No asumas defaults; loggea en consola para depuración.

## Paso 2: Envío AJAX al Backend
- **Archivo**: `shared/js/classes/TemplateManager.js`.
- **Función**: `createTemplate()` o evento de botón.
- **Acciones**:
  - Envía POST a `modules/template/php/load-template.php` con FormData, incluyendo nonce.
  - **Evitar errores**: Maneja respuestas de error (ej. si datos no se transmiten, muestra mensaje). Prueba con payloads de ejemplo en dev tools para simular nuevas funcionalidades.

## Paso 3: Recepción y Preparación en load-template.php
- **Archivo**: `modules/template/php/load-template.php`.
- **Acciones**:
  - Verifica nonce y permisos.
  - Extrae y sanitiza nuevos campos explícitamente: ej. $data['newField'] = sanitize_text_field($_POST['newField']);
  - Incluye en $data para pasar a calculate-template.php.
  - **Evitar errores**: No asumas que los datos están nested en arrays existentes (ej. como charterRates); extráelos directamente si es necesario. Loggea si datos faltan para depuración. Verifica con view_files antes de editar.

## Paso 4: Integración en Cálculos (calculate.php)
- **Archivo**: `modules/calc/php/calculate.php`.
- **Funciones**: `calculate()` y `textResult()`.
- **Acciones en calculate()**:
  - Parsear nuevos inputs (ej. $newValue = floatval($rate['newField'])).
  - Agrega lógica de cálculo (ej. if (active flag), aplica fórmula y calcula nuevo resultado).
  - Incluye en $structuredResults: Agrega keys nuevas (ej. 'newResult' => formatCurrency($calculatedValue)). Asegura condiciones correctas (ej. no condicionar a diferencias innecesarias como promotedRate != discountedRate).
- **Acciones en textResult()**:
  - Agrega bloque para imprimir nuevo resultado: ej. if ($block['newResult'] !== '--') { $str .= "New Feature: " . $block['newResult'] . "\n"; }
  - **Evitar errores**: Coloca el bloque if fuera de otros condicionales (ej. no anidado en descuento) para independencia. Usa condiciones simples basadas en flags activos.
- **Consejo**: Siempre verifica el array de resultados incluye flags (ej. 'newActive' => $active ? '1' : '0'). Prueba cálculos aislados antes de integrar.

## Paso 5: Visualización en default-template.php
- **Archivo**: `modules/template/templates/default-template.php`.
- **Acciones**:
  - En bucles foreach ($resultArray), agrega condicionales: ej. if ($resultData['newActive'] === '1' && $resultData['newResult'] !== '--') { echo "New Feature: " . $resultData['newResult']; }
  - Usa formatos consistentes (ej. NxM para promociones).
  - **Evitar errores**: No uses condiciones estrictas como === '1'; prefiere !empty() para flexibilidad. Elimina chequeos innecesarios (ej. no comparar con valores default como '--' si flag está activo).

## Paso 6: Pruebas y Verificación
- Después de cambios: Usa open_preview para verificar UI.
- Prueba flujos completos: Envía payloads con/ sin la nueva funcionalidad activa.
- Loggea en cada paso para rastrear datos.

## Errores Comunes y Cómo Evitarlos
- **Transmisión de datos**: Siempre extrae explícitamente en load-template.php y verifica en calculate.php.
- **Lógica condicional en cálculos**: Usa flags para activar, no comparaciones indirectas.
- **Impresión en textResult**: Bloques independientes, no anidados.
- **Condiciones en template**: Simples y flexibles.
- Errores resueltos de promoción (aplicar similares):
  - Problema: promotionActive no en resultados. Solución: Agregarlo en calculate().
  - Problema: Bloque anidado en textResult. Solución: Mover fuera.
  - Problema: Condición estricta para promotedRate. Solución: Basar en flag activo.