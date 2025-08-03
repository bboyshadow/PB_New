
# Documentación de Impuestos Base: APA y APA %

## Visión General

Este documento detalla el flujo y la lógica de cálculo para los impuestos APA (Advance Provisioning Allowance) y APA % dentro de la calculadora de chárter. El análisis abarca desde la entrada de datos en el formulario del frontend hasta el procesamiento en el backend y la presentación final de los resultados.

---

## Flujo de Datos y Funcionalidad

### 1. Frontend: Entrada de Usuario

La interacción del usuario comienza en el formulario de la calculadora, definido en `modules/calc/calculator.php`.

- **Activación de Campos:**
    - El usuario debe marcar las casillas de verificación (checkboxes) para activar los campos de entrada de APA:
        - **`apaCheck`**: Al marcarse, se muestra el campo para un monto fijo de APA (`apaField`).
        - **`apaPercentageCheck`**: Al marcarse, se muestra el campo para un porcentaje de APA (`apaPercentageField`).

- **Campos de Entrada:**
    - **APA Fijo (`apaAmount`):** Un campo de texto para introducir un monto monetario fijo.
    - **APA Porcentaje (`apaPercentage`):** Un campo de texto para introducir un valor porcentual.

- **Recolección de Datos (JavaScript):**
    - El archivo `shared/js/classes/Calculator.js` es responsable de recolectar los datos del formulario.
    - La clase `Calculator` en su método `collectFormData` verifica si las casillas `apaCheck` y `apaPercentageCheck` están marcadas.
    - Si están marcadas, los valores de los campos `apaAmount` y `apaPercentage` se añaden al objeto `FormData` que se enviará al backend.

### 2. Comunicación: Frontend a Backend

- **Petición AJAX:**
    - El método `calculate` en `Calculator.js` inicia una petición AJAX (`fetch`) al backend.
    - La petición se dirige a la acción `calculate_charter` de WordPress.
    - Los datos del formulario, incluyendo los valores de APA, se envían como parte del cuerpo de la petición.

### 3. Backend: Procesamiento de Datos

El procesamiento de la petición AJAX se realiza en el archivo `modules/calc/php/calculate.php`.

- **Recepción de Datos (`handle_calculate_charter`):**
    - La función `handle_calculate_charter` es el punto de entrada para la acción `calculate_charter`.
    - Recibe los datos enviados desde el frontend a través de la superglobal `$_POST`.
    - Los valores `apaAmount` y `apaPercentage` son recuperados y sanitizados usando `sanitize_text_field`.

- **Lógica de Cálculo (`calculate`):**
    - La función `calculate` recibe los datos sanitizados.
    - Los valores de `apaAmount` y `apaPercentage` se convierten a números de punto flotante (`floatval`). El porcentaje se divide por 100 para su uso en cálculos.
    - **Cálculo de APA %:**
        - Se calcula multiplicando la tarifa de chárter (después de aplicar descuentos) por el porcentaje de APA:
          ```php
          $apaPercCalc = $discountedRate * $apaPercentage;
          ```
        - El resultado se suma al subtotal.
    - **Monto Fijo de APA:**
        - Si se proporciona un `apaAmount` mayor que cero, este se suma directamente al subtotal.
    - **Almacenamiento de Resultados:**
        - Los resultados de los cálculos de APA (tanto el monto porcentual como el fijo) se almacenan en un array asociativo (`$structuredResults`) para su posterior formateo.

### 4. Frontend: Presentación de Resultados

- **Formateo de Salida (`textResult`):**
    - La función `textResult` en `modules/calc/php/calculate.php` toma el array de resultados y lo convierte en una cadena de texto formateada.
    - **Visibilidad Condicional:** La función verifica si el usuario ha marcado la opción `hideAPA` en el formulario. Si no está marcada, se procede a mostrar los detalles de APA.
    - **Generación de Texto:**
        - Si hay un valor para "APA %", se añade una línea al resultado, mostrando tanto el porcentaje como el monto calculado.
          ```
          APA (20%): € 2,000.00
          ```
        - Si hay un valor para "APA (Fixed Amount)", se añade otra línea.
          ```
          APA (Fixed Amount): € 5,000.00
          ```

- **Visualización en el Navegador:**
    - El texto formateado se envía de vuelta al frontend como parte de la respuesta JSON.
    - El método `displayResult` en `Calculator.js` recibe este texto y lo inserta en el contenedor de resultados (`<div id="result">`), haciéndolo visible para el usuario.

---

## Resumen de Archivos y Funciones Clave

- **`modules/calc/calculator.php`**:
    - Contiene los elementos del formulario HTML para la entrada de APA y APA %.
- **`shared/js/classes/Calculator.js`**:
    - `collectFormData()`: Recolecta los valores de APA del formulario.
    - `calculate()`: Envía los datos al backend mediante AJAX.
    - `displayResult()`: Muestra el resultado final en la página.
- **`modules/calc/php/calculate.php`**:
    - `handle_calculate_charter()`: Maneja la petición AJAX, recibe y sanitiza los datos.
    - `calculate()`: Realiza la lógica de negocio principal, calculando los montos de APA.
    - `textResult()`: Formatea los resultados para su visualización, respetando las opciones de ocultar elementos.
