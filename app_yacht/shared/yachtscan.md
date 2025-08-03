# Implementación de YachtScan: Reutilización de Código y Optimización

## Descripción
YachtScan centraliza la extracción de información de yates en `shared`, reutilizando el código de `extraerInformacionYate` para eliminar duplicación. Al mover esto a `shared/php/yachtscan.php` y usar un array global, eliminamos el proceso de scraping de template, permitiendo que use directamente el global. Esto ahorra pasos al usuario, ya que la funcionalidad ahora pasa por core y shared, inicializándose tempranamente en `core/app-yacht.php`.

## Objetivos Cumplidos
- Reutilizar `extraerInformacionYate` moviéndola a `shared/php/yachtscan.php`.
- Crear array global `$yachtInfoGlobal` accesible app-wide.
- Integrar con core para carga temprana.
- Eliminar scraping en template, simplificando flujo y ahorrando pasos.

## Cambios Realizados

### Estructura de Archivos
- Creado `shared/php/yachtscan.php` con función y lógica global.
- Actualizado `core/app-yacht.php` con `require_once` para `yachtscan.php`.
- Refactorizado `load-template.php`: removida función, agregada llamada a `initYachtScan` y uso de global.

### Reutilización y Extracción de Datos
- Función movida a `yachtscan.php` sin cambios mayores.
- `initYachtScan($yachtUrl)` setea `$yachtInfoGlobal`.
- Reutiliza `buildYachtInfoArray` donde necesario.
- Errores manejados con defaults.

### Globalización e Integración con Core
- Global declarado en `yachtscan.php`.
- Core carga `yachtscan.php` temprano, permitiendo inicialización basada en inputs.
- Shared manejado por core, asegurando disponibilidad.

### Actualización en Template y Uso
- `load-template.php` ahora requiere `yachtscan.php`, llama `initYachtScan` y asigna `$yachtInfo = $yachtInfoGlobal`.
- Elimina scraping local, usando global directamente.
- Ahorro: Usuario ingresa URL una vez; info global evita repeticiones.

### Pruebas y Validación
- Verificar global en core y uso en template.
- Confirmar no scraping en template.
- Testear flujo: URL input → global set → template usa global.
- Actualizaciones documentadas aquí.

## Beneficios
- Reutilización: Código centralizado en shared.
- Optimización: Elimina pasos redundantes en template.
- Eficiencia: Funcionalidad pasa por core/shared, mejorando mantenimiento.

## Riesgos y Consideraciones
- Dependencias verificadas (DOMDocument, cURL).
- Casos sin URL: global fallback.
- Seguridad: Validación de URLs en core.
- Actualizar docs para nuevo flujo global.