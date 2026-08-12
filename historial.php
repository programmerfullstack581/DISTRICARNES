<?php
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Historial de compra</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/theme.css" />
    <script src="<?php echo $basePath; ?>/static/js/theme.js"></script>
    <script src="<?php echo $basePath; ?>/static/js/auth_utils.js"></script>
</head>
<body class="">
    <!-- Header -->
<?php
$activeNav = '';
include __DIR__ . '/includes/header.php';
?>


<?php include __DIR__ . '/includes/header-scripts.php'; ?>
    

    <main class="container" style="max-width:1000px;margin:30px auto;padding:0 16px;" id="main-content">
        <h1 style="font-size:1.8rem;margin-bottom:16px;">Historial de compra</h1>
        <div id="historyList" class="grid" style="display:grid;gap:12px;grid-template-columns:1fr;">
            <!-- Orders render here -->
        </div>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <!-- Columna 1: Información de Contacto -->
            <div class="footer-column">
                <h4>INFORMACIÓN DE CONTACTO</h4>
                <p><i class="fas fa-map-marker-alt"></i> Dirección: OLAYA HERRERA</p>
                <p><i class="fas fa-phone"></i> Teléfono: 301 5210177</p>
                <p><i class="fas fa-envelope"></i> Email: districarneshermanosnavarro@gmail.com</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Columna 2: Información -->
            <div class="footer-column">
                <h4>INFORMACIÓN</h4>
                <ul>
                    <li><i class="fas fa-info-circle"></i> Información Delivery</li>
                    <li><i class="fas fa-shield-alt"></i> Políticas de Privacidad</li>
                    <li><i class="fas fa-file-contract"></i> Términos y condiciones</li>
                    <li><i class="fas fa-headset"></i> Contáctanos</li>
                </ul>
            </div>

            <!-- Columna 3: Mi Cuenta -->
            <div class="footer-column">
                <h4>MI CUENTA</h4>
                <ul>
                    <li><i class="fas fa-user"></i> Mi cuenta</li>
                    <li><i class="fas fa-history"></i> Historial de ordenes</li>
                    <li><i class="fas fa-heart"></i> Lista de deseos</li>
                    <li><i class="fas fa-newspaper"></i> Boletín</li>
                    <li><i class="fas fa-undo"></i> Reembolsos</li>
                </ul>
            </div>

            <!-- Columna 4: Boletín Informativo -->
            <div class="footer-column">
                <h4>BOLETÍN INFORMATIVO</h4>
                <p>Suscríbete a nuestros boletines ahora y mantente al día con nuevas colecciones y ofertas exclusivas.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Ingresa el correo aquí..." required />
                    <button type="submit">SUSCRÍBETE</button>
                </form>
            </div>

        </div>

        <!-- Pie inferior -->
        <center>
            <h4>&copy; 2026 DISTRICARNES HERMANOS NAVARRO. Todos los derechos reservados.</h4>
        </center>
    </footer>

    <!-- SweetAlert2 para confirmación y feedback de cierre de sesión -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./static/js/header_actions.js"></script>
    <script src="./static/js/auth_modal.js?v=<?= filemtime(__DIR__ . '/static/js/auth_modal.js') ?>"></script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/history_favorites.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/index.js"></script>
    <script src="./static/js/chatbot.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
                    if (window.AuthSystem && typeof AuthSystem.checkUserSession === 'function') {
                        AuthSystem.checkUserSession();
                    }
                    const container = document.getElementById('historyList');
                    container.innerHTML = '<div style="text-align:center;padding:40px;opacity:0.7;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;margin-bottom:10px;display:block;"></i> Cargando tus compras...</div>';
                    // Obtener email e ID del usuario logueado
                    let email = '';
                    let userId = 0;
                    try {
                        const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                        const raw = rawStr ? JSON.parse(rawStr) : null;
                        if (raw) {
                            const u = raw.user || raw;
                            email = u.correo_electronico || u.email || u.user_email || u.contact_email || '';
                            userId = u.id || u.id_usuario || 0;
                            // Si el email sigue vacío, intentar buscar en cualquier campo que parezca un email
                            if (!email) {
                                for (const key in u) {
                                    if (typeof u[key] === 'string' && u[key].includes('@')) {
                                        email = u[key];
                                        break;
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        email = '';
                    }

                    console.log('Buscando historial para:', email, 'UserID:', userId);

                    let orders = [];
                    if (email || userId) {
                        try {
                            const res = await fetch(`backend/php/orders/orders_get.php?email=${encodeURIComponent(email.trim())}&user_id=${userId}`);
                            const text = await res.text();
                            console.log('Respuesta bruta del servidor:', text);
                            const data = JSON.parse(text);
                            console.log('Órdenes encontradas:', data.count || 0);
                            if (data && data.ok) {
                                orders = data.orders || [];
                            } else {
                                console.error('Error fetching orders:', data.error);
                            }
                        } catch (e) {
                            console.error('Fetch error:', e);
                        }
                    } else {
                        // Intentar sin parámetros (el servidor usará la sesión)
                        try {
                            const res = await fetch(`backend/php/orders/orders_get.php`);
                            const data = await res.json();
                            if (data && data.ok) {
                                orders = data.orders || [];
                            }
                        } catch (e) {}
                    }
                    
                    // Si aún no hay órdenes, intentar buscar por el nombre del usuario
                    if (!orders.length) {
                        try {
                            const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                            const raw = rawStr ? JSON.parse(rawStr) : null;
                            const u = raw ? (raw.user || raw) : null;
                            const name = u ? (u.nombres_completos || u.name || u.user_name || '') : '';
                            if (name) {
                                console.log('Intentando búsqueda por nombre:', name);
                                const res = await fetch('backend/php/orders/orders_get.php?email=' + encodeURIComponent(name.trim()));
                                const data = await res.json();
                                if (data && data.ok) {
                                    orders = data.orders || [];
                                }
                            }
                        } catch (e) {}
                    }

                    // Fallback: usar historial local si backend no responde
                    if (!orders.length && window.PurchaseHistoryStore) {
                        orders = PurchaseHistoryStore.all();
                    }

                    // Utilidades de visualización
                    function formatCOP(v){
                        try { return Number(v||0).toLocaleString('es-CO', { style:'currency', currency:'COP', maximumFractionDigits:0 }); }
                        catch(e){ return '$' + Number(v||0).toLocaleString('es-CO'); }
                    }
                    function formatDate(v){
                        if (!v) return '';
                        try {
                            const d = new Date(v);
                            if (isNaN(d.getTime())) return v;
                            return d.toLocaleDateString('es-CO', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                        } catch(e){ return v; }
                    }
                    function orderStatus(st){
                        const map = {
                            PENDING:'Pendiente', PENDIENTE:'Pendiente',
                            PROCESSING:'En proceso', PREPARANDO:'En proceso',
                            SHIPPED:'Enviado', ENVIADO:'Enviado',
                            DELIVERED:'Entregado', ENTREGADO:'Entregado',
                            COMPLETED:'Completado', COMPLETADO:'Completado',
                            CANCELLED:'Cancelado', CANCELADO:'Cancelado'
                        };
                        const colors = { Pendiente:'#f59e0b', 'En proceso':'#3b82f6', Enviado:'#8b5cf6', Entregado:'#22c55e', Completado:'#22c55e', Cancelado:'#ef4444' };
                        const key = map[String(st||'').toUpperCase()] || st || 'Desconocido';
                        return [key, colors[key] || '#9ca3af'];
                    }
                    function statusTimeline(st){
                        const norm = {
                            PENDING:'PENDING', PENDIENTE:'PENDING',
                            PROCESSING:'PROCESSING', PREPARANDO:'PROCESSING',
                            SHIPPED:'SHIPPED', ENVIADO:'SHIPPED',
                            DELIVERED:'DELIVERED', ENTREGADO:'DELIVERED',
                            COMPLETED:'COMPLETED', COMPLETADO:'COMPLETED'
                        }[String(st||'').toUpperCase()];
                        if (String(st||'').toUpperCase() === 'CANCELLED' || String(st||'').toUpperCase() === 'CANCELADO') {
                            return '<div style="padding:8px;color:#ef4444;font-weight:700;font-size:.85rem;background:#ef444410;border:1px solid #ef444433;border-radius:8px;">Este pedido fue cancelado</div>';
                        }
                        if (!norm) return '';
                        const steps = [
                            { key:'PENDING', label:'Pedido recibido' },
                            { key:'PROCESSING', label:'Preparando tu pedido' },
                            { key:'SHIPPED', label:'En camino' },
                            { key:'DELIVERED', label:'Entregado' },
                            { key:'COMPLETED', label:'Completado' }
                        ];
                        const currentIdx = steps.findIndex(s => s.key === norm);
                        if (currentIdx < 0) return '';
                        return '<div style="margin:6px 0 2px;padding:8px 4px;">' + steps.map((s, i) => {
                            const done = i <= currentIdx;
                            return `<div style="display:flex;align-items:flex-start;gap:10px;padding:3px 0;">
                                <span style="width:18px;height:18px;border-radius:50%;flex:0 0 18px;margin-top:2px;background:${done ? '#ff0000' : 'var(--dc-border-strong)'};color:#fff;font-size:.65rem;display:flex;align-items:center;justify-content:center;">${done ? '&#10003;' : (i + 1)}</span>
                                <span style="color:${done ? 'var(--dc-text)' : 'var(--dc-text-muted)'};font-weight:${done ? '700' : '400'};font-size:.85rem;">${s.label}</span>
                            </div>`;
                        }).join('') + '</div>';
                    }

                    if (!orders.length) {
                        container.innerHTML = `
                            <div style="text-align:center;padding:40px;background:var(--dc-surface);border:1px solid var(--dc-border);border-radius:12px;">
                                <i class="fas fa-shopping-bag" style="font-size:3rem;color:var(--dc-text-faint);margin-bottom:15px;display:block;"></i>
                                <h3 style="margin-bottom:8px;">No hay compras registradas</h3>
                                <p style="color:var(--dc-text-muted);margin-bottom:20px;">Aún no has realizado ninguna compra con esta cuenta.</p>
                                <a href="./productos.php" class="btn" style="background:#ff0000;color:#fff;padding:10px 25px;border-radius:50px;font-weight:700;text-decoration:none;display:inline-block;">Ir a comprar</a>
                            </div>
                        `;
                        return;
                    }
                    container.innerHTML = orders.map(order => {
                        const [stLabel, stColor] = orderStatus(order.status);
                        return `
        <div class="card" style="background:var(--dc-surface);border:1px solid var(--dc-border);border-radius:10px;padding:12px;">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:6px;">
            <strong>Orden #${order.id || ''}</strong>
            <span style="display:inline-flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <span style="display:inline-block;padding:3px 10px;border-radius:999px;background:${stColor}22;color:${stColor};border:1px solid ${stColor}55;font-weight:700;font-size:.75rem;">${stLabel}</span>
              <span class="date" style="opacity:.7;">${formatDate(order.created_at || order.date)}</span>
            </span>
          </div>
          <div class="card-body" style="display:grid;gap:6px;">
            ${(order.items || []).map(i => `
              <div style="display:grid;grid-template-columns:1fr auto auto;gap:8px;">
                <span>${i.title || i.name || 'Producto'}</span>
                <span>x${i.qty || i.quantity || 1}</span>
                <span>${formatCOP(i.price)}</span>
              </div>
            `).join('')}
          </div>
          ${statusTimeline(order.status)}
          <div class="card-footer" style="margin-top:8px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <div><strong>Total:</strong> ${formatCOP(order.total)}</div>
            <div style="display:flex;gap:8px;">
              ${order.id ? `<a href="backend/php/orders/order_invoice.php?order_id=${order.id}" class="btn" style="padding:6px 10px;border:1px solid var(--dc-border-strong);border-radius:6px;color:var(--dc-text);text-decoration:none;">Ver factura</a>` : ''}
            </div>
          </div>
        </div>`;
                    }).join('');
    });
    </script>
    <!-- CHAT BOT -->
    <div class="chatbot-toggle" onclick="toggleChatbot()" title="Abrir chat DISTRICARNES" aria-label="Abrir chat DISTRICARNES">
        <i class="fas fa-robot"></i>
    </div>
    <div class="chatbot-container">
        <div class="chatbot-header">
            <div class="header-info">
                <div class="bot-avatar"><i class="fas fa-robot"></i></div>
                <h3>DISTRICARNES HERMANOS NAVARRO</h3>
                <p>Asistente Virtual</p>
                <p>Tu especialista en carnes premium</p>
            </div>
            <button class="close-btn" onclick="toggleChatbot()" aria-label="Cerrar chat">
        <i class="fas fa-times"></i>
      </button>
        </div>
        <div class="chatbot-messages" id="chatBox">
            <div class="message bot-message">
                ¡Hola! 🥩 Soy tu asistente de DISTRICARNES. ¿En qué puedo ayudarte hoy?
                <div class="menu-options">
                    <div class="menu-option">
                        <i class="fas fa-drumstick-bite"></i> Ver productos cárnicos
                    </div>
                    <div class="menu-option">
                        <i class="fas fa-cut"></i> Tipos de cortes
                    </div>
                    <div class="menu-option">
                        <i class="fas fa-clock"></i> Horarios y ubicación
                    </div>
                    <div class="menu-option">
                        <i class="fas fa-tags"></i> Precios y ofertas
                    </div>
                    <div class="menu-option">
                        <i class="fas fa-info-circle"></i> Sobre nosotros
                    </div>
                    <div class="menu-option">
                        <i class="fas fa-phone"></i> Contactar
                    </div>
                </div>
                <div class="message-timestamp">10:01 AM</div>
            </div>
        </div>
        <div class="chatbot-input">
            <div class="input-container">
                <input type="text" class="chat-input" id="userInput" placeholder="¿Qué deseas saber sobre nuestras carnes?" onkeypress="handleKeyPress(event)" autocomplete="off" />
                <button class="voice-btn" title="Entrada de voz (No implementado)">
          <i class="fas fa-microphone"></i>
        </button>
                <button class="send-btn" onclick="sendMessage()">
          <i class="fas fa-paper-plane"></i>
        </button>
            </div>
            <div class="quick-actions">
                <button class="quick-action" onclick="handleQuickAction('productos')">
          <i class="fas fa-drumstick-bite"></i> Ver Productos
        </button>
                <button class="quick-action" onclick="handleQuickAction('horarios')">
          <i class="fas fa-clock"></i> Horarios
        </button>
                <button class="quick-action" onclick="handleQuickAction('contacto')">
          <i class="fas fa-phone"></i> Contacto
        </button>
            </div>
        </div>
    </div>
    <script src="./static/js/loader.js" defer></script>
    <script src="./static/js/session_guard.js" defer></script>
    <script src="./static/js/network_guard.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('.chatbot-toggle');
            var container = document.querySelector('.chatbot-container');
            if (!toggle || !container) return;
            function openClose(e) {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                container.classList.toggle('active');
                if (container.classList.contains('active')) {
                    setTimeout(function () {
                        var input = document.getElementById('userInput') || document.querySelector('.chat-input');
                        if (input) input.focus();
                    }, 200);
                }
            }
            toggle.addEventListener('click', openClose);
            toggle.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') openClose(e);
            });
        });
    </script>
</body>

</html>
