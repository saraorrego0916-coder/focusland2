# FocusLand - Plataforma de Apoyo Educativo

## 📋 Descripción

**FocusLand** es una plataforma web moderna diseñada para brindar servicios de apoyo educativo, terapia infantil y orientación familiar. Ofrece una experiencia de usuario completa con información sobre servicios, recursos educativos interactivos y sistema de agendamento de citas.

## 🎯 Características Principales

- ✨ **Diseño Moderno y Responsivo** - Adapta perfectamente a todos los dispositivos
- 🎨 **Interfaz Intuitiva** - Navegación clara y accesible
- 📹 **Reproductor de Videos** - Modal interactivo para videos educativos
- 📚 **Biblioteca de Artículos** - Contenido especializado organizado en modal
- 🛠️ **Modalidades de Servicios** - Información expandible sobre evaluación, terapia y orientación
- 📅 **Sistema de Citas** - Formulario para agendar consultas
- 🌈 **Animaciones Suaves** - Transiciones y efectos CSS3 profesionales
- 📱 **Menú Móvil** - Navegación hamburguesa para dispositivos pequeños

## 🛠️ Tecnologías Utilizadas

- **HTML5** - Estructura semántica
- **CSS3** - Estilos avanzados con animaciones y gradientes
- **JavaScript Vanilla** - Interactividad sin dependencias externas
- **Font Awesome 6.4.0** - Iconografía profesional
- **Google Fonts** - Tipografía (Poppins)

## 📁 Estructura del Proyecto

```
focusland2.0/
├── index.html              # Página principal (Hero + Introducción)
├── quienes.html            # Sobre el proyecto (Misión, Visión, Valores)
├── servicios.html          # Servicios con modales expandibles
├── recursos.html           # Recursos educativos (Videos + Artículos)
├── cita.html              # Formulario de agendamiento
├── contacto.html          # Información y redes de contacto
├── style.css              # Estilos globales
├── imagenes/              # Carpeta de imágenes y logo
│   └── logo.jpg.jpeg
└── README.md              # Este archivo
```

## 📄 Descripción de Páginas

### 1. **index.html** - Inicio
- Hero section con imagen de fondo y textos inspiradores
- Introducción al proyecto
- Botones de llamada a acción (Agendar Cita, Conocer Más)

### 2. **quienes.html** - Sobre el Proyecto
- Sección hero con gradiente
- Presentación de FocusLand
- **Pilares corporativos** en paneles con glow animation:
  - Misión
  - Visión
  - Valores
  - Objetivos
- **Sección "Por Qué Elegirnos"** con 6 beneficios
- Call to action final

### 3. **servicios.html** - Nuestros Servicios
- Tarjetas de servicios principales:
  - Evaluación Psicopedagógica
  - Terapia Personalizada
  - Orientación Familiar
- **Modal interactivo** con descripciones detalladas al hacer clic en "Saber más"

### 4. **recursos.html** - Recursos Educativos
- **Videos Educativos**
  - Tile clickeable con icono de play
  - Modal con reproductor YouTube embebido
  - 3 opciones de video seleccionables
- **Artículos Especializados**
  - Tile clickeable con icono de libro
  - Modal con listado de artículos
  - Cada artículo con título, descripción y enlace

### 5. **cita.html** - Agendar Cita
- Formulario completo con campos:
  - Nombre del padre/madre
  - Nombre del niño/a
  - Email
  - Fecha
  - Descripción de la situación
- Envío de solicitud

### 6. **contacto.html** - Contacto
- Información de contacto
- Enlaces a redes sociales
- Email y teléfono disponibles

## 🎨 Paleta de Colores

| Color | Hex | Uso |
|-------|-----|-----|
| Púrpura Primario | #6a5acd | Botones, iconos, enlaces |
| Púrpura Oscuro | #5741c6 | Hover states |
| Rosa Claro | #ffb6f9 | Botones secundarios |
| Gris Claro | #f8f9ff | Fondos alternos |
| Gris Texto | #666 | Párrafos |
| Blanco | #ffffff | Fondos principales |

## 🔧 Componentes Principales

### Navegación
```html
<header class="navbar">
  <!-- Logo, Links, Botones de login/registro -->
  <!-- Menú hamburguesa responsivo con js -->
</header>
```

### Paneles Sobre (Misión, Visión, etc.)
```html
<div class="about-panels">
  <div class="about-panel slide-up">
    <!-- Icono circular, título, descripción -->
    <!-- Animación glow border en hover -->
  </div>
</div>
```

### Modal de Servicios
```html
<div id="serviceModal" class="modal">
  <div class="modal-content">
    <!-- Título dinámico, descripción, botón cerrar -->
  </div>
</div>
```

### Modal de Videos
```html
<div id="videoModal" class="modal">
  <iframe id="videoPlayer"></iframe>
  <!-- Botones para seleccionar video -->
</div>
```

### Modal de Artículos
```html
<div id="articlesModal" class="modal">
  <div class="article-list">
    <!-- Listado de artículos con item styling -->
  </div>
</div>
```

## ⚙️ Funcionalidades JavaScript

### 1. **Menú Móvil Responsivo**
```javascript
const toggle = document.querySelector('.menu-toggle');
const links = document.querySelector('.nav-links');
toggle.addEventListener('click', () => links.classList.toggle('show'));
```

### 2. **Modal de Servicios**
- Abre modal al hacer clic en botón "Saber más"
- Carga contenido dinámico según el servicio
- Cierra al hacer clic en X o fuera del modal

### 3. **Reproductor de Videos**
- Selecciona video y carga en iframe
- Limpia video al cerrar modal
- Soporta múltiples videos con botones

### 4. **Lista de Artículos**
- Abre modal con listado de artículos
- Acciones hover para interactividad
- Enlaces navegables

## 🎬 Animaciones CSS

| Animación | Duración | Uso |
|-----------|----------|-----|
| fadeIn | 0.8s | Entradas suaves |
| slideUp | 0.6s | Elementos fluyen hacia arriba |
| glowBorder | 1.5s | Borde luminoso en hover |
| bgPan | 20s | Movimiento subtle de fondo |

## 📱 Responsividad

- **Desktop** (>800px): Layout completo, navegación expandida
- **Tablet** (600-800px): Ajustes de padding y gap
- **Mobile** (<600px): Menú hamburguesa, grid flexible

## 🚀 Cómo Usar

1. **Descargar/Clonar** el proyecto
2. **Abrir archivos** HTML en navegador moderno (Chrome, Firefox, Edge, Safari)
3. **No requiere instalación** - Es un proyecto frontend puro
4. **Editar contenido** directamente en archivos HTML
5. **Personalizar colores** en style.css

## 📝 Personalización

### Cambiar Color Principal
En `style.css`, buscar `#6a5acd` y reemplazar con tu color:
```css
.btn-secondary, .card i, etc. { color: #tu_color; }
```

### Cambiar Imágenes
En HTML, reemplazar rutas `src`:
```html
<img src="tu_imagen.jpg" alt="descripción">
```

### Actualizar URLs de Videos
En `recursos.html`, buscar `data-src` y actualizar:
```html
<button class="video-btn" data-src="https://www.youtube.com/embed/TU_VIDEO_ID">
```

## 🔐 Consideraciones de Seguridad

- Validar formularios en backend (no implementado)
- Sanitizar inputs antes de procesar datos
- Usar HTTPS en producción
- Proteger emails con servicios como Formspree

## ⚡ Optimizaciones Futuras

- [ ] Backend con Node.js/Python para procesamiento de formularios
- [ ] Base de datos para gestión de citas
- [ ] Sistema de autenticación de usuarios
- [ ] Panel administrativo
- [ ] Integración con Google Calendar
- [ ] Email automático de confirmación
- [ ] SEO mejorado (meta tags, structured data)
- [ ] Compresión de imágenes
- [ ] Lazy loading de contenido

## 📞 Contacto

Para consultas sobre FocusLand:
- Email: info@focusland.com
- Teléfono: +123 456 789
- Redes: Facebook, Instagram, WhatsApp

## 📄 Licencia

Proyecto educativo/comercial. Todos los derechos reservados © 2026 FocusLand.

---

**Última actualización:** 22 de febrero de 2026
