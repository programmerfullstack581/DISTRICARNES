<?php
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Juego de Naves</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/tailwind.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/theme.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/navegame.css" />
    <script src="<?php echo $basePath; ?>/static/js/theme.js"></script>
    <script src="<?php echo $basePath; ?>/static/js/auth_utils.js"></script>
</head>

<body class="navegame-body bg-black text-white">
    <?php
    $activeNav = 'navegame';
    include __DIR__ . '/includes/header.php';
    ?>

    <?php include __DIR__ . '/includes/header-scripts.php'; ?>

    <main style="background-color: #000000;" class="main-content">
        <div class="game-page-wrapper">
            <div class="game-header-title">
                <div class="game-badge">
                    <i class="fas fa-rocket"></i>
                    <span>Minijuego</span>
                </div>
                <h1>Misión <span>Espacial</span></h1>
                <p>Pilotea tu nave y defiende a DISTRICARNES del ataque de los meteoritos.</p>
            </div>

            <div class="game-container">
                <div class="game-canvas-wrap">
                    <canvas id="naveCanvas" width="800" height="560"></canvas>

                    <!-- Menú inicial -->
                    <div class="game-overlay" id="startOverlay">
                        <h2>Misión <span>Espacial</span></h2>
                        <p class="overlay-sub">Pilotea tu nave, esquiva los meteoritos y dispara para lograr la máxima puntuación.</p>
                        <div class="controls-hint">
                            <span><kbd>←</kbd> <kbd>→</kbd> <kbd>↑</kbd> <kbd>↓</kbd> Mover</span>
                            <span><kbd>W</kbd> <kbd>A</kbd> <kbd>S</kbd> <kbd>D</kbd> Mover</span>
                            <span><kbd>Espacio</kbd> Disparar</span>
                            <span><kbd>P</kbd> Pausa</span>
                        </div>
                        <button class="btn-game" id="btnStart"><i class="fas fa-play"></i> Iniciar Juego</button>
                    </div>

                    <!-- Pausa -->
                    <div class="game-overlay hidden" id="pauseOverlay">
                        <h2>Juego en <span>Pausa</span></h2>
                        <p class="overlay-sub">Pulsa P o vuelve a hacer clic para continuar.</p>
                        <button class="btn-game" id="btnResume"><i class="fas fa-play"></i> Continuar</button>
                        <button class="btn-game btn-secondary" id="btnMenu"><i class="fas fa-home"></i> Menú</button>
                    </div>

                    <!-- Game Over -->
                    <div class="game-overlay hidden" id="gameOverOverlay">
                        <h2>Game <span>Over</span></h2>
                        <p class="overlay-sub" id="gameOverMsg">Tu nave fue destruida. ¡Inténtalo de nuevo, piloto!</p>
                        <div class="game-hud" style="width: 100%; max-width: 320px; border: 1px solid #1f1f1f; border-radius: 12px; margin-bottom: 1rem;">
                            <div class="hud-stat"><i class="fas fa-star"></i> Puntos: <span class="hud-value" id="finalScore">0</span></div>
                            <div class="hud-stat"><i class="fas fa-trophy"></i> Récord: <span class="hud-value" id="finalBest">0</span></div>
                        </div>
                        <button class="btn-game" id="btnRestart"><i class="fas fa-redo"></i> Reintentar</button>
                        <button class="btn-game btn-secondary" id="btnMenu2"><i class="fas fa-home"></i> Menú</button>
                    </div>
                </div>

                <!-- HUD -->
                <div class="game-hud">
                    <div class="hud-stat"><i class="fas fa-star"></i> Puntos: <span class="hud-value" id="hudScore">0</span></div>
                    <div class="hud-stat"><i class="fas fa-heart"></i> Vidas: <span class="hud-value" id="hudLives">3</span></div>
                    <div class="hud-stat"><i class="fas fa-level-up-alt"></i> Nivel: <span class="hud-value" id="hudLevel">1</span></div>
                    <button class="btn-game btn-secondary" id="btnPause" style="padding: 0.4rem 1.2rem; font-size: 0.85rem;"><i class="fas fa-pause"></i> Pausa</button>
                </div>

                <!-- Panel de información -->
                <div class="game-panel">
                    <div class="game-card">
                        <h3><i class="fas fa-gamepad"></i> Cómo jugar</h3>
                        <ul>
                            <li><i class="fas fa-arrows-alt"></i> Muévete con las flechas o WASD.</li>
                            <li><i class="fas fa-mouse-pointer"></i> En PC también puedes mover la nave con el ratón.</li>
                            <li><i class="fas fa-space-shuttle"></i> Dispara con ESPACIO o clic sostenido.</li>
                            <li><i class="fas fa-crosshairs"></i> Destruye meteoritos para sumar puntos.</li>
                            <li><i class="fas fa-shield-alt"></i> No dejes que impacten contra tu nave.</li>
                        </ul>
                    </div>
                    <div class="game-card">
                        <h3><i class="fas fa-trophy"></i> Récords</h3>
                        <div class="records-list">
                            <div class="record-item">
                                <span class="record-label">Mejor puntuación</span>
                                <span class="record-score best" id="bestScore">0</span>
                            </div>
                            <div class="record-item">
                                <span class="record-label">Última partida</span>
                                <span class="record-score" id="lastScore">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-container">
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
            <div class="footer-column">
                <h4>INFORMACIÓN</h4>
                <ul>
                    <li><i class="fas fa-info-circle"></i> Información Delivery</li>
                    <li><i class="fas fa-shield-alt"></i> Políticas de Privacidad</li>
                    <li><i class="fas fa-file-contract"></i> Términos y condiciones</li>
                    <li><i class="fas fa-headset"></i> Contáctanos</li>
                </ul>
            </div>
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
            <div class="footer-column">
                <h4>BOLETÍN INFORMATIVO</h4>
                <p>Suscríbete a nuestros boletines ahora y mantente al día con nuevas colecciones y ofertas exclusivas.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Ingresa el correo aquí... " required />
                    <button type="submit">SUSCRÍBETE</button>
                </form>
            </div>
        </div>
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
                    <div class="menu-option"><i class="fas fa-drumstick-bite"></i> Ver productos cárnicos</div>
                    <div class="menu-option"><i class="fas fa-cut"></i> Tipos de cortes</div>
                    <div class="menu-option"><i class="fas fa-clock"></i> Horarios y ubicación</div>
                    <div class="menu-option"><i class="fas fa-tags"></i> Precios y ofertas</div>
                    <div class="menu-option"><i class="fas fa-info-circle"></i> Sobre nosotros</div>
                    <div class="menu-option"><i class="fas fa-phone"></i> Contactar</div>
                </div>
                <div class="message-timestamp">10:01 AM</div>
            </div>
        </div>
        <div class="chatbot-input">
            <div class="input-container">
                <input type="text" class="chat-input" id="userInput" placeholder="¿Qué deseas saber sobre nuestras carnes?" onkeypress="handleKeyPress(event)" autocomplete="off" />
                <button class="voice-btn" title="Entrada de voz (No implementado)"><i class="fas fa-microphone"></i></button>
                <button class="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
            <div class="quick-actions">
                <button class="quick-action" onclick="handleQuickAction('productos')"><i class="fas fa-drumstick-bite"></i> Ver Productos</button>
                <button class="quick-action" onclick="handleQuickAction('horarios')"><i class="fas fa-clock"></i> Horarios</button>
                <button class="quick-action" onclick="handleQuickAction('contacto')"><i class="fas fa-phone"></i> Contacto</button>
            </div>
        </div>
    </div>

    <script src="./static/js/header_actions.js"></script>
    <script src="./static/js/auth_modal.js?v=<?= filemtime(__DIR__ . '/static/js/auth_modal.js') ?>"></script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/history_favorites.js"></script>
    <script src="./static/js/index.js"></script>
    <script src="./static/js/chatbot.js"></script>
    <script src="./js/navegame.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggle = document.querySelector('.chatbot-toggle');
            var container = document.querySelector('.chatbot-container');
            if (!toggle || !container) return;
            function openClose(e) {
                if (e) { e.preventDefault(); e.stopPropagation(); }
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AuthSystem && typeof AuthSystem.checkUserSession === 'function') {
                AuthSystem.checkUserSession();
            }
        });
    </script>
    <script src="./static/js/loader.js" defer></script>
    <script src="./static/js/session_guard.js" defer></script>
    <script src="./static/js/network_guard.js" defer></script>
</body>

</html>
