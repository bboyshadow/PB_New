# AppYacht v0.01

## 📋 Descripción

AppYacht v0.01 es una aplicación modular para gestionar operaciones relacionadas con yates desde un tema de WordPress.
Proporciona servicios básicos para cálculos, plantillas y envío de correos mediante un contenedor de dependencias sencillo.

## 🏗️ Estructura de Directorios

```
app_yacht/                                   # raíz de la aplicación
├── ARCHITECTURE.md                          # guía de arquitectura
├── CHANGELOG.md                             # registro de cambios
├── DOC/                                     # documentación auxiliar
├── README.md                                # descripción general
├── core/                                    # núcleo de la aplicación
│   ├── api-request.php                      # manejador de peticiones HTTP
│   ├── app-yacht.php                        # integración con WordPress
│   ├── bootstrap.php                        # arranque del contenedor
│   ├── config.php                           # configuración inicial
│   ├── container.php                        # contenedor de dependencias
│   ├── data-validation.php                  # validaciones comunes
│   ├── security-headers.php                 # cabeceras de seguridad
│   └── yacht-functions.php                  # funciones utilitarias
├── modules/                                 # módulos autónomos
│   ├── calc/                                # cálculos de tarifas
│   │   ├── calc-service.php                 # servicio de cálculos
│   │   ├── calculator.php                   # controlador principal
│   │   ├── js/                              # scripts del módulo calc
│   │   │   ├── MixedTaxes.js                # manejo de impuestos mixtos
│   │   │   ├── VatRateMix.js               # tasas de IVA mixtas
│   │   │   ├── calculate.js                 # cálculo principal
│   │   │   ├── extraPerPerson.js            # extras por persona
│   │   │   ├── interfaz.js                  # interfaz de usuario
│   │   │   ├── mix.js                       # mezcla de tasas
│   │   │   └── promotion.js                 # promociones
│   │   └── php/                             # endpoints PHP calc
│   │       ├── calculate.php                # endpoint de cálculo
│   │       └── calculatemix.php             # cálculo mixto
│   ├── mail/                                # envío de correos
│   │   ├── mail-service.php                 # servicio de correo
│   │   ├── mail.css                         # estilos de correo
│   │   ├── mail-hidden-fields.js            # campos ocultos
│   │   ├── mail.js                          # lógica de formulario
│   │   ├── mail.php                         # endpoint de correo
│   │   ├── outlook/                         # submódulo Outlook
│   │   │   ├── outlook-ajax.js              # ajax para Outlook
│   │   │   ├── outlook-form.php             # formulario Outlook
│   │   │   ├── outlook-functions.php        # funciones Outlook
│   │   │   └── outlook-loader.php           # cargador Outlook
│   │   └── signature/                       # firmas de correo
│   │       ├── msp-signature.js             # JS de firma
│   │       ├── msp-styles.css               # estilos de firma
│   │       └── signature-functions.php      # funciones de firma
│   ├── render/                              # motor de renderizado
│   │   └── render-engine.php                # clase de renderizado
│   ├── template/                            # plantillas de correo
│   │   ├── template.php                     # controlador de plantillas
│   │   ├── js/                              # scripts de plantillas
│   │   │   └── template.js                  # lógica de plantillas
│   │   ├── php/                             # endpoints de plantillas
│   │   │   ├── calculate-template.php       # cálculo de plantilla
│   │   │   ├── load-template.php            # carga de plantillas
│   │   │   └── template-data.php            # datos de plantillas
│   │   └── templates/                       # plantillas prediseñadas
│   │       ├── default-template-prev.php    # vista previa por defecto
│   │       ├── default-template.php         # plantilla por defecto
│   │       ├── email-signature.php          # firma por email
│   │       ├── template-01-prev.php         # vista previa plantilla 01
│   │       ├── template-01.php              # plantilla 01
│   │       ├── template-02-prev.php         # vista previa plantilla 02
│   │       └── template-02.php              # plantilla 02
│   └── yachtinfo/                           # información de yates
│       └── yacht-info-service.php           # servicio de datos de yates
├── shared/                                  # utilidades compartidas
│   ├── css/                                 # estilos comunes
│   │   └── app_yacht.css                    # estilos base
│   ├── helpers/                             # ayudantes de PHP
│   │   ├── cache-helper.php                 # caché simple
│   │   └── validator-helper.php             # validador genérico
│   ├── interfaces/                          # contratos de servicios
│   │   ├── calc-service-interface.php       # interfaz de calc
│   │   ├── mail-service-interface.php       # interfaz de mail
│   │   ├── render-engine-interface.php      # interfaz de render
│   │   └── yacht-info-service-interface.php # interfaz de yacht info
│   ├── js/                                  # scripts compartidos
│   │   ├── classes/                         # clases JS
│   │   │   ├── Calculator.js                # clase calculadora
│   │   │   ├── MailComposer.js              # clase de correo
│   │   │   └── TemplateManager.js           # clase de plantillas
│   │   ├── currency.js                      # utilidades de moneda
│   │   ├── events.js                        # manejador de eventos
│   │   ├── ini.js                           # inicialización
│   │   ├── resources.js                     # recursos locales
│   │   ├── storage.js                       # almacenamiento local
│   │   ├── ui.js                            # interfaz común
│   │   ├── utils/                           # utilidades JS
│   │   │   ├── debounce.js                  # antirebote
│   │   │   └── dom.js                       # utilidades DOM
│   │   ├── validate.js                      # validaciones JS
│   │   └── yacht-preview.js                 # vista previa de yate
│   ├── php/                                 # funciones PHP compartidas
│   │   ├── currency-functions.php           # funciones de moneda
│   │   ├── security.php                     # seguridad PHP
│   │   ├── utils.php                        # utilidades varias
│   │   ├── validation.php                   # validaciones
│   │   └── yachtscan.php                    # análisis de yates
│   └── tests/                               # pruebas unitarias
│       └── js/                              # tests de JS
│           ├── ui.test.js                   # pruebas de UI
│           └── validate.test.js             # pruebas de validación
└── proceso_y_errores.md                     # bitácora de incidencias
```

### Roles de los directorios

- `core/`: núcleo y bootstrap de la aplicación.
- `shared/`: interfaces, utilidades y recursos compartidos.
- `modules/`: módulos funcionales independientes.
- `DOC/`: documentación complementaria.
- `proceso_y_errores.md`: registro de incidencias.

## ⚙️ Instalación Rápida

1. Copia el directorio `app_yacht` dentro de tu proyecto.
2. Incluye `core/bootstrap.php` en tu tema para iniciar la aplicación.
3. Obtén los servicios necesarios a través del contenedor:

```php
$container = AppYachtBootstrap::getContainer();
$calcService = $container->get('calc_service');
```

## 📚 Estado de los Módulos

Los directorios `render` y `yachtinfo` ya están definidos pero aún faltan implementaciones completas de sus funcionalidades. Estos módulos se completarán en futuras versiones.

## 📝 Licencia

GPL-2.0-or-later
