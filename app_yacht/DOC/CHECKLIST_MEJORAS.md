# CHECKLIST DE MEJORAS - APP YACHT

## 🎯 Objetivo
Implementar mejoras de forma fraccionada y segura, testando cada cambio antes de aplicar el siguiente.

---

## 📋 FASE 1: ESTABILIZACIÓN Y DEBUGGING (Prioridad ALTA)

### ✅ 1.1 Sistema de Logs Mejorado - **COMPLETADO**
- [x] **Tarea 1.1.1**: Crear archivo `app_yacht/shared/helpers/Logger.php`
  - ✅ Implementar clase Logger básica con niveles (ERROR, WARNING, INFO, DEBUG)
  - ✅ **Test**: Verificar que se crea el archivo de log correctamente
  - **Rollback**: Eliminar archivo si hay problemas

- [x] **Tarea 1.1.2**: Integrar Logger en `calculate.php`
  - ✅ Añadir logs en puntos críticos (inicio, validaciones, errores)
  - ✅ **Test**: Ejecutar calculadora y verificar logs generados
  - **Rollback**: Comentar líneas de log si afecta rendimiento

- [x] **Tarea 1.1.3**: Integrar Logger en archivos AJAX críticos
  - ✅ Aplicar en `calculateRelocation.php`, `calculatemix.php`
  - ✅ **Test**: Probar cada calculadora individualmente
  - **Rollback**: Revertir archivo por archivo si es necesario

### ✅ 1.2 Validación de Datos Robusta - **COMPLETADO**
- [x] **Tarea 1.2.1**: Crear validador de `charterRates`
  - ✅ Archivo: `app_yacht/shared/helpers/DataValidator.php`
  - ✅ Validar estructura, tipos de datos, rangos
  - ✅ **Test**: Enviar datos inválidos y verificar rechazo
  - **Rollback**: Usar validación anterior si falla

- [x] **Tarea 1.2.2**: Aplicar validación en `calculate.php`
  - ✅ Reemplazar validación básica con DataValidator
  - ✅ **Test**: Probar con datos válidos e inválidos
  - **Rollback**: Restaurar validación original

- [x] **Tarea 1.2.3**: Extender validación a otros endpoints
  - ✅ Aplicar en calculadoras de reubicación y mix
  - ✅ **Test**: Verificar funcionamiento de cada calculadora
  - **Rollback**: Implementar uno por uno, revertir si falla

### ✅ 1.3 Manejo de Errores Frontend - **COMPLETADO**
- [x] **Tarea 1.3.1**: Mejorar `calculate.js` - Manejo de errores
  - ✅ Añadir try-catch en `handleCalculateButtonClick`
  - ✅ Mostrar mensajes de error específicos al usuario
  - ✅ **Test**: Simular error 500 y verificar mensaje amigable
  - **Rollback**: Mantener alert() anterior si hay problemas

- [x] **Tarea 1.3.2**: Implementar estado de loading
  - ✅ Deshabilitar botón durante cálculo
  - ✅ Mostrar spinner o indicador visual (hooks implementados)
  - ✅ **Test**: Verificar UX durante cálculo lento
  - **Rollback**: Remover indicadores si interfieren

- [x] **Tarea 1.3.3**: Fix VAT Mix 422 Error
  - ✅ Corregir validación en `calculate.php` para manejar `vatRate[]` como array cuando Mix está activo
  - ✅ Evitar sanitización incorrecta de arrays como strings
  - ✅ **Test**: Activar "VAT rate mix" y verificar que no devuelve 422
  - **Rollback**: Revertir lógica de validación si genera otros errores

### ✅ 1.4 Validación Frontend Preventiva - **COMPLETADO**
- [x] **Tarea 1.4.1**: Validación de campos básicos antes de envío
  - ✅ Implementada función `validateFieldsWithWarnings()` en `validate.js`
  - ✅ Validaciones para Mixed Seasons (nights > 0, suma correcta)
  - ✅ Validaciones para VAT Mix (al menos una entrada válida)
  - ✅ Mensajes preventivos sin bloquear envío
  - ✅ **Test**: Integrada en Calculator.js, calculate.js y TemplateManager.js
  - **Rollback**: Comentar llamadas `validateFieldsWithWarnings()` si interfieren con UX

- [x] **Tarea 1.4.2**: Validación de coherencia de datos
  - ✅ Verificar que Mixed Seasons tenga nights > 0 si está activo
  - ✅ Verificar suma correcta (lowSeasonNights + highSeasonNights = mix-nights)
  - ✅ Verificar que VAT Mix tenga al menos una fila completa si está activo
  - ✅ Advertencias visuales en campos específicos con auto-hide
  - ✅ **Test**: Funciona con fallback a errorMessage si no hay AppYacht.ui
  - **Rollback**: Desactivar validaciones específicas si causan falsas alarmas

---

## 📋 FASE 2: SEGURIDAD (Prioridad ALTA) - **COMPLETADO**

### ✅ 2.1 Sanitización de Datos - **COMPLETADO**
- [x] **Tarea 2.1.1**: Crear helper de sanitización
  - ✅ Archivo: `app_yacht/shared/helpers/sanitizer-helper.php`
  - ✅ Métodos para números, strings, arrays
  - ✅ **Test**: Probar con datos maliciosos simulados
  - **Rollback**: Usar sanitize_text_field() de WordPress

- [x] **Tarea 2.1.2**: Aplicar sanitización en calculate.php
  - ✅ Sanitizar todos los inputs $_POST
  - ✅ **Test**: Enviar datos con caracteres especiales
  - **Rollback**: Restaurar código original

- [x] **Tarea 2.1.3**: Extender a otros endpoints
  - ✅ Aplicar en todos los archivos AJAX
  - ✅ **Test**: Verificar funcionamiento normal
  - **Rollback**: Implementar gradualmente

- [x] **Tarea 2.1.4**: Correcciones de sanitización específicas - **COMPLETADO**
  - ✅ Aplicar `wp_kses_post()` a las firmas en `signature-functions.php` (al guardar y mostrar)
  - ✅ Cambiar `sanitize_text_field()` por `esc_url_raw()` para `yachtUrl` en `bootstrap.php`
  - ✅ Normalizar campos `to/cc/bcc` en handler AJAX de Outlook (defensa en profundidad)
  - ✅ **Test**: Verificar prevención de XSS en firmas y manejo correcto de URLs
  - **Rollback**: Revertir cambios específicos si interfieren con funcionalidad

### ✅ 2.2 Validación de Nonces Mejorada - **COMPLETADO**
- [x] **Tarea 2.2.1**: Centralizar validación de nonces
  - Implementado helper pb_verify_ajax_nonce en app_yacht/shared/php/security.php
  - Aplicado en calculate.php, calculateRelocation.php, calculatemix.php, core/bootstrap.php (handlers), template/load-template.php y módulos de Outlook
  - **Test**: Nonces funcionando y eventos de seguridad logueados correctamente
  - **Rollback**: Fallback a validación distribuida en cada endpoint si helper no existe
  - **Nota**: Mantenemos algunas verificaciones originales en signature/ y functions.php (fuera del scope yachts)

- [x] **Tarea 2.2.2**: Añadir logs de seguridad
  - ✅ Registrar intentos de acceso sin nonce válido
  - ✅ **Test**: Intentar acceso sin nonce y verificar log
  - **Rollback**: Comentar logs si generan mucho volumen

---

## 📋 FASE 3: RENDIMIENTO Y UX (Prioridad MEDIA)

### ✅ 3.1 Optimización de Requests
- [x] **Tarea 3.1.1**: Implementar debounce en inputs
  - ✅ Evitar cálculos automáticos excesivos
  - ✅ Aplicado debounce (300ms) en listeners de formatNumber en:
    - interfaz.js (addCharterRate, addExtraField)
    - template.js (addCharterRateGroup, addExtraGroup)
    - VatRateMix.js (addCountryField)
    - MixedTaxes.js (_updateVatFields)
  - ✅ Fallback a múltiples funciones debounce disponibles (pbDebounce, debounce)
  - **Test**: Escribir rápido en campos y verificar requests
  - **Rollback**: Eliminar debounce si afecta responsividad

- [x] **Tarea 3.1.2**: Cache de resultados básico
  - ✅ Cachear cálculos idénticos por sesión (sessionStorage, maxAge configurable)
  - ✅ Implementado en Calculator.js (método calculate) y calculate.js (handleCalculateButtonClick)
  - ✅ Clave: hash estable de FormData (JSON ordenado)
  - ✅ Feature flag: AppYacht.config.enableCache y AppYacht.config.cacheMaxAgeMs
  - **Test**: Repetir mismo cálculo y verificar velocidad (hit de caché)
  - **Rollback**: Desactivar flag enableCache si causa problemas

### ✅ 3.2 Feedback Visual Mejorado
- [x] **Tarea 3.2.1**: Mejorar indicadores de estado
  - ✅ Estados: loading, success, error, warning
  - ✅ Implementado helpers en `shared/js/ui.js`: `setLoading`, `notifyError`, `notifyWarning`, `notifySuccess` (expuestos en `window.AppYacht.ui`)
  - ✅ Integrado en `modules/calc/js/calculate.js`, `modules/calc/js/mix.js` y `shared/js/classes/Calculator.js` con llamadas opcionales `window.AppYacht?.ui?...`
  - ✅ Fallback a `#errorMessage` y cambios de texto/botón cuando AppYacht.ui no está disponible
  - **Test**: Probar cada estado visualmente en flujos de cálculo y mixto
  - **Rollback**: Usar alertas simples

- [x] **Tarea 3.2.2**: Validación en tiempo real
  - ✅ Delegación de eventos en `#charterForm` para `input` y `change`
  - ✅ Validación puntual de campo (`validateSingleField`) y warnings debounced (`validateFieldsWithWarnings`)
  - ✅ Cobertura de campos dinámicos (VAT Mix) y toggles (`enableMixedSeasons`, `vatRateMix`)
  - ✅ Fallback a `#errorMessage` cuando no está `AppYacht.ui`
  - **Test**: Escribir/modificar campos, activar toggles, verificar marcado y mensajes en tiempo real
  - **Rollback**: Validar solo al enviar

### ✅ 3.3 Sistema de Plantillas Inteligente - **COMPLETADO**
- [x] **Tarea 3.3.1**: Comportamiento condicional del selector de plantillas
  - ✅ Implementar lógica en `onTemplateChange()` para detectar presencia de datos
  - ✅ Sin datos: mostrar vista previa (prev.php) únicamente
  - ✅ Con datos: ejecutar creación completa automáticamente (como botón "Crear plantilla")
  - ✅ Fallback a vista previa si creación falla (validación, 401/403, etc.)
  - ✅ **Test**: Probar selector vacío vs. con datos (yachtUrl + currency + al menos una tarifa)
  - **Rollback**: Restaurar comportamiento anterior (siempre vista previa)

- [x] **Tarea 3.3.2**: Funciones helper para detección y carga
  - ✅ `hasFormData()`: detecta si hay datos suficientes para crear plantilla
  - ✅ `loadTemplatePreview()`: carga vista previa como función separada
  - ✅ Criterio: yachtUrl + currency + al menos una tarifa con baseRate/guests/nights
  - ✅ **Test**: Verificar detección correcta en diferentes estados del formulario
  - **Rollback**: Simplificar criterio si es muy restrictivo

---

## 📋 FASE 4: MANTENIBILIDAD (Prioridad MEDIA)

### ✅ 4.1 Documentación de Código
- [x] **Tarea 4.1.1**: Documentar funciones críticas
  - ✅ Añadir PHPDoc a funciones de cálculo
  - ✅ Documentado CalcService, calculate.php, calculatemix.php, calculateRelocation.php
  - ✅ PHPDoc completo en clases y métodos públicos/privados
  - ✅ **Test**: PhpDocumentor/IDE reconocen la documentación correctamente
  - **Rollback**: No hay riesgo, solo documentación

- [ ] **Tarea 4.1.2**: Crear guía de debugging
  - Documento con checklist de problemas comunes
  - **Test**: Usar guía en problema real
  - **Rollback**: No aplica

### ✅ 4.2 Refactoring Gradual
- [ ] **Tarea 4.2.1**: Extraer lógica de cálculo
  - Mover cálculos complejos a clases dedicadas
  - **Test**: Verificar que resultados son idénticos
  - **Rollback**: Restaurar lógica inline

- [ ] **Tarea 4.2.2**: Unificar estructura de respuestas
  - Estandarizar formato JSON de todas las calculadoras
  - **Test**: Verificar frontend interpreta respuestas
  - **Rollback**: Mantener formatos originales

---

## 📋 FASE 5: TESTING Y MONITOREO (Prioridad BAJA)

### ✅ 5.1 Tests Básicos
- [ ] **Tarea 5.1.1**: Tests unitarios para validadores
  - Probar casos edge de DataValidator
  - **Test**: Ejecutar tests y verificar cobertura
  - **Rollback**: Comentar tests si fallan CI

- [ ] **Tarea 5.1.2**: Tests de integración AJAX
  - Simular requests completos
  - **Test**: Verificar respuestas esperadas
  - **Rollback**: Tests opcionales, no afectan app

### ✅ 5.2 Monitoreo
- [ ] **Tarea 5.2.1**: Dashboard de salud básico
  - Página admin con métricas de errores
  - **Test**: Verificar métricas se actualizan
  - **Rollback**: Eliminar dashboard si consume recursos

---

## 🚨 PROTOCOLO DE TESTING PARA CADA TAREA

### Antes de implementar:
1. ✅ Hacer backup del archivo a modificar
2. ✅ Documentar estado actual (screenshots si aplica)
3. ✅ Tener plan de rollback específico

### Durante implementación:
1. ✅ Implementar cambio mínimo viable
2. ✅ Probar en entorno local
3. ✅ Verificar funcionalidad principal no se rompe

### Después de implementar:
1. ✅ Probar escenarios críticos:
   - Cálculo básico con datos válidos
   - Cálculo con datos edge cases
   - Manejo de errores
2. ✅ Verificar logs/console sin errores nuevos
3. ✅ Probar en diferentes navegadores si es frontend

### Si algo falla:
1. 🔄 Ejecutar rollback inmediatamente
2. 📝 Documentar el problema
3. 🔍 Investigar causa antes de reintento

---

## 📊 MÉTRICAS DE ÉXITO

### Por Fase:
- **Fase 1**: Reducir errores 500 a cero, logs informativos funcionando
- **Fase 2**: Sin vulnerabilidades de sanitización, nonces validados
- **Fase 3**: Tiempo de respuesta < 2s, UX fluida
- **Fase 4**: Código documentado, fácil de debugger
- **Fase 5**: Cobertura de tests >80%, monitoreo activo

### Indicadores de alerta:
- ⚠️ Aumento en tiempo de carga > 10%
- ⚠️ Errores JavaScript en console
- ⚠️ Usuarios reportan funcionalidad rota
- ⚠️ Logs muestran errores PHP nuevos

---

## 📝 NOTAS IMPORTANTES

1. **Nunca implementar más de una tarea por commit**
2. **Siempre probar en navegador incógnito**
3. **Documentar cualquier comportamiento inesperado**
4. **Si dudas, pregunta antes de continuar**
5. **Mantener backups de archivos críticos**

---

*Última actualización: [Fecha actual]*
*Creado por: Asistente AI*
*Revisado por: [Tu nombre]*