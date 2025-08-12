# Revisión quirúrgica del código de la app (App Yacht)

Este documento ofrece una evaluación técnica y práctica del código, centrándose en arquitectura, calidad, seguridad, rendimiento, mantenibilidad y riesgos. Incluye hallazgos concretos y recomendaciones priorizadas para seguir elevando el nivel del proyecto.

## 1) Resumen ejecutivo
- Arquitectura modular: app_yacht separa core, módulos (calc, template, mail, yachtinfo) y recursos compartidos. Buena base para escalar.
- Frontend JS organizado en piezas pequeñas (calculate.js, interfaz.js, TemplateManager, etc.) con responsabilidades relativamente claras.
- Backend PHP con endpoints AJAX explícitos y verificación de nonce. Correcta orientación a seguridad.
- Documentación interna útil (DOC/*.md) que ayuda a comprender dependencias y flujos.
- Oportunidades: robustecer contratos de datos cliente-servidor, reforzar validaciones y errores, unificar convenciones, mejorar DX (tests, linting, types), y afinar rendimiento percibido.

## 2) Puntos fuertes
- Separación por módulos y responsabilidades.
- Uso sistemático de nonces en AJAX y control de errores HTTP (wp_send_json_error), lo que reduce superficie de ataque.
- Formato de salida consistente para cálculo (texto estructurado y datos formateados).
- Estructura de extras, descuentos y promociones flexible, preparada para nuevas reglas.
- Implementación de Mix VAT (VAT Rate Mix) con desglose por país/noches.

## 3) Hallazgos quirúrgicos clave
- Orden de inicialización en PHP: se detectó y corrigió un uso de variable antes de su definición en calculate.php (enableVatRateMix). Este tipo de detalle puede provocar 500 y es fácil que pase desapercibido sin tests.
- Contrato de datos implícito: el cliente construye FormData con campos complejos (charterRates[i][...], extras[i][...]). Si el DOM cambia (por ejemplo, template/ UI), hay riesgo de enviar datos incompletos o mal formateados.
- Toggling de UI vs. payload: hay campos que aparecen/desaparecen (descuentos, promociones, horas vs. noches) y el cliente decide qué enviar. Es correcto, pero conviene formalizar el contrato para evitar estados inconsistentes.
- Logging en server: se usa error_log en VAT Mix. Útil en desarrollo, pero conviene encapsularlo/condicionarlo por entorno para no generar ruido en producción.

## 4) Calidad del frontend
- calculate.js recoge datos de forma clara y envía con fetch/async-await. Bien estructurado.
- interfaz.js genera dinámicamente grupos de tarifas y extras; la UI es flexible, pero la lógica del DOM es sensible a regresiones si se renombra o reestructura HTML.
- TemplateManager y template.js documentan y orquestan partes de la UI. La reciente salida de invocaciones automáticas sugiere que la inicialización debe revisarse cuidadosamente para evitar dependencias invisibles.

Sugerencias frontend:
- Añadir validaciones de tipo/casting en el cliente antes de enviar (p.ej., normalizar números, vacíos a 0, etc.).
- Introducir un esquema compartido (p.ej., JSON Schema) para validar payloads en cliente y servidor.
- Añadir manejo de estados de carga/errores más explícitos (spinners, mensajes diferenciados por tipo de error: nonce, validación, server).

## 5) Calidad del backend (PHP)
- Endpoints AJAX claros y con verificación de nonce.
- Sanitización básica presente (sanitize_text_field) y casting numérico consistente.
- Cálculo separado en funciones (calculate, textResult) mejora la legibilidad.

Sugerencias backend:
- Verificación de presencia y forma (shape) de charterRates y extras con validadores dedicados (campos obligatorios, rangos numéricos, tipos).
- Normalización de entradas en una capa previa (DTO o mapper) para centralizar el casting y evitar ifs repetidos.
- Manejo de errores: devolver códigos/errores más específicos (p.ej., código de “datos incompletos”, “nonce”, “validación de negocio”).
- Extraer el cálculo de Mix VAT a una función pura con tests (unitarios) e inputs bien definidos.

## 6) Seguridad
- Buen uso de nonces y hooks wp_ajax/wp_ajax_nopriv.
- Sanitización en inputs clave; reforzar en arrays anidados (p.ej., recorrer charterRates y extras sanitizando campo a campo).
- Evitar logs sensibles. Asegurar que no se imprimen trazas en producción salvo con flag de depuración.

## 7) Rendimiento y UX
- Payloads pequeños y orientados a FormData: eficiente.
- Oportunidad: cachear o memoizar fórmulas inmutables si en el futuro hay múltiples recomputaciones con los mismos datos.
- Mostrar progreso (spinner) y feedback contextual para errores en el cliente.

## 8) Mantenibilidad
- Estándares: el proyecto ya tiene ESLint/Stylelint, pero conviene asegurar su ejecución en CI y que la configuración refleja las reglas reales del equipo.
- Tipado: incorporar JSDoc/TypeScript gradual en JS y PHPStan/Psalm en PHP ayuda a prevenir errores de orden y tipos (como el de enableVatRateMix).
- Tests: añadir una capa mínima de unit tests a funciones puras (cálculo, formateo de moneda) y tests de integración para endpoints AJAX críticos.

## 9) Documentación
- DOC/ contiene guías útiles. Recomendación: añadir un “contrato de datos” entre UI y server (tabla de campos, tipos, ejemplo de payload) y un diagrama de flujo de cálculo (con flags: oneDay, mixedSeasons, vatMix, descuentos, promociones).

## 10) Recomendaciones priorizadas
1) Contrato de payload formal (cliente-servidor), con validación en ambos extremos.
2) Tests unitarios para cálculo base, descuentos, promociones y Mix VAT; test de smoke para endpoint calculate_charter.
3) Tipado gradual (JSDoc/TS en frontend, PHPStan en backend) y rules de CI.
4) Sanitización y validación profunda de arrays anidados (charterRates, extras, vatMix).
5) Logging condicional por entorno y mensajes de error más accionables para el usuario.
6) Checklist de inicialización de UI para evitar dependencias implícitas (quién crea DOM, cuándo, y en qué orden).

## 11) Opinión general
El proyecto está bien diseñado para evolucionar: modularidad, seguridad básica bien aplicada, y una UI flexible. El mayor riesgo hoy está en la fragilidad del contrato de datos entre UI y backend, dada la generación dinámica del DOM y la diversidad de escenarios (horas/noches, descuentos, promociones, Mix VAT). Con un pequeño esfuerzo en tipado, validación, tests y documentación del contrato, la app ganará robustez, reducirá regresiones y facilitará la incorporación de nuevas reglas de negocio.

## 12) Próximos pasos sugeridos
- Definir JSON Schema del payload y validarlo en cliente y servidor.
- Añadir 5-8 tests unitarios clave (cálculos, Mix VAT, descuentos porcentuales vs. absolutos).
- Activar linters/formatters en pre-commit/CI.
- Crear una guía rápida de “cómo depurar errores de cálculo” (checklist de flags, ejemplos de payload).