
# 🚀 Guía de Migración - App Yacht v2.0.0

## 📋 Resumen de Cambios

La refactorización de App Yacht v2.0.0 introduce una **arquitectura limpia** manteniendo **100% de compatibilidad** con la versión anterior.

### ✅ Lo que NO ha cambiado

- **Interfaz de usuario** - Toda la UI funciona igual
- **Endpoints AJAX** - Mismas URLs y respuestas
- **Archivos de plantillas** - Templates existentes funcionan
- **Configuración de usuario** - No requiere reconfiguración
- **Integración con WordPress** - Hooks y filtros mantienen compatibilidad

### 🆕 Lo que se ha mejorado

- **Arquitectura interna** más limpia y mantenible
- **Performance** optimizada con caché inteligente  
- **Seguridad** mejorada con validación robusta
- **Escalabilidad** preparada para futuras funcionalidades
- **Debugging** más fácil con logs estructurados

## 🔄 Proceso de Migración

### Paso 1: Backup de Seguridad

```bash
# Crear backup del tema completo
cp -r /ruta/al/tema /ruta/al/backup/tema_backup_$(date +%Y%m%d)

# Backup específico de app_yacht
cp -r app_yacht app_yacht_backup_v1
```

### Paso 2: Aplicar la Refactorización

1. **Copiar nuevos archivos**:
   - `core/bootstrap.php`
   - `core/container.php`  
   - `core/config.php`
   - `shared/interfaces/`
   - `shared/helpers/`
   - `modules/*/servicios.php`

2. **Actualizar archivos existentes**:
   - `core/app-yacht.php` (punto de entrada)
   - `functions.php` (carga del bootstrap)

3. **Mantener archivos UI**:
   - `modules/calc/calculator.php`
   - `modules/template/template.php`
   - `modules/mail/mail.php`
   - Todos los archivos JavaScript y CSS

### Paso 3: Verificación Post-Migración

#### Test Básico de Funcionamiento

```php
// Verificar que la nueva arquitectura se carga
if (class_exists('AppYachtBootstrap')) {
    echo "✅ Nueva arquitectura cargada correctamente";
} else {
    echo "❌ Error: Bootstrap no encontrado";
}

// Verificar servicios
$container = AppYachtBootstrap::getContainer();
$services = $container->getRegisteredServices();
echo "Servicios registrados: " . implode(', ', $services);
```

#### Test de Funcionalidad

1. **Calculadora**:
   - Abrir página de App Yacht
   - Realizar un cálculo básico
   - Verificar que el resultado es correcto

2. **Templates**:
   - Seleccionar un template
   - Generar vista previa
   - Crear template completo

3. **Email**:
   - Enviar email de prueba
   - Verificar conexión Outlook (si estaba configurada)

## 🛠️ Resolución de Problemas

### Error: "Class 'AppYachtBootstrap' not found"

**Causa**: El bootstrap no se está cargando correctamente.

**Solución**:
```php
// Verificar en functions.php
require_once get_template_directory() . '/app_yacht/core/bootstrap.php';

// Verificar permisos de archivos
chmod 644 app_yacht/core/bootstrap.php
```

### Error: "Service 'xxx' not registered"

**Causa**: Un servicio no está registrado en el contenedor.

**Solución**:
```php
// Verificar en bootstrap.php que el servicio esté registrado
$container->register('service_name', function() {
    return new ServiceClass();
});
```

### Error: Template no encontrado

**Causa**: Ruta de templates incorrecta en configuración.

**Solución**:
```php
// Verificar en config.php
'templates_path' => get_template_directory() . '/app_yacht/modules/template/templates/',
```

### JavaScript/AJAX no funciona

**Causa**: Los endpoints AJAX han cambiado o no se cargan correctamente.

**Solución**:
1. Verificar que `yacht-functions.php` legacy se esté cargando
2. Comprobar en browser developer tools las llamadas AJAX
3. Revisar logs de PHP para errores

### Performance degradada

**Causa**: Caché no habilitada o configuración subóptima.

**Solución**:
```php
// En config.php, habilitar caché
'cache' => [
    'enabled' => true,
    'default_duration' => 3600
]
```

## 📊 Verificación de Migración Exitosa

### Checklist Post-Migración

- [ ] ✅ Aplicación carga sin errores
- [ ] ✅ Calculadora funciona correctamente
- [ ] ✅ Templates se generan normalmente  
- [ ] ✅ Email se envía correctamente
- [ ] ✅ No hay errores en logs de PHP
- [ ] ✅ Performance igual o mejor que antes
- [ ] ✅ Outlook sigue funcionando (si estaba configurado)

### Test de Regresión

```php
// Script de test básico
function test_app_yacht_migration() {
    $tests = [];
    
    // Test 1: Bootstrap carga
    $tests['bootstrap'] = class_exists('AppYachtBootstrap');
    
    // Test 2: Servicios disponibles
    try {
        $container = AppYachtBootstrap::getContainer();
        $tests['services'] = count($container->getRegisteredServices()) >= 4;
    } catch (Exception $e) {
        $tests['services'] = false;
    }
    
    // Test 3: Configuración accesible
    $tests['config'] = !is_null(AppYachtConfig::get('app'));
    
    // Test 4: Helpers funcionan
    $tests['helpers'] = class_exists('CacheHelper') && class_exists('ValidatorHelper');
    
    return $tests;
}

// Ejecutar tests
$results = test_app_yacht_migration();
foreach ($results as $test => $passed) {
    echo $test . ': ' . ($passed ? '✅ PASS' : '❌ FAIL') . "\n";
}
```

## 🔧 Configuración Avanzada

### Personalizar Configuración

```php
// Agregar configuraciones personalizadas
add_action('init', function() {
    AppYachtConfig::set('custom_module', [
        'option1' => 'value1',
        'option2' => 'value2'
    ]);
});
```

### Agregar Servicio Personalizado

```php
// En bootstrap.php
$container->register('mi_servicio', function() use ($config) {
    return new MiServicioPersonalizado($config['mi_modulo']);
});
```

### Configurar Caché Personalizada

```php
// Configuración específica de caché
AppYachtConfig::set('cache', [
    'enabled' => true,
    'default_duration' => 7200, // 2 horas
    'prefix' => 'mi_app_',
    'cleanup_interval' => 24 * HOUR_IN_SECONDS
]);
```

## 📈 Monitoreo Post-Migración

### Logs a Vigilar

```bash
# Errores críticos
grep "CRITICAL\|FATAL" /var/log/wordpress/debug.log

# Errores de App Yacht
grep "AppYacht" /var/log/wordpress/debug.log

# Performance
grep "slow query\|timeout" /var/log/wordpress/debug.log
```

### Métricas de Performance

```php
// Tiempo de carga de página
add_action('wp_footer', function() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        echo "<!-- Page load time: " . round($time, 3) . "s -->";
    }
});
```

## 🔄 Rollback Plan

### Si necesitas volver a la versión anterior:

1. **Restaurar backup**:
```bash
# Restaurar tema completo
rm -rf /ruta/al/tema
cp -r /ruta/al/backup/tema_backup_YYYYMMDD /ruta/al/tema
```

2. **Restaurar solo app_yacht**:
```bash
# Restaurar solo la carpeta app_yacht
rm -rf app_yacht
cp -r app_yacht_backup_v1 app_yacht
```

3. **Revertir functions.php**:
```php
// Cambiar de nuevo a:
require_once get_template_directory() . '/app_yacht/core/yacht-functions.php';
```

## 📞 Soporte y Ayuda

### Antes de solicitar soporte:

1. ✅ Revisar esta guía completamente
2. ✅ Ejecutar tests de verificación  
3. ✅ Revisar logs de error
4. ✅ Probar en entorno de desarrollo
5. ✅ Crear backup antes de hacer cambios

### Información a incluir en solicitud de soporte:

- Versión de WordPress
- Versión de PHP
- Configuración del servidor
- Logs de error relevantes
- Pasos para reproducir el problema
- Resultado de tests de verificación

### Contacto

- 📧 Email técnico: [email de soporte]
- 📱 Teléfono urgencias: [teléfono]
- 💬 Chat técnico: [enlace al chat]

---

**La migración a App Yacht v2.0.0 mejora significativamente la arquitectura interna manteniendo toda la funcionalidad existente. Sigue esta guía paso a paso para una migración exitosa.**
