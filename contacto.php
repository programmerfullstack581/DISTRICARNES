<?php require_once __DIR__ . '/config/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Contacto</title>
    <link rel="stylesheet" href="./static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="./assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="./static/css/header_en_general.css" />
    <link rel="stylesheet" href="./static/css/contacto.css" />
    <link rel="stylesheet" href="./static/css/base.css" />
    <link rel="stylesheet" href="./static/css/chatbot.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="./static/css/responsive.css" />
    <link rel="stylesheet" href="./static/css/tailwind.css" />
    <link rel="stylesheet" href="./static/css/theme.css" />
    <script src="./static/js/theme.js"></script>
    <script src="./static/js/auth_utils.js"></script>

</head>

<body class=" bg-black text-white ">
    <!-- Header -->
<?php
$activeNav = 'contacto';
include __DIR__ . '/includes/header.php';
?>


<?php include __DIR__ . '/includes/header-scripts.php'; ?>
    

    <!--Contactano -->
    <div class="contact-section">
        <div class="contact-info">

            <h1 class="text-white font-extrabold text-lg mb-4 " style="font-size: 2rem; color: red; ">
                CONTÁCTENOS</h1>

            <p>En <strong>Carnicería La Noble Carne</strong>, nos apasiona ofrecer cortes de carne frescos, selectos y de máxima calidad. Desde hace más de 15 años, servimos a familias y chefs con productos que cumplen con los más altos estándares de sabor,
                origen y ética animal.</p>

            <p>¿Tienes dudas sobre nuestros productos, horarios, delivery o necesitas un pedido especial? ¡Estamos aquí para ayudarte!</p>

            <div class="tagline">“Porque cada bocado merece lo mejor.”</div>

            <a href="#" class="learn-more">APRENDE MÁS</a>

            <div class="footer-info">
                <p><strong>Horario:</strong> Lunes a Sábado 8:00 AM - 8:00 PM | Domingos 9:00 AM - 5:00 PM</p>
                <p><strong>Ubicación:</strong> Av. Principal #123, Barrio del Sabor, Ciudad Gastronómica</p>
                <p><strong>Teléfono:</strong> +55 555 123 4567</p>
                <p><em>Imágenes de Freepik</em></p>
            </div>
        </div>

        <div class="contact-form" style="max-width:560px; width:100%;">
            <style>
                /* Improved styling for contact form to increase padding and avoid placeholders touching borders */
                
                .contact-form {
                    background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
                    padding: 1.75rem;
                    border-radius: 14px;
                    border: 1px solid rgba(255, 255, 255, 0.06);
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
                    font-family: 'Montserrat', sans-serif;
                    margin-left: auto;
                }
                
                .contact-form h2 {
                    color: #fff;
                    margin: 0 0 0.75rem 0;
                    font-size: 1.125rem;
                    font-weight: 700;
                }
                
                .contact-form .form-group {
                    margin-bottom: 0.95rem;
                }
                
                .contact-form label {
                    display: block;
                    margin-bottom: 0.4rem;
                    color: rgba(255, 255, 255, 0.9);
                    font-weight: 600;
                    font-size: 0.88rem;
                }
                
                .contact-form input[type="text"],
                .contact-form input[type="email"],
                .contact-form textarea {
                    width: 100%;
                    padding: 0.9rem 1rem;
                    /* bigger padding so placeholder doesn't touch edges */
                    border-radius: 10px;
                    border: 1px solid rgba(255, 255, 255, 0.12);
                    background: rgba(255, 255, 255, 0.02);
                    color: #fff;
                    font-size: 0.95rem;
                    line-height: 1.25;
                    box-sizing: border-box;
                    transition: all .18s ease-in-out;
                    outline: none;
                }
                /* Clearer placeholder color and spacing */
                
                .contact-form input::placeholder,
                .contact-form textarea::placeholder {
                    color: rgba(255, 255, 255, 0.5);
                }
                /* Focus styles: clear contrast and subtle shadow */
                
                .contact-form input:focus,
                .contact-form textarea:focus {
                    border-color: rgba(255, 0, 0, 0.95);
                    box-shadow: 0 6px 20px rgba(255, 0, 0, 0.12);
                    background: rgba(0, 0, 0, 0.55);
                }
                
                .contact-form textarea {
                    min-height: 140px;
                    resize: vertical;
                }
                
                .contact-form .form-actions {
                    display: flex;
                    gap: 0.6rem;
                    align-items: center;
                    justify-content: flex-start;
                    margin-top: 0.6rem;
                }
                
                .contact-form .btn-submit {
                    padding: 0.75rem 1.25rem;
                    background: linear-gradient(90deg, #ff0000 0%, #b30000 100%);
                    color: #fff;
                    border: none;
                    border-radius: 999px;
                    cursor: pointer;
                    font-weight: 700;
                    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.18);
                    transition: all .16s ease;
                }
                
                .contact-form .btn-submit:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 14px 40px rgba(255, 0, 0, 0.22);
                }
                /* Small screen handling */
                
                @media (max-width: 768px) {
                    .contact-form {
                        padding: 1rem;
                        border-radius: 10px;
                    }
                    .contact-form .form-actions {
                        justify-content: center;
                    }
                }
                @media (max-width: 992px) {
                    .contact-section {
                        flex-direction: column;
                        gap: 1.25rem;
                        padding: 20px 16px;
                    }
                    .contact-info {
                        padding-right: 0;
                    }
                    .contact-form {
                        margin-left: 0;
                        max-width: 100%;
                    }
                }
                @media (max-width: 576px) {
                    .contact-info h1 {
                        font-size: 1.8rem;
                    }
                    .contact-info p {
                        font-size: 0.95rem;
                    }
                    .contact-form .btn-submit {
                        width: 100%;
                    }
                }
            </style>

            <h2>Envíanos un mensaje</h2>

            <form id="contactForm" action="https://formspree.io/f/mnjbandn" method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Ingresa un correo válido" aria-label="Email" required>
                </div>

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" placeholder="Ingresa tu nombre" aria-label="Nombre" required>
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <input type="text" id="address" name="address" placeholder="Ingresa tu dirección" aria-label="Dirección" required>
                </div>

                <div class="form-group">
                    <label for="message">Mensaje</label>
                    <textarea id="message" name="message" placeholder="Escribe tu mensaje aquí..." aria-label="Mensaje" required></textarea>
                </div>

                <div class="form-actions">
                    <button class="btn-submit" type="submit">ENVIAR</button>
                </div>
            </form>
        </div>
    </div>

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
    <script src="./static/js/header_actions.js"></script>
    <script src="./static/js/auth_modal.js"></script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/history_favorites.js"></script>
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
    <script src="./static/js/loader.js" defer></script>
    <script src="./static/js/session_guard.js" defer></script>
    <script src="./static/js/network_guard.js" defer></script>
    <script>
        const contactForm = document.getElementById('contactForm');
        contactForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(contactForm);
            const action = contactForm.getAttribute('action');

            Swal.fire({
                title: 'Enviando...',
                text: 'Por favor, espera un momento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    Swal.fire(
                        '¡Gracias!',
                        'Tu mensaje ha sido enviado con éxito.',
                        'success'
                    );
                    contactForm.reset();
                } else {
                    response.json().then(data => {
                        let errorMessage = 'Ocurrió un error al enviar tu mensaje.';
                        if (data && data.errors) {
                            errorMessage = data.errors.map(error => error.message).join(', ');
                        }
                        Swal.fire(
                            'Error',
                            errorMessage,
                            'error'
                        );
                    })
                }
            }).catch(error => {
                Swal.fire(
                    'Error',
                    'No se pudo enviar el mensaje. Inténtalo de nuevo más tarde.',
                    'error'
                );
            });
        });
    </script>

</body>

</html>
