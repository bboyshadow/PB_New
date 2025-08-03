# Documentación Técnica: CI/CD - Integración Continua y Despliegue Continuo

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

Este documento describe la configuración y el flujo de trabajo de integración continua para el validador de documentación.

## Visión General

Hemos configurado un flujo de trabajo de GitHub Actions que se ejecuta automáticamente en cada push o pull request que afecte a los archivos de documentación. Este flujo de trabajo:

1. Configura un entorno de Python
2. Instala las dependencias necesarias
3. Ejecuta el validador de documentación
4. Ejecuta las pruebas unitarias
5. Genera informes de cobertura
6. Falla la compilación si se encuentran errores

## Configuración

El flujo de trabajo está definido en:
```
.github/workflows/validate-docs.yml
```

### Disparadores

El flujo de trabajo se activa cuando:
- Se hace push a ramas que modifican archivos en `DOC/**`
- Se abre o actualiza un pull request que afecta a archivos en `DOC/**`
- Se activa manualmente desde la pestaña "Actions" de GitHub

## Pasos del Flujo de Trabajo

### 1. Checkout del Código

```yaml
- name: Checkout del código
  uses: actions/checkout@v3
```

### 2. Configuración de Python

Configura Python 3.10 con caché para pip:

```yaml
- name: Configurar Python
  uses: actions/setup-python@v4
  with:
    python-version: '3.10'
    cache: 'pip'
```

### 3. Instalación de Dependencias

Instala las dependencias listadas en `requirements-dev.txt`:

```yaml
- name: Instalar dependencias
  run: |
    python -m pip install --upgrade pip
    pip install -r DOC/tools/test/requirements-dev.txt
```

### 4. Validación de Documentación

Ejecuta el validador de documentación:

```yaml
- name: Ejecutar validador de documentación
  id: validate
  continue-on-error: true
  run: |
    echo "Validando documentación..."
    python DOC/tools/doc-validator.py DOC/tools/test --lenient

    if [ $? -ne 0 ]; then
      echo "::error::Se encontraron errores en la validación de documentación"
      exit 1
    fi
```

### 5. Ejecución de Pruebas Unitarias

Ejecuta las pruebas unitarias con cobertura:

```yaml
- name: Ejecutar pruebas unitarias
  run: |
    echo "Ejecutando pruebas unitarias..."
    cd DOC/tools/test && python -m pytest test_validation.py -v --cov=. --cov-report=xml
```

### 6. Subida de Cobertura

Sube los resultados de cobertura a Codecov:

```yaml
- name: Subir resultados de cobertura
  if: always()
  uses: codecov/codecov-action@v3
  with:
    file: ./coverage.xml
    flags: unittests
    name: codecov-umbrella
    fail_ci_if_error: false
    verbose: true
```

## Monitoreo y Notificaciones

### Estado de las Ejecuciones

Puedes ver el estado de las ejecuciones en:
```
https://github.com/tu-usuario/tu-repo/actions/workflows/validate-docs.yml
```

### Notificaciones

Las notificaciones están configuradas para enviarse a:
- Correo electrónico del autor del commit
- Canal de Slack del equipo (si está configurado)
- Comentarios en los pull requests

## Configuración de Entornos

### Variables de Entorno

| Variable           | Descripción                                  | Requerido | Valor por Defecto |
|--------------------|----------------------------------------------|-----------|-------------------|
| `PYTHON_VERSION`  | Versión de Python a usar                     | No        | 3.10             |
| `REQUIREMENTS`    | Ruta al archivo de requisitos               | No        | DOC/tools/test/requirements-dev.txt |


### Secretos

| Secreto         | Descripción                                  |
|-----------------|----------------------------------------------|
| `CODECOV_TOKEN` | Token para subir métricas de cobertura      |


## Solución de Problemas

### La validación falla inesperadamente

1. Verifica los logs de GitHub Actions para ver el error específico
2. Ejecuta el validador localmente para depurar:
   ```bash
   python DOC/tools/doc-validator.py DOC/tools/test --lenient
   ```
3. Revisa que todos los archivos tengan el formato correcto

### Las pruebas fallan en CI pero no localmente

1. Asegúrate de usar la misma versión de Python localmente
2. Instala las mismas versiones de dependencias:
   ```bash
   pip install -r DOC/tools/test/requirements-dev.txt
   ```
3. Verifica las variables de entorno específicas del entorno de CI

## Personalización

### Añadir Nuevas Pruebas

1. Añade nuevos archivos de prueba en `DOC/tools/test/` con el prefijo `test_`
2. Asegúrate de que las pruebas sigan la convención de nombres de pytest
3. Las pruebas se ejecutarán automáticamente en el siguiente push

### Modificar las Reglas de Validación

1. Edita el archivo `DOC/tools/doc-validator.py`
2. Añade o modifica las reglas según sea necesario
3. Añade pruebas para las nuevas reglas

## Meores Prácticas

- Mantén las pruebas rápidas y aisladas
- Usa fixtures de pytest para configuraciones comunes
- Documenta cualquier asunción en las pruebas
- Mantén actualizadas las dependencias
- Revisa regularmente los informes de cobertura

## Soporte

Para problemas con la configuración de CI/CD:
1. Revisa la documentación de [GitHub Actions](https://docs.github.com/es/actions)
2. Consulta los logs de ejecución
3. Abre un issue en el repositorio si necesitas ayuda
