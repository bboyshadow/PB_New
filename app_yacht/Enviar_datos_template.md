# Plan para Integración de VAT Rate en Templates

## Introducción
Este documento explica el flujo de los resultados de cálculos, enfocándonos en campos como VAT Rate, APA, APA %, Relocation Fee y Security Deposit, desde su procesamiento hasta su visualización en el frontend a través de `modules/template/templates/default-template.php`. También consideramos la adición de VAT Rate Mix, que actualmente no se maneja en el template.

## Flujo General de Cálculos
1. **Recolección de Datos**: Los datos se recolectan desde el formulario en el frontend (usando `TemplateManager.js`). Esto incluye valores para VAT, APA, tasas de charter, extras, etc. Para VAT Rate Mix, asegurar que se recolecten si está habilitado.

2. **Envío AJAX**: Al hacer clic en `#createTemplateButton`, se envía una solicitud AJAX a `admin-ajax.php` con acción 'createTemplate'.

3. **Manejo en Backend (`load-template.php`)**:
   - La función `handle_create_template()` valida los datos, extrae información del yate (usando `extraerInformacionYate` para scraping).
   - Recopila y sanitiza datos de VAT Mix de $_POST (vatRateMix, vatCountryName[], vatNights[], vatRate[]).
   - Llama a `calcularResultadosTemplate()` (de `calculate-template.php`) para realizar los cálculos, pasando VAT Mix.

4. **Cálculos Específicos**:
   - **VAT Rate**: Se calcula basado en las tasas de IVA ingresadas, considerando si es mixto o no.
   - **APA**: Advance Provisioning Allowance, calculado como un porcentaje del base rate.
   - **APA %**: El porcentaje aplicado al APA.
   - **Relocation Fee**: Tarifa por reubicación, sumada directamente si aplica.
   - **Security Deposit**: Depósito de seguridad, manejado como un valor fijo o calculado.
   - **VAT Rate Mix**: Si vatRateMix=1, calcular VAT segmentado por país, noches y tasa.

5. **Generación de HTML**: Se incluye el archivo `default-template.php`, que usa los resultados calculados para generar el HTML final, mostrando breakdown de VAT Mix si aplica.

6. **Respuesta y Visualización**: El HTML generado se envía como respuesta JSON y se inserta en el contenedor `#result` en el frontend.

## Detalles de Cálculos
- **VAT Rate**: En `calcularResultadosTemplate()`, se aplica la tasa de IVA al base rate total. Si hay mezcla de tasas (VAT Rate Mix), se calcularía por país, pero actualmente no se soporta.
- **APA**: Calculado como `base_rate * (APA % / 100)`.
- **APA %**: Valor ingresado directamente del formulario.
- **Relocation Fee**: Agregado como extra fijo.
- **Security Deposit**: Similar, agregado como valor fijo en el total.

## Integración en `default-template.php`
En `default-template.php`, los valores se imprimen usando variables PHP como `$vat_rate`, `$apa_amount`, etc., dentro de una estructura HTML (tablas o secciones). Para VAT Mix, agregar lógica condicional para mostrar el breakdown.

## Propuesta para Agregar VAT Rate Mix
- **Modificación en Frontend (`TemplateManager.js`)**: En `collectFormData()`, agregar lógica para recolectar vatRateMix como bandera (e.g., desde checkbox, '1' si activo) y los arrays separados: vatCountryName[], vatNights[], vatRate[] de los elementos .country-vat-item-wrapper, coincidiendo con el formato de calculate.js (no usar vatRateMix[index][field]).
- **Modificación en Backend (`load-template.php`)**: Sanitizar $_POST['vatRateMix'], $_POST['vatCountryName'], $_POST['vatNights'], $_POST['vatRate']. Pasarlos a $data para calculate(), estructurando como en calculate.php.
- **Modificación en Cálculos (`calculate-template.php`)**: Actualizar `calcularResultadosTemplate()` para usar VAT Mix si presente, llamando lógica similar a calculate.php, manejando los arrays separados.
- **Actualización del Template (`default-template.php`)**: Agregar secciones para mostrar VAT por país, noches, tasa y total mixto si VAT Mix está habilitado.
- **Verificación**: Asegurar que el payload incluya vatRateMix como bandera y arrays separados al crear template, igual que en calculate_charter. Probar para confirmar que los datos se envíen correctamente.

Este plan asegura una integración fluida y escalable, corrigiendo la ausencia y formato incorrecto de VAT Mix en el payload de createTemplate.

## Guía General para Agregar Nuevos Datos de Cálculo

Para agregar nuevos campos de cálculo como VAT Rate, APA, APA %, Relocation Fee o Security Deposit, sigue estos pasos basados en la implementación de VAT Rate Mix, evitando errores comunes como selectores mismatched, UI ausente o formatos de datos incorrectos:

1. **Agregar UI en Frontend (modules/template/template.php)**: Incluye un checkbox para habilitar el campo, un botón "Add" si es dinámico, y un contenedor para elementos (e.g., .country-vat-item-wrapper). Asegúrate de que los nombres de inputs coincidan con los esperados (e.g., vatRate[], etc.).

2. **Incluir Script JS**: Agrega <script src="path/to/Script.js"></script> en template.php. Crea o adapta un script como VatRateMix.js para manejar adición/elimino de campos dinámicos, validando selectores y eventos.

3. **Recolección de Datos (shared/js/classes/TemplateManager.js)**: En collectFormData(), agrega lógica condicional para recolectar datos si el checkbox está marcado. Usa querySelectorAll con selectores precisos (e.g., input[name="vatRate[]"]) y append a FormData como arrays o valores simples. Verifica presencia de elementos antes de setear flags.

4. **Manejo en Backend (modules/template/php/load-template.php)**: Sanitiza $_POST para los nuevos campos. Pásalos a calcularResultadosTemplate() en el array $data, estructurando arrays separados si es mixto.

5. **Actualizar Cálculos (modules/template/php/calculate-template.php)**: Modifica calcularResultadosTemplate() para procesar el nuevo dato, integrándolo en totales o breakdowns. Maneja condicionales para modos simple/mixto.

6. **Visualización en Template (modules/template/templates/default-template.php)**: Agrega secciones HTML condicionales para mostrar el nuevo dato, usando variables PHP del cálculo. Asegura estilos consistentes y formatos como textResult.

**Evitar Errores Comunes**:
- Verifica que selectores en JS coincidan exactamente con nombres en HTML.
- Prueba el payload AJAX para confirmar datos enviados.
- Usa view_files para inspeccionar archivos antes de editar.
- Después de cambios, usa open_preview para verificar visualmente.