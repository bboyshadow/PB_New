# Documentación Técnica: README - Pruebas del Validador

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

Este directorio contiene pruebas para el validador de documentación técnica (`doc-validator.py`).

## Estructura del Directorio

```
DOC/tools/test/
├── core/
│   └── example-file/         # Documentación de ejemplo para pruebas
│       ├── 01_index.md
│       ├── 02_propósito-general.md
│       ├── ...
│       └── 11_historial.md
├── test_validation.py        # Pruebas unitarias
└── README.md                 # Este archivo
```

## Ejecutar las Pruebas

Para ejecutar las pruebas, sigue estos pasos:

1. Asegúrate de tener Python 3.8+ instalado.
2. Instala las dependencias de desarrollo:
   ```bash
   pip install -r requirements-dev.txt
   ```
3. Ejecuta las pruebas:
   ```bash
   python -m unittest test_validation.py -v
   ```

## Casos de Prueba

### 1. Documentación Completa
- **Propósito**: Verificar que el validador acepta documentación completa y correcta.
- **Archivos**: Todos los archivos en `core/example-file/`.
- **Resultado Esperado**: Validación exitosa sin errores.

### 2. Archivos Faltantes
- **Propósito**: Verificar que el validador detecta archivos requeridos faltantes.
- **Archivos**: Solo `01_index.md`.
- **Resultado Esperado**: La validación falla indicando los archivos faltantes.

### 3. Metadatos Inválidos
- **Propósito**: Verificar que el validador detecta metadatos faltantes o inválidos.
- **Archivos**: `01_index.md` con metadatos incompletos.
- **Resultado Esperado**: La validación falla indicando los metadatos inválidos.

## Cómo Añadir Más Pruebas

1. Añade nuevos métodos de prueba en `test_validation.py`.
2. Crea archivos de prueba en subdirectorios de `test/` según sea necesario.
3. Ejecuta las pruebas para verificar el comportamiento.

## Depuración

Para depurar las pruebas, puedes usar el módulo `pdb`:

```bash
python -m pdb test_validation.py
```

O configurar puntos de interrupción en tu IDE favorito.

## Integración Continua

Estas pruebas pueden integrarse en un sistema de CI/CD. Un ejemplo de configuración para GitHub Actions:

```yaml
name: Validar Documentación

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    - name: Configurar Python
      uses: actions/setup-python@v2
      with:
        python-version: '3.8'
    - name: Instalar dependencias
      run: |
        python -m pip install --upgrade pip
        pip install -r requirements-dev.txt
    - name: Ejecutar pruebas
      run: |
        python -m unittest test_validation.py -v
    - name: Validar documentación
      run: |
        python DOC/tools/doc-validator.py DOC/tools/test --lenient
```

## Recursos

- [Documentación de Python unittest](https://docs.python.org/3/library/unittest.html)
- [Guía de estilo de documentación](DOC/README.md)
- [Especificación del validador](DOC/tools/README.md)
