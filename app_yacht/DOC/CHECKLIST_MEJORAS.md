# CHECKLIST DE MEJORAS - APP YACHT

## 🎯 Objetivo
Implementar mejoras de forma fraccionada y segura, testando cada cambio antes de aplicar el siguiente.

---

## 📋 FASE 1: ESTABILIZACIÓN Y DEBUGGING (Prioridad ALTA)

### ✅ 1.1 Sistema de Logs Mejorado
- [ ] **Tarea 1.1.1**: Crear archivo `app_yacht/shared/helpers/Logger.php`
  - Implementar clase Logger básica con niveles (ERROR, WARNING, INFO, DEBUG)
  - **Test**: Verificar que se crea el archivo de log correctamente
  - **Rollback**: Eliminar archivo si hay problemas

- [ ] **Tarea 1.1.2**: Integrar Logger en `calculate.php`
  - Añadir logs en puntos críticos (inicio, validaciones, errores)
  - **Test**: Ejecutar calculadora y verificar logs generados
  - **Rollback**: Comentar líneas de log si afecta rendimiento

- [ ] **Tarea 1.1.3**: Integrar Logger en archivos AJAX críticos
  - Aplicar en `calculateRelocation.php`, `calculatemix.php`
  - **Test**: Probar cada calculadora individualmente
  - **Rollback**: Revertir archivo por archivo si es necesario

### ✅ 1.2 Validación de Datos Robusta
- [ ] **Tarea 1.2.1**: Crear validador de `charterRates`
  - Archivo: `app_yacht/shared/helpers/DataValidator.php`
  - Validar estructura, tipos de datos, rangos
  - **Test**: Enviar datos inválidos y verificar rechazo
  - **Rollback**: Usar validación anterior si falla

- [ ] **Tarea 1.2.2**: Aplicar validación en `calculate.php`
  - Reemplazar validación básica con DataValidator
  - **Test**: Probar con datos válidos e inválidos
  - **Rollback**: Restaurar validación original

- [ ] **Tarea 1.2.3**: Extender validación a otros endpoints
  - Aplicar en calculadoras de reubicación y mix
  - **Test**: Verificar funcionamiento de cada calculadora
  - **Rollback**: Implementar uno por uno, revertir si falla

### ✅ 1.3 Manejo de Errores Frontend
- [ ] **Tarea 1.3.1**: Mejorar `calculate.js` - Manejo de errores
  - Añadir try-catch en `handleCalculateButtonClick`
  - Mostrar mensajes de error específicos al usuario
  - **Test**: Simular error 500 y verificar mensaje amigable
  - **Rollback**: Mantener alert() anterior si hay problemas

- [ ] **Tarea 1.3.2**: Implementar estado de loading
  - Deshabilitar botón durante cálculo
  - Mostrar spinner o indicador visual
  - **Test**: Verificar UX durante cálculo lento
  - **Rollback**: Remover indicadores si interfieren

- [ ] **Tarea 1.3.3**: Validación frontend antes de envío
  - Verificar campos requeridos antes de AJAX
  - Validar formatos numéricos
  - **Test**: Intentar enviar formulario incompleto
  - **Rollback**: Permitir validación solo en servidor

---

## 📋 FASE 2: SEGURIDAD (Prioridad ALTA)

### ✅ 2.1 Sanitización de Datos
- [ ] **Tarea 2.1.1**: Crear helper de sanitización
  - Archivo: `app_yacht/shared/helpers/Sanitizer.php`
  - Métodos para números, strings, arrays
  - **Test**: Probar con datos maliciosos simulados
  - **Rollback**: Usar sanitize_text_field() de WordPress

- [ ] **Tarea 2.1.2**: Aplicar sanitización en calculate.php
  - Sanitizar todos los inputs $_POST
  - **Test**: Enviar datos con caracteres especiales
  - **Rollback**: Restaurar código original

- [ ] **Tarea 2.1.3**: Extender a otros endpoints
  - Aplicar en todos los archivos AJAX
  - **Test**: Verificar funcionamiento normal
  - **Rollback**: Implementar gradualmente

### ✅ 2.2 Validación de Nonces Mejorada
- [ ] **Tarea 2.2.1**: Centralizar validación de nonces
  - Crear función helper en `yacht-functions.php`
  - **Test**: Verificar que nonces siguen funcionando
  - **Rollback**: Mantener validación distribuida

- [ ] **Tarea 2.2.2**: Añadir logs de seguridad
  - Registrar intentos de acceso sin nonce válido
  - **Test**: Intentar acceso sin nonce y verificar log
  - **Rollback**: Comentar logs si generan mucho volumen

---

## 📋 FASE 3: RENDIMIENTO Y UX (Prioridad MEDIA)

### ✅ 3.1 Optimización de Requests
- [ ] **Tarea 3.1.1**: Implementar debounce en inputs
  - Evitar cálculos automáticos excesivos
  - **Test**: Escribir rápido en campos y verificar requests
  - **Rollback**: Eliminar debounce si afecta responsividad

- [ ] **Tarea 3.1.2**: Cache de resultados básico
  - Cachear cálculos idénticos por sesión
  - **Test**: Repetir mismo cálculo y verificar velocidad
  - **Rollback**: Desactivar cache si causa problemas

### ✅ 3.2 Feedback Visual Mejorado
- [ ] **Tarea 3.2.1**: Mejorar indicadores de estado
  - Estados: loading, success, error, warning
  - **Test**: Probar cada estado visualmente
  - **Rollback**: Usar alertas simples

- [ ] **Tarea 3.2.2**: Validación en tiempo real
  - Mostrar errores mientras el usuario escribe
  - **Test**: Ingresar valores inválidos y verificar feedback
  - **Rollback**: Validar solo al enviar

---

## 📋 FASE 4: MANTENIBILIDAD (Prioridad MEDIA)

### ✅ 4.1 Documentación de Código
- [ ] **Tarea 4.1.1**: Documentar funciones críticas
  - Añadir PHPDoc a funciones de cálculo
  - **Test**: Verificar que código sigue funcionando
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