# Plantilla de Prompt para Restaurar Funcionalidad Perdida

### Objetivo del Prompt

Esta plantilla sirve como una guía para solicitar a la IA la restauración de una funcionalidad que se ha perdido o dañado después de una actualización o un error. El objetivo es forzar un análisis basado estrictamente en una versión funcional previa del código, evitando que la IA "alucine" o invente soluciones nuevas.

---

### Prompt para Copiar y Usar

Hola, necesito tu ayuda para restaurar una funcionalidad que se ha perdido o dañado en mi aplicación.

El objetivo es analizar la versión **original y funcional** del código para entender cómo operaba la funcionalidad de **`FUNCIONALIDAD_A_RESTAURAR`** y luego crear un plan de acción detallado para restaurarla en la versión **actual**.

**Regla Crítica:** Es **absolutamente crucial** que **no inventes, alucines ni crees código nuevo**. Tu única tarea es replicar el comportamiento *exacto* de la versión original. Todas las modificaciones deben basarse estrictamente en el código que ya existía.

**Ubicaciones del Código:**

-   **Ruta a la versión original (funcional):** `[Ruta a la carpeta original, ej: app_yacht_original/]`
-   **Ruta a la versión actual (dañada):** `[Ruta a la carpeta actual, ej: app_yacht/]`

**Proceso Requerido:**

**Paso 1: Análisis Comparativo y Mapeo de Flujo**

1.  **Identifica los archivos clave:** Comienza en la versión **original**. Busca y lee todos los archivos (HTML, PHP, JavaScript, CSS) que implementaban la funcionalidad de **`FUNCIONALIDAD`**. Presta especial atención a cómo se conectan entre sí.

2.  **Mapea el flujo de datos completo:** Documenta el proceso desde el inicio hasta el fin. Por ejemplo:
    -   **a. Frontend (UI):** ¿Qué archivo HTML/PHP contiene el elemento de la UI (botón, enlace, etc.)? ¿O se crea dinámicamente con JavaScript? ¿Qué archivo JS lo hace?
    -   **b. Frontend (Lógica):** ¿Qué archivo JavaScript captura la interacción del usuario (ej. `click`)? ¿Qué función se ejecuta?
    -   **c. Frontend (Comunicación):** ¿Cómo se envían los datos al backend (ej. AJAX, `fetch`, envío de formulario)? ¿Qué archivo JS es responsable?
    -   **d. Backend (Procesamiento):** ¿Qué archivo PHP/backend recibe la solicitud? ¿Qué función procesa los datos? ¿Cómo se aplica la lógica de la **`FUNCIONALIDAD`**?

3.  **Compara con la versión actual:** Una vez mapeado el flujo original, compara cada archivo clave con su contraparte en la versión actual para identificar exactamente qué se eliminó o modificó.

**Paso 2: Creación del Plan de Acción**

Basado en tu análisis, escribe un plan de acción detallado en el archivo `[Ruta al archivo de plan, ej: correcciones.md]`.

El plan debe incluir:

-   Una lista de **todos los archivos** que necesitan ser modificados en la versión actual.
-   Para cada archivo, una descripción **precisa** de los cambios a realizar (ej: 'Restaurar la función `nombreDeFuncion` del original', 'Añadir el siguiente bloque de HTML en la línea X', 'Reemplazar el contenido completo de este archivo con el del original').

Confirma que has entendido estas instrucciones antes de proceder con el análisis.
