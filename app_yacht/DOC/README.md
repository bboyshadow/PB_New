Documentación Técnica: Guía del Proyecto App_Yacht
IMPRESCINDIBLE LEER LAS [400] LÍNEAS DE ESTE ARCHIVO EN UNA SOLA CONSULTA PARA COMPRENDERLO Y EVITAR ERRORES. AL FINALIZAR, CONFIRMAR QUE SE HA LEÍDO COMPLETO ANTES DE MARCAR COMO COMPLETADA CUALQUIER TAREA RELACIONADA.

Tipo: Guía General
Versión del Documento: 1.6
Fecha de Creación: 2025-06-26
Última Actualización: 2025-06-26
Autor: [Nombre del Autor]
Estado de Auditoría: 🔍 En Progreso

Descripción General

El proyecto App_Yacht gestiona reservas de yates, proporcionando funcionalidades para cálculos, envío de correos, y gestión de plantillas. Esta documentación mapea el código existente para identificar duplicaciones, código no utilizado, y permitir ediciones seguras. La documentación se organiza en app_yacht/DOC/ con subcarpetas para archivos de código (core/, modules/, shared/). Las herramientas de validación, incluyendo la instalación de Python, se encuentran en app_yacht/DOC/tools/py/, y el validador de documentación está en app_yacht/DOC/tools/.

### Estructura de Carpetas

La estructura del proyecto se lista con rutas completas, agrupadas por carpeta principal, para mayor compacidad.

#### Nota sobre Estructura de Manejo de Errores

En algunos archivos con documentación extensa de manejo de errores (como `shared/js/events.js`, `shared/js/ini.js` y `shared/js/storage.js`), la sección `08_manejo-errores` se ha subdividido en múltiples archivos (por ejemplo, `08_manejo-errores/01_introduccion.md`, `02_tipos-errores.md`, etc.). Esta estructura alternativa se implementó para:

1. **Mejorar la legibilidad**: Dividir contenido extenso en secciones manejables.
2. **Evitar errores de rendimiento**: Algunos editores y herramientas tienen dificultades con archivos muy grandes.
3. **Facilitar el mantenimiento**: Permite actualizar secciones específicas sin afectar el resto de la documentación.

Esta variación de la estructura estándar se aplica únicamente cuando la documentación de manejo de errores es particularmente extensa y compleja.

core/
app_yacht/core/api-request.php
app_yacht/core/app-yacht.php
app_yacht/core/data-validation.php
app_yacht/core/security-headers.php
app_yacht/core/yacht-functions.php

modules/
calc/
app_yacht/modules/calc/js/calculate.js
app_yacht/modules/calc/js/extraPerPerson.js
app_yacht/modules/calc/js/interfaz.js
app_yacht/modules/calc/js/mix.js
app_yacht/modules/calc/js/MixedTaxes.js
app_yacht/modules/calc/php/calculate.php
app_yacht/modules/calc/php/calculatemix.php
app_yacht/modules/calc/calculator.php

mail/
app_yacht/modules/mail/outlook/outlook-ajax.js
app_yacht/modules/mail/outlook/outlook-form.php
app_yacht/modules/mail/outlook/outlook-functions.php
app_yacht/modules/mail/outlook/outlook-loader.php
app_yacht/modules/mail/signature/msp-signature.js
app_yacht/modules/mail/signature/msp-styles.css
app_yacht/modules/mail/signature/signature-functions.php
app_yacht/modules/mail/mail-hidden-fields.js
app_yacht/modules/mail/mail.css
app_yacht/modules/mail/mail.js
app_yacht/modules/mail/mail.php

template/
app_yacht/modules/template/js/template.js
app_yacht/modules/template/php/calculate-template.php
app_yacht/modules/template/php/load-template.php
app_yacht/modules/template/php/template-data.php
app_yacht/modules/template/templates/default-template-prev.php
app_yacht/modules/template/templates/default-template.php
app_yacht/modules/template/templates/template-01-prev.php
app_yacht/modules/template/templates/template-01.php
app_yacht/modules/template/templates/template-02-prev.php
app_yacht/modules/template/templates/template-02.php
app_yacht/modules/template/template.php

shared/
css/
app_yacht/shared/css/app_yacht.css

js/
app_yacht/shared/js/classes/Calculator.js
app_yacht/shared/js/classes/MailComposer.js
app_yacht/shared/js/classes/TemplateManager.js
app_yacht/shared/js/tests/ui.test.js
app_yacht/shared/js/tests/validate.test.js
app_yacht/shared/js/utils/debounce.js
app_yacht/shared/js/utils/dom.js
app_yacht/shared/js/currency.js
app_yacht/shared/js/events.js
app_yacht/shared/js/ini.js
app_yacht/shared/js/resources.js
app_yacht/shared/js/storage.js
app_yacht/shared/js/ui.js
app_yacht/shared/js/validate.js

php/
app_yacht/shared/php/currency-functions.php
app_yacht/shared/php/security.php
app_yacht/shared/php/utils.php
app_yacht/shared/php/validation.php

tests/
app_yacht/shared/tests/js/

py/
app_yacht/DOC/tools/py/ Instalación de Python local en el proyecto para validar la documentación

DOC/
app_yacht/DOC/README.md
app_yacht/DOC/seguimiento_de_tareas.md
app_yacht/DOC/core/index.md
app_yacht/DOC/core/[nombre-archivo]/01_index.md
app_yacht/DOC/core/[nombre-archivo]/02_propósito-general.md
app_yacht/DOC/core/[nombre-archivo]/03_estructura-código.md
app_yacht/DOC/core/[nombre-archivo]/04_dependencias.md
app_yacht/DOC/core/[nombre-archivo]/05_lógica-negocio.md
app_yacht/DOC/core/[nombre-archivo]/06_puntos-entrada.md
app_yacht/DOC/core/[nombre-archivo]/07_flujo-datos.md
app_yacht/DOC/core/[nombre-archivo]/08_manejo-errores.md
app_yacht/DOC/core/[nombre-archivo]/09_pruebas-integración.md
app_yacht/DOC/core/[nombre-archivo]/10_referencias.md
app_yacht/DOC/core/[nombre-archivo]/11_historial.md
app_yacht/DOC/modules/calc/index.md
app_yacht/DOC/modules/calc/[nombre-archivo]/01_index.md
app_yacht/DOC/modules/calc/[nombre-archivo]/02_propósito-general.md
app_yacht/DOC/modules/calc/[nombre-archivo]/[... (y otras secciones)]
app_yacht/DOC/modules/mail/index.md
app_yacht/DOC/modules/mail/[nombre-archivo]/01_index.md
app_yacht/DOC/modules/mail/[nombre-archivo]/02_propósito-general.md
app_yacht/DOC/modules/mail/[nombre-archivo]/[... (y otras secciones)]
app_yacht/DOC/modules/template/index.md
app_yacht/DOC/modules/template/[nombre-archivo]/01_index.md
app_yacht/DOC/modules/template/[nombre-archivo]/02_propósito-general.md
app_yacht/DOC/modules/template/[nombre-archivo]/[... (y otras secciones)]
app_yacht/DOC/shared/css/index.md
app_yacht/DOC/shared/css/[nombre-archivo]/01_index.md
app_yacht/DOC/shared/css/[nombre-archivo]/02_propósito-general.md
app_yacht/DOC/shared/css/[nombre-archivo]/[... (y otras secciones)]
app_yacht/DOC/shared/js/index.md
app_yacht/DOC/shared/js/[nombre-archivo]/01_index.md
app_yacht/DOC/shared/js/[nombre-archivo]/02_propósito-general.md
app_yacht/DOC/shared/js/[nombre-archivo]/[... (y otras secciones)]
app_yacht/DOC/shared/php/index.md
app_yacht/DOC/shared/php/[nombre-archivo]/01_index.md
app_yacht/DOC/shared/php/[nombre-archivo]/02_propósito-general.md
app_yacht/DOC/shared/php/[nombre-archivo]/[... (y otras secciones)]
app_yacht/DOC/tools/README.md
app_yacht/DOC/tools/doc-validator.py

Nota: La carpeta app_yacht/DOC/tools/py/ contiene la instalación de Python utilizada por el proyecto, incluyendo python.exe y bibliotecas asociadas. El validador de documentación está ubicado en app_yacht/DOC/tools/doc-validator.py.

Estándares de Documentación

La documentación se genera en app_yacht/DOC/[ruta]/[nombre-archivo]/ usando esta plantilla. Cada archivo de código tiene una subcarpeta con 11 archivos .md, nombrados con prefijos numéricos para reflejar el orden de las secciones:

01_index.md: Panel de navegación con metadatos y enlaces.
02_propósito-general.md: Rol y funcionalidades clave.
03_estructura-código.md: Código fuente exacto y organización.
04_dependencias.md: Dependencias internas (entrantes/salientes), externas, y conexiones implícitas (no olvidar archivos).
05_lógica-negocio.md: Detalle de funciones/clases, incluyendo uso y variables globales.
06_puntos-entrada.md: Hooks, endpoints, o eventos.
07_flujo-datos.md: Flujo de entrada, procesamiento, y salida.
08_manejo-errores.md: Errores manejados.
09_pruebas-integración.md: Pruebas de integración.
10_referencias.md: Archivos relacionados y documentación externa.
11_historial.md: Registro de cambios.

Cabecera Común de Documentación

Todos los archivos .md generados (01_index.md a 11_historial.md) comienzan con la siguiente cabecera, adaptada según el archivo:
# Documentación Técnica: [ruta/nombre-archivo] - [Nombre de la Sección]

**IMPRESCINDIBLE LEER LAS [X] LÍNEAS DE ESTE ARCHIVO EN UNA SOLA CONSULTA PARA COMPRENDERLO Y EVITAR ERRORES. AL FINALIZAR, CONFIRMAR QUE SE HA LEÍDO COMPLETO ANTES DE MARCAR COMO COMPLETADA CUALQUIER TAREA RELACIONADA.**

- **Tipo**: [Backend (PHP) | Frontend (JS) | CSS | Python | etc.]
- **Versión del Documento**: 1.0
- **Fecha de Creación**: 2025-06-26
- **Última Actualización**: 2025-06-26
- **Autor**: [Nombre del Autor]
- **Estado de Auditoría**: 🔍 En Progreso
- **Enlace al Índice**: [01_index.md](01_index.md)
- **Enlace al Panel de Control**: [../../README.md](../../README.md)

Notas sobre la cabecera

Título: Incluye [ruta/nombre-archivo] (por ejemplo, core/app-yacht.php) y el nombre de la sección (por ejemplo, Índice de Documentación para 01_index.md, Propósito General para 02_propósito-general.md).
Tipo: Seleccionar según el archivo (por ejemplo, Backend (PHP) para .php, Frontend (JS) para .js, CSS para .css, Python para .py).
Enlace al Índice: Usar [01_index.md](01_index.md) en todas las secciones excepto 01_index.md, que usa Enlace al Índice Superior: [../index.md](../index.md).
Líneas [X]: Especificar el número total de líneas del archivo .md generado.

Secciones de Documentación

A continuación, se describe el contenido específico de cada archivo .md después de la cabecera común.

01_index.md
Propósito: Panel de navegación con metadatos y enlaces a todas las secciones.
Contenido (después de la cabecera):
## Secciones de Documentación

1. [Propósito General](02_propósito-general.md)
2. [Estructura del Código](03_estructura-código.md)
3. [Dependencias](04_dependencias.md)
4. [Lógica de Negocio](05_lógica-negocio.md)
5. [Puntos de Entrada](06_puntos-entrada.md)
6. [Flujo de Datos](07_flujo-datos.md)
7. [Manejo de Errores](08_manejo-errores.md)
8. [Pruebas de Integración](09_pruebas-integración.md)
9. [Referencias](10_referencias.md)
10. [Historial de Cambios](11_historial.md)


02_propósito-general.md
Propósito: Describe el rol y las funcionalidades clave del archivo.
Contenido (después de la cabecera):
## Propósito General

El archivo `[ruta/nombre-archivo]` [descripción del propósito]. Sus funcionalidades clave incluyen:

- [Función o tarea principal 1].
- [Función o tarea principal 2].
- [Función o tarea principal 3].

**Ubicación del código fuente**: [ruta/nombre-archivo]


03_estructura-código.md
Propósito: Muestra el código fuente exacto y su organización.
Contenido (después de la cabecera):
## Estructura del Código

[código fuente completo]

### Contexto de la Estructura

- **Verificación de Seguridad**: [Descripción].
- **Inclusiones**: [Archivos incluidos].
- **Funciones/Clases**: [Resumen de funciones o clases].
- **Salida**: [Descripción de la salida].


04_dependencias.md
Propósito: Lista todas las dependencias internas (entrantes/salientes), externas, y conexiones implícitas.
Contenido (después de la cabecera):
## Dependencias

### Dependencias Internas (Entrantes)

- `[ruta/archivo].md`: [Descripción].

### Dependencias Internas (Salientes)

- `[ruta/archivo].md`: [Descripción].

### Dependencias Externas

- [Librería/API]: [Descripción].

### Conexiones Implícitas

- [Hook/Evento/Contexto]: [Descripción, por ejemplo, "Llamado por el hook dinámico `custom_hook`"].


05_lógica-negocio.md
Propósito: Detalla cada función o clase, incluyendo dónde se usa.
Contenido (después de la cabecera):
## Lógica de Negocio

### Función/Clase: [nombre]

- **Propósito**: [Descripción].
- **Parámetros**:
  - [parámetro]: [Tipo, descripción].
- **Valor de Retorno**: [Descripción].
- **Uso**: [Dónde se llama, por ejemplo, "Invocada por `otro-archivo.php` en la línea 50"].
- **Variables Globales/Estado**: [Lista de variables globales o estado compartido usado].
- **Lógica**:
  - [Paso 1].
  - [Paso 2].
- **Ejemplo de Uso** (si aplica):

  ```php
  [código]




06_puntos-entrada.md
Propósito: Enumera hooks, endpoints, o eventos que activan el archivo.
Contenido (después de la cabecera):
## Puntos de Entrada

- **Hook/Endpoint/Evento**: [nombre]
  - **Función asociada**: [Nombre].
  - **Contexto**: [Descripción].


07_flujo-datos.md
Propósito: Describe el flujo de datos del archivo.
Contenido (después de la cabecera):
## Flujo de Datos

1. **Entrada**:
   - [Fuente, por ejemplo, "Datos POST"].
2. **Procesamiento**:
   - [Paso, por ejemplo, "Valida datos"].
3. **Salida**:
   - [Resultado, por ejemplo, "HTML renderizado"].


08_manejo-errores.md
Propósito: Lista los errores manejados por el archivo.
Contenido (después de la cabecera):
## Manejo de Errores

- **Error: [Tipo de error]**:
  - **Condición**: [Cuándo ocurre, por ejemplo, "Archivo no encontrado"].
  - **Manejo**: [Cómo se maneja, por ejemplo, "Registra en el log y retorna `false`"].


09_pruebas-integración.md
Propósito: Describe cómo se prueba el archivo en el sistema.
Contenido (después de la cabecera):
## Pruebas de Integración

- **Prueba: [Nombre]**:
  - **Pasos**: [Pasos para probar, por ejemplo, "Navegar a la página X"].
  - **Resultado esperado**: [Por ejemplo, "HTML renderizado correctamente"].


10_referencias.md
Propósito: Enumera archivos relacionados y documentación externa.
Contenido (después de la cabecera):
## Referencias

### Archivos Relacionados

- `[ruta/archivo].md`: [Descripción].

### Documentación Externa

- [Nombre]: [URL y descripción].


11_historial.md
Propósito: Registra cambios en el archivo y su documentación.
Contenido (después de la cabecera):
## Historial de Cambios

| Fecha       | Versión | Descripción del Cambio          | Autor            |
|-------------|---------|--------------------------------|------------------|
| 2025-06-26  | 1.0     | Creación inicial del archivo   | [Nombre del Autor] |

# Proceso de Auditoría

Generar documentación:
Usar esta plantilla para crear archivos .md por cada archivo de código en app_yacht/DOC/[ruta]/[nombre-archivo]/, con nombres numerados (01_index.md a 11_historial.md).

Validar:
Ejecutar python app_yacht/DOC/tools/py/python.exe app_yacht/DOC/tools/doc-validator.py --lenient para verificar:
Presencia de todos los archivos .md requeridos (01_index.md a 11_historial.md).
Metadatos completos en la cabecera común de cada archivo.
Subsecciones "Uso" y "Variables Globales/Estado" en 05_lógica-negocio.md.
Contenido no vacío en 08_manejo-errores.md y 09_pruebas-integración.md.
Reciprocidad de dependencias en 04_dependencias.md.

Identificar fallos:
Duplicaciones: Comparar funciones en 05_lógica-negocio.md entre archivos.
Código no utilizado: Verificar si funciones o archivos aparecen en 04_dependencias.md o 06_puntos-entrada.md.
Falta de reutilización: Analizar dependencias para identificar redundancias.

Registrar progreso:
Actualizar app_yacht/DOC/seguimiento_de_tareas.md con el estado de cada tarea.

Instrucciones para la IA

Leer: Este archivo (app_yacht/DOC/README.md), el código fuente, y los documentos referenciados en una sola consulta.
Generar: Archivos .md en app_yacht/DOC/[ruta]/[nombre-archivo]/ con nombres numerados (01_index.md a 11_historial.md) según esta plantilla, incluyendo la cabecera común adaptada al archivo.
No modificar: Este archivo (app_yacht/DOC/README.md).
Actualizar: Solo app_yacht/DOC/seguimiento_de_tareas.md para el progreso de las tareas.
Validar: Usar app_yacht/DOC/tools/doc-validator.py --lenient para verificar la documentación.
Confirmar: Lectura completa de todos los documentos antes de completar tareas.

Prompt Ejemplo

Genera la documentación para `core/app-yacht.php` usando la plantilla en `app_yacht/DOC/README.md`. Crea `app_yacht/DOC/core/app-yacht/` con archivos `.md` numerados (`01_index.md` a `11_historial.md`) para cada sección. Asegúrate de:

- Incluir la cabecera común en cada archivo, adaptando el título (`Documentación Técnica: core/app-yacht.php - [Nombre de la Sección]`) y el tipo (`Backend (PHP)`).
- Incluir el código fuente exacto en 03_estructura-código.md.
- Detallar cada función/clase en 05_lógica-negocio.md, incluyendo dónde se usa.
- Listar todas las dependencias y conexiones implícitas en 04_dependencias.md.
- Documentar errores manejados en 08_manejo-errores.md.
- Describir pruebas existentes en 09_pruebas-integración.md.
- No sugerir mejoras.
- Usar enlaces relativos al índice `app_yacht/DOC/core/app-yacht/01_index.md` y a `app_yacht/DOC/README.md`.
- No modificar `app_yacht/DOC/README.md`.
- Actualizar `app_yacht/DOC/seguimiento_de_tareas.md` con el progreso.

Confirma que has leído el archivo de código y los documentos de referencia.

