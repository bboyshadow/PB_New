# 🏗️ Arquitectura - AppYacht v0.01

## 📐 Visión general

AppYacht v0.01 es una aplicación modular que se ejecuta dentro de un tema de WordPress. Su código se organiza en un núcleo común y en módulos independientes que se comunican a través de un contenedor de dependencias ligero.

## 🔧 Capas

```
Presentación (PHP/JS de los módulos)
        ↓
Aplicación (core/bootstrap.php)
        ↓
Dominio/Servicios (servicios de cada módulo)
        ↓
Compartido (helpers, interfaces, assets)
```

## 🧰 Contenedor de dependencias

`core/container.php` ofrece registro y resolución sencilla de servicios.

```php
$container->register('calc_service', function () {
    return new CalcService();
});

$calc = $container->get('calc_service');
```

`core/bootstrap.php` instancia el contenedor, carga la configuración y registra los servicios disponibles.

## 🧩 Módulos

- **calc/**: cálculos de tarifas de charter.
- **mail/**: envío de correos y soporte Outlook.
- **template/**: gestión de plantillas de correo.
- **render/**: motor de renderizado *(pendiente de completar)*.
- **yachtinfo/**: extracción de datos de yates *(pendiente de completar)*.

## ♻️ Flujo de ejemplo

1. El tema incluye `core/bootstrap.php`.
2. El contenedor registra los servicios.
3. Un módulo solicita un servicio:

```php
$container = AppYachtBootstrap::getContainer();
$result = $container->get('calc_service')->calculate($data);
```

## 📦 Recursos compartidos

El directorio `shared/` provee interfaces, helpers de PHP, scripts y estilos usados por los módulos.
