# Guía para Documentar Archivos 04_dependencias.md

## 🔴 CONVENCIONES DE NOMENCLATURA DE CARPETAS

**IMPORTANTE: SIGA ESTRICTAMENTE ESTAS REGLAS PARA EVITAR ERRORES**

1. **NUNCA** cree carpetas con extensión (como `.js`, `.php`, `.css`, etc.)
2. La documentación de un archivo debe ir en una carpeta con el **nombre base** del archivo, **sin extensión**
   - ✅ CORRECTO: `DOC/shared/js/utils/debounce/`
   - ❌ INCORRECTO: `debounce.js/`, `debounce.php/`, `debounce.css/`
3. Si encuentra carpetas con extensión, deben ser renombradas siguiendo esta convención
4. La estructura de carpetas de documentación debe reflejar exactamente la estructura del código fuente, pero sin las extensiones de archivo

## Estructura Obligatoria

Cada archivo `04_dependencias.md` DEBE seguir esta estructura exacta:

```markdown
# Documentación Técnica: [ruta/relativa/al/archivo] - Dependencias

**IMPORTANTE: LEA ESTE DOCUMENTO COMPLETO PARA COMPRENDER LAS DEPENDENCIAS DEL MÓDULO. NO OMITA NINGUNA SECCIÓN.**

- **Tipo**: [Backend (PHP) | Frontend (JS) | CSS | Otro]
- **Módulo**: [Nombre del módulo, ej: "Core", "Mail", "Template"]
- **Archivo**: `[ruta/relativa/al/archivo]`
- **Versión del Documento**: [ej: 1.0.0]
- **Fecha de Creación**: YYYY-MM-DD
- **Última Actualización**: YYYY-MM-DD
- **Autor**: [Nombre del autor]
- **Estado de Auditoría**: [✅ Completado | 🔍 En Progreso]
- **Enlace al Índice**: [01_index.md](01_index.md)
- **Enlace al Panel de Control**: [ruta relativa a README.md principal]

## 1. Dependencias

### 1.1 Dependencias Internas (Entrantes)

| Ruta | Tipo | Descripción |
|------|------|-------------|
| `ruta/al/archivo.ext` | PHP/JS/CSS | Descripción de la dependencia |

### 1.2 Dependencias Internas (Salientes)

| Ruta | Tipo | Descripción |
|------|------|-------------|
| `ruta/al/archivo.ext` | PHP/JS/CSS | Descripción de la dependencia |

### 1.3 Dependencias Externas

| Nombre | Versión | Propósito |
|--------|---------|-----------|
| Librería | 1.0.0 | Descripción del uso |

### 1.4 Conexiones Implícitas

| Origen | Destino | Tipo | Descripción |
|--------|---------|------|-------------|
| `archivo_origen` | `archivo_destino` | Hook/Evento | Descripción de la conexión |
```

## Reglas de Documentación

1. **Metadatos Obligatorios**:
   - Todos los metadatos deben estar presentes
   - Las fechas en formato YYYY-MM-DD
   - Rutas entre backticks (`)
   - Versiones con formato semántico (ej: 1.0.0)

2. **Secciones Obligatorias**:
   - Dependencias Internas (Entrantes)
   - Dependencias Internas (Salientes)
   - Dependencias Externas
   - Conexiones Implícitas

3. **Formato de Tablas**:
   - Usar formato de tablas Markdown
   - Incluir encabezados de columna
   - Mantener la alineación
   - Usar rutas relativas al proyecto

4. **Validación**:
   - Ejecutar el validador: `DOC\tools\py\python.exe tools\scripts\validate_dependencies.py`
   - Corregir TODAS las advertencias y errores
   - Verificar que no falten secciones

## Ejemplo de Archivo Válido

```markdown
# Documentación Técnica: core/yacht-functions.php - Dependencias

**IMPORTANTE: LEA ESTE DOCUMENTO COMPLETO PARA COMPRENDER LAS DEPENDENCIAS DEL MÓDULO. NO OMITA NINGUNA SECCIÓN.**

- **Tipo**: Backend (PHP)
- **Módulo**: Core
- **Archivo**: `core/yacht-functions.php`
- **Versión del Documento**: 1.0.0
- **Fecha de Creación**: 2024-01-01
- **Última Actualización**: 2024-06-29
- **Autor**: Equipo de Desarrollo
- **Estado de Auditoría**: 🔍 En Progreso
- **Enlace al Índice**: [01_index.md](01_index.md)
- **Enlace al Panel de Control**: ../../../../../../README.md

## 1. Dependencias

### 1.1 Dependencias Internas (Entrantes)

| Ruta | Tipo | Descripción |
|------|------|-------------|
| `shared/php/utils.php` | PHP | Utilidades generales |
| `shared/php/security.php` | PHP | Funciones de seguridad |

### 1.2 Dependencias Internas (Salientes)

| Ruta | Tipo | Descripción |
|------|------|-------------|
| `modules/calc/php/calculate.php` | PHP | Cálculos básicos |
| `modules/template/php/load-template.php` | PHP | Carga de plantillas |

### 1.3 Dependencias Externas

| Nombre | Versión | Propósito |
|--------|---------|-----------|
| WordPress | 6.0+ | Sistema base |
| PHP | 7.4+ | Lenguaje de programación |

### 1.4 Conexiones Implícitas

| Origen | Destino | Tipo | Descripción |
|--------|---------|------|-------------|
| `core/yacht-functions.php` | `wp-content/plugins/app-yacht/` | Escritura | Almacenamiento de configuración |
```

## Proceso de Validación

1. Verificar que todos los metadatos estén presentes
2. Comprobar que todas las secciones obligatorias existen
3. Validar el formato de las tablas
4. Asegurar que las rutas sean correctas
5. Ejecutar el validador y corregir errores

## Notas Importantes

- No modificar manualmente las fechas de actualización, usar la fecha actual
- Mantener el formato consistente en todos los archivos
- Documentar TODAS las dependencias, sin excepción
- Usar términos claros y descriptivos
- Mantener el orden alfabético en las tablas cuando sea posible
