# Plan de modificación: VAT Rate Mix

Este documento define cómo adaptar la funcionalidad de **Mixed Taxes** para ofrecer un nuevo modo llamado **VAT Rate Mix**. El objetivo es calcular el VAT de forma proporcional por país manteniendo el resto de impuestos (APA, Relocation Fee y Security Deposit) como valores globales. El envío de datos al backend y la estructura de archivos existente deben mantenerse para reducir el impacto en el código actual.

## 1. Cambios en el Frontend

### 1.1 Nuevo checkbox
- Añadir en `modules/calc/calculator.php` un checkbox con id `vatRateMix`.
- Debe seguir el estilo de los checkboxes existentes y ser excluyente con el VAT global.
- Ejecutará `VatRateMix.toggleVisibility(this)` al cambiar su estado.

### 1.2 Objeto JavaScript
- Crear `modules/calc/js/VatRateMix.js` reutilizando la lógica de `MixedTaxes.js` pero sólo para manejar campos de país, noches y VAT rate.
- Funciones clave: `init`, `toggleVisibility`, `addCountryField`, `removeCountryField` y `_updateCurrencySymbol`.
- No debe ocultar los campos globales de APA, Relocation ni Security.

### 1.3 Recolección de datos
- En `modules/calc/js/calculate.js` añadir la variable `vatRateMix` y enviarla con `formData.append('vatRateMix', vatRateMix ? '1' : '0');`.
- Si el checkbox está activo, recorrer los elementos `.vat-rate-item` (estructura generada por `VatRateMix.js`) y enviar:
  - `countryName[]`
  - `nights[]`
  - `vat[]`
- Los campos globales (`apaAmount`, `apaPercentage`, `relocationFee`, `securityDeposit`) se envían siempre.

## 2. Cambios en el Backend

### 2.1 Recepción de datos
- En `modules/calc/php/calculate.php` crear la variable `$vatRateMix`:
  ```php
  $vatRateMix = !empty($_POST['vatRateMix']) && $_POST['vatRateMix'] === '1';
  ```
- Cuando esté activa, construir `$mixedTaxes` únicamente con `country`, `nights` y `vatRate`.
- Mantener intacta la recolección de impuestos globales.

### 2.2 Cálculo proporcional del VAT
- Si `$vatRateMix` es verdadero, calcular el VAT proporcional por noches tomando los valores de `$mixedTaxes`.
- Si no, usar el VAT global (`$vatRate`).
- Las funciones de cálculo de APA, Relocation Fee y Security Deposit no cambian.

### 2.3 Formato de salida
- `textResult()` debe mostrar un bloque por país cuando `$vatRateMix` esté activo indicando noches y VAT.
- Los demás impuestos se muestran de forma global como hasta ahora.

## 3. Archivos afectados
- `modules/calc/calculator.php` – añadir checkbox y referencia al nuevo script.
- `modules/calc/js/VatRateMix.js` – nuevo archivo basado en `MixedTaxes.js`.
- `modules/calc/js/calculate.js` – envío de `vatRateMix` y datos de país.
- `modules/calc/php/calculate.php` – recepción y cálculo.
- No se requiere modificar nada dentro de `shared/`.

## 4. Pasos de implementación propuestos
1. Crear copia de `MixedTaxes.js` como `VatRateMix.js` eliminando la lógica de APA, Relocation y Security por país.
2. Ajustar `calculate.js` para enviar los nuevos datos.
3. Actualizar `calculator.php` con el checkbox `vatRateMix` y cargar el nuevo script.
4. Modificar `calculate.php` para procesar `vatRateMix` y calcular el VAT proporcional.
5. Actualizar `textResult()` para mostrar el desglose de VAT por país.
6. Probar casos con y sin `vatRateMix` activo verificando que los impuestos globales se mantienen.

## 5. Formato de Salida Unificado para textResult()

### 5.1. Estructura de Salida Esperada
El formato de salida de `textResult()` debe ser exactamente como se muestra a continuación:

```
---------------------------------------
5 nights, 5 Guests: € 83,334
---------------------------------------
Country: x (3 nights)
VAT (5%): € 2,500.02
Country: y (2 nights)
VAT (5%): € 1,666.68
APA (5%): € 4,166.70
Relocation fee: € 5.00
Security deposit: € 5.00
---------------------------------------
Subtotal for charter: € 91,678
---------------------------------------
Extras
---------------------------------------
x: € 2,000.00
Guest Fee: y (45 guests x € 45 pp): € 2,025.00
---------------------------------------
Grand Total: € 95,703
---------------------------------------
Suggested gratuity (10%): € 8,334
Suggested gratuity (15%): € 12,501
---------------------------------------
```

### 5.2. Modificaciones en textResult() para VAT Rate Mix

```php
function textResult(array $calcArray, array $hideElements = [], bool $enableExpenses = false, bool $enableMixedSeasons = false): array {
    // ... código existente ...
    
    // Nuevo manejo para VAT Rate Mix
    if ($vatRateMix && !empty($mixedTaxes)) {
        foreach ($mixedTaxes as $country) {
            $str .= "Country: {$country['country']} ({$country['nights']} nights)\n";
            if (!empty($hideElements['hideVAT']) && $country['vat_rate'] > 0) {
                $str .= "VAT ({$country['vat_rate']}%): {$country['vat_amount_formatted']}\n";
            }
        }
    }
    
    // APA, Relocation, Security se mantienen globales
    if (!$hideElements['hideAPA']) {
        $taxStr .= "APA ({$block['apaRateForDisplay']}%): {$block['apaPercDisplay']}\n";
    }
    if (!$hideElements['hideAPA'] && $block['apaAmountDisplay'] !== '--') {
        $taxStr .= "APA (amount): {$block['apaAmountDisplay']}\n";
    }
    if (!$hideElements['hideRelocation'] && $block['relocationDisplay'] !== '--') {
        $taxStr .= "Relocation fee: {$block['relocationDisplay']}\n";
    }
    if (!$hideElements['hideSecurity'] && $block['securityDisplay'] !== '--') {
        $taxStr .= "Security deposit: {$block['securityDisplay']}\n";
    }
}
```

## 6. Pasos de Implementación

### Fase 1: Preparación
1. Hacer backup de archivos actuales
2. Crear rama Git para desarrollo
3. Documentar cambios actuales

### Fase 2: Frontend
1. Renombrar MixedTaxes.js a VatRateMix.js
2. Modificar funciones para nuevo comportamiento
3. Actualizar calculator.php con nuevos elementos
4. Actualizar calculate.js para nuevo flujo de datos

### Fase 3: Backend
1. Modificar calculate.php para nuevo esquema
2. Actualizar validaciones
3. Probar cálculos con datos de prueba

### Fase 4: Testing
1. Casos de prueba para VAT Rate Mix activo/inactivo
2. Verificar cálculos proporcionales
3. Validar integración con impuestos globales
4. Test de cambio de moneda

## 7. Consideraciones de Retrocompatibilidad

### 7.1. Datos Existentes
- Los datos actuales de Mixed Taxes no serán compatibles
- Considerar migración de datos o limpieza
- Advertir a usuarios sobre cambio de funcionalidad

### 7.2. Documentación
- Actualizar toda la documentación de MixedTaxes
- Crear nuevos ejemplos de uso
- Actualizar manuales de usuario

## 8. Verificación del Flujo de Impuestos en Carpeta Shared

### 8.1. Análisis del Flujo Actual
Tras revisar la carpeta `shared/`, se ha determinado que:

**✅ Flujo de Impuestos NO está en Shared**
- La carpeta `shared/` contiene utilidades generales (CSS, validaciones, templates)
- **El flujo completo de Mixed Taxes está en `modules/calc/`**
- **El flujo de VAT Rate Mix debe seguir en `modules/calc/`**

### 8.2. Archivos Relacionados en Shared
```
shared/
├── css/app_yacht.css          # Estilos generales (incluye tax checkboxes)
├── js/classes/Calculator.js   # Template para cálculos (referencia a VAT)
├── js/classes/TemplateManager.js # Gestión de templates (incluye VAT)
├── php/validation.php         # Validaciones generales
└── php/security.php         # Seguridad
```

### 8.3. Impacto en Shared
**✅ Sin cambios necesarios en shared/**
- Los estilos CSS ya existentes son compatibles
- Los templates mantienen compatibilidad
- Las validaciones no requieren modificación

**✅ Todos los cambios deben realizarse en:**
- `modules/calc/js/VatRateMix.js` (nuevo archivo)
- `modules/calc/php/calculate.php` (modificar)
- `modules/calc/js/calculate.js` (modificar)
- `modules/calc/calculator.php` (modificar)

## 9. Recomendación Final

**Recomiendo CREAR UN NUEVO MÓDULO VAT RATE MIX** por las siguientes razones:
- Mantiene la estructura actual del proyecto.
- Incorpora el cálculo prorrateado únicamente para el VAT.
- Minimiza el impacto en el resto de la aplicación.
- Facilita la migración y retrocompatibilidad.

## 10 nuevo formato de salida simplificado

Low Season 2 nights: € 33,333.33
High Season 3 nights: € 50,000.00
---------------------------------------
5 nights, 5 Guests: € 83,334
---------------------------------------
VAT (5%): 2N usa: € 1,666.68
VAT (5%): 3N canada: € 2,500.02
APA (5%): € 4,166.70
Relocation fee: € 5.00
Security deposit: € 5.00
---------------------------------------
Subtotal for charter: € 91,678
---------------------------------------
Extras
---------------------------------------
x: € 2,000.00
Guest Fee: y (45 guests x € 45 pp): € 2,025.00
---------------------------------------
Grand Total: € 95,703
---------------------------------------
Suggested gratuity (10%): € 8,334
Suggested gratuity (15%): € 12,501

# explicacion
- 2N se refiere a la cantidad de noches en el pais 
- usa el nombre que el usuario agregue en el campo vatCountryName[] 
- esl resto es obvio.
VAT (5%): 2N usa: € 1,666.68
VAT (5%): 3N canada: € 2,500.02