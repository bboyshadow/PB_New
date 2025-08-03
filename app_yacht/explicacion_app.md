# Explicación de la App para Replicación por IA pienso usar https://deepagent.abacus.ai/ 

Hola IA, vengo por lo siguiente: necesito que me ayudes a estudiar mi proyecto y hacer un documento que explique lo que es, de forma que una IA pueda replicarlo para mí. Aparte, dame tu opinión más sincera acerca de lo que es la seguridad, ya que necesito que el modo en que se procesan los cálculos nunca se pueda ver por nadie.

## Descripción General de la App
Esta app web está integrada en un servidor WordPress, utilizando una plantilla en blanco para aprovechar su capa de seguridad. Mi propia app le agrega más seguridad, pero es una app aislada porque dentro del mismo tema quiero agregar varios tipos de apps similares a esta, pero que no se mezclen entre ellas. Así, uso un mismo servidor para todo mi proyecto que llevará varias aplicaciones web.

La idea principal en esta primera app es que funcione con un sistema de módulos separados:
- **Calc**: Se encarga de todos los cálculos. Es el epicentro, el core, el corazón. Tiene una salida llamada text result, pero necesito crear un nuevo módulo llamado **Texttemplate** que se encargue de las salidas visuales de texto plano, enlaces e imágenes, dejando a Calc solo con la lógica de cálculo.
- **Template**: Creación de templates en HTML para emails, ya compatibles con todos los clientes de correo. Tendrá la opción de personalizar estos templates en cuanto a colores y elementos que se puedan ocultar si el usuario no quiere que se muestren.
- **Mail**: Usa los tokens de autenticación y una bandeja de redacción de emails para que el usuario edite como si estuviese directamente desde su cuenta. Permite enviar emails personalizados ya creados por la app. La app ya muestra todo este flujo y funciona, incluso el guardado de firma para emails personalizado.
- **Nuevo Módulo de Chatbox**: Debe incluir dos chatbox que se puedan entrenar con documentos .md. Lo único que debes hacer para entrenarlos es agregar documentos .md con la información que necesita saber. Así, puedo hacer dentro de la app los chatbox que necesito, especializados para cada tema que manejará el usuario. Por ejemplo, un chatbox que conozca toda la ley marítima y pueda responder a cualquier pregunta legal en la fecha de hoy (julio 2025). Para asegurarse de eso, yo prepararé los .md y serán subidos a la sesión de cada chatbox especialista.

En el módulo de template, se agrega una URL para extraer información de los yates a trabajar. Mi app tiene ya esto preparado, pero debe ser un recurso compartido en la carpeta **shared**, que es la carpeta que tiene el código compartido para todos los módulos (ejemplo: formatos de moneda, filtros, clases, etc., todo lo reutilizable para facilitar su mantenimiento y evolución).

Ya que necesito agregarle nuevas funcionalidades y módulos, en teoría:
- core es el corazon con la seguridad api tokens autenticaciones filtros etc aqui esta la seguridad.
- Calc es el epicentro.
- Template: la creación automática visual de templates personalizados para enviar al módulo mail.
- Mail: un editor de correo robusto para enviar correos super profesionales.

## Estructura de Directorios
Los únicos directorios que deben existir son:
- **Core**: El corazón y refuerzo de seguridad. Nota importante: core\yacht-functions.php es un enlace a functions del tema, así separo cada app una de la otra sin ensuciar mi tema principal.
- **Modules**: Para los módulos (calc, template, mail, texttemplate y el nuevo de chatbox).
- **Shared**: Para el código compartido.

Incluye esto que estamos haciendo de colocar la información del yate al comienzo para que sea utilizada globalmente. Esto se refiere a colocar la información del barco en un contenedor, extrayendo no solo los datos ya aplicados en la app, sino también los necesarios para calcular millas náuticas, como capacidad de combustible y consumo en un recorrido. Esto permitirá una nueva funcionalidad que calcule automáticamente el Relocation Fee, con opción para el usuario de ingresarlo manualmente o seleccionar un checkbox 'Relocation Fee Auto' colocado al lado de Relocation Fee: que aproveche los datos disponibles y aplique los cálculos según indicaciones del usuario. Tengo un plan para esta funcionalidad que explicaré mientras hacemos el documento de refactorización.

## Autenticación
Los cálculos y cómo se procesan, la forma en que el usuario autentica su email de Outlook también la quiero para Gmail, Yahoo y Apple.

## Mejoras y Refactorización
Estudia mi app, explica lo que quiero hacer en este primer mensaje basado en mi app para que pueda explicar lo mejor posible a la IA de cómo hacer mi app nuevamente de la mejor forma, manteniendo mi idea inicial. Incluye:
- Corregir la modularidad pero manteniendo la idea exacta de lo que he creado.
- El aspecto visual y flujo de interacciones.
- La forma en que se manejan los cálculos.
- Refactorizar código para mejorarlo, hacerlo más mantenible, limpiarlo de duplicados y cosas que puedan dañar el flujo o cometer errores de cálculo.

## Opinión Sincera sobre Seguridad
En cuanto a la seguridad, tu enfoque de usar WordPress como base es un buen inicio porque aprovecha sus capas de seguridad establecidas, como autenticación de usuarios y protección contra inyecciones SQL. Sin embargo, como la app es aislada y planeas múltiples apps en el mismo tema, hay riesgos de exposición cruzada si no se aíslan correctamente los módulos. Los cálculos deben procesarse en el servidor sin exponer lógica al cliente; usa encriptación para datos sensibles y tokens seguros para autenticación (extiende a Gmail, Yahoo, Apple usando OAuth). No es el peor camino, pero considera microservicios o contenedores (como Docker) para mejor aislamiento en un servidor compartido. Asegúrate de que shared no exponga código crítico. En general, es viable pero requiere auditorías regulares para mantener la confidencialidad de los cálculos.