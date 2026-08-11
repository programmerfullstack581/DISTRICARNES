<?php
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Hermanos Navarro</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/inicio_districarnes.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/tailwind.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/theme.css" />
    <script src="<?php echo $basePath; ?>/static/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
</head>

<body class=" bg-black text-white ">
    <!-- Header -->
<?php
$activeNav = 'inicio';
$headerExtra = '<style>a{text-decoration:none}</style>';
include __DIR__ . '/includes/header.php';
?>


                <script>
                    // Función mejorada para toggle del dropdown
                    function toggleUserDropdown() {
                        const dropdown = document.getElementById('userDropdown');
                        const button = document.querySelector('.menu-button');
                        const isOpen = dropdown.classList.contains('active');

                        // Cerrar todos los dropdowns primero
                        document.querySelectorAll('.user-dropdown').forEach(d => d.classList.remove('active'));
                        document.querySelectorAll('.menu-button').forEach(b => b.setAttribute('aria-expanded', 'false'));

                        if (!isOpen) {
                            dropdown.classList.add('active');
                            button.setAttribute('aria-expanded', 'true');
                        }
                    }

                    document.addEventListener('click', function(event) {
                        const container = document.querySelector('.user-profile-container');
                        if (!container.contains(event.target)) {
                            const dd = document.getElementById('userDropdown');
                            if (dd) dd.classList.remove('active');
                            const btn = document.querySelector('.menu-button');
                            if (btn) btn.setAttribute('aria-expanded', 'false');
                        }
                    });

                    document.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape') {
                            const dd = document.getElementById('userDropdown');
                            if (dd) dd.classList.remove('active');
                            const btn = document.querySelector('.menu-button');
                            if (btn) btn.setAttribute('aria-expanded', 'false');
                        }
                    });

                    function updateUserProfile(userData) {
                        if (!userData) return;
                        const name = userData.name || userData.nombres_completos || userData.nombre || 'Usuario';
                        const emailVal = userData.email || userData.correo_electronico || '';
                        const roleVal = userData.role || userData.rol || 'Usuario';
                        const photo = userData.usuario_foto || userData.foto || userData.picture || '';
                        const initials = (name.charAt(0) || 'U').toUpperCase();
                        const avatar = document.getElementById('userAvatar');
                        const userName = document.getElementById('userName');
                        const avatarLarge = document.getElementById('userAvatarLarge');
                        const fullName = document.getElementById('userFullName');
                        const email = document.getElementById('userEmail');
                        const role = document.getElementById('userRole');

                        const applyPhoto = (el, url) => {
                            if (!el || !url) return;
                            el.style.backgroundImage = `url("${url}")`;
                            el.style.backgroundSize = 'cover';
                            el.style.backgroundPosition = 'center';
                            el.style.backgroundRepeat = 'no-repeat';
                            el.textContent = '';
                            el.classList.add('has-photo');
                        };

                        if (photo) {
                            applyPhoto(avatar, photo);
                            applyPhoto(avatarLarge, photo);
                        } else {
                            if (avatar) avatar.textContent = initials;
                            if (avatarLarge) avatarLarge.textContent = initials;
                        }
                        if (userName) userName.textContent = name;
                        if (fullName) fullName.textContent = userData.fullName || name;
                        if (email) email.textContent = emailVal;
                        if (role) role.textContent = roleVal;
                    }
                 </script>
    <!-- Modal (se mostrará automáticamente) -->
    <div id="welcomeModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-bg-shape"></div>
            <div class="modal-bg-shape2"></div>

            <!-- Contenedor principal horizontal -->
            <div class="modal-body">
                <div class="modal-text">
                    <div class="modal-header">
                        <h2 class="text-3xl md:text-4xl font-extrabold mb-2 ">
                            DISTRICARNES
                            <span class="text-red-600 italic " style="color: red;">HERMANOS NAVARRO</span>
                        </h2>

                    </div>
                    <div class="modal-content">
                        <p><strong>Tu carnicería favorita te da la bienvenida.</strong></p>
                        <p>Descubre nuestra selección de cortes premium, frescos y de calidad garantizada. ¡Te esperamos
                            con los mejores precios y atención personalizada!</p>
                    </div>
                    <button class="modal-button" onclick="closeModal()">Explorar Productos</button>
                </div>

                <div class="modal-image">
                    <img src="./static/images/carnicero_navarro.png" alt="Carnicería Districarnes">
                </div>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
        </div>
    </div>
    <script>
        // Función para cerrar el modal
        function closeModal() {
            document.getElementById('welcomeModal').style.display = 'none';
            if (window.playHeroEntrance) window.playHeroEntrance();
        }

        // Mostrar el modal al cargar la página
        window.onload = function() {
            document.getElementById('welcomeModal').style.display = 'flex';
            checkUserSession(); // Verificar sesión del usuario
        };

        // Verificar si el usuario está logueado y poblar datos del header
        function checkUserSession() {
            const userData = localStorage.getItem('userData');
            const sessionData = sessionStorage.getItem('currentSession');

            if (userData || sessionData) {
                const raw = JSON.parse(userData || sessionData);
                if (raw && raw.isLoggedIn) {
                    const currentUser = raw.user ? raw.user : raw;

                    // Ocultar elementos de autenticación en el header
                    const authButtons = document.getElementById('authButtons');
                    const userLoggedButtons = document.getElementById('userLoggedButtons');
                    if (authButtons) authButtons.style.display = 'none';
                    if (userLoggedButtons) userLoggedButtons.style.display = 'block';

                    // Nombre y correo desde la BD
                    const displayName = currentUser.nombres_completos || currentUser.nombre || currentUser.correo_electronico || currentUser.email || 'Usuario';
                    const displayEmail = currentUser.correo_electronico || currentUser.email || '';
                    const displayRole = currentUser.rol || '';
                    const initials = (displayName.charAt(0) || 'U').toUpperCase();

                    // Poblar elementos del menú
                    const userAvatar = document.getElementById('userAvatar');
                    const userName = document.getElementById('userName');
                    const userAvatarLarge = document.getElementById('userAvatarLarge');
                    const userFullName = document.getElementById('userFullName');
                    const userEmail = document.getElementById('userEmail');
                    const userRole = document.getElementById('userRole');

                    const photo = currentUser.usuario_foto || currentUser.foto || currentUser.picture || '';
                    const applyPhoto = (el, url) => {
                        if (!el || !url) return;
                        el.style.backgroundImage = `url("${url}")`;
                        el.style.backgroundSize = 'cover';
                        el.style.backgroundPosition = 'center';
                        el.style.backgroundRepeat = 'no-repeat';
                        el.textContent = '';
                        el.classList.add('has-photo');
                    };
                    if (photo) {
                        applyPhoto(userAvatar, photo);
                        applyPhoto(userAvatarLarge, photo);
                    } else {
                        if (userAvatar) userAvatar.textContent = initials;
                        if (userAvatarLarge) userAvatarLarge.textContent = initials;
                    }
                    if (userName) userName.textContent = displayName;
                    if (userFullName) userFullName.textContent = displayName;
                    if (userEmail) userEmail.textContent = displayEmail;
                    if (userRole) userRole.textContent = displayRole ? displayRole.charAt(0).toUpperCase() + displayRole.slice(1) : '';

                    // Avatar móvil
                    try {
                        var mhLink = document.getElementById('mhUserLink');
                        var mhIcon = document.getElementById('mhUserIcon');
                        if (photo && mhLink) {
                            mhLink.style.backgroundImage = 'url(\"' + photo + '\")';
                            mhLink.style.backgroundSize = 'cover';
                            mhLink.style.backgroundPosition = 'center';
                            mhLink.style.backgroundRepeat = 'no-repeat';
                            if (mhIcon) mhIcon.style.display = 'none';
                        } else {
                            if (mhIcon) mhIcon.style.display = 'inline-block';
                            if (mhLink) mhLink.style.backgroundImage = '';
                        }
                    } catch (e) {}

                    // Fallback: si no hay foto en sesión, consultar al backend y aplicar
                    if (!photo && displayEmail) {
                        try {
                            fetch('./backend/php/auth/get_user_by_email.php?email=' + encodeURIComponent(displayEmail))
                                .then(function(r) {
                                    return r.ok ? r.json() : Promise.reject();
                                })
                                .then(function(d) {
                                    if (d && d.success && d.user && d.user.foto) {
                                        applyPhoto(userAvatar, d.user.foto);
                                        applyPhoto(userAvatarLarge, d.user.foto);
                                        try {
                                            var data = userData ? JSON.parse(userData) : (sessionData ? JSON.parse(sessionData) : null);
                                            if (data) {
                                                if (data.user) data.user.usuario_foto = d.user.foto;
                                                else data.usuario_foto = d.user.foto;
                                                if (userData) localStorage.setItem('userData', JSON.stringify(data));
                                                else if (sessionData) sessionStorage.setItem('currentSession', JSON.stringify(data));
                                            }
                                        } catch (_) {}
                                    }
                                })
                                .catch(function() {});
                        } catch (_) {}
                    }

                    // Mensaje de bienvenida
                    const welcomeElement = document.getElementById('userWelcome');
                    if (welcomeElement) {
                        welcomeElement.textContent = `¡Bienvenido, ${displayName}!`;
                    }
                }
            }
            // Ajustar enlace de perfil/login en móvil cuando NO hay sesión
            try {
                var mhLink = document.getElementById('mhUserLink');
                var mhIcon = document.getElementById('mhUserIcon');
                var raw = userData || sessionData ? JSON.parse(userData || sessionData) : null;
                if (!raw || !raw.isLoggedIn) {
                    if (mhLink) mhLink.href = './login/login.php';
                    if (mhIcon) mhIcon.style.display = 'inline-block';
                } else {
                    if (mhLink) mhLink.href = './perfil.php';
                }
            } catch (e) {}
            // Sincronizar badge del carrito en header móvil
            try {
                var c = document.getElementById('cartCount');
                var m = document.getElementById('mhCartCount');
                if (c && m) {
                    m.textContent = c.textContent || '0';
                }
            } catch (e) {}
        }

        window.addEventListener('cart:updated', function() {
            try {
                var c = document.getElementById('cartCount');
                var m = document.getElementById('mhCartCount');
                if (c && m) {
                    m.textContent = c.textContent || '0';
                }
            } catch (e) {}
        });
    </script>


    <!-- Hero Section  contendio donde va la imagen donde esta el titulo y el carnicero navarro -->
    <section class="hero1section" id="main-content">
        <!-- FONDO VIDEO DE CARNICERÍA (limitado al hero) -->
        <div id="anime-bg">
            <video id="hero-video" autoplay muted loop playsinline preload="auto"
                src="<?php echo $basePath; ?>/assets/icon/video de fondo para carniceria-modo claro y oscuro.mp4">
                Tu navegador no soporta video HTML5.
            </video>
            <div class="hero-overlay"></div>
        </div>
        <section id="hero1section"
            class=" relative max-w-7xl mx-auto px-20 py-[150px] flex flex-col md:flex-row items-center md:items-start gap-12 ">

            <!--Contenido el mensaje que esta al lado del carnicero-->
            <div class="md:w-1/2 flex flex-col justify-center space-y-4">
                <br><br><br>
                <!--Modal para colocar distintas img de fondos con js-->
                <h1 id="heroTitle" class="text-6xl md:text-7xl font-extrabold leading-tight">
                    CARNE FRESCA, SEGURA Y DE CALIDAD
                </h1>
                <p id="heroSub" class="text-gray-300 text-lg md:text-xl max-w-md">
                    <span id="typedSub"></span><span class="typed-caret typed-caret--sm" aria-hidden="true"></span>
                </p>
                <button id="heroCta" onclick="window.location.href='productos.php'" style="background-color: red;"
                    class="bg-red-700 hover:bg-red-800 transition flex items-center space-x-2 text-white font-semibold px-4 py-2 rounded w-max ">
                    <i class="fas fa-shopping-cart "></i><span>Comprar online</span>
                </button>
            </div>



            <!--imagen de carnicero navarro-->
            <div class="md:w-1/2 relative flex items-center justify-center">
                <img id="heroImg" alt="Man in white uniform holding meat cleaver in butcher shop with blurred background lights "
                    class="w-full max-h-[600px] object-contain rounded-md ml-2 mr-4 mt--2 mb-4 "
                    src="./static/images/carnicero_navarro.png "
                    fetchpriority="high" decoding="async" />

                <!-- Contenedor de elementos de usuario logueado al lado de la imagen -->
                <div id="userLoggedButtonsHero" style="display: none;">
                    <div class="user-info-hero">
                        <span id="userWelcomeHero" class="user-welcome-hero"></span>
                        <div class="nav-links-hero">
                            <a href="./index.php" class="active">Inicio</a>
                            <a href="./productos.php">Productos</a>
                            <a href="./promociones.php">Promociones</a>
                            <a href="./contacto.php">Contacto</a>
                            <a href="./sobre_nosotros.php">Quienes Somos</a>
                        </div>
                        <button onclick="logout()" class="logout-btn-hero">
                            <i class="bi bi-box-arrow-right" style="font-size: 1.2rem;"></i> Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </section>

    <style>
        #anime-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            overflow: hidden;
            background: #0a0505;
        }

        #hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 30% 50%, rgba(0, 0, 0, 0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 70% 70%, rgba(0, 0, 0, 0.35) 0%, transparent 55%);
            z-index: -2;
            pointer-events: none;
        }

        .hero1section {
            background: transparent !important;
            min-height: 100vh;
            z-index: 1;
            position: relative;
            overflow: hidden;
        }

        .hero1section::before {
            content: " ";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: -1;
        }

        /* --- Adaptación del video y el hero al modo claro --- */
        html[data-theme="light"] #anime-bg {
            background: #ffffff !important;
        }

        html[data-theme="light"] .hero-overlay {
            background:
                radial-gradient(ellipse at 30% 50%, rgba(0, 0, 0, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse at 70% 70%, rgba(0, 0, 0, 0.18) 0%, transparent 55%);
        }

        html[data-theme="light"] .hero1section::before {
            background: rgba(255, 255, 255, 0.4);
        }

        html[data-theme="light"] .hero1section h1,
        html[data-theme="light"] .hero1section p,
        html[data-theme="light"] .hero1section .text-white,
        html[data-theme="light"] .hero1section .text-gray-300 {
            color: #1f2937 !important;
        }

        html[data-theme="light"] .hero1section button.text-white,
        html[data-theme="light"] .hero1section button .text-white {
            color: #ffffff !important;
        }

        html[data-theme="light"] .hero1section .typed-caret {
            background: #1f2937 !important;
        }

        html[data-theme="light"] #userLoggedButtonsHero .user-welcome-hero {
            color: #111827;
        }

        html[data-theme="light"] #userLoggedButtonsHero .nav-links-hero a {
            color: #111827;
            border-color: rgba(0, 0, 0, 0.2);
        }

        html[data-theme="light"] #userLoggedButtonsHero .nav-links-hero a:hover {
            background: rgba(255, 0, 0, 0.12);
            border-color: rgba(255, 0, 0, 0.5);
        }
    </style>
    <script>
        (function () {
          'use strict';

          // --- Entrada del hero con anime.js (título letra por letra) ---
          function splitLetters(el) {
            var text = el.textContent.replace(/\s+/g, ' ').trim();
            el.textContent = '';
            var frag = document.createDocumentFragment();
            var words = text.split(' ');
            for (var wi = 0; wi < words.length; wi++) {
              var w = document.createElement('span');
              w.style.display = 'inline-block';
              w.style.whiteSpace = 'nowrap';
              for (var ci = 0; ci < words[wi].length; ci++) {
                var s = document.createElement('span');
                s.className = 'hero-letter';
                s.style.display = 'inline-block';
                s.style.opacity = '0';
                s.textContent = words[wi][ci];
                w.appendChild(s);
              }
              frag.appendChild(w);
              if (wi < words.length - 1) frag.appendChild(document.createTextNode(' '));
            }
            el.appendChild(frag);
            return el.querySelectorAll('.hero-letter');
          }

          window.playHeroEntrance = function () {
            if (window.__heroPlayed) return;
            window.__heroPlayed = true;
            if (!window.anime) return;

            var a = window.anime;
            var h1 = document.getElementById('heroTitle');
            var sub = document.getElementById('heroSub');
            var cta = document.getElementById('heroCta');
            var img = document.getElementById('heroImg');

            if (sub) sub.style.opacity = '0';
            if (cta) cta.style.opacity = '0';
            if (img) img.style.opacity = '0';

            if (h1) {
              var letters = splitLetters(h1);
              a({
                targets: letters,
                translateY: [60, 0],
                opacity: [0, 1],
                rotate: [6, 0],
                delay: a.stagger(45, { start: 150 }),
                duration: 900,
                easing: 'easeOutExpo'
              });
            }
            if (sub) {
              a({
                targets: sub,
                opacity: [0, 1],
                translateY: [26, 0],
                delay: 900,
                duration: 700,
                easing: 'easeOutQuad'
              });
            }
            if (cta) {
              a({
                targets: cta,
                opacity: [0, 1],
                scale: [0.6, 1],
                delay: 1100,
                duration: 700,
                easing: 'easeOutBack'
              });
            }
            if (img) {
              a({
                targets: img,
                opacity: [0, 1],
                translateY: [50, 0],
                scale: [0.85, 1],
                delay: 450,
                duration: 1000,
                easing: 'easeOutCubic'
              });
            }
          };
        })();
    </script>

    <section class="brand-marquee" aria-label="Marcas y servicios">
        <style>
            /* brand-marquee — respeta el tema activo vía tokens CSS */
            .brand-marquee {
                background: var(--dc-surface, #1a1414);
                border-top: 1px solid var(--dc-border, rgba(255,255,255,.06));
                border-bottom: 1px solid var(--dc-border, rgba(255,255,255,.06));
                overflow: hidden;
                transition: background-color .25s ease, border-color .25s ease;
            }
            html[data-theme="light"] .brand-marquee {
                background: var(--dc-surface-2) !important;
                border-top-color: var(--dc-border) !important;
                border-bottom-color: var(--dc-border) !important;
            }

            .brand-marquee__viewport {
                max-width: 1200px;
                margin: 0 auto;
                position: relative
            }

            .brand-marquee__fade {
                position: absolute;
                top: 0;
                bottom: 0;
                width: 60px;
                pointer-events: none;
                z-index: 1;
            }

            .brand-marquee__fade.left {
                left: 0;
                background: linear-gradient(90deg, var(--dc-surface, #1a1414) 0%, transparent 100%)
            }
            .brand-marquee__fade.right {
                right: 0;
                background: linear-gradient(270deg, var(--dc-surface, #1a1414) 0%, transparent 100%)
            }
            html[data-theme="light"] .brand-marquee__fade.left {
                background: linear-gradient(90deg, var(--dc-surface-2) 0%, transparent 100%) !important
            }
            html[data-theme="light"] .brand-marquee__fade.right {
                background: linear-gradient(270deg, var(--dc-surface-2) 0%, transparent 100%) !important
            }

            .brand-marquee__track {
                display: flex;
                gap: 28px;
                align-items: center;
                width: max-content;
                animation: brandScroll 28s linear infinite;
                padding: 10px 0
            }

            .brand-marquee:hover .brand-marquee__track {
                animation-play-state: paused
            }

            .brand-item {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: var(--dc-text-muted, #b0a8a8);
                text-decoration: none;
                white-space: nowrap;
                font-weight: 700;
                opacity: .9;
                transition: transform .2s ease, opacity .2s ease, color .2s ease
            }
            html[data-theme="light"] .brand-item {
                color: var(--dc-text-muted) !important;
            }

            .brand-item i {
                font-size: 1.4rem
            }

            .brand-item:hover {
                transform: scale(1.05);
                opacity: 1;
                color: var(--dc-text, #f3f0ef)
            }

            .brand-item .is-red {
                color: var(--dc-primary, #e8192c)
            }

            @keyframes brandScroll {
                from { transform: translateX(0) }
                to   { transform: translateX(-50%) }
            }
        </style>
        <div class="brand-marquee__viewport">
            <div class="brand-marquee__fade left"></div>
            <div class="brand-marquee__fade right"></div>
            <div class="brand-marquee__track">
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fab fa-cc-visa is-red"></i><span>Visa</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fab fa-cc-mastercard is-red"></i><span>Mastercard</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fab fa-cc-amex"></i><span>Amex</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-money-check-alt is-red"></i><span>PSE</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-university"></i><span>Bancolombia</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-wallet is-red"></i><span>Nequi</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-wallet"></i><span>Daviplata</span></a>
                <a class="brand-item" href="https://wa.me/573015210177" target="_blank" rel="noopener"><i
                        class="fab fa-whatsapp is-red"></i><span>WhatsApp</span></a>
                <a class="brand-item" href="https://instagram.com" target="_blank" rel="noopener"><i
                        class="fab fa-instagram"></i><span>Instagram</span></a>
                <a class="brand-item" href="https://facebook.com" target="_blank" rel="noopener"><i
                        class="fab fa-facebook is-red"></i><span>Facebook</span></a>
                <a class="brand-item" href="https://maps.google.com/?q=DistriCarnes%20Cartagena" target="_blank"
                    rel="noopener"><i class="fas fa-map-marker-alt"></i><span>Google Maps</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-truck is-red"></i><span>Domicilios</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-shield-alt"></i><span>Pagos Seguros</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-award is-red"></i><span>Calidad</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fab fa-cc-visa is-red"></i><span>Visa</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fab fa-cc-mastercard is-red"></i><span>Mastercard</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fab fa-cc-amex"></i><span>Amex</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-money-check-alt is-red"></i><span>PSE</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-university"></i><span>Bancolombia</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-wallet is-red"></i><span>Nequi</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-wallet"></i><span>Daviplata</span></a>
                <a class="brand-item" href="https://wa.me/573015210177" target="_blank" rel="noopener"><i
                        class="fab fa-whatsapp is-red"></i><span>WhatsApp</span></a>
                <a class="brand-item" href="https://instagram.com" target="_blank" rel="noopener"><i
                        class="fab fa-instagram"></i><span>Instagram</span></a>
                <a class="brand-item" href="https://facebook.com" target="_blank" rel="noopener"><i
                        class="fab fa-facebook is-red"></i><span>Facebook</span></a>
                <a class="brand-item" href="https://maps.google.com/?q=DistriCarnes%20Cartagena" target="_blank"
                    rel="noopener"><i class="fas fa-map-marker-alt"></i><span>Google Maps</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-truck is-red"></i><span>Domicilios</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-shield-alt"></i><span>Pagos Seguros</span></a>
                <a class="brand-item" href="#" target="_blank" rel="noopener"><i
                        class="fas fa-award is-red"></i><span>Calidad</span></a>
            </div>
        </div>
    </section>


    <!-- Cómo realizar un pedido -->
    <section class="max-w-7xl mx-auto px-6 py-12 ">
        <h2 class="text-white font-extrabold text-xl md:text-2xl mb-8 " style="text-align: center; ">
            ¿CÓMO PUEDES HACER TU PEDIDO?
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-gray-300 text-sm max-w-7xl mx-auto ">

                <!-- Telefono -->
                <div class="flex flex-col items-center text-center space-y-4 ">
                    <i class="fas fa-phone-alt text-red-600 text-4xl "></i>
                    <h3 class="text-white font-semibold ">
                        Llámanos y te asesoramos al instante
                    </h3>
                    <p>
                        La asesoría es totalmente gratuita y sin compromiso. Te ayudamos a elegir el mejor producto para
                        ti.
                        Recuerda que puedes pagar con tarjeta débito o crédito, efectivo o transferencia.
                    </p>
                    <p class="font-semibold text-white ">
                        +57 301 5210177
                    </p>
                    <button onclick="window.location.href='contacto.php'" style="background-color: red;"
                        class="bg-red-700 hover:bg-red-800 transition flex items-center space-x-2 text-white font-semibold px-4 py-2 rounded w-max ">
                        <i class="fas fa-phone-alt "></i>
                        <span>Llámanos al +57</span>
                    </button>
                </div>


                <!-- WhatsApp -->
                <div class="flex flex-col items-center text-center space-y-4 ">
                    <!-- Sección de WhatsApp -->
                    <i class="fab fa-whatsapp text-red-600 text-4xl "></i>
                    <h3 class="text-white font-semibold ">
                        Atención personalizada por WhatsApp
                    </h3>
                    <p>
                        Haz tus pedidos vía WhatsApp y recibe la mejor atención personalizada. Estamos para ayudarte en
                        todo
                        lo que necesites.
                    </p>
                    <button
                        onclick="window.open('https://wa.me/573015210177?text=Hola%2C%20quiero%20hacer%20un%20pedido%20de%20la%20carnicer%C3%ADa', '_blank')"
                        style="background-color: red;"
                        class="bg-red-700 hover:bg-red-800 transition flex items-center space-x-2 text-white font-semibold px-4 py-2 rounded w-max">
                        <i class="fab fa-whatsapp"></i>
                        <span>Enviar un mensaje</span>
                    </button>
                </div>



                <!-- Pedido web-->
                <div class="flex flex-col items-center text-center space-y-4 ">
                    <!-- Sección de pedido web -->
                    <i class="fas fa-shopping-cart text-red-600 text-4xl "></i>
                    <h3 class="text-white font-semibold ">
                        Haz tu pedido a través de la web
                    </h3>
                    <p>
                        Puedes realizar tu compra desde la comodidad de tu casa o trabajo. Recibe tu pedido en la puerta
                        de
                        tu hogar.
                    </p>
                    <button onclick="window.location.href='productos.php'" style="background-color: red;"
                        class="bg-red-700 hover:bg-red-800 transition flex items-center space-x-2 text-white font-semibold px-4 py-2 rounded w-max ">
                        <i class="fas fa-shopping-cart "></i><span>Comprar online</span>
                    </button>
                </div>
            </div>
    </section>





    <!-- Sección del mapa -->
    <section class="px-4 py-8 max-w-7xl mx-auto" id="sobre_nosotros">
        <div style="display:flex; gap:16px; align-items:stretch; flex-wrap:wrap;">
            <div class="map-wrapper" style="flex:1 1 480px; min-width:300px;">
                <div id="districarnes-map" class="districarnes-map"
                    data-lat="10.39697399240679"
                    data-lng="-75.55148638476352"
                    data-name="DistriCarnes - Hermanos Navarro"
                    data-address="Olaya Herrera #34-71A-60, Cartagena de Indias, Colombia"
                    data-phone="+573015210177"
                    style="width:100%; height:350px; border-radius:8px;">
                </div>
            </div>
            <div class="ubicanos-card"
                style="flex:1 1 320px; min-width:280px; border-radius:8px; padding:16px;">
                <h3 class="ubicanos-title" style="margin:0 0 8px; font-weight:800;">Ubícanos</h3>
                <div
                    style="display:grid; grid-template-columns:20px 1fr; gap:8px; align-items:start; margin-bottom:12px;">
                    <div style="opacity:.9;">📍</div>
                    <div>Olaya Herrera #34-71A-60, Cartagena de Indias, Colombia</div>
                    <div style="opacity:.9;">⏰</div>
                    <div>Lunes a Sábado 8:00–20:00 · Domingo 9:00–17:00</div>
                    <div style="opacity:.9;">☎️</div>
                    <div><a href="tel:+573015210177" style="color:#fff; text-decoration:none;">301 521 0177</a></div>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="https://www.google.com/maps?q=DistriCarnes+Cartagena&hl=es" target="_blank"
                        rel="noopener noreferrer"
                        style="background:var(--dc-primary,#e8192c); color:#fff; padding:10px 14px; border-radius:999px; font-weight:700; text-decoration:none;">Ver
                        en Google Maps</a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=10.39697399240679,-75.55148638476352"
                        target="_blank" rel="noopener noreferrer"
                        style="background:var(--dc-surface-3,#2c2222); color:var(--dc-text,#fff); padding:10px 14px; border-radius:999px; font-weight:700; text-decoration:none;">Cómo
                        llegar</a>
                    <a href="https://wa.me/573015210177?text=Hola%20DistriCarnes,%20quiero%20m%C3%A1s%20informaci%C3%B3n"
                        target="_blank" rel="noopener noreferrer"
                        style="background:#22c55e; color:#fff; padding:10px 14px; border-radius:999px; font-weight:700; text-decoration:none;">WhatsApp</a>
                    <button type="button"
                        onclick="(function(){var t='Olaya Herrera #34-71A-60, Cartagena de Indias, Colombia';navigator.clipboard&&navigator.clipboard.writeText(t).then(function(){try{showToast('Dirección copiada','success',{duration:1500});}catch(e){}});})()"
                        style="background:var(--dc-surface-2,#221a1a); color:var(--dc-text,#fff); padding:10px 14px; border-radius:999px; font-weight:700; border:1px solid var(--dc-border,rgba(255,255,255,0.1)); cursor:pointer;">Copiar
                        dirección</button>
                </div>
            </div>
        </div>
    </section>






    <!-- Formulario de contacto -->
    <div class="contact-section">
        <div class="contact-info">
            <style>
                .contact-section {
                    display: grid;
                    grid-template-columns: 1.1fr 1fr;
                    gap: 24px;
                    align-items: start;
                    min-width: 0;
                }

                .contact-info {
                    color: #fff;
                    padding-right: 24px;
                    min-width: 0;
                }

                .contact-form {
                    background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
                    padding: 1.75rem;
                    border-radius: 14px;
                    border: 1px solid rgba(255, 255, 255, 0.06);
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
                    font-family: 'Montserrat', sans-serif;
                    margin-left: auto;
                    max-width: 520px;
                    width: 100%;
                    box-sizing: border-box;
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

                /* ============================================================
                   Responsividad del formulario de contacto.
                   IMPORTANTE: los breakpoints van AL FINAL del bloque para que
                   (misma especificidad) puedan sobrescribir las reglas base del
                   formulario que están más arriba.
                   ============================================================ */

                /* Tablets y pantallas medianas: una sola columna y el formulario
                   centrado, sin desbordar el ancho disponible. */
                @media (max-width: 992px) {
                    .contact-section {
                        grid-template-columns: 1fr;
                    }

                    .contact-info {
                        padding-right: 0;
                        margin-bottom: 1.5rem;
                    }

                    .contact-form {
                        margin: 0 auto;
                        max-width: 640px;
                        width: 100%;
                    }
                }

                @media (max-width: 768px) {
                    .contact-form {
                        margin: 0 auto;
                        max-width: 100%;
                        width: 100%;
                        padding: 1rem;
                        border-radius: 10px;
                    }

                    .contact-form .form-actions {
                        justify-content: center;
                    }
                }

                @media (max-width: 576px) {
                    .contact-form .btn-submit {
                        width: 100%;
                    }
                }
            </style>

            <h1 class="text-white font-extrabold text-lg mb-4 " style="font-size: 2rem; color: red; ">
                CONTÁCTENOS</h1>

            <p>En <strong>Carnicería La Noble Carne</strong>, nos apasiona ofrecer cortes de carne frescos, selectos y
                de máxima calidad. Desde hace más de 15 años, servimos a familias y chefs con productos que cumplen con
                los más altos estándares de sabor,
                origen y ética animal.</p>

            <p>¿Tienes dudas sobre nuestros productos, horarios, delivery o necesitas un pedido especial? ¡Estamos aquí
                para ayudarte!</p>

            <div class="tagline">“Porque cada bocado merece lo mejor.”</div>

            <a href="./contacto.php" class="learn-more">APRENDE MÁS</a>

            <div class="footer-info">
                <p><strong>Horario:</strong> Lunes a Sábado 8:00 AM - 8:00 PM | Domingos 9:00 AM - 5:00 PM</p>
                <p><strong>Ubicación:</strong> Av. Principal #123, Barrio del Sabor, Ciudad Gastronómica</p>
                <p><strong>Teléfono:</strong> 301 5210177</p>
                <p><em>Imágenes de Freepik</em></p>
            </div>
        </div>

        <div class="contact-form">
            <form id="contactForm" method="post" action="https://formspree.io/f/mnjbandn" autocomplete="on">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Ingresa un correo válido" required>
                </div>

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" placeholder="Ingresa tu nombre" required>
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <input type="text" id="address" name="address" placeholder="Ingresa tu dirección" required>
                </div>

                <div class="form-group">
                    <label for="message">Mensaje</label>
                    <textarea id="message" name="message" placeholder="Escribe tu mensaje aquí..." required></textarea>
                </div>

                <div class="form-actions">
                    <button class="btn-submit" type="submit">ENVIAR</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Footer -->
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
    <div class="chatbot-toggle" onclick="toggleChatbot()" title="Abrir chat DISTRICARNES" tabindex="0"
        aria-label="Abrir chat DISTRICARNES">
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
                <input type="text" class="chat-input" id="userInput"
                    placeholder="¿Qué deseas saber sobre nuestras carnes?" onkeypress="handleKeyPress(event)"
                    autocomplete="off" />
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
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var contactForm = document.getElementById('contactForm');
            if (!contactForm) return;
            contactForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var formData = new FormData(contactForm);
                var action = contactForm.getAttribute('action');

                Swal.fire({
                    title: 'Enviando...',
                    text: 'Por favor, espera un momento.',
                    allowOutsideClick: false,
                    didOpen: function () { Swal.showLoading(); }
                });

                fetch(action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                }).then(function (response) {
                    if (response.ok) {
                        Swal.fire('¡Gracias!', 'Tu mensaje ha sido enviado con éxito.', 'success');
                        contactForm.reset();
                    } else {
                        response.json().then(function (data) {
                            var errorMessage = 'Ocurrió un error al enviar tu mensaje.';
                            if (data && data.errors) {
                                errorMessage = data.errors.map(function (error) { return error.message; }).join(', ');
                            }
                            Swal.fire('Error', errorMessage, 'error');
                        });
                    }
                }).catch(function () {
                    Swal.fire('Error', 'No se pudo enviar el mensaje. Inténtalo de nuevo más tarde.', 'error');
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <script>
        window.DISTRICARNES_CONFIG = {
            googleMapsApiKey: <?php echo json_encode(GOOGLE_MAPS_API_KEY, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            googleMapsStyleId: <?php echo json_encode(GOOGLE_MAPS_STYLE_ID, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            basePath: <?php echo json_encode(BASE_PATH, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            baseUrl: <?php echo json_encode(BASE_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
        };
    </script>
    <script src="./static/js/header_actions.js"></script>
    <script src="./static/js/auth_modal.js?v=<?= filemtime(__DIR__ . '/static/js/auth_modal.js') ?>"></script>
    <script src="./static/js/index.js"></script>
    <script src="./static/js/google-maps.js"></script>
    <script src="./static/js/chatbot.js"></script>
    <script>
        (function() {
            const elSub = document.getElementById('typedSub');
            if (!elSub) return;
            const phrasesSub = [
                'Selección premium de carnes, vacuno, porcino y aves.',
                'Calidad garantizada y servicio de excelencia.'
            ];
            let i = 0,
                j = 0,
                writing = true;
            const typeSpeed = 36,
                deleteSpeed = 28,
                hold = 1000;

            function step() {
                const phrase = phrasesSub[i];
                if (writing) {
                    j = Math.min(j + 1, phrase.length);
                    elSub.textContent = phrase.slice(0, j);
                    if (j === phrase.length) {
                        writing = false;
                        setTimeout(step, hold);
                        return;
                    }
                    setTimeout(step, typeSpeed);
                } else {
                    j = Math.max(0, j - 1);
                    elSub.textContent = phrase.slice(0, j);
                    if (j === 0) {
                        i = (i + 1) % phrasesSub.length;
                        writing = true;
                        setTimeout(step, typeSpeed);
                        return;
                    }
                    setTimeout(step, deleteSpeed);
                }
            }
            step();
        })();
    </script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/history_favorites.js"></script>
    <script src="./static/js/loader.js" defer></script>
    <script src="./static/js/session_guard.js" defer></script>
    <script src="./static/js/network_guard.js" defer></script>
    <script src="./static/js/onboarding.js" defer></script>
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
    <script src="<?php echo $basePath; ?>/static/js/public_tour.js"></script>
</body>

</html>