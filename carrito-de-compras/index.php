<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Carrito de Compras - DISTRICARNES</title>
    <link rel="stylesheet" href="../static/css/nav_pills.css" />
    <link rel="stylesheet" href="../static/css/cart.css">
    <!-- Estilos globales del sitio para header unificado -->
    <link rel="stylesheet" href="../static/css/header_en_general.css" />
    <link rel="stylesheet" href="../static/css/base.css" />
    <link rel="stylesheet" href="../static/css/theme.css" />
    <link rel="stylesheet" href="../static/css/chatbot.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../static/css/responsive.css" />
    <script src="../static/js/auth_utils.js"></script>
    <script src="../static/js/theme.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <!-- app.js antiguo deshabilitado en la nueva vista CRUD -->
    <link rel="icon" type="image/x-icon" href="../assets/icon/image-removebg-preview sin fondo (1).ico" />

</head>

<body class="">
    <!-- Header compartido del sitio (igual al de las demás páginas) -->
    <?php
    if (!isset($basePath)) {
        require_once __DIR__ . '/../config/bootstrap.php';
    }
    include __DIR__ . '/../includes/header.php';
    include __DIR__ . '/../includes/header-scripts.php';
    ?>

    <main class="cart-page" id="main-content">
        <div class="cart-head">
            <h2 class="cart-head-title">
                <i class="bi bi-cart-fill"></i>
                <span>Tu Carrito</span>
                <span class="cart-head-count" id="cartCount">0</span>
            </h2>
            <button id="btnClearHead" class="cart-clear-link" type="button">
                <i class="fas fa-trash-alt"></i> Vaciar carrito
            </button>
        </div>

        <div class="cart-layout">
            <section class="cart-list">
                <!-- Progreso hacia envío gratis -->
                <div class="shipping-progress" id="shippingBarWrap">
                    <div class="shipping-progress-label" id="shippingGoalMsg"></div>
                    <div class="shipping-progress-track">
                        <div class="shipping-progress-fill" id="shippingBarFill"></div>
                    </div>
                </div>

                <div id="cartRows"></div>

                <div class="empty-cart" id="emptyCart" style="display:none;">
                    <i class="bi bi-cart-x"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p>Explora nuestros cortes premium y ofertas exclusivas de la semana.</p>
                    <a class="btn-primary" href="../productos.php">Ver productos</a>
                </div>

                <!-- Guardados para después -->
                <div class="saved-section" id="savedSection" style="display:none;">
                    <h3 class="saved-title"><i class="bi bi-bookmark-heart"></i> Guardados para después</h3>
                    <div id="savedRows"></div>
                </div>
            </section>

            <aside class="cart-summary">
                <div class="summary-title"><i class="bi bi-receipt"></i> Resumen del pedido</div>
                <div class="session-info hidden" id="sessionInfo">No has iniciado sesión.</div>
                <div class="promo-box">
                    <input id="promoInput" type="text" placeholder="Código de descuento" autocomplete="off" />
                    <button id="applyPromo" type="button">Aplicar</button>
                </div>
                <div class="promo-status" id="promoStatus"></div>
                <div class="summary-row"><span>Subtotal</span><span id="subtotal">$0</span></div>
                <div class="summary-row"><span>IVA (incluido)</span><span id="tax">$0</span></div>
                <div class="summary-row"><span>Envío estimado</span><span id="shipping">$0</span></div>
                <div id="freeShippingMsg" class="free-ship-msg" style="display:none;">¡Envío gratis aplicado!</div>
                <div class="summary-row" id="discountRow" style="display:none;"><span>Descuento</span><span id="discount">-$0</span></div>
                <div class="summary-row summary-total"><span>Total</span><span id="total">$0</span></div>
                <button id="btnCheckout" class="btn-primary" type="button">Continuar compra</button>
                <button id="btnPayUQuick" class="btn-paypal" type="button"><i class="fas fa-credit-card"></i> Pagar con PayU</button>
                <button id="btnClear" class="btn-secondary" type="button"><i class="fas fa-trash-alt"></i> Vaciar carrito</button>
                <div class="summary-trust">
                    <span><i class="fas fa-lock"></i> Pago seguro</span>
                    <span><i class="fas fa-truck"></i> Envío a domicilio</span>
                    <span><i class="fas fa-headset"></i> Soporte 24/7</span>
                </div>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">

            <!-- Columna 1: Información de Contacto -->
            <div class="footer-column">
                <h4>INFORMACIÓN DE CONTACTO</h4>
                <p><i class="fas fa-map-marker-alt"></i> Dirección: OLAYA HERRERA</p>
                <p><i class="fas fa-phone"></i> Teléfono: 12345678</p>
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

    <!-- SweetAlert para feedback -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Scripts del carrito CRUD -->
    <script src="../static/js/cart_utils.js"></script>
    <script src="../static/js/cart_page.js"></script>
    <!-- Scripts del header -->
    <script src="../static/js/header_actions.js"></script>
    <script src="../static/js/auth_modal.js?v=<?= filemtime(__DIR__ . '/../static/js/auth_modal.js') ?>"></script>
    <script src="../js/auth.js"></script>
    <script src="../static/js/index.js"></script>
    <script src="../static/js/chatbot.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggle = document.querySelector('.chatbot-toggle');
            var container = document.querySelector('.chatbot-container');
            if (!toggle || !container) return;

            function openClose(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                container.classList.toggle('active');
                if (container.classList.contains('active')) {
                    setTimeout(function() {
                        var input = document.getElementById('userInput') || document.querySelector('.chat-input');
                        if (input) input.focus();
                    }, 200);
                }
            }
            toggle.addEventListener('click', openClose);
            toggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') openClose(e);
            });
        });
    </script>
    <script src="../static/js/cart_badge.js"></script>
    <script src="../static/js/history_favorites.js"></script>
    <script src="../static/js/loader.js" defer></script>
    <script src="../static/js/session_guard.js" defer></script>
    <script src="../static/js/network_guard.js" defer></script>
    <script src="../static/js/public_tour.js"></script>
</body>

</html>