# Análisis de Funcionalidad: Mixed Taxes (Impuestos Mixtos)

## Fecha de Análisis: 2025-01-09

## Resumen Ejecutivo

La funcionalidad de **Mixed Taxes** permite calcular impuestos ponderados por país cuando un chárter de yate opera en múltiples jurisdicciones fiscales. Implementa una interfaz dinámica que gestiona la entrada de datos por país, el cálculo proporcional de impuestos, y la integración con el sistema principal de cálculo.

## 1. Archivos Clave Identificados

### Frontend (JavaScript)
- **Principal**: `modules/calc/js/MixedTaxes.js` (199 líneas)
- **Integración**: `modules/calc/js/calculate.js` (líneas 127-128, 140-160)
- **UI**: `modules/calc/calculator.php` (líneas 62-65, 80-81)

### Backend (PHP)
- **Procesamiento**: `modules/calc/php/calculate.php` (líneas 103-155, 180-220)

### Documentación
- **Índice**: `DOC/modules/calc/js/MixedTaxes/01_index.md`
- **Propósito**: `DOC/modules/calc/js/MixedTaxes/02_propósito-general.md`
- **Lógica**: `DOC/modules/calc/js/MixedTaxes/05_lógica-negocio.md`
- **Flujo**: `DOC/modules/calc/js/MixedTaxes/07_flujo-datos.md`

## 2. Flujo de Datos Completo

### 2.1. Activación del Módulo
1. **Usuario activa checkbox** `enableMixedTaxes`
2. **Evento onchange** ejecuta `MixedTaxes.toggleVisibility(this)`
3. **Visibilidad**: Muestra contenedor de países, oculta campos globales
4. **Auto-creación**: Si no hay países, ejecuta `MixedTaxes.addCountryField()`

### 2.2. Entrada de Datos por País
```
Usuario → Formulario → MixedTaxes.js → calculate.js → AJAX → calculate.php
```

**Campos por país**:
- Nombre del país (texto)
- Noches de estancia (número)
- VAT Rate (%) - si está activado
- APA Amount (fijo) - si está activado
- APA Percentage (%) - si está activado
- Relocation Fee - si está activado
- Security Deposit - si está activado

### 2.3. Procesamiento Backend
1. **Recepción**: `calculate.php` recibe arrays de datos
2. **Validación**: Sanitización de todos los campos
3. **Cálculo proporcional**:
   - Ratio por país = noches_país / total_noches
   - Impuestos aplicados proporcionalmente al base rate

### 2.4. Respuesta y Visualización
1. **JSON response** con desglose por país
2. **Frontend** muestra resultados con formato de moneda
3. **Actualización dinámica** al cambiar moneda

## 3. Componentes Principales

### 3.1. Objeto MixedTaxes (JavaScript)
```javascript
const MixedTaxes = {
    countryCount: 0,
    currencySymbol: '€',
    init(),                    // Inicialización
    toggleVisibility(),        // Control de visibilidad
    addCountryField(),         // Añadir país
    removeCountryField(),      // Eliminar país
    _updateVatFields(),        // Actualizar campos
    _handleApaExclusivity(),   // Manejar exclusividad APA
    _updateCurrencySymbol()    // Actualizar símbolos
}
```

### 3.2. Funciones Clave

#### addCountryField()
- **Propósito**: Añade dinámicamente un bloque de campos para un nuevo país
- **Genera**: HTML con estructura Bootstrap (col-12 col-md-6)
- **Incluye**: Inputs para país, noches, y botón de eliminación

#### _updateVatFields()
- **Propósito**: Reconstruye los campos de impuestos según checkboxes activos
- **Lógica**: Itera sobre taxCheckboxes (vat, apa, relocation, security)
- **Formato**: Input con símbolo de moneda/porcentaje según tipo

#### toggleVisibility()
- **Propósito**: Controla la visibilidad de toda la sección de impuestos mixtos
- **Efectos**: 
  - Muestra/oculta contenedor de países
  - Muestra/oculta campos globales de impuestos
  - Auto-crea primer país si está vacío

### 3.3. Validaciones
- **Frontend**: Validación de números con `formatNumber(this)`
- **Backend**: Sanitización con `sanitize_text_field()` y conversión a float
- **Exclusividad**: Solo puede estar activo APA fijo O APA porcentual

## 4. Integración con Sistema Principal

### 4.1. Dependencias
- **currency.js**: Actualización de símbolos de moneda
- **validate.js**: Validación de inputs
- **calculate.js**: Recolección de datos y envío AJAX

### 4.2. Flujo AJAX
```javascript
// En calculate.js (líneas 127-160)
const mixedTaxesEnabled = document.getElementById('enableMixedTaxes')?.checked;
formData.append('enableMixedTaxes', mixedTaxesEnabled ? '1' : '0');

// Recolectar datos de cada país
const mixedTaxesItems = document.querySelectorAll('.country-tax-item-wrapper');
mixedTaxesItems.forEach(item => {
    // Extraer valores de cada campo
    formData.append('countryName[]', country);
    formData.append('nights[]', nights);
    formData.append('vat[]', vatRate);
    // ... otros campos
});
```

### 4.3. Procesamiento Backend
```php
// En calculate.php (líneas 103-155)
$enableMixedTaxes = !empty($_POST['enableMixedTaxes']) && $_POST['enableMixedTaxes'] === '1';
$mixedTaxes = [];
if ($enableMixedTaxes) {
    // Recolectar arrays de datos
    for ($i = 0; $i < count($countryNames); $i++) {
        $mixedTaxes[] = [
            'country' => sanitize_text_field($countryNames[$i] ?? ''),
            'nights' => (int)($nightsArr[$i] ?? 0),
            'vatRate' => sanitize_text_field($vats[$i] ?? ''),
            // ... otros campos
        ];
    }
}
```

## 5. Casos de Uso Específicos

### 5.1. Chárter Multi-país
**Escenario**: Un chárter visita España (7 noches), Francia (5 noches), Italia (3 noches)
- **Configuración**: 3 países con sus respectivas tasas de VAT
- **Cálculo**: Impuestos ponderados por duración en cada país

### 5.2. Cambio de Moneda
- **Activación**: Cambio en selector de moneda principal
- **Efecto**: Todos los símbolos se actualizan dinámicamente
- **Preservación**: Valores numéricos se mantienen

### 5.3. Exclusividad APA
- **Regla**: No puede haber simultáneamente APA fijo y porcentual
- **Implementación**: Al activar uno, el otro se desactiva automáticamente
- **Ámbito**: A nivel global, afecta a todos los países

## 6. Manejo de Errores

### 6.1. Validaciones Frontend
- **Campos requeridos**: País y noches son obligatorios
- **Formato numérico**: Patrones regex para decimales
- **Feedback visual**: Clases de Bootstrap para estados válidos/inválidos

### 6.2. Validaciones Backend
- **Sanitización**: Todos los inputs se limpian antes de procesar
- **Conversión segura**: Uso de floatval() con manejo de errores
- **Logging**: Registro de errores con error_log()

## 7. Estado Actual y Observaciones

### 7.1. Funcionalidad Completa
✅ Añadir/eliminar países dinámicamente
✅ Cálculo proporcional de impuestos
✅ Actualización de moneda en tiempo real
✅ Exclusividad APA implementada
✅ Integración completa con sistema de cálculo

### 7.2. Documentación Disponible
- ✅ Documentación técnica completa en DOC/modules/calc/js/MixedTaxes/
- ✅ Casos de prueba definidos
- ✅ Historial de cambios actualizado
- ✅ Referencias cruzadas con otros módulos

### 7.3. Posibles Mejoras Identificadas
- **Validación adicional**: Verificar que la suma de noches coincida con total del chárter
- **Plantillas guardadas**: Permitir guardar configuraciones frecuentes de países/tasas
- **Autocompletado**: Sugerir países basados en entrada del usuario
- **Validación cruzada**: Verificar consistencia entre tasas de países vecinos

## 8. Conclusión

La funcionalidad de Mixed Taxes está completamente implementada y documentada. El sistema permite un cálculo preciso y flexible de impuestos para chárteres multi-país, con una interfaz intuitiva y validaciones robustas. La integración con el sistema principal es fluida y el flujo de datos está bien definido desde la entrada del usuario hasta el cálculo final.