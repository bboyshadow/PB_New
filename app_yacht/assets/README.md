
# Assets - App Yacht v2.0.0

## 📁 Estructura de Assets

```
assets/
├── css/          # Estilos específicos de módulos
├── js/           # JavaScript específico de servicios
└── images/       # Imágenes y gráficos
```

## 🎨 CSS

### Organización
- `app_yacht.css` - Estilos principales (en shared/css/)
- `module-specific.css` - Estilos por módulo

### Convenciones
- Usar prefijo `ay-` para clases específicas
- Mobile-first approach
- Variables CSS para colores y espaciado

## 📜 JavaScript

### Arquitectura JS
- **Compatibility Layer** - Mantiene funcionamiento existente
- **Service Layer** - Nuevas funcionalidades
- **UI Layer** - Interacciones de usuario

### Convenciones
- Usar ES6+ donde sea posible
- Namespace `AppYacht` para funciones globales
- Event-driven architecture

## 🖼️ Images

### Organización
- `icons/` - Iconos SVG optimizados
- `backgrounds/` - Imágenes de fondo
- `yacht-types/` - Imágenes por tipo de yate

### Optimización
- SVG para iconos
- WebP con fallback para fotos
- Lazy loading implementado
