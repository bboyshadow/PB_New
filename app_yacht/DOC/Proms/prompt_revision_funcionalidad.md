# Plantilla de Prompt para Revisión Exhaustiva de Funcionalidad

### Objetivo del Prompt

Esta plantilla sirve como una guía para solicitar una revisión detallada y exhaustiva de una funcionalidad específica. El objetivo es asegurar una comprensión completa de su implementación actual (código y documentación) antes de proponer o realizar cualquier modificación evitando dañarla.

---

### Prompt para Copiar y Usar

Hola, necesito tu ayuda para realizar una revisión exhaustiva de la funcionalidad de **`NOMBRE_DE_LA_FUNCIONALIDAD`** en mi aplicación.

**Regla Crítica:** Es **absolutamente crucial** que **no realices ninguna modificación en el código o los archivos durante esta fase de revisión**. Tu única tarea es analizar y documentar el estado actual de la funcionalidad.

**apsolutamente crucial**
leer la totalidad de los archivos ya que el inicio se conecta con el final de cada archivo si ignoras y solo lees el inicio estaremos haciendo un mal trabajo.
**Regla Crítica:** La revisión debe ser exhaustiva, es decir, **no debes omitir ningún detalle**. Tu objetivo es comprender completamente la funcionalidad antes de proponer cambios.
**Regla Crítica:** No realices modificaciones en el código o los archivos durante esta fase de revisión. Tu única tarea es analizar y documentar el estado actual de la funcionalidad.

**Alcance de la Revisión (Archivos Relevantes):**
Tu análisis debe centrarse exclusivamente en los archivos funcionales de la aplicación y su documentación asociada. Esto incluye:

*   **Archivos de la aplicación:** Todos los archivos `.js`, `.php`, `.css`, `.html` ubicados dentro de la carpeta `app_yacht/` (o la ruta principal de la aplicación) sin saltarse ni una linea de código por leer.
*   **Archivos de documentación:** Cualquier archivo `.md` relevante dentro de la carpeta `DOC/` que describa la funcionalidad sin saltarse ni una linea de codigo o texto por leer.

**Exclusiones (Archivos a Ignorar):**
Por favor, **ignora completamente** los siguientes tipos de archivos, ya que no son parte del código funcional de la aplicación o su documentación activa:

*   Archivos de respaldo (ej. `*.bak`, `*.old`, `*.tmp`).
*   Archivos de librerías o dependencias externas (ej. `DOC/tools/py/Lib/site-packages/`, `DOC/tools/py/python311.dll`, etc.).
*   Cualquier otro archivo que no sea directamente parte de la lógica de la aplicación o su documentación oficial.

**Proceso de Revisión Requerido:**

**Paso 1: Identificación de Archivos Clave y Conexiones**

1.  **Búsqueda semántica inicial:** Realiza una búsqueda amplia con `search_codebase` usando el término **`NOMBRE_DE_LA_FUNCIONALIDAD`** para obtener una lista inicial de archivos potencialmente relevantes en todo el proyecto (`.js`, `.php`, `.css`, `.html`).
2.  **Búsqueda por patrones específicos (regex):** A partir de los archivos iniciales, utiliza `search_by_regex` para encontrar conexiones directas y precisas. Busca patrones como:
    *   Nombres de funciones clave (ej. `createTemplate`, `handle_create_template`).
    *   Identificadores de AJAX (ej. `action: 'createTemplate'`).
    *   Nombres de clases o IDs de CSS/HTML (ej. `#template-form`, `.create-template-btn`).
    *   Variables importantes (ej. `enableMixedSeasons`).
    Esto te ayudará a mapear cómo se conectan el frontend y el backend de manera más efectiva.
3.  **Documentación asociada:** Identifica cualquier archivo de documentación (`.md`) dentro de la carpeta `DOC/` que mencione o describa esta funcionalidad.

**Paso 2: Análisis de Código y Mapeo de Flujo**

1.  **Rol de cada archivo:** Para cada archivo de código identificado en el Paso 1, describe brevemente su rol específico dentro de la funcionalidad.
2.  **Mapeo del flujo de datos:** Documenta el proceso completo de la funcionalidad, desde su inicio hasta su finalización. Incluye:
    *   **a. Frontend (UI):** ¿Qué elementos de la interfaz de usuario (botones, campos, etc.) están involucrados? ¿Qué archivos HTML/PHP los contienen o qué JavaScript los genera dinámicamente?
    *   **b. Frontend (Lógica):** ¿Qué archivos JavaScript capturan las interacciones del usuario? ¿Qué funciones o eventos se disparan?
    *   **c. Comunicación (Frontend-Backend):** ¿Cómo se envían los datos al backend (ej. AJAX, `fetch`, envío de formulario)? ¿Qué archivos JavaScript y PHP están involucrados en esta comunicación?
    *   **d. Backend (Procesamiento):** ¿Qué archivos PHP/backend reciben y procesan los datos? ¿Qué lógica de negocio se aplica? ¿Cómo se manejan los errores?
3.  **Identificación de componentes clave:** Lista las principales variables, funciones, clases y elementos de la interfaz de usuario que son fundamentales para el funcionamiento de esta característica.

**Paso 3: Revisión de Documentación Existente**

1.  **Comparación:** Lee la documentación (`.md`) identificada en el Paso 1 y compárala con tu entendimiento del código.
2.  **Reporte de inconsistencias:** Reporta cualquier inconsistencia, información faltante, o áreas donde la documentación actual no refleje con precisión el comportamiento del código.

**Formato de Salida:**
Presenta tus hallazgos de manera clara y concisa, utilizando un formato estructurado (encabezados, listas). Incluye:

*   Una lista detallada de los archivos clave identificados.
*   Un resumen del flujo de la funcionalidad, con énfasis en la interacción entre componentes.
*   Una lista de las principales variables, funciones y elementos de UI.
*   Cualquier observación importante, dependencias críticas o preguntas que surjan de tu análisis.
*   Un apartado específico para "Inconsistencias/Mejoras en la Documentación" si las encuentras.

---
