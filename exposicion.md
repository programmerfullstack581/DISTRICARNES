# DISTRICARNES - Guía de Presentación

## Información del Proyecto

**Nombre:** DistriCarnes Hermanos Navarro  
**Tipo:** Plataforma e-commerce para venta de carnes  
**Técnologías:** PHP 8+, PostgreSQL, HTML5, CSS3, JavaScript

---

## Equipo de Desarrollo

| Rol | Nombre |
|-----|--------|
| Líder Técnico / Jefe de Proyecto | Jaider Alberto Navarro |
| Analista de Datos | Jairo Requena Caraballo |
| Desarrollador Full-Stack | Juan Humberto Vega Sanchez |
| Desarrollador Frontend/Backend | Diego Andres Cardona Quintana |
| Encargado de Pruebas y Entrega | Francisco Javier Sanz Ortiz |

---

## Estructura de la Presentación

### 1. Introducción (1-2 minutos)
> "DistriCarnes es una plataforma e-commerce para venta de carnes y productos cárnicos desarrollada con PHP, PostgreSQL y JavaScript moderno. Permite a los clientes comprar carnes premium desde cualquier dispositivo."

### 2. Tecnologías (30 segundos)

| Capa | Herramientas |
|------|--------------|
| Frontend | HTML5, CSS3, JavaScript ES6+, Tailwind CSS, SweetAlert2, Font Awesome |
| Backend | PHP 8+ con PDO |
| Base de datos | PostgreSQL (Supabase) |
| Pagos | PayPal, Nequi, Tarjeta, Google Pay, Efectivo |

### 3. Arquitectura del Proyecto (1 minuto)

```
DISTRICARNES/
├── index.php              # Landing con efecto plexus (Canvas)
├── productos.php          # Catálogo con filtros y búsqueda
├── promociones.php        # Ofertas y promociones
├── perfil.php             # Perfil de usuario
├── historial.php          # Historial de compras
├── favoritos.php          # Productos favoritos
├── contacto.php           # Página de contacto
├── sobre_nosotros.php     # Información de la empresa
│
├── login/                 # Sistema de autenticación
│   ├── login.php
│   ├── register.php
│   ├── cambiar_contrasena.php
│   └── restablecer_contrasena.php
│
├── carrito-de-compras/    # Carrito de compras
│   ├── index.php
│   └── app.js             # Lógica del carrito
│
├── checkout/              # Proceso de compra
│   └── direccion.php      # Dirección y métodos de pago
│
├── admin/                 # Panel de administración
│   ├── admin_dashboard.html
│   ├── admin_sales.html
│   ├── admin_products.html
│   ├── admin_orders.html
│   ├── admin_inventory.html
│   └── ...
│
├── backend/php/           # Lógica del servidor
│   ├── conexion.php       # Conexión PDO PostgreSQL
│   ├── orders_save.php    # Guardar órdenes PayPal
│   ├── orders_save_pending.php  # Guardar órdenes otros métodos
│   ├── order_invoice.php  # Generación de facturas HTML
│   ├── smtp_mailer.php    # Envío de correos
│   └── ...
│
├── static/
│   ├── js/               # JavaScript del cliente
│   ├── css/              # Estilos CSS
│   └── images/           # Imágenes del sitio
│
└── db/                   # Scripts de base de datos
    └── districarnes_navarro.sql
```

---

## Funcionalidades Principales

### Cliente (Usuario Final)

- **Catálogo de productos** con tarjetas, filtros por categoría y búsqueda
- **Favoritos e historial** de navegación
- **Carrito de compras** con tema oscuro, cantidades y totales
- **Checkout multi-método de pago:**
  - PayPal (captura inmediata)
  - Nequi
  - Tarjeta de crédito/débito
  - Google Pay
  - Efectivo (contraentrega)
- **Sistema de facturación** con código QR y envío por correo
- **Gestión de cuenta** (perfil, contraseñas, direcciones guardadas)
- **Diseño responsivo** (funciona en móvil y escritorio)

### Administrador

- **Dashboard** con estadísticas y gráficos (Chart.js)
- **Gestión de productos** (CRUD completo)
- **Gestión de órdenes** con cambio de estados
- **Gestión de usuarios**
- **Gestión de inventario**
- **Reportes** de ventas
- **Promociones** y ofertas

---

## Flujo de Compra (2 minutos)

```
1. Usuario se registra o inicia sesión
        ↓
2. Navega el catálogo de productos
        ↓
3. Agrega productos al carrito
        ↓
4. Va al checkout (direccion.php)
        ↓
5. Selecciona método de entrega:
   - Domicilio
   - Retiro en punto
        ↓
6. Selecciona método de pago:
   - PayPal / Nequi / Tarjeta / Google Pay / Efectivo
        ↓
7. Confirma la compra
        ↓
8. Backend guarda orden en PostgreSQL
   - Tabla: orders_pg (orden)
   - Tabla: order_items_pg (ítems)
   - Disminuye stock automáticamente
        ↓
9. Se genera factura HTML con QR
        ↓
10. Se envía factura por email (SMTP)
        ↓
11. Usuario puede imprimir la factura
```

---

## Código Clave para Mostrar

### 1. Guardado de Órdenes (orders_save.php)

**Ubicación:** `backend/php/orders/orders_save.php` (líneas 89-118)

```php
// Inserta la orden en la base de datos
$stmt = $conexion->prepare("
  INSERT INTO orders_pg (user_id, paypal_id, user_email, user_name, status, 
                         total, delivery_method, pay_method, address_json, schedule_json)
  VALUES (?,?,?,?,?,?,?,?,?::jsonb,?::jsonb)
  RETURNING id
");
$stmt->execute([$userId ?: null, $paypalId, $userEmail, $userName, $status, 
                $total, $delivery, $payMethod, json_encode($address), 
                json_encode($schedule)]);

// Inserta cada item de la orden
foreach ($items as $it) {
  $ins->execute([$orderId, $title, $price, $qty, $img]);
  
  // Disminuye el stock del producto
  $stockStmt = $conexion->prepare("UPDATE producto SET stock = stock - ? 
                                    WHERE id_producto = ? AND stock >= ?");
  $stockStmt->execute([$qty, $productId, $qty]);
}
```

**Explicar:** Este código inserta la orden y sus items en PostgreSQL, además de actualizar el inventario automáticamente.

---

### 2. Lógica del Carrito (app.js)

**Ubicación:** `carrito-de-compras/app.js` (líneas 81-128)

```javascript
// Función que agrega un item al carrito
function agregarItemAlCarrito(titulo, precio, imagenSrc){
    var item = document.createElement('div');
    var itemsCarrito = document.getElementsByClassName('carrito-items')[0];

    // Controla que el item no esté duplicado
    var nombresItemsCarrito = itemsCarrito.getElementsByClassName('carrito-item-titulo');
    for(var i=0;i < nombresItemsCarrito.length;i++){
        if(nombresItemsCarrito[i].innerText==titulo){
            alert("El item ya se encuentra en el carrito");
            return;
        }
    }

    // Crea el elemento HTML del item
    var itemCarritoContenido = `
        <div class="carrito-item">
            <img src="${imagenSrc}" width="80px" alt="">
            <div class="carrito-item-detalles">
                <span class="carrito-item-titulo">${titulo}</span>
                <div class="selector-cantidad">
                    <i class="fa-solid fa-minus restar-cantidad"></i>
                    <input type="text" value="1" class="carrito-item-cantidad" disabled>
                    <i class="fa-solid fa-plus sumar-cantidad"></i>
                </div>
                <span class="carrito-item-precio">${precio}</span>
            </div>
            <button class="btn-eliminar">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `
    item.innerHTML = itemCarritoContenido;
    itemsCarrito.append(item);
    
    // Agregar eventos a los nuevos botones
    item.getElementsByClassName('btn-eliminar')[0].addEventListener('click', eliminarItemCarrito);
    item.getElementsByClassName('restar-cantidad')[0].addEventListener('click', restarCantidad);
    item.getElementsByClassName('sumar-cantidad')[0].addEventListener('click', sumarCantidad);
    
    actualizarTotalCarrito();
}
```

**Explicar:** Esta función crea dinámicamente los elementos HTML del carrito, evita productos duplicados y actualiza el total.

---

### 3. Efecto Visual Plexus (index.php)

**Ubicación:** `index.php` (líneas 1039-1144)

```javascript
// Efecto de red de puntos interconectados
function initPlexusNetwork() {
    const canvas = document.getElementById('plexus-canvas');
    const ctx = canvas.getContext('2d');
    
    // Configuración de partículas
    const config = {
        dotCount: 100,
        dotSize: 2,
        connectionDistance: 150,
        mouseDistance: 250,
        baseSpeed: 0.4,
    };
    
    // Crear dots (partículas)
    const dots = [];
    for (let i = 0; i < config.dotCount; i++) {
        dots.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * config.baseSpeed,
            vy: (Math.random() - 0.5) * config.baseSpeed,
            size: config.dotSize + Math.random() * 1.5,
        });
    }
    
    // Animación en loop (60 FPS con requestAnimationFrame)
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        dots.forEach((dot, index) => {
            // Mover partícula
            dot.x += dot.vx;
            dot.y += dot.vy;
            
            // Rebotar en bordes
            if (dot.x < 0 || dot.x > canvas.width) dot.vx *= -1;
            if (dot.y < 0 || dot.y > canvas.height) dot.vy *= -1;
            
            // Dibujar punto
            drawDot(dot);
            
            // Conectar puntos cercanos
            for (let j = index + 1; j < dots.length; j++) {
                const dist = getDistance(dot.x, dot.y, dots[j].x, dots[j].y);
                if (dist < config.connectionDistance) {
                    const opacity = 1 - (dist / config.connectionDistance);
                    drawLine(dot.x, dot.y, dots[j].x, dots[j].y, opacity);
                }
            }
        });
        requestAnimationFrame(animate);
    }
    animate();
}
```

**Explicar:** Este código usa Canvas HTML5 y requestAnimationFrame para crear un efecto visual de partículas interconectadas que reacciona al movimiento del mouse. Es un ejemplo de técnicas avanzadas de JavaScript.

---

### 4. Factura con QR (order_invoice.php)

**Ubicación:** `backend/php/orders/order_invoice.php` (líneas 110-119)

```php
// Código único de factura
$invoiceCode = 'FAC-' . date('Ymd', $createdAt) . '-' . 
               strtoupper(base_convert($orderId, 10, 36));

// URL de la factura para QR
$invoiceUrl = $scheme . '://' . $host . $base . '/order_invoice.php?order_id=' . 
              urlencode((string)$orderId);

// Generar QR
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . 
         urlencode($invoiceUrl);
```

**Explicar:** Cada factura tiene un código único y un código QR que permite acceder directamente a la factura digital.

---

## Base de Datos

### Tablas Principales

**orders_pg** - Órdenes de compra
```sql
CREATE TABLE orders_pg (
  id SERIAL PRIMARY KEY,
  user_id INT NULL,
  paypal_id VARCHAR(64) NULL,
  user_email VARCHAR(255) NULL,
  user_name VARCHAR(255) NULL,
  status VARCHAR(32) NOT NULL,
  total NUMERIC(12,2) NOT NULL DEFAULT 0,
  delivery_method VARCHAR(32) NOT NULL,
  pay_method VARCHAR(32) NULL,
  address_json JSONB NULL,
  schedule_json JSONB NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**order_items_pg** - Ítems de cada orden
```sql
CREATE TABLE order_items_pg (
  id SERIAL PRIMARY KEY,
  order_id INT NOT NULL REFERENCES orders_pg(id) ON DELETE CASCADE,
  title VARCHAR(255),
  price NUMERIC(12,2) NOT NULL DEFAULT 0,
  qty INT NOT NULL DEFAULT 1,
  image TEXT NULL
);
```

### Estados de Orden
- PENDING (Pendiente)
- PROCESSING (Procesando)
- SHIPPED (Enviado)
- DELIVERED (Entregado)
- COMPLETED (Completado)
- CANCELLED (Cancelado)

---

## Panel de Administración

El admin incluye:
- **Dashboard** - Estadísticas con gráficos Chart.js
- **Ventas** - Listado, filtros, cambio de estado
- **Productos** - CRUD completo
- **Órdenes** - Gestión de pedidos
- **Usuarios** - Administración de clientes
- **Inventario** - Control de stock
- **Reportes** - Gráficos de ventas

---

## Puntos Fuertes del Proyecto

1. ✅ **Código bien estructurado** - Separación clara frontend/backend
2. ✅ **Diseño responsivo** - Funciona en móvil y escritorio
3. ✅ **Múltiples métodos de pago** - PayPal, Nequi, Tarjeta, Efectivo
4. ✅ **Facturación automática** - QR, HTML, correo electrónico
5. ✅ **Panel administrativo completo** - CRUD de productos, órdenes, usuarios
6. ✅ **Sistema de favoritos e historial**
7. ✅ **Efectos visuales atractivos** - Animaciones Canvas, gradientes
8. ✅ **Seguridad** - Prepared statements, validación de datos

---

## Cómo Ejecutar el Proyecto

### Desarrollo Local (Windows)

```bash
php -S localhost:8080 -t C:\Users\progr\DISTRICARNES
```

Abrir en navegador: `http://localhost:8080/`

### Requisitos
- PHP 8+ instalado
- Extensión PDO PostgreSQL habilitada
- PostgreSQL accesible (local o Supabase)

---

## Credenciales de Prueba

### Usuario Normal
```
Correo: comprador@gmail.com
Clave: [redactada]
```

### Administrador
```
Correo: juanhumbertovega600@gmail.com
Clave: [redactada]
```

### PayPal Sandbox (Pruebas)
```
Business: sb-d8pfl45742180@business.example.com
Clave: [redactada]

Personal: sb-dkqun45742788@personal.example.com
Clave: [redactada]
```

---

## Conclusión

**DistriCarnes** es un proyecto e-commerce completo y funcional que demuestra competencias en:

- Programación PHP y JavaScript
- Diseño de bases de datos PostgreSQL
- Integración de pasarelas de pago
- Desarrollo frontend responsivo
- Arquitectura de aplicaciones web

El sistema cubre todo el ciclo de venta online: desde la navegación del catálogo hasta la facturación y entrega del producto.

---

*Material preparado para presentación del proyecto - Marzo 2026*