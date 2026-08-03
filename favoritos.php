<?php
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Mis favoritos</title>
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
<body class=" bg-black text-white ">
    <!-- Header -->
<?php
$activeNav = '';
include __DIR__ . '/includes/header.php';
?>


<?php include __DIR__ . '/includes/header-scripts.php'; ?>
  

    <main class="container" style="max-width:1000px;margin:30px auto;padding:0 16px;">
        <h1 style="font-size:1.8rem;margin-bottom:16px;">Mis favoritos</h1>
        <div id="favoritesList" class="grid" style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
            <!-- Favorites render here -->
        </div>
    </main>

    <!--footer-->
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
                <p>Suscríbete a nuestros boletines ahora y mantente al día con nuevas colecciones y ofertas exclusivas.
                </p>
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
    <script src="./static/js/auth_modal.js"></script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/history_favorites.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/index.js"></script>
    <script src="./static/js/chatbot.js"></script>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AuthSystem && typeof AuthSystem.checkUserSession === 'function') {
                AuthSystem.checkUserSession();
            }
            const container = document.getElementById('favoritesList');
            // Verificar sesión: sin sesión no se muestran favoritos
            let hasSession = false;
            try {
                const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                if (rawStr) {
                    const raw = JSON.parse(rawStr);
                    const user = raw && raw.user ? raw.user : raw;
                    hasSession = Boolean((user && (user.correo_electronico || user.email)) || (user && (user.id_usuario || user.id)));
                }
            } catch (e) {
                hasSession = false;
            }

            if (!hasSession) {
                container.innerHTML = '<p>Inicia sesión para ver tu lista de favoritos.</p>';
                return;
            }

            const favorites = (window.FavoritesStore && FavoritesStore.all()) || [];
            if (!favorites.length) {
                container.innerHTML = '<p>No tienes favoritos aún.</p>';
                return;
            }
            container.innerHTML = favorites.map(item => `
        <div class="card" style="background:#0b0b0b;border:1px solid #222;border-radius:10px;overflow:hidden;">
          <img src="${item.image || './assets/icon/LOGO-DISTRICARNES.png'}" alt="${item.name || 'Producto'}" style="width:100%;height:160px;object-fit:cover;" />
          <div class="card-body" style="padding:12px;">
            <h3 style="margin:0 0 6px;">${item.name || 'Producto'}</h3>
            <p style="opacity:.8;margin:0 0 10px;">${item.price ? ('$' + item.price) : ''}</p>
            <small style="opacity:.7;">Agregado: ${item.addedAt ? new Date(item.addedAt).toLocaleString() : ''}</small>
          </div>
          <div class="card-actions" style="padding:12px;display:flex;gap:8px;">
            <button class="btn-remove" data-id="${item.id}" style="background:#111;color:#fff;border:1px solid #333;border-radius:8px;padding:8px 10px;cursor:pointer;">Quitar</button>
          </div>
        </div>
      `).join('');
            container.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-remove');
                if (!btn) return;
                FavoritesStore.remove(btn.dataset.id);
                location.reload();
            });
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
    <script src="./static/js/session_guard.js" defer></script>
</body>

</html>
