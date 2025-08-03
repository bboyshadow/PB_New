# Documentación Técnica: TROUBLESHOOTING - Guía de Solución de Problemas

**IMPRESCINDIBLE LEER...**

- **Tipo**: Documentación
- **Versión del Documento**: 1.0
- **Fecha de Creación**: 2025-06-26
- **Última Actualización**: 2025-06-26
- **Autor**: Equipo de Desarrollo
- **Estado de Auditoría**: Completado
- **Enlace al Índice**: 01_index.md
- **Enlace al Panel de Control**: ../../README.md

## Descripción

Esta guía proporciona soluciones a problemas comunes que pueden surgir al usar el validador de documentación.

## Índice

1. [Problemas de Instalación](#problemas-de-instalación)
2. [Errores de Validación](#errores-de-validación)
3. [Problemas de Rendimiento](#problemas-de-rendimiento)
4. [Problemas de Formato](#problemas-de-formato)
5. [Preguntas Frecuentes](#preguntas-frecuentes)

## Problemas de Instalación

### Error: Python no encontrado

**Síntomas**:
```
'python' no se reconoce como un comando interno o externo,
programa o archivo por lotes ejecutable.
```

**Solución**:
1. Verifica que Python esté instalado ejecutando `python --version` en una nueva terminal.
2. Si no está instalado, descárgalo desde [python.org](https://www.python.org/downloads/).
3. Durante la instalación, asegúrate de marcar la opción **"Add Python to PATH"**.
4. Reinicia la terminal después de la instalación.

### Error: Módulos faltantes

**Síntomas**:
```
ModuleNotFoundError: No module named 'module_name'
```

**Solución**:
1. Instala las dependencias del proyecto:
   ```
   pip install -r requirements-dev.txt
   ```
2. Si usas un entorno virtual, asegúrate de activarlo primero.

## Errores de Validación

### Error: Metadatos faltantes

**Síntomas**:
```
ERROR: Falta el campo de metadato obligatorio: autor
```

**Solución**:
1. Asegúrate de que todos los archivos Markdown tengan la cabecera completa:
   ```markdown
   # Documentación Técnica: ruta/archivo - Título
   
   **IMPRESCINDIBLE LEER...**
   
   - **Tipo**: Tipo de archivo
   - **Versión del Documento**: 1.0
   - **Fecha de Creación**: YYYY-MM-DD
   - **Última Actualización**: YYYY-MM-DD
   - **Autor**: Nombre del autor
   - **Estado de Auditoría**: ✅ Completado
   ```

### Error: Archivos faltantes

**Síntomas**:
```
ERROR: Falta el archivo requerido: 02_propósito-general.md
```

**Solución**:
1. Asegúrate de que todos los archivos requeridos estén presentes en el directorio.
2. Los archivos requeridos son:
   - 01_index.md
   - 02_propósito-general.md
   - 03_estructura-código.md
   - ... (y los demás archivos numerados)

## Problemas de Rendimiento

### La validación es muy lenta

**Posibles causas y soluciones**:

1. **Causa**: Muchos archivos para validar.
   **Solución**: Usa el parámetro `--max-depth` para limitar la profundidad del análisis.

2. **Causa**: Archivos muy grandes.
   **Solución**: Divide archivos grandes en secciones más pequeñas.

3. **Causa**: Múltiples validaciones en paralelo.
   **Solución**: Ejecuta una validación a la vez.

## Problemas de Formato

### Error: Formato de fecha incorrecto

**Síntomas**:
```
ERROR: Formato de fecha inválido en fecha_creacion. Use YYYY-MM-DD
```

**Solución**:
Asegúrate de que las fechas sigan el formato `YYYY-MM-DD`:
```
- **Fecha de Creación**: 2025-06-26
- **Última Actualización**: 2025-06-26
```

### Error: Estructura de encabezados incorrecta

**Síntomas**:
```
ERROR: Estructura de encabezados incorrecta en archivo.md
```

**Solución**:
Los encabezados deben seguir una jerarquía consistente:
```markdown
# Título Principal (Nivel 1)
## Sección (Nivel 2)
### Subsección (Nivel 3)
```

## Preguntas Frecuentes

### ¿Puedo personalizar las reglas de validación?

Sí, puedes modificar el archivo `.markdownlint.json` en la raíz del proyecto para ajustar las reglas de validación.

### ¿Cómo ignorar ciertos archivos o directorios?

Crea un archivo `.markdownlintignore` en la raíz del proyecto y lista los patrones a ignorar:
```
# Ignorar directorios
node_modules/
.vscode/

# Ignorar archivos específicos
**/temp/*.md
```

### ¿Cómo actualizar el validador?

1. Actualiza el paquete:
   ```
   pip install --upgrade doc-validator
   ```
2. Verifica la versión instalada:
   ```
   doc-validator --version
   ```

## Obtener Ayuda

Si no encuentras solución a tu problema:

1. Revisa los mensajes de error completos.
2. Verifica que estés usando la última versión del validador.
3. Consulta la documentación en [DOC/README.md](../README.md).
4. Si el problema persiste, abre un issue en el repositorio del proyecto.

## Registro de Cambios en la Guía

| Fecha       | Versión | Cambio Realizado               | Autor           |
|-------------|---------|--------------------------------|-----------------|
| 2025-06-26 | 1.0.0   | Versión inicial de la guía     | Equipo de Soporte |
| 2025-06-26 | 1.0.1   | Añadida sección de rendimiento | Equipo de Soporte |
