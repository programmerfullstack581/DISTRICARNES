# DISTRICARNES - Presentación para el SENA

**Fecha de presentación:** 26 de marzo de 2026  
**Proyecto:** E-commerce para carnicería

---

## 1. Información del Proyecto

**Nombre:** DistriCarnes Hermanos Navarro  
**Tipo:** Plataforma de comercio electrónico (e-commerce)  
**Naturaleza:** Venta de carnes y productos cárnicos online  
**Technologías:** PHP 8+, PostgreSQL, HTML5, CSS3, JavaScript

---

## 2. Equipo de Desarrollo

| Rol | Nombre |
|-----|--------|
| Líder Técnico / Jefe de Proyecto | Jaider Alberto Navarro |
| Analista de Datos | Jairo Requena Caraballo |
| Desarrollador Full-Stack | Juan Humberto Vega Sanchez |
| Desarrollador Frontend/Backend | Diego Andres Cardona Quintana |
| Encargado de Pruebas y Entrega | Francisco Javier Sanz Ortiz |

---

## 3. Funcionalidades Principales

### Cliente (Usuario Final)
- **Catálogo de productos** con tarjetas, filtros y búsqueda
- **Favoritos e historial** de navegación
- **Carrito de compras** con tema oscuro
- **Checkout multi-método de pago:**
  - PayPal (captura inmediata)
  - Nequi
  - Tarjeta de crédito/débito
  - Google Pay
  - Efectivo (contraentrega)
- **Sistema de facturación** con QR y envío por correo
- **Gestión de cuenta** (perfil, contraseñas, direcciones)
- **Diseño responsivo** (móvil y escritorio)

### Administrador
- **Dashboard** con estadísticas
- **Gestión de productos** (CRUD)
- **Gestión de órdenes** (estados, filtros)
- **Gestión de usuarios**
- **Gestión de inventario**
- **Reportes** de ventas
- **Promociones** y ofertas

---

## 4. Arquitectura del Sistema

```
DISTRICARNES/
├── index.php              # Página principal
├── productos.php          # Catálogo de productos
├── promociones.php        # Ofertas y promociones
├── perfil.php            # Perfil de usuario
├── historial.php         # Historial de compras
├── favoritos.php         # Productos favoritos
├── contacto.php          # Página de contacto
├── sobre_nosotros.php    # Información de la empresa
│
├── login/                # Sistema de autenticación
│   ├── login.php
│   ├── register.php
│   └── cambiar_contrasena.php
│
├── carrito-de-compras/   # Carrito de compras
│   ├── index.php
│   └── estilo.css
│
├── checkout/             # Proceso de compra
│   └── direccion.php
│
├── admin/               # Panel de administración
│   ├── admin_dashboard.html
│   ├── admin_sales.html
│   ├── admin_products.html
│   ├── admin_orders.html
│   └── ...
│
├── backend/php/         # Lógica del servidor
│   ├── conexion.php     # Conexión a PostgreSQL
│   ├── orders_save.php  # Guardar órdenes PayPal
│   ├── order_invoice.php # Generación de facturas
│   ├── smtp_mailer.php # Envío de correos
│   └── ... (otros endpoints)
│
├── static/
│   ├── js/             # JavaScript del cliente
│   ├── css/            # Estilos CSS
│   └── images/          # Imágenes del sitio
│
└── db/                 # Base de datos
    └── districarnes_navarro.sql
```

---

## 5. Tecnologías Utilizadas

### Frontend
- **HTML5** - Estructura semántica
- **CSS3** - Estilos personalizados, animaciones, gradientes
- **JavaScript (ES6+)** - Lógica interactiva
- **Tailwind CSS** (vía CDN) - Framework CSS utilitario
- **SweetAlert2** - Alertas y confirmaciones
- **Font Awesome** - Iconos

### Backend
- **PHP 8+** - Lenguaje del servidor
- **PDO PostgreSQL** - Conexión a base de datos
- **Supabase** - Base de datos en la nube (PostgreSQL)

### Integraciones
- **PayPal** - Pagos en línea
- **SMTP (Gmail)** - Envío de facturas por correo

### Técnicas Avanzadas
- **Canvas HTML5** - Efecto visual plexus (red de puntos)
- **API de Animación** - requestAnimationFrame para 60 FPS
- **Responsive Design** - Mobile-first
- **CSS Grid/Flexbox** - Layouts modernos

---

## 6. Base de Datos

### Tablas Principales

**orders_pg** - Órdenes de compra
- id, paypal_id, user_email, user_name, status
- total, delivery_method, pay_method
- address_json, schedule_json, created_at

**order_items_pg** - Ítems de cada orden
- id, order_id, title, price, qty, image

---

## 7. Configuración Necesaria

### Variables de Entorno (para despliegue en Render)

```
HOST=...           # Host de PostgreSQL (Supabase)
DB_PORT=6543      # Puerto de PostgreSQL
DB_NAME=...       # Nombre de la base de datos
DB_USER=...       # Usuario de PostgreSQL
DB_PASSWORD=...   # Contraseña de PostgreSQL

PAYPAL_CLIENT_ID=...    # ID de cliente PayPal
PAYPAL_SECRET=...       # Secreto de PayPal
PAYPAL_ENV=sandbox      # Entorno (sandbox/live)

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=districarneshermanosnavarro@gmail.com
SMTP_PASS=...
```

---

## 8. Cómo Ejecutar el Proyecto

### Desarrollo Local (Windows)

```bash
php -S localhost:8080 -t C:\Users\progr\DISTRICARNES
```

Abrir en navegador: `http://localhost:8080/`

### Requisitos
- PHP 8+ instalado
- Extensión PDO PostgreSQL habilitada
- PostgreSQL accesible (local o en la nube)

---

## 9. Puntos Fuertes del Proyecto

1. ✅ **Código bien estructurado** - Separación clara frontend/backend
2. ✅ **Diseño responsivo** - Funciona en móvil y escritorio
3. ✅ **Múltiples métodos de pago** - PayPal, Nequi, Tarjeta, Efectivo
4. ✅ **Facturación automática** - QR, PDF, correo
5. ✅ **Panel administrativo completo** - CRUD de productos, órdenes, usuarios
6. ✅ **Sistema de favoritos e historial**
7. ✅ **Efectos visuales atractivos** - Animaciones Canvas, gradientes
8. ✅ **Seguridad** - Prepared statements, validación de datos

---

## 10. Mejoras Identificadas

1. ⚠️ **Optimización de imágenes** - Compresión de imágenes para mejor velocidad
2. ⚠️ **Cacheo de datos** - Implementar Redis o cache en cliente
3. ⚠️ **PWA** - Convertir en Aplicación Web Progresiva
4. ⚠️ **Panel de administración moderno** - Migrar de HTML a框架 moderno
5. ⚠️ **Sistema de reseñas** - Agregar calificaciones de productos
6. ⚠️ **Chatbot mejorado** - Integrar IA para atención al cliente

---

## 11. Credenciales de Prueba

### Usuarios de Prueba
```
Usuario: comprador@gmail.com
Clave: [redactada]

Administrador: juanhumbertovega600@gmail.com
Clave: [redactada]
```

### PayPal Sandbox
```
Business: sb-d8pfl45742180@business.example.com
Clave: [redactada]

Personal: sb-dkqun45742788@personal.example.com
Clave: [redactada]
```

---

## 12. Conclusión

**DistriCarnes** es un proyecto e-commerce completo y funcional, desarrollado con tecnologías modernas y prácticas de desarrollo profesional. El sistema cubre todo el ciclo de venta online: desde la navegación del catálogo hasta la facturación y entrega del producto.

El proyecto demuestra competencias en:
- Programación PHP y JavaScript
- Diseño de bases de datos PostgreSQL
- Integración de pasarelas de pago
- Desarrollo frontend responsivo
- Arquitectura de aplicaciones web

---

*Documento preparado para presentación en el SENA - Marzo 2026*
