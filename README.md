# DistriCarnes Hermanos Navarro — Plataforma e‑commerce

Proyecto web para ventas de carnes y productos relacionados, con catálogo, carrito, checkout multi‑método de pago, factura imprimible/QR/correo y panel administrativo de ventas. Este documento describe arquitectura, módulos, endpoints, datos, operación y guías de mantenimiento.

## Tabla de contenidos

- Introducción y alcance
- Arquitectura y tecnologías
- Estructura del proyecto
- Funcionalidades (cliente y admin)
- Flujo de compra y facturación
- Base de datos (esquema)
- API (endpoints y payloads)
- Configuración (empresa, PayPal, SMTP)
- Puesta en marcha (local y despliegue)
- Convenciones, seguridad y mantenimiento
- Solución de problemas frecuentes (FAQ)

## Arquitectura y tecnologías
 n
- Frontend: HTML5, CSS3, JavaScript (vanilla). Librerías de apoyo: SweetAlert2, Font Awesome.
- Backend: PHP 8+, PDO sobre PostgreSQL.
- Integraciones: PayPal (SDK JS + API), SMTP para correo.
- Patrón: frontend estático que interactúa con endpoints PHP; estado de sesión en Storage del navegador; vistas serverless amigables para hosting tipo Render.

## Estructura del proyecto

- Frontend general
  - [static/js/header_actions.js](file:///c:/Users/progr/DISTRICARNES/static/js/header_actions.js) — gestión de sesión, avatar, visibilidad de botones y navegación del menú de usuario con rutas absolutas.
  - [static/js/index.js](file:///c:/Users/progr/DISTRICARNES/static/js/index.js) — UX móvil, modal de búsqueda, organización del header y eventos globales.
  - [static/css/header_en_general.css](file:///c:/Users/progr/DISTRICARNES/static/css/header_en_general.css) — estilos responsivos del header.
  - Carrito: [carrito-de-compras/index.html](file:///c:/Users/progr/DISTRICARNES/carrito-de-compras/index.html) con estilos [static/css/cart.css](file:///c:/Users/progr/DISTRICARNES/static/css/cart.css) (tema oscuro).

- Checkout
  - [checkout/direccion.html](file:///c:/Users/progr/DISTRICARNES/checkout/direccion.html) — pasos de dirección/entrega/pago. Integra PayPal, Nequi, Tarjeta, Google Pay y Efectivo (pendiente).

- Backend
  - Factura HTML: [backend/php/orders/order_invoice.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/order_invoice.php) — muestra empresa/cliente, ítems, totales, estado legible, QR y “Factura enviada a: …”.
  - Guardado de órdenes: [backend/php/orders/orders_save.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/orders_save.php) (PayPal) y [backend/php/orders/orders_save_pending.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/orders_save_pending.php) (otros métodos).
  - Captura de PayPal: [backend/php/payments/capture_paypal_order.php](file:///c:/Users/progr/DISTRICARNES/backend/php/payments/capture_paypal_order.php) (si aplica).
  - Envío de factura por email: [backend/php/orders/send_invoice_email.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/send_invoice_email.php) (SMTP configurable).
  - Cotización de envío: [backend/php/orders/shipping_quote.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/shipping_quote.php).
  - Consulta de usuario por email (foto): [backend/php/auth/get_user_by_email.php](file:///c:/Users/progr/DISTRICARNES/backend/php/auth/get_user_by_email.php).
  - CRUD de ventas (admin): [backend/php/sales/admin_sales_crud.php](file:///c:/Users/progr/DISTRICARNES/backend/php/sales/admin_sales_crud.php).
  - Configuración de empresa: [backend/php/core/factus_config.php](file:///c:/Users/progr/DISTRICARNES/backend/php/core/factus_config.php).
  - Configuración de PayPal: [backend/php/core/paypal_config.php](file:///c:/Users/progr/DISTRICARNES/backend/php/core/paypal_config.php).
  - Configuración de correo: [backend/php/core/email_config.php](file:///c:/Users/progr/DISTRICARNES/backend/php/core/email_config.php).

- Admin
  - Ventas: [admin/admin_sales.html](file:///c:/Users/progr/DISTRICARNES/admin/admin_sales.html). Enlaces desde [admin/admin_dashboard.html](file:///c:/Users/progr/DISTRICARNES/admin/admin_dashboard.html), [admin/admin_orders.html](file:///c:/Users/progr/DISTRICARNES/admin/admin_orders.html), [admin/admin_reports.html](file:///c:/Users/progr/DISTRICARNES/admin/admin_reports.html), [admin/admin_inventory.html](file:///c:/Users/progr/DISTRICARNES/admin/admin_inventory.html).

## Funcionalidades

Cliente (web)
- Catálogo con tarjetas de producto, favoritos e historial.
- Buscador con modal en móvil; tecla Enter en escritorio.
- Carrito oscuro con cantidades, totales, promo y PayPal rápido.
- Header responsivo con avatar; carrito visible solo con sesión. El avatar se resuelve con URL raíz y fallback por email.
- Checkout:
  - Entrega a domicilio o retiro en punto.
  - Métodos: PayPal (captura y guardado), Nequi, Tarjeta, Google Pay y Efectivo (estos guardan como PENDING).
  - Al finalizar: se abre la factura automáticamente y se envía por correo.

Admin
- Ventas: listado con filtros (estado/fecha), creación manual, cambio rápido de estado, eliminación, acceso a factura.
- Enlace a reportes e inventario.

## Flujo de compra y facturación

1) Sesión del usuario
- Se conserva en `localStorage.userData` o `sessionStorage.currentSession` con `user` adentro.
- El header toma la foto de `usuario_foto`/`foto`/`picture`. Si falta, solicita al backend por email (ruta absoluta) o muestra iniciales.

2) Carrito y resumen
- Eventos `cart:updated` sincronizan componentes. El total incluye envío según [shipping_quote.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/shipping_quote.php).

3) Pago
- PayPal: SDK JS crea/captura orden, backend guarda con `orders_save.php`. Tras éxito, limpia carrito, envía email y redirige a factura `order_invoice.php?order_id=…&print=1`.
- Otros métodos: `orders_save_pending.php` crea orden PENDING, envía email y redirige a la factura de inmediato.

4) Factura
- Código único `FAC-YYYYMMDD-<base36(id)>` con QR. Moneda: COP. Estado legible: Pendiente/Preparando/Enviado/Entregado/Completado/Cancelado (mapea `0` a “Pendiente”).

## Base de datos

Tablas (creadas automáticamente si no existen):

```sql
CREATE TABLE IF NOT EXISTS orders_pg (
  id SERIAL PRIMARY KEY,
  paypal_id VARCHAR(128) NULL,
  user_email VARCHAR(255),
  user_name VARCHAR(255),
  status VARCHAR(32) NOT NULL,
  total NUMERIC(12,2) NOT NULL DEFAULT 0,
  delivery_method VARCHAR(32) NOT NULL,
  pay_method VARCHAR(32) NULL,
  address_json JSONB NULL,
  schedule_json JSONB NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items_pg (
  id SERIAL PRIMARY KEY,
  order_id INT NOT NULL REFERENCES orders_pg(id) ON DELETE CASCADE,
  title VARCHAR(255),
  price NUMERIC(12,2) NOT NULL DEFAULT 0,
  qty INT NOT NULL DEFAULT 1,
  image TEXT NULL
);
```

Estados sugeridos: `PENDING`, `PROCESSING`, `SHIPPED`, `DELIVERED`, `COMPLETED`, `CANCELLED`.

## API (endpoints principales)

- Guardar orden PayPal — [orders_save.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/orders_save.php)
  - Entrada JSON (resumen): `{ paypal_id, status, total, delivery, address, schedule, items[], user{email,name} }`
  - Respuesta: `{ ok: true, order_id }`

- Guardar orden PENDING — [orders_save_pending.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/orders_save_pending.php)
  - Entrada: `{ total, delivery, address, schedule, pay, items[], user{email,name} }`
  - Respuesta: `{ ok: true, order_id }`

- Factura — [order_invoice.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/order_invoice.php)
  - GET `?order_id=ID[&print=1]` — imprime si `print=1`.

- Envío de factura por correo — [send_invoice_email.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/send_invoice_email.php)
  - Entrada: `{ order_id, to }` — usa SMTP según configuración.

- CRUD de ventas (admin) — [admin_sales_crud.php](file:///c:/Users/progr/DISTRICARNES/backend/php/sales/admin_sales_crud.php)
  - `GET ?action=list[&status=&from=&to=]` — lista.
  - `POST ?action=create` — crea (JSON: `email,name,total,status,pay_method,items[]`).
  - `POST ?action=update` — actualiza (JSON: `id` + campos).
  - `GET ?action=delete&id=ID` — elimina.

## Configuración

### Datos de la empresa
- Centralizados en [factus_config.php](file:///c:/Users/progr/DISTRICARNES/backend/php/core/factus_config.php). Definen nombre, NIT, correo, teléfono, dirección y moneda.

### PayPal
- Variables de entorno usadas por [paypal_config.php](file:///c:/Users/progr/DISTRICARNES/backend/php/core/paypal_config.php):
  - `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_ENV` (`sandbox`/`live`), `PAYPAL_CURRENCY` (ej. `USD`/`COP`), `PAYPAL_SKIP_SSL_VERIFY` (`1` en entornos locales si el certificado no está disponible).

### SMTP/Email
- Variables de [email_config.php](file:///c:/Users/progr/DISTRICARNES/backend/php/core/email_config.php):
  - `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE` (`tls`/`ssl`), `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM`, `MAIL_FROM_NAME`.
- Envío implementado en [smtp_mailer.php](file:///c:/Users/progr/DISTRICARNES/backend/php/core/smtp_mailer.php).

## Puesta en marcha

Requisitos: PHP 8+, PostgreSQL accesible, variables de entorno configuradas.

Desarrollo local (Windows):
```bash
php -S localhost:8080 -t c:/Users/progr/DISTRICARNES
```
Abrir: `http://localhost:8080/`

Despliegue (Render u otro hosting):
- Apuntar document root al directorio del proyecto.
- Definir variables de entorno (PayPal/SMTP).
- Asegurar permisos de conexión a PostgreSQL.

## Convenciones, seguridad y mantenimiento

- Rutas absolutas para navegación del menú usuario y fotos de avatar (evita fallos desde subrutas como `/checkout`). Ver [header_actions.js](file:///c:/Users/progr/DISTRICARNES/static/js/header_actions.js).
- Nunca exponer llaves en el repositorio; usar variables de entorno.
- Validar/escapar entradas en endpoints y sanitizar salidas en HTML.
- Estados legibles en factura (mapeo de `0` a “Pendiente”). Ver [order_invoice.php](file:///c:/Users/progr/DISTRICARNES/backend/php/orders/order_invoice.php).
- Limpieza del carrito tras crear orden y notificación con `cart:updated`.

## Solución de problemas frecuentes (FAQ)

- “Orden no encontrada” al abrir la factura:
  - Usar el `order_id` devuelto por el backend tras guardar la orden. El checkout ya redirige automáticamente.

- “No se envía el correo de factura”:
  - Revisar `SMTP_HOST/PORT/SECURE/USER/PASS` y `MAIL_FROM`. Probar credenciales fuera del sistema.

- “El avatar del usuario no carga”:
  - Verificar que la foto sea accesible con ruta desde la raíz (`/uploads/...`) o que exista en la BD para `get_user_by_email.php`.

- “El carrito sigue mostrando datos después de pagar”:
  - Asegurar que el Storage no esté bloqueado; el sistema limpia `cart_items*` y emite `cart:updated`.

Tecnologías Utilizadas: para el fondo de sesion1hero en la pagina principal index.html 
Frontend Base:
HTML5 - Estructura semántica
CSS3 - Estilos personalizados, animaciones, gradientes
JavaScript (ES6+) - Lógica interactiva
Librerías y Frameworks:
Tailwind CSS (vía CDN) - Framework CSS utilitario
anime.js (v3.2.2) - Animaciones JavaScript ligeras
Typed.js (v2.0.16) - Efecto de escritura typewriter
Font Awesome 6.4.0 - Iconos
Bootstrap Icons - Iconos adicionales
Tipografía:
Google Fonts:
Playfair Display - Títulos (serif elegante)
Poppins - Texto body (sans-serif moderna)
Técnicas Implementadas:
Canvas HTML5 - Renderizado del efecto plexus (red de puntos y líneas)
API de Animación - requestAnimationFrame para 60 FPS
Gradientes CSS - Fondos y efectos visuales
Responsive Design - Mobile-first con Tailwind
CSS Grid/Flexbox - Layouts modernos

## Autores

- Jaider Alberto Navarro — Líder Técnico / Jefe de Proyecto
- Jairo Requena Caraballo — Analista de Datos
- Juan Humberto Vega Sanchez — Desarrollador Full‑Stack
- Diego Andres Cardona Quintana — Desarrollador Frontend/Backend
- Francisco Javier Sanz Ortiz — Encargado de Pruebas y Entrega
