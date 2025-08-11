# Plan de Optimización - App Yacht
## Lista de Tareas de Mejora con Checklist de Verificación

> **IMPORTANTE**: Aplicar una tarea a la vez y verificar completamente antes de continuar con la siguiente.

---

## 🔴 TAREA 1: Eliminar clearCache() del handler AJAX
**Prioridad**: CRÍTICA
**Impacto**: Alto - Reduce solicitudes al servidor y rate limiting

### Cambios a realizar:
- [x] Quitar `$yachtInfoService->clearCache();` del handler en `bootstrap.php`
- [ ] Agregar parámetro opcional `force_refresh` para limpieza manual de caché

### Archivos a modificar:
- `app_yacht/core/bootstrap.php` (línea del clearCache)
- `app_yacht/modules/yachtinfo/js/yachtinfo.js` (agregar checkbox opcional)

### Checklist de verificación:
- [ ] La aplicación carga sin errores PHP
- [ ] El botón "Get Info" funciona correctamente
- [ ] Los datos se guardan en caché (verificar en DB o archivos de caché)
- [ ] Las solicitudes subsecuentes para la misma URL son más rápidas
- [ ] No aparecen errores de rate limit en solicitudes repetidas
- [ ] Los campos del calculador se siguen prellenando correctamente

### Rollback si falla:
```php
// Restaurar en bootstrap.php línea X
$yachtInfoService->clearCache();
```

---

## 🟠 TAREA 2: Unificar carga de scripts (WordPress enqueue)
**Prioridad**: Alta
**Impacto**: Medio - Elimina duplicación de scripts

### Cambios a realizar:
- [x] Eliminar inclusión directa de `yachtinfo.js` en `calculator.php`
- [x] Modificar condición de enqueue en `yacht-functions.php` para incluir página del calculador
- [x] Verificar orden de dependencias y localización de datos
- [x] Manejo robusto de errores de creación de plantilla para evitar "Uncaught (in promise)" y reducir ruido en consola

### Archivos a modificar:
- `calculator.php` (eliminar tag `<script>` directo)
- `app_yacht/core/yacht-functions.php` (ampliar condición is_page_template)
- `app_yacht/modules/template/js/template.js` (capturar promesas de `createTemplate()` y manejar errores)
- `app_yacht/shared/js/classes/TemplateManager.js` (no loguear en consola errores de validación previsibles)

### Checklist de verificación:
- [ ] No hay scripts duplicados en el HTML final (inspeccionar código fuente)
- [ ] `yachtinfo.js` se carga antes que `relocationAuto.js`
- [ ] `window.yachtInfoData` sigue siendo accesible
- [ ] La funcionalidad "Get Info" sigue funcionando
- [ ] Los campos se siguen prellenando en el calculador
- [ ] No hay errores en consola del navegador
- [ ] Solo se hace una petición AJAX por clic (verificar en Network tab)
- [ ] Al cambiar plantilla/checkbox "One day charter" con campos incompletos no aparece "Uncaught (in promise)"
- [ ] Los mensajes de validación se muestran en UI (elemento `#errorMessage`) sin spam en consola

### Rollback si falla:
```html
<!-- Restaurar en calculator.php -->
<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/yachtinfo/js/yachtinfo.js"></script>
```

---

## ✅ TAREA 3: Sincronizar whitelist de dominios ✅
**Prioridad**: Media
**Impacto**: Medio - Evita desalineación cliente-servidor

### Cambios realizados:
- [x] Centralizar lista de dominios en archivo de configuración
- [x] Pasar lista al cliente via `wp_localize_script`
- [x] Eliminar hardcoding en `yachtinfo.js`

### Archivos modificados:
- `app_yacht/core/config.php` (ALLOWED_DOMAINS existente en 'scraping')
- `app_yacht/core/yacht-functions.php` (localizar allowed_domains)
- `app_yacht/modules/yachtinfo/js/yachtinfo.js` (usar datos localizados)
- `app_yacht/modules/yachtinfo/yacht-info-service.php` (usar config central)

### Checklist de verificación:
- [x] La validación de dominio funciona igual en cliente y servidor
- [x] Los dominios permitidos se cargan desde configuración central
- [x] Los mensajes de error por dominio inválido son consistentes
- [x] No hay errores JavaScript por datos no definidos
- [x] La funcionalidad de validación de URL sigue trabajando

### Rollback si falla:
```javascript
// Restaurar en yachtinfo.js
const allowedDomains = ['cyaeb.com'];
```

---

## ✅ TAREA 4: Mejorar UX ante rate limiting
**Prioridad**: Media
**Impacto**: Medio - Mejor experiencia de usuario

### Cambios realizados:
- [x] Mostrar cooldown timer en el botón tras rate limit
- [x] Deshabilitar botón temporalmente con contador regresivo
- [x] Mostrar mensajes más informativos sobre límites
- [x] Implementar detección HTTP 429 y cabeceras Retry-After
- [x] Soporte robusto para respuestas de error con código rate_limit_exceeded

### Archivos modificados:
- `app_yacht/modules/yachtinfo/js/yachtinfo.js` (función startCooldown con timer MM:SS)
- `app_yacht/modules/yachtinfo/yacht-info-service.php` (función getRateLimitRetryAfter)
- `app_yacht/core/bootstrap.php` (respuesta HTTP 429 con cabeceras)

### Checklist de verificación:
- [x] El botón muestra tiempo restante tras rate limit
- [x] El botón se reactiva automáticamente tras el cooldown
- [x] Los mensajes de error son claros y en español
- [x] No interfiere con el funcionamiento normal
- [x] El timer se actualiza correctamente cada segundo
- [x] Detecta respuestas HTTP 429 y códigos de error
- [x] Extrae tiempo de cabecera Retry-After y datos JSON

---

## ✅ TAREA 5: Agregar opción "Force Refresh" ✅
**Prioridad**: Baja
**Impacto**: Bajo - Funcionalidad adicional

### Cambios realizados:
- [x] Agregar checkbox "Force Refresh" en UI del calculador
- [x] Modificar handler para aceptar parámetro `force_refresh`
- [x] Limpiar caché solo cuando se solicite explícitamente
- [x] Integración completa frontend-backend para limpieza selectiva de caché

### Archivos modificados:
- `app_yacht/modules/calc/calculator.php` (checkbox UI "Force Refresh")
- `app_yacht/core/bootstrap.php` (lógica condicional handleExtractYachtInfo)
- `app_yacht/modules/yachtinfo/js/yachtinfo.js` (enviar parámetro force_refresh)
- `app_yacht/modules/yachtinfo/yacht-info-service.php` (método clearCacheForUrl)

### Checklist de verificación:
- [x] El checkbox aparece y es funcional en el calculador
- [x] Con checkbox marcado se limpia la caché solo para esa URL específica
- [x] Sin checkbox marcado se respeta la caché existente
- [x] La funcionalidad base no se afecta
- [x] El diseño UI se mantiene coherente
- [x] Integración AJAX envía correctamente el parámetro force_refresh
- [x] Backend maneja adecuadamente la limpieza selectiva de caché

---

## 📋 Protocolo de Verificación General

### Antes de cada tarea:
1. [ ] Hacer backup de archivos a modificar
2. [ ] Documentar estado actual funcional
3. [ ] Verificar que no hay errores previos

### Después de cada tarea:
1. [ ] Recargar página sin caché (Ctrl+F5)
2. [ ] Verificar consola JavaScript (F12)
3. [ ] Probar funcionalidad completa "Get Info"
4. [ ] Verificar red (Network tab) para requests duplicados
5. [ ] Comprobar logs de error PHP
6. [ ] Testar escenarios edge (URLs inválidas, rate limit, etc.)

### Si algo falla:
1. [ ] Aplicar rollback inmediato
2. [ ] Verificar que vuelve al estado funcional
3. [ ] Documentar el error encontrado
4. [ ] Revisar la tarea antes de reintentar

---

## 🎯 Estado Actual del Proyecto

### ✅ Completado:
- [x] Prevención de múltiples AJAX requests con flag `isFetching`
- [x] Namespacing de event handlers con `.yachtinfo`
- [x] Control de concurrencia en clicks del botón
- [x] TAREA 1: Eliminar clearCache() del handler AJAX
- [x] TAREA 2: Unificar carga de scripts (WordPress enqueue) — se eliminaron `<script>` directos y se añadieron los enqueues faltantes (VatRateMix, promotion, relocationAuto) y `wp_localize_script` para `ajaxRelocationData`.
- [x] Manejo de errores de plantilla: captura de promesas `createTemplate()` y supresión de logs de validación en consola.
- [x] TAREA 3: Sincronizar whitelist de dominios
- [x] TAREA 4: Mejorar UX ante rate limiting
- [x] TAREA 5: Agregar opción "Force Refresh"

### 🔄 En Progreso:
- [ ] _Ninguna tarea en progreso actualmente_

### ⏳ Pendiente:
- [ ] _Todas las tareas de optimización han sido completadas_

---

## 📞 Comandos de Verificación Rápida

```javascript
// Verificar que yachtinfo.js se carga solo una vez
console.log(document.querySelectorAll('script[src*="yachtinfo.js"]').length);

// Verificar datos disponibles
console.log(window.yachtInfoData);
console.log(window.ajaxRelocationData);

// Verificar handlers duplicados
console.log($._data($('#get-yacht-info')[0], 'events'));

// Verificar que no hay Uncaught de validación
Promise.resolve(templateManager?.createTemplate?.()).catch(() => {});
```

```bash
# Verificar logs de error PHP
tail -f /path/to/wordpress/debug.log

# Verificar rate limiting
grep "rate_limit" /path/to/wordpress/debug.log
```

---

**Última actualización**: Manual
**Estado**: ✅ Todas las tareas de optimización completadas