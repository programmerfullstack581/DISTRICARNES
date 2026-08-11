<?php
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Perfil</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/perfil.css"/>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/theme.css" />
    <script src="<?php echo $basePath; ?>/static/js/theme.js"></script>
    <script src="<?php echo $basePath; ?>/static/js/auth_utils.js"></script>
    <script src="<?php echo $basePath; ?>/static/js/header_actions.js"></script>
    <script src="<?php echo $basePath; ?>/static/js/auth_modal.js?v=<?= filemtime(__DIR__ . '/static/js/auth_modal.js') ?>"></script>
</head>

<body class=" bg-black text-white ">
    <!-- Header -->
<?php
$activeNav = '';
$headerExtra = '<style>.user-name,.user-details h4{text-transform:none!important}</style>';
include __DIR__ . '/includes/header.php';
?>


<?php include __DIR__ . '/includes/header-scripts.php'; ?>


    <!-- perfl del usuario-->
    <div style="text-align: center; margin-top: 3rem; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; font-weight: 800; font-family: 'Montserrat', sans-serif;">Mi perfil
        </h1>
    </div>

    <div class="page-wrap">

        <div class="tabs">
            <button data-tab="overview" class="active">Resumen</button>
            <button data-tab="edit">Editar Perfil</button>
            <button data-tab="password">Cambiar Contraseña</button>
            <button data-tab="settings">Configuración</button>
        </div>
        <div class="card">
            <div id="tab-overview" class="content active">
                <div class="grid">
                    <div>
                        <label>Foto de perfil</label>
                        <div id="avatarPreviewOverview" class="avatar-preview"
                            style="width:72px;height:72px;font-size:1.2rem;">
                            <span id="profileInitial"></span>
                        </div>
                    </div>
                    <div>
                        <label>Nombre completo</label>
                        <div id="ovName" class="note"></div>
                    </div>
                    <div>
                        <label>Correo electrónico</label>
                        <div id="ovEmail" class="note"></div>
                    </div>
                    <div>
                        <label>Rol</label>
                        <div id="ovRole" class="note"></div>
                    </div>
                </div>
            </div>
            <div id="tab-edit" class="content">
                <div class="grid">
                    <div>
                        <label>Foto de perfil</label>
                        <div id="avatarPreview" class="avatar-preview" title="Vista previa"></div>
                        <div class="photo-actions">
                            <label for="profilePhoto" class="btn-inline">Seleccionar imagen</label>
                            <input type="file" id="profilePhoto" accept="image/*">
                            <button id="uploadPhoto" class="btn-inline primary">Cambiar foto</button>
                        </div>
                        <div class="note">JPG/PNG/WEBP, máximo 2MB.</div>
                    </div>
                    <div>
                        <label for="fullName">Nombre completo</label>
                        <input type="text" id="fullName" placeholder="Tu nombre" />
                    </div>
                    <div>
                        <label for="email">Correo</label>
                        <input type="email" id="email" placeholder="tu@correo.com" />
                    </div>
                </div>
                <div class="actions">
                    <button id="saveProfile" class="btn">Guardar cambios</button>
                    <button id="resetProfile" class="btn secondary">Restablecer</button>
                </div>
                <div id="editAlert" class="note"></div>
            </div>
            <div id="tab-password" class="content">
                <div class="grid">
                    <div>
                        <label for="currentPassword">Contraseña actual</label>
                        <div class="password-wrapper">
                            <input type="password" id="currentPassword" placeholder="Contraseña actual" />
                            <button type="button" class="password-toggle" data-target="currentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="newPassword">Nueva contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="newPassword" placeholder="Nueva contraseña (mín. 8)" />
                            <button type="button" class="password-toggle" data-target="newPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="confirmPassword">Confirmar nueva contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirmPassword" placeholder="Repite la nueva contraseña" />
                            <button type="button" class="password-toggle" data-target="confirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button id="changePassword" class="btn">Actualizar contraseña</button>
                </div>
                <div id="passAlert" class="note"></div>
            </div>
            <div id="tab-settings" class="content">
                <div class="settings-list">
                    <div class="setting-item">
                        <div>
                            <div style="font-weight:700;color:#fff;">Notificaciones por correo</div>
                            <div class="note">Recibir avisos de ofertas y estado de pedidos</div>
                        </div>
                        <label><input type="checkbox" id="stEmailNotifs"> Activar</label>
                    </div>
                    <div class="setting-item">
                        <div>
                            <div style="font-weight:700;color:#fff;">Recordar favoritos</div>
                            <div class="note">Guardarlos en este dispositivo</div>
                        </div>
                        <label><input type="checkbox" id="stRememberFavs"> Activar</label>
                    </div>
                    <div class="setting-item">
                        <div>
                            <div style="font-weight:700;color:#fff;">Mostrar precios con IVA</div>
                            <div class="note">Aplicar IVA en el listado</div>
                        </div>
                        <label><input type="checkbox" id="stShowIVA"> Activar</label>
                    </div>
                </div>
                <div class="actions">
                    <button id="saveSettings" class="btn">Guardar configuración</button>
                </div>
                <div id="settingsAlert" class="note"></div>
            </div>
        </div>
    </div>

     <!--footer-->
    <footer class="footer ">
        <div class="footer-container ">

            <!-- Columna 1: Información de Contacto -->
            <div class="footer-column ">
                <h4>INFORMACIÓN DE CONTACTO</h4>
                <p><i class="fas fa-map-marker-alt "></i> Dirección: OLAYA HERRERA</p>
                <p><i class="fas fa-phone "></i> Teléfono: 301 5210177</p>
                <p><i class="fas fa-envelope "></i> Email: districarneshermanosnavarro@gmail.com</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Columna 2: Información -->
            <div class="footer-column ">
                <h4>INFORMACIÓN</h4>
                <ul>
                    <li><i class="fas fa-info-circle "></i> Información Delivery</li>
                    <li><i class="fas fa-shield-alt "></i> Políticas de Privacidad</li>
                    <li><i class="fas fa-file-contract "></i> Términos y condiciones</li>
                    <li><i class="fas fa-headset "></i> Contáctanos</li>
                </ul>
            </div>

            <!-- Columna 3: Mi Cuenta -->
            <div class="footer-column ">
                <h4>MI CUENTA</h4>
                <ul>
                    <li><i class="fas fa-user "></i> Mi cuenta</li>
                    <li><i class="fas fa-history "></i> Historial de ordenes</li>
                    <li><i class="fas fa-heart "></i> Lista de deseos</li>
                    <li><i class="fas fa-newspaper "></i> Boletín</li>
                    <li><i class="fas fa-undo "></i> Reembolsos</li>
                </ul>
            </div>

            <!-- Columna 4: Boletín Informativo -->
            <div class="footer-column ">
                <h4>BOLETÍN INFORMATIVO</h4>
                <p>Suscríbete a nuestros boletines ahora y mantente al día con nuevas colecciones y ofertas exclusivas.
                </p>
                <form class="newsletter-form ">
                    <input type="email " placeholder="Ingresa el correo aquí... " required />
                    <button type="submit ">SUSCRÍBETE</button>
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

    <!-- Script de autenticación global -->
    <script src="./static/js/user_profile.js"></script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/history_favorites.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Ultra Modern Sobre Nosotros JavaScript -->
    <script src="./js/sobre_nosotros.js"></script>
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
        });
    </script>
    <script src="./static/js/loader.js" defer></script>
    <script src="./static/js/session_guard.js" defer></script>
    <script src="./static/js/network_guard.js" defer></script>
</body>

</html>
