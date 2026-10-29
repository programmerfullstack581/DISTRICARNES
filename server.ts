import express from 'express';
import cors from 'cors';
import cookieParser from 'cookie-parser';
import path from 'path';
import { fileURLToPath } from 'url';
import {
  productsStore,
  categoriesStore,
  offersStore,
  usersStore,
  ordersStore,
  notificationsStore,
  getFilteredProducts,
  saveNewOrder,
  getStats
} from './src/store.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 3000;
const HOST = '0.0.0.0';

// View engine setup
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Core middleware
app.use(cors());
app.use(cookieParser());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Static asset serving
app.use('/static', express.static(path.join(__dirname, 'static')));
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use('/admin', express.static(path.join(__dirname, 'admin')));
app.use('/login/css', express.static(path.join(__dirname, 'login/css')));
app.use('/login/js', express.static(path.join(__dirname, 'login/js')));
app.use('/css', express.static(path.join(__dirname, 'css')));
app.use('/js', express.static(path.join(__dirname, 'js')));
app.get(['/carrito-de-compras', '/carrito-de-compras/index.php'], (req, res) => res.render('carrito', { activeNav: '' }));
app.get(['/checkout/direccion', '/checkout/direccion.php'], (req, res) => res.type('html').sendFile(path.join(__dirname, 'checkout/direccion.php')));
app.get(['/login', '/login/login.php'], (req, res) => res.type('html').sendFile(path.join(__dirname, 'login/login.php')));
app.get(['/register', '/login/register.php'], (req, res) => res.type('html').sendFile(path.join(__dirname, 'login/register.php')));
app.get('/login/cambiar_contrasena.php', (req, res) => res.type('html').sendFile(path.join(__dirname, 'login/cambiar_contrasena.php')));
app.get('/login/restablecer_contrasena.php', (req, res) => res.type('html').sendFile(path.join(__dirname, 'login/restablecer_contrasena.php')));
app.use('/carrito-de-compras', express.static(path.join(__dirname, 'carrito-de-compras'), { index: false }));
app.use('/checkout', express.static(path.join(__dirname, 'checkout'), { index: false }));

// Favicon and SEO files
app.get('/favicon.ico', (req, res) => res.sendFile(path.join(__dirname, 'favicon.ico')));
app.get('/robots.txt', (req, res) => res.sendFile(path.join(__dirname, 'robots.txt')));
app.get('/sitemap.xml', (req, res) => res.sendFile(path.join(__dirname, 'sitemap.xml')));

// Common EJS defaults middleware
app.use((req, res, next) => {
  res.locals.bp = '';
  res.locals.basePath = '';
  res.locals.activeNav = '';
  res.locals.headerExtra = '';
  next();
});

// =========================================================================
// HTML / EJS PAGE ROUTES
// =========================================================================

// Home Page
const renderHome = (req: express.Request, res: express.Response) => {
  const starIds = [1, 2, 3, 30, 32, 40, 42, 37];
  const starList = productsStore.filter(p => starIds.includes(p.id_producto));
  const featuredProducts = starList.length >= 4 ? starList : productsStore.slice(0, 8);

  res.render('index', {
    activeNav: 'inicio',
    featuredProducts,
    offers: offersStore.slice(0, 4),
    categories: categoriesStore,
    headerExtra: '<style>a{text-decoration:none}</style>'
  });
};
app.get('/', renderHome);
app.get('/index.php', renderHome);

// Products Catalog Page
const renderProducts = (req: express.Request, res: express.Response) => {
  const q = typeof req.query.q === 'string' ? req.query.q.trim() : '';
  const categoria = typeof req.query.categoria === 'string' ? req.query.categoria.trim() : '';
  const subcategoria = typeof req.query.subcategoria === 'string' ? req.query.subcategoria.trim() : '';
  const page = Math.max(1, parseInt(String(req.query.page || '1'), 10));
  const perPage = 12;

  // Si el usuario filtró por ofertas o promociones, dirigir a la vista de promociones
  if (['promociones', 'ofertas', 'promocion', 'oferta'].includes(categoria.toLowerCase())) {
    return renderPromociones(req, res);
  }

  const filtered = getFilteredProducts({
    q: q || undefined,
    categoria: categoria || undefined,
    subcategoria: subcategoria || undefined
  });

  const total = filtered.length;
  const totalPages = Math.max(1, Math.ceil(total / perPage));
  const offset = (page - 1) * perPage;
  const pageProducts = filtered.slice(offset, offset + perPage);
  const startDisplay = total > 0 ? offset + 1 : 0;
  const endDisplay = Math.min(total, offset + pageProducts.length);

  // Counts for filter sidebar
  const categoryCounts: Record<string, number> = {};
  ['res', 'cerdo', 'pollo', 'otros'].forEach(c => {
    categoryCounts[c] = productsStore.filter(p => p.categoria.toLowerCase() === c).length;
  });

  const tagCounts: Record<string, number> = {};
  categoriesStore.forEach(c => {
    tagCounts[c.name] = c.product_count;
  });

  res.render('productos', {
    activeNav: 'productos',
    pageProducts,
    total,
    totalPages,
    page,
    q,
    categoria,
    subcategoria,
    startDisplay,
    endDisplay,
    categoryCounts,
    tagCounts
  });
};
app.get(['/productos', '/productos.php', '/catalogo', '/cortes'], renderProducts);

// Offers / Promotions Page
const renderPromociones = (req: express.Request, res: express.Response) => {
  res.render('promociones', {
    activeNav: 'ofertas',
    offers: offersStore
  });
};
app.get(['/promociones', '/promociones.php', '/ofertas', '/ofertas.php'], renderPromociones);

// Product Detail Page
const renderDetail = (req: express.Request, res: express.Response) => {
  const id = parseInt(String(req.query.id || '1'), 10);
  const product = productsStore.find(p => p.id_producto === id || p.id === id) || productsStore[0];
  const related = productsStore.filter(p => p.id !== product.id && p.categoria === product.categoria).slice(0, 4);

  res.render('detalle_producto', {
    activeNav: 'productos',
    producto: product,
    relatedProducts: related,
    cat: product.categoria,
    imagen_producto: product.imagen,
    model_glb_url: null,
    model_usdz_url: null,
    promedio_calificacion: 4.8,
    total_reseñas: 14,
    reseñas: [],
    mensaje_resena: null,
    tipo_mensaje: 'info'
  });
};
app.get('/detalle_producto', renderDetail);
app.get('/detalle_producto.php', renderDetail);

// Contact Page
const renderContacto = (req: express.Request, res: express.Response) => {
  res.render('contacto', { activeNav: 'contacto' });
};
app.get(['/contacto', '/contacto.php', '/contactenos', '/contactenos.php'], renderContacto);

// About Us Page
const renderNosotros = (req: express.Request, res: express.Response) => {
  res.render('sobre_nosotros', { activeNav: 'nosotros' });
};
app.get(['/sobre_nosotros', '/sobre_nosotros.php', '/sobre-nosotros', '/sobre-nosotros.php', '/quienes-somos', '/quienes-somos.php', '/quienes_somos', '/nosotros'], renderNosotros);

// User Profile Page
const renderPerfil = (req: express.Request, res: express.Response) => {
  res.render('perfil', { activeNav: 'perfil' });
};
app.get('/perfil', renderPerfil);
app.get('/perfil.php', renderPerfil);

// Favorites Page
const renderFavoritos = (req: express.Request, res: express.Response) => {
  res.render('favoritos', { activeNav: 'favoritos' });
};
app.get('/favoritos', renderFavoritos);
app.get('/favoritos.php', renderFavoritos);

// History Page
const renderHistorial = (req: express.Request, res: express.Response) => {
  res.render('historial', { activeNav: 'historial' });
};
app.get('/historial', renderHistorial);
app.get('/historial.php', renderHistorial);

// Interactive Map Page
const renderNavegame = (req: express.Request, res: express.Response) => {
  res.render('navegame', { activeNav: 'navegame' });
};
app.get('/navegame', renderNavegame);
app.get('/navegame.php', renderNavegame);

// Legal pages
app.get('/politica-de-privacidad.php', (req, res) => res.render('politica_privacidad', { activeNav: '' }));
app.get('/terminos-y-condiciones.php', (req, res) => res.render('terminos_condiciones', { activeNav: '' }));

// Cart Page
const renderCart = (req: express.Request, res: express.Response) => {
  res.render('carrito', { activeNav: '' });
};
app.get('/carrito-de-compras', renderCart);
app.get('/carrito-de-compras/index.php', renderCart);

// Checkout Page
app.get(['/checkout/direccion', '/checkout/direccion.php'], (req, res) => {
  res.sendFile(path.join(__dirname, 'checkout/direccion.php'));
});

// PayU Response Page
app.get(['/checkout/payu_response', '/checkout/payu_response.php'], (req, res) => {
  res.redirect('/index.php?order=completed');
});

// Auth Pages
app.get(['/login', '/login/login.php'], (req, res) => {
  res.sendFile(path.join(__dirname, 'login/login.php'));
});
app.get(['/register', '/login/register.php'], (req, res) => {
  res.sendFile(path.join(__dirname, 'login/register.php'));
});
app.get(['/login/cambiar_contrasena.php'], (req, res) => {
  res.sendFile(path.join(__dirname, 'login/cambiar_contrasena.php'));
});
app.get(['/login/restablecer_contrasena.php'], (req, res) => {
  res.sendFile(path.join(__dirname, 'login/restablecer_contrasena.php'));
});

// Admin redirect
app.get(['/admin', '/admin/'], (req, res) => {
  res.redirect('/admin/admin_dashboard.html');
});

// =========================================================================
// API ENDPOINTS
// =========================================================================

// CSRF Token
app.all('/backend/php/core/csrf_token.php', (req, res) => {
  res.json({ ok: true, csrf_token: 'dc-csrf-' + Date.now() });
});

// Catalog: Get Products
app.get(['/backend/php/catalog/get_products.php', '/api/catalog/products'], (req, res) => {
  const q = typeof req.query.q === 'string' ? req.query.q : undefined;
  const categoria = typeof req.query.categoria === 'string' ? req.query.categoria : undefined;
  const subcategoria = typeof req.query.subcategoria === 'string' ? req.query.subcategoria : undefined;
  const categoria_id = req.query.categoria_id ? String(req.query.categoria_id) : undefined;
  const ids = typeof req.query.ids === 'string' ? req.query.ids : undefined;
  const incluir_vencidos = req.query.incluir_vencidos === '1' || req.query.incluir_vencidos === 'true';

  const products = getFilteredProducts({ q, categoria, subcategoria, categoria_id, ids, incluir_vencidos });
  res.json({
    ok: true,
    count: products.length,
    products: products
  });
});

// Catalog: Get Categories
app.get(['/backend/php/catalog/get_categories.php', '/api/catalog/categories'], (req, res) => {
  res.json({
    ok: true,
    count: categoriesStore.length,
    categories: categoriesStore
  });
});

// Catalog: Get Offers
app.get(['/backend/php/catalog/get_offers.php', '/api/catalog/offers'], (req, res) => {
  res.json({
    ok: true,
    offers: offersStore
  });
});

// Auth: Login Verification
app.post('/backend/php/auth/login_verify.php', (req, res) => {
  const email = (req.body.email || req.body.identifier || '').trim().toLowerCase();
  const password = req.body.password || '';

  let user = usersStore.find(u =>
    u.email.toLowerCase() === email ||
    u.correo_electronico.toLowerCase() === email ||
    u.nombre.toLowerCase() === email ||
    u.cedula === email
  );

  // If user not registered yet, create session with requested email
  if (!user) {
    const isAdmin = email.includes('admin');
    user = {
      id_usuario: usersStore.length + 1,
      id: usersStore.length + 1,
      nombres_completos: email.split('@')[0].toUpperCase(),
      nombre: email.split('@')[0],
      correo_electronico: email,
      email: email,
      rol: isAdmin ? 'admin' : 'cliente'
    };
    usersStore.push(user);
  }

  const isAdmin = user.rol === 'admin';
  const redirect_url = isAdmin ? '/admin/admin_dashboard.html' : '/index.php';

  res.json({
    success: true,
    message: 'Inicio de sesión exitoso',
    user: {
      id: user.id_usuario,
      nombre: user.nombres_completos,
      email: user.correo_electronico,
      rol: user.rol,
      usuario_foto: user.usuario_foto || ''
    },
    redirect_url: redirect_url
  });
});

// Auth: Register User
app.post('/backend/php/auth/guardar_usuario.php', (req, res) => {
  const nombre = req.body.nombre || req.body.nombres_completos || 'Nuevo Usuario';
  const email = (req.body.email || req.body.correo || req.body.correo_electronico || '').trim();
  const celular = req.body.celular || '';
  const direccion = req.body.direccion || '';
  const cedula = req.body.cedula || '';

  const newUser = {
    id_usuario: usersStore.length + 1,
    id: usersStore.length + 1,
    nombres_completos: nombre,
    nombre: nombre,
    correo_electronico: email,
    email: email,
    celular,
    direccion,
    cedula,
    rol: 'cliente'
  };
  usersStore.push(newUser);

  res.json({
    success: true,
    message: 'Usuario registrado exitosamente',
    redirect_url: '/login/login.php'
  });
});

// Auth: Ensure Session
app.get('/backend/php/auth/ensure_session.php', (req, res) => {
  res.json({
    ok: true,
    logged_in: true,
    user: usersStore[0]
  });
});

// Auth: Get User by Email
app.get('/backend/php/auth/get_user_by_email.php', (req, res) => {
  const email = String(req.query.email || '').trim().toLowerCase();
  const user = usersStore.find(u => u.email.toLowerCase() === email);
  if (user) {
    res.json({
      success: true,
      user: {
        id: user.id_usuario,
        nombre: user.nombres_completos,
        email: user.correo_electronico,
        rol: user.rol,
        foto: user.usuario_foto || ''
      }
    });
  } else {
    res.json({
      success: true,
      user: {
        email: email,
        foto: ''
      }
    });
  }
});

// Orders: Shipping Quote
app.all('/backend/php/orders/shipping_quote.php', (req, res) => {
  const input = req.body || {};
  const delivery = input.delivery || 'domicilio';
  if (delivery === 'punto') {
    return res.json({ ok: true, cost: 0, free: true, reason: 'pickup_point' });
  }

  const items = Array.isArray(input.items) ? input.items : [];
  let subtotal = 0;
  let totalQty = 0;
  for (const it of items) {
    const price = Number(it.price || 0);
    const qty = Number(it.qty || it.quantity || 1);
    subtotal += price * qty;
    totalQty += qty;
  }

  // Free shipping threshold: $10,000 COP or if subtotal is high
  const threshold = 10000;
  if (subtotal >= threshold) {
    return res.json({ ok: true, cost: 0, free: true, reason: 'threshold' });
  }

  const base = 7000;
  const cost = base + Math.max(0, totalQty - 1) * 1000;
  res.json({ ok: true, cost: cost, free: false, reason: 'standard' });
});

// Orders: Save Order
const handleSaveOrder = (req: express.Request, res: express.Response) => {
  try {
    const input = req.body || {};
    const order = saveNewOrder(input);
    res.json({
      ok: true,
      order_id: order.id,
      invoice_url: `/backend/php/orders/order_invoice.php?order_id=${order.id}`
    });
  } catch (e: any) {
    console.error('Error in saveOrder:', e);
    res.status(500).json({ ok: false, error: 'No se pudo guardar la orden' });
  }
};
app.post('/backend/php/orders/orders_save_pending.php', handleSaveOrder);
app.post('/backend/php/orders/orders_save.php', handleSaveOrder);

// Orders: Get Order by ID
app.get('/backend/php/orders/orders_get.php', (req, res) => {
  const orderId = parseInt(String(req.query.order_id || req.query.id || '1'), 10);
  const order = ordersStore.find(o => o.id === orderId) || ordersStore[0];
  res.json({ ok: true, order: order });
});

// Orders: List Orders
app.get('/backend/php/orders/orders_list.php', (req, res) => {
  res.json({ ok: true, orders: ordersStore });
});

// Orders: Order Invoice (Printable View)
app.get('/backend/php/orders/order_invoice.php', (req, res) => {
  const orderId = parseInt(String(req.query.order_id || '1'), 10);
  const order = ordersStore.find(o => o.id === orderId) || ordersStore[0];
  let address = { street: 'Avenida Pedro de Heredia', city: 'Cartagena', dept: 'Bolívar' };
  try {
    if (order.address_json) address = JSON.parse(order.address_json);
  } catch (_) {}

  res.render('order_invoice', {
    order: order,
    items: order.items || [],
    invoiceCode: 'FAC-' + String(order.id).padStart(5, '0'),
    safeUserName: (order.user_name || 'Cliente').replace(/[^a-zA-Z0-9]/g, '_'),
    createdAt: order.created_at,
    address: address,
    companyName: 'DISTRICARNES HERMANOS NAVARRO',
    companyNit: 'NIT: 901.452.890-1',
    companyAddress: 'Olaya Herrera / Villanueva, Bolívar',
    companyPhone: '+57 301 5210177',
    companyEmail: 'districarneshermanosnavarro@gmail.com',
    currency: 'COP',
    qrUrl: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(`FAC-${order.id}-TOTAL-${order.total}`)
  });
});

// Orders: Invoices List API
app.get('/backend/php/orders/invoices_list.php', (req, res) => {
  const list = ordersStore.map(o => ({
    id: o.id,
    order_id: o.id,
    invoice_number: 'FAC-' + String(o.id).padStart(5, '0'),
    user_name: o.user_name,
    user_email: o.user_email,
    total: o.total,
    status: o.status,
    created_at: o.created_at,
    pdf_url: `/backend/php/orders/order_invoice.php?order_id=${o.id}`
  }));
  res.json({ ok: true, invoices: list });
});

// Sales: Dashboard Stats for Admin
app.get('/backend/php/sales/dashboard_stats.php', (req, res) => {
  res.json(getStats());
});

// Sales: Admin Sales CRUD / List
app.get(['/backend/php/sales/admin_sales_crud.php', '/backend/php/sales/sales_list.php'], (req, res) => {
  res.json({ ok: true, sales: ordersStore });
});

// Sales: Reports
app.get('/backend/php/sales/get_reports.php', (req, res) => {
  res.json({
    ok: true,
    sales_summary: {
      today: 145000,
      week: 890000,
      month: 4520000
    },
    top_products: productsStore.slice(0, 5)
  });
});

// Sales: Get Users
app.get('/backend/php/sales/get_users.php', (req, res) => {
  res.json({ ok: true, users: usersStore });
});

// Sales: Notifications List
app.get('/backend/php/sales/notifications_list.php', (req, res) => {
  res.json({ ok: true, notifications: notificationsStore });
});

// Sales: POS Checkout
app.post('/backend/php/sales/pos_checkout.php', (req, res) => {
  const order = saveNewOrder({
    total: req.body.total || 0,
    items: req.body.items || [],
    user_name: req.body.client_name || 'Cliente Mostrador',
    delivery: 'punto',
    pay: 'efectivo',
    status: 'COMPLETED'
  });
  res.json({ ok: true, sale_id: order.id, receipt_url: `/backend/php/orders/order_invoice.php?order_id=${order.id}` });
});

// Sales: Search Clients
app.get('/backend/php/sales/search_clients.php', (req, res) => {
  const q = String(req.query.q || '').toLowerCase();
  const matched = usersStore.filter(u =>
    u.nombre.toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q) ||
    (u.cedula && u.cedula.includes(q))
  );
  res.json({ ok: true, clients: matched });
});

// AI Store Assistant Chatbot API
app.post('/backend/api/chat.php', (req, res) => {
  const message = String(req.body.message || '').trim().toLowerCase();
  let reply = '¡Hola! Bienvenido a DistriCarnes Hermanos Navarro. ¿En qué corte o producto te podemos asesorar hoy?';

  if (/lomo|posta|bistec|res|carne de res/i.test(message)) {
    reply = 'Para res te recomendamos nuestro Lomo Fino ($25.000) o Punta Gorda ($22.000). Son cortes frescos, tiernos e ideales para asar o freír.';
  } else if (/cerdo|costilla|chuleta|tocino/i.test(message)) {
    reply = 'En cerdo tenemos Costilla de Cerdo especial ($19.000) y Chuleta especial ($21.000). ¡Están en promoción esta semana!';
  } else if (/pollo|pechuga|alas|muslo/i.test(message)) {
    reply = 'Tenemos Pollo Entero ($18.000) y Alas con costillar ($17.000). 100% frescos del día.';
  } else if (/domicilio|envio|entrega/i.test(message)) {
    reply = 'Realizamos envíos a todo Cartagena y Villanueva, Bolívar. ¡Por compras superiores a $10.000 tu envío es totalmente gratis!';
  } else if (/precio|costo|cuanto vale/i.test(message)) {
    reply = 'Nuestros precios van desde $8.000 en huesos de res hasta $25.000 en cortes premium. Puedes consultar todo el catálogo en la pestaña de Productos.';
  } else if (/horario|abierto|contacto/i.test(message)) {
    reply = 'Atendemos de lunes a sábado de 6:00 a.m. a 6:00 p.m. y domingos hasta el mediodía. Llámanos al 301 5210177.';
  }

  res.json({
    reply: reply,
    answer: reply,
    text: reply
  });
});

// Health Checks
app.get(['/api/health', '/api/health.php'], (req, res) => {
  res.json({
    status: 'ok',
    app: 'DISTRICARNES Hermanos Navarro',
    version: '1.0.0',
    port: PORT,
    timestamp: new Date().toISOString()
  });
});

// Fallback for any other /backend/php/* route
app.all('/backend/php/*', (req, res) => {
  res.json({ ok: true, data: [] });
});

// Start server
app.listen(PORT, HOST, () => {
  console.log(`[DISTRICARNES] Dev server running at http://${HOST}:${PORT}`);
});
