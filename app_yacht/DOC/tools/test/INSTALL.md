# Documentación Técnica: INSTALL - Guía de Instalación

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

Esta guía explica cómo configurar el entorno necesario para ejecutar el validador de documentación y sus pruebas.

## Requisitos del Sistema

- **Sistema Operativo**: Windows 10/11, macOS 10.15+, o Linux
- **Python**: Versión 3.8 o superior
- **Espacio en Disco**: Mínimo 100 MB de espacio libre
- **Memoria RAM**: Mínimo 2 GB recomendados

## Instalación de Python

### Windows

1. Descarga el instalador de Python desde [python.org](https://www.python.org/downloads/)
2. Ejecuta el instalador y asegúrate de marcar la opción **"Add Python to PATH"**
3. Haz clic en "Install Now" y espera a que termine la instalación
4. Verifica la instalación abriendo una nueva terminal y ejecutando:
   ```
   python --version
   ```

### macOS

1. Instala Homebrew si no lo tienes:
   ```bash
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```
2. Instala Python:
   ```bash
   brew install python
   ```
3. Verifica la instalación:
   ```bash
   python3 --version
   ```

### Linux (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install python3 python3-pip python3-venv
```

## Configuración del Entorno Virtual

Se recomienda usar un entorno virtual para aislar las dependencias del proyecto.

### Windows

```powershell
# Crear entorno virtual
python -m venv .venv

# Activar el entorno virtual
.venv\Scripts\activate

# Instalar dependencias
pip install -r requirements-dev.txt
```

### macOS/Linux

```bash
# Crear entorno virtual
python3 -m venv .venv

# Activar el entorno virtual
source .venv/bin/activate

# Instalar dependencias
pip install -r requirements-dev.txt
```

## Verificación de la Instalación

Para verificar que todo está configurado correctamente:

```bash
# Verificar versión de Python
python --version

# Verificar instalación de pip
pip --version

# Verificar dependencias instaladas
pip list
```

## Configuración del Editor de Código

### Visual Studio Code (Recomendado)

1. Instala VS Code desde [code.visualstudio.com](https://code.visualstudio.com/)
2. Instala las siguientes extensiones:
   - Python (Microsoft)
   - Pylance
   - Python Docstring Generator
   - Markdown All in One
   - markdownlint

### Configuración Recomendada

Agrega esta configuración a tu archivo `settings.json` de VS Code:

```json
{
    "python.pythonPath": ".venv/bin/python",
    "python.linting.enabled": true,
    "python.linting.pylintEnabled": true,
    "python.formatting.provider": "black",
    "editor.formatOnSave": true,
    "editor.codeActionsOnSave": {
        "source.organizeImports": true
    }
}
```

## Solución de Problemas Comunes

### Python no se reconoce como comando

- Verifica que Python esté agregado al PATH del sistema
- Reinicia la terminal después de instalar Python
- En Windows, asegúrate de marcar "Add Python to PATH" durante la instalación

### Error al instalar dependencias

- Actualiza pip: `python -m pip install --upgrade pip`
- Asegúrate de tener los compiladores necesarios instalados
- En Windows, instala las herramientas de compilación de C++

### Problemas con el entorno virtual

- Si ves errores de permisos, intenta ejecutar la terminal como administrador
- Asegúrate de estar en el directorio correcto del proyecto
- Si el entorno virtual está dañado, elimínalo y créalo de nuevo

## Recursos Adicionales

- [Documentación de Python](https://docs.python.org/3/)
- [Guía de Entornos Virtuales](https://docs.python.org/3/tutorial/venv.html)
- [Documentación de pip](https://pip.pypa.io/en/stable/)

## Siguientes Pasos

Una vez completada la instalación, consulta el archivo [README.md](README.md) para ejecutar las pruebas y validar la documentación.
