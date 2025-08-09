# Plan de Optimización - App Yacht
## Lista de Tareas de Mejora con Checklist de Verificación

> **IMPORTANTE**: Aplicar una tarea a la vez y verificar completamente antes de continuar con la siguiente.

---

## 🔴 TAREA 1: Eliminar clearCache() del handler AJAX
**Prioridad**: CRÍTICA
**Impacto**: Alto - Reduce solicitudes al servidor y rate limiting

### Cambios a realizar:
- [ ] Quitar `$yachtInfoService->clearCache();` del handler en `bootstrap.php`
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
- [ ] Eliminar inclusión directa de `yachtinfo.js` en `calculator.php`
- [ ] Modificar condición de enqueue en `yacht-functions.php` para incluir página del calculador
- [ ] Verificar orden de dependencias y localización de datos

### Archivos a modificar:
- `calculator.php` (eliminar tag `<script>` directo)
- `app_yacht/core/yacht-functions.php` (ampliar condición is_page_template)

### Checklist de verificación:
- [ ] No hay scripts duplicados en el HTML final (inspeccionar código fuente)
- [ ] `yachtinfo.js` se carga antes que `relocationAuto.js`
- [ ] `window.yachtInfoData` sigue siendo accesible
- [ ] La funcionalidad "Get Info" sigue funcionando
- [ ] Los campos se siguen prellenando en el calculador
- [ ] No hay errores en consola del navegador
- [ ] Solo se hace una petición AJAX por clic (verificar en Network tab)

### Rollback si falla:
```html
<!-- Restaurar en calculator.php -->
<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/yachtinfo/js/yachtinfo.js"></script>
```

---

## 🟡 TAREA 3: Sincronizar whitelist de dominios
**Prioridad**: Media
**Impacto**: Medio - Evita desalineación cliente-servidor

### Cambios a realizar:
- [ ] Centralizar lista de dominios en archivo de configuración
- [ ] Pasar lista al cliente via `wp_localize_script`
- [ ] Eliminar hardcoding en `yachtinfo.js`

### Archivos a modificar:
- `app_yacht/core/config.php` (agregar ALLOWED_DOMAINS)
- `app_yacht/core/yacht-functions.php` (localizar datos)
- `app_yacht/modules/yachtinfo/js/yachtinfo.js` (usar datos localizados)
- `app_yacht/modules/yachtinfo/yacht-info-service.php` (usar config central)

### Checklist de verificación:
- [ ] La validación de dominio funciona igual en cliente y servidor
- [ ] Los dominios permitidos se cargan desde configuración central
- [ ] Los mensajes de error por dominio inválido son consistentes
- [ ] No hay errores JavaScript por datos no definidos
- [ ] La funcionalidad de validación de URL sigue trabajando

### Rollback si falla:
```javascript
// Restaurar en yachtinfo.js
const allowedDomains = ['cyaeb.com'];
```

---

## 🟡 TAREA 4: Mejorar UX ante rate limiting
**Prioridad**: Media
**Impacto**: Medio - Mejor experiencia de usuario

### Cambios a realizar:
- [ ] Mostrar cooldown timer en el botón tras rate limit
- [ ] Deshabilitar botón temporalmente con contador regresivo
- [ ] Mostrar mensajes más informativos sobre límites

### Archivos a modificar:
- `app_yacht/modules/yachtinfo/js/yachtinfo.js` (lógica de cooldown)
- `app_yacht/modules/yachtinfo/yacht-info-service.php` (headers de rate limit)

### Checklist de verificación:
- [ ] El botón muestra tiempo restante tras rate limit
- [ ] El botón se reactiva automáticamente tras el cooldown
- [ ] Los mensajes de error son claros y en español
- [ ] No interfiere con el funcionamiento normal
- [ ] El timer se actualiza correctamente cada segundo

---

## 🟢 TAREA 5: Internacionalización de mensajes
**Prioridad**: Baja
**Impacto**: Bajo - Consistencia de idioma

### Cambios a realizar:
- [ ] Convertir strings hardcoded a funciones `__()`
- [ ] Crear archivo de traducción español
- [ ] Pasar textos localizados al JavaScript

### Archivos a modificar:
- Todos los archivos PHP con strings de usuario
- `app_yacht/modules/yachtinfo/js/yachtinfo.js`
- Crear `languages/es_ES.po`

### Checklist de verificación:
- [ ] Todos los mensajes aparecen en español
- [ ] No hay strings en inglés visibles al usuario
- [ ] La funcionalidad no se ve afectada
- [ ] Los textos son coherentes y profesionales

---

## 🟢 TAREA 6: Agregar opción "Force Refresh"
**Prioridad**: Baja
**Impacto**: Bajo - Funcionalidad adicional

### Cambios a realizar:
- [ ] Agregar checkbox "Forzar actualización" en UI
- [ ] Modificar handler para aceptar parámetro `force_refresh`
- [ ] Limpiar caché solo cuando se solicite explícitamente

### Archivos a modificar:
- `app_yacht/modules/yachtinfo/yacht-info-container.php` (UI)
- `app_yacht/core/bootstrap.php` (lógica condicional)
- `app_yacht/modules/yachtinfo/js/yachtinfo.js` (enviar parámetro)

### Checklist de verificación:
- [ ] El checkbox aparece y es funcional
- [ ] Con checkbox marcado se limpia la caché
- [ ] Sin checkbox marcado se respeta la caché
- [ ] La funcionalidad base no se afecta
- [ ] El diseño UI se mantiene coherente

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

### 🔄 En Progreso:
- [ ] _Ninguna tarea en progreso actualmente_

### ⏳ Pendiente:
- [ ] Todas las tareas listadas arriba

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
```

```bash
# Verificar logs de error PHP
tail -f /path/to/wordpress/debug.log

# Verificar rate limiting
grep "rate_limit" /path/to/wordpress/debug.log
```

---

**Última actualización**: $(date)
**Próxima tarea**: TAREA 1 - Eliminar clearCache() del handler AJAX