<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
$basePath = str_replace('\\', '/', $basePath);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Hermanos Navarro</title>
    <script src="<?php echo $basePath; ?>/static/js/tailwind.browser.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/inicio_districarnes.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
</head>

<body class=" bg-black text-white ">
    <!-- Header -->
    <header class="header ">
        <style>
            .header {
                background: #000;
                border-bottom: none !important;
                box-shadow: none !important
            }

            .mobile-header {
                display: none;
                align-items: center;
                justify-content: space-between;
                background: #000;
                border-bottom: none;
                padding: .4rem .5rem;
                position: sticky;
                top: 0;
                z-index: 2000;
                min-height: 50px
            }

            .mh-left,
            .mh-right {
                display: flex;
                align-items: center;
                gap: 10px
            }

            .mh-left {
                padding-left: .25rem
            }

            .mh-right {
                padding-right: .25rem
            }

            .mh-icon {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                background: #111;
                border: 1px solid #222
            }

            .mh-icon i {
                font-size: 1.2rem
            }

            .mh-cart {
                position: relative
            }

            .mh-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                background: #ff0000;
                color: #fff;
                border-radius: 999px;
                font-size: .65rem;
                padding: 2px 6px;
                line-height: 1
            }

            @media (max-width:768px) {
                .mobile-header {
                    display: flex
                }

                .ml-search {
                    display: none
                }

                #quickLinks {
                    display: none
                }

                #userLoggedButtons {
                    display: none !important
                }

                .nav-menu {
                    display: none
                }

                .header .logo {
                    display: none !important
                }

                .header .mobile-toggle {
                    display: none !important
                }

                .header .header-content {
                    padding: 0;
                    margin: 0
                }
            }

            .header-content,
            .nav-menu,
            .ml-search {
                border-bottom: none !important;
                box-shadow: none !important
            }

            .user-avatar.has-photo,
            .user-avatar-large.has-photo {
                background-color: transparent !important
            }

            @media (min-width:769px) {

                .mobile-drawer,
                .mobile-drawer-overlay {
                    display: none !important
                }
            }
        </style>
        <div class="mobile-header" id="mobileHeader">
            <style>
                .mh-center {
                    position: absolute;
                    left: 50%;
                    transform: translateX(-50%);
                    display: flex;
                    align-items: center
                }

                .mh-center img {
                    height: 26px;
                    max-width: 120px
                }
            </style>
            <div class="mh-left">
                <button class="mh-icon" aria-label="Menú" onclick="(function(){document.body.classList.add('drawer-open');})()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="mh-center">
                <a href="./index.php" aria-label="Inicio">
                    <img src="./assets/icon/LOGO-DISTRICARNES.png" alt="DistriCarnes">
                </a>
            </div>
            <div class="mh-right">
                <a href="./carrito-de-compras/index.php" class="mh-icon mh-cart" aria-label="Carrito">
                    <i class="bi bi-cart"></i>
                    <span class="mh-badge" id="mhCartCount">0</span>
                </a>
                <a href="./perfil.php" class="mh-icon" id="mhUserLink" aria-label="Perfil o Iniciar sesión">
                    <i class="far fa-user-circle" id="mhUserIcon"></i>
                </a>
            </div>
            <div class="mobile-drawer-overlay" id="drawerOverlay" onclick="(function(){document.body.classList.remove('drawer-open');})()"></div>
            <aside class="mobile-drawer" id="mobileDrawer" style="z-index:10000">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid #111;color:#fff">
                    <span>Menú</span>
                    <button type="button" aria-label="Cerrar" onclick="(function(){document.body.classList.remove('drawer-open');})()" style="background:#111;color:#fff;border:1px solid #222;border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center">×</button>
                </div>
                <style>
                    .mobile-drawer {
                        background: #000;
                        border: 1px solid #111;
                        border-radius: 12px;
                        box-shadow: 0 10px 32px rgba(0, 0, 0, .6)
                    }

                    .drawer-quicklinks a {
                        margin: 6px 0;
                        font-weight: 800;
                        letter-spacing: .2px
                    }

                    .drawer-nav {
                        padding: 10px 12px
                    }

                    .drawer-nav a {
                        display: block;
                        background: #0d0d0d;
                        border: 1px solid #1a1a1a;
                        color: #fff;
                        text-decoration: none;
                        padding: 12px 14px;
                        border-radius: 12px;
                        font-weight: 700;
                        text-align: center
                    }

                    .drawer-nav a+a {
                        margin-top: 8px
                    }

                    .drawer-nav a.active {
                        background: #1a1a1a;
                        border-color: #333;
                        position: relative
                    }

                    .drawer-nav a.active::after {
                        content: "";
                        display: block;
                        height: 2px;
                        background: #ff0000;
                        width: 80%;
                        margin: 8px auto 0;
                        border-radius: 2px
                    }

                    a {
                        text-decoration: none;
                    }
                </style>
                <nav class="drawer-nav" style="display:flex;flex-direction:column;align-items:stretch;padding:10px 12px;gap:8px">
                    <a href="./index.php" class="active">Inicio</a>
                    <a href="./productos.php">Productos</a>
                    <a href="./promociones.php">Ofertas</a>
                    <a href="./contacto.php">Contacto</a>
                    <a href="./sobre_nosotros.php">Quienes Somos</a>
                </nav>
                <div id="drawerAuthButtons" class="drawer-quicklinks" style="padding:8px 12px;gap:10px;display:flex;flex-direction:column;align-items:stretch">
                    <a href="./login/login.php" style="background:#ff0000;color:#fff;border:1px solid #ff0000;border-radius:999px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="bi bi-box-arrow-in-right"></i> INICIAR SESIÓN</a>
                    <a href="./login/register.php" style="background:#ff0000;color:#fff;border:1px solid #ff0000;border-radius:999px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="bi bi-person-plus-fill"></i> REGISTRARSE</a>
                </div>
                <div id="drawerUserLogged" style="display:none;padding:8px 12px;gap:10px;flex-direction:column;align-items:stretch">
                    <a href="./perfil.php" style="background:#111;color:#fff;border:1px solid #222;border-radius:10px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="fas fa-user"></i> Mi Perfil</a>
                    <a href="#" onclick="logout()" style="background:#111;color:#ff6b6b;border:1px solid #222;border-radius:10px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </aside>
        </div>
        <div class="header-content ">
            <div class="logo ">
                <a href="./index.php">
                    <img src="./assets/icon/LOGO-DISTRICARNES.png" alt="DISTRICARNES Logo" style="cursor: pointer;">
                </a>
            </div>

            <!-- Buscador central estilo ML y pill promocional -->
            <div class="ml-search">
                <form action="productos.php" method="get">
                    <input type="search" name="q" id="site-search" placeholder="Buscar productos, marcas y más…" />
                    <button type="submit" aria-label="Buscar"><i class="fas fa-search"></i></button>
                </form>
            </div>


            <!-- Enlaces rápidos + botón de carrito (siempre visibles) -->
            <div id="quickLinks" class="ml-actions">
                <a id="cartButton" class="ml-icon-btn ml-icon-bounce" href="./carrito-de-compras/index.php"
                    aria-label="Carrito">
                    <i class="bi bi-cart"></i>
                    <span class="ml-label">Carrito</span>
                    <span class="ml-badge" id="cartCount">0</span>
                </a>
                <!-- Botones de acceso y registro -->
                <div id="authButtons" class="flex gap-3" style="display: none;">
                    <a href="./login/login.php" class="block">
                        <button
                            style="background-color: rgb(255, 0, 0); border-radius: 50px; color: white; border: 2px solid red;"
                            onmouseover="this.style.borderColor='red'; this.style.backgroundColor='black'; this.style.color='white';"
                            onmouseout="this.style.borderColor='red'; this.style.backgroundColor='red'; this.style.color='white';"
                            class="bg-red-700 hover:bg-red-800 transition text-white text-sm font-semibold px-4 py-2 rounded">
                            <i class="bi bi-box-arrow-in-right" style="font-size: 1.5rem;"></i> INICIAR SESIÓN
                        </button>
                    </a>
                    <a href="./login/register.php" class="block">
                        <button
                            style="background-color: rgb(255, 0, 0); border-radius: 50px; color: white; border: 2px solid red;"
                            onmouseover="this.style.borderColor='red'; this.style.backgroundColor='black'; this.style.color='white';"
                            onmouseout="this.style.borderColor='red'; this.style.backgroundColor='red'; this.style.color='white';"
                            class="bg-red-700 hover:bg-red-800 transition text-white text-sm font-semibold px-4 py-2 rounded">
                            <i class="bi bi-person-plus-fill" style="font-size: 1.5rem;"></i>
                            REGISTRARSE
                        </button>
                    </a>
                </div>
            </div>


            <!-- Botones para usuario logueado (inicialmente ocultos) -->
            <div id="userLoggedButtons" style="display: none; box-shadow: 0 0 20px rgba(0,0,0,0.8);">
                <div class="user-profile-container">
                    <button class="menu-button" onclick="toggleUserDropdown()" aria-expanded="false"
                        aria-haspopup="true">
                        <span class="user-avatar" id="userAvatar"></span>
                        <span class="user-name" id="userName"></span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </button>

                    <!-- User Dropdown Menu -->
                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-info-dropdown">
                            <div class="user-avatar-large" id="userAvatarLarge"></div>
                            <div class="user-details">
                                <h4 id="userFullName"></h4>
                                <p id="userEmail"></p>
                                <span class="user-role" id="userRole"></span>
                            </div>
                        </div>


                        <div class="menu-divider"></div>

                        <div class="menu-items">
                            <a href="./perfil.php?tab=overview" class="menu-item">
                                <i class="fas fa-user"></i>
                                <span>Mi Perfil</span>
                            </a>


                            <a href="./historial.php" class="menu-item">
                                <i class="fas fa-clock"></i> Historial de compra
                            </a>
                            <a href="./favoritos.php" class="menu-item">
                                <i class="fas fa-heart"></i> Mis favoritos
                            </a>
                            <a href="./perfil.php?tab=edit" class="menu-item">
                                <i class="fas fa-edit"></i>
                                <span>Editar Perfil</span>
                            </a>
                            <a href="./perfil.php?tab=password" class="menu-item">
                                <i class="fas fa-key"></i>
                                <span>Cambiar Contraseña</span>
                            </a>

                            <a href="./perfil.php?tab=settings" class="menu-item">
                                <i class="fas fa-cog"></i>
                                <span>Configuración</span>
                            </a>


                            <div class="menu-divider"></div>

                            <a href="#" class="menu-item logout" onclick="logout()">
                                <i class="fas fa-sign-out-alt" style="color: red;"></i>
                                <span style="color: red;">Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="nav-menu" id="navMenu">
                <style>
                    /* Contenedor centrado en la página */

                    .nav-menu {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: center;
                        justify-content: center;
                        gap: 6rem;
                        /* Increased spacing */
                        width: 100%;
                        max-width: 960px;
                        /* ancho máximo del nav centrado */
                        margin: 0 auto;
                        /* centra horizontalmente dentro del header */
                        box-sizing: border-box;
                        padding: 0.25rem 0.5rem;
                        text-align: center;
                    }

                    /* Asegura que los enlaces y botones queden centrados visualmente */

                    .nav-menu>a,
                    .nav-menu>div {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .nav-menu a {
                        text-decoration: none;
                        color: inherit;
                        padding: 0.35rem 0.75rem;
                        font-family: 'Montserrat', sans-serif;
                        /* Applied Montserrat font */
                    }

                    /* Mantener los botones de auth alineados y centrados */

                    #authButtons {
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        justify-content: center;
                    }

                    /* En móvil, permitir que los elementos se apilen centrados */

                    @media (max-width: 768px) {
                        .nav-menu {
                            justify-content: center;
                            padding: 0.5rem;
                        }

                        /* Hacer los botones más compactos en móvil */
                        #authButtons {
                            width: 100%;
                            justify-content: center;
                            gap: 0.5rem;
                        }
                    }
                </style>

                <!-- Botones de navegación -->
                <a href="./index.php" class="active">Inicio</a>
                <a href="./productos.php">Productos</a>
                <a href="./promociones.php">Ofertas</a>
                <a href="./contacto.php">Contacto</a>
                <a href="./sobre_nosotros.php">Quienes Somos</a>



                <!-- Estilos y funcionalidad mejorados PARA EL MENU DEL USUARIO LOGUEADO -->
                <style>
                    /* Contenedor principal con fondo negro y separación de bordes */

                    #userLoggedButtons {
                        background-color: #000000;
                        padding: 1rem;
                        border-radius: 10px;
                        margin: 0.5rem;
                    }

                    .user-profile-container {
                        position: relative;
                        display: inline-block;
                    }

                    .menu-button {
                        display: flex;
                        align-items: center;
                        gap: 0.75rem;
                        background: linear-gradient(135deg, #000000 0%, #000000 100%);
                        border: 2px solid #000000;
                        border-radius: 50px;
                        padding: 0.75rem 1.5rem;
                        color: #ffffff;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                    }

                    .menu-button:hover {
                        border-color: #ff0000;
                        background: linear-gradient(135deg, #000000 0%, #000000 100%);
                        box-shadow: 0 6px 25px rgba(255, 0, 0, 0.25);
                        transform: translateY(-2px);
                    }

                    .menu-button:active {
                        transform: translateY(0);
                    }

                    .user-avatar {
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 1.1rem;
                        color: white;
                        box-shadow: 0 2px 8px rgba(255, 0, 0, 0.3);
                    }

                    .user-name {
                        font-size: 0.95rem;
                        color: #f0f0f0;
                        max-width: 120px;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    .dropdown-arrow {
                        margin-left: 0.5rem;
                        transition: transform 0.3s ease;
                        color: #ff0000;
                    }

                    .menu-button[aria-expanded="true"] .dropdown-arrow {
                        transform: rotate(180deg);
                    }

                    .user-dropdown {
                        position: absolute;
                        top: calc(100% + 12px);
                        right: 0;
                        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
                        border: 2px solid #333;
                        border-radius: 16px;
                        min-width: 280px;
                        max-width: 320px;
                        opacity: 0;
                        visibility: hidden;
                        transform: translateY(-10px);
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        z-index: 1000;
                        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
                        backdrop-filter: blur(10px);
                    }

                    .user-dropdown.active {
                        opacity: 1;
                        visibility: visible;
                        transform: translateY(0);
                    }

                    .user-info-dropdown {
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                        padding: 1.5rem;
                        border-bottom: 1px solid #333;
                    }

                    .user-avatar-large {
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 1.5rem;
                        color: white;
                        box-shadow: 0 4px 12px rgba(255, 0, 0, 0.3);
                    }

                    .user-details h4 {
                        color: #ffffff;
                        margin: 0 0 0.25rem 0;
                        font-size: 1.1rem;
                        font-weight: 600;
                    }

                    .user-details p {
                        color: #cccccc;
                        margin: 0 0 0.5rem 0;
                        font-size: 0.85rem;
                    }

                    .user-role {
                        background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
                        color: white;
                        padding: 0.25rem 0.75rem;
                        border-radius: 20px;
                        font-size: 0.75rem;
                        font-weight: 600;
                        display: inline-block;
                    }

                    .menu-items {
                        padding: 0.5rem 0;
                    }

                    .menu-item {
                        display: flex;
                        align-items: center;
                        gap: 0.75rem;
                        padding: 0.875rem 1.5rem;
                        color: #e0e0e0;
                        text-decoration: none;
                        transition: all 0.2s ease;
                        border-radius: 8px;
                        margin: 0 0.5rem;
                    }

                    .menu-item:hover {
                        background: rgba(255, 0, 0, 0.1);
                        color: #ffffff;
                        transform: translateX(4px);
                    }

                    .menu-item.logout {
                        color: #ff6b6b;
                    }

                    .menu-item.logout:hover {
                        background: rgba(255, 0, 0, 0.15);
                        color: #ff4444;
                    }

                    .menu-item i {
                        width: 20px;
                        text-align: center;
                        color: #ff0000;
                    }

                    .menu-divider {
                        height: 1px;
                        background: linear-gradient(90deg, transparent 0%, #333 50%, transparent 100%);
                        margin: 0.5rem 1.5rem;
                    }

                    @media (max-width: 768px) {
                        .user-dropdown {
                            right: -10px;
                            min-width: 260px;
                        }

                        .menu-button {
                            padding: 0.625rem 1.25rem;
                        }

                        .user-name {
                            max-width: 80px;
                        }
                    }
                </style>

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
            </nav>
            <script>
                (function() {
                    function ensureResponsiveState() {
                        var isMobile = window.matchMedia('(max-width: 768px)').matches;
                        var ql = document.getElementById('quickLinks');
                        var nav = document.getElementById('navMenu');
                        var logo = document.querySelector('.header .logo');
                        if (isMobile) {
                            if (ql) ql.style.display = 'none';
                            if (nav) nav.style.display = 'none';
                            if (logo) logo.style.display = 'none';
                        } else {
                            document.body.classList.remove('drawer-open');
                            var ov = document.getElementById('drawerOverlay');
                            var md = document.getElementById('mobileDrawer');
                            if (ov) ov.style.display = 'none';
                            if (md) md.style.display = '';
                            if (ql) ql.style.display = '';
                            if (nav) nav.style.display = '';
                            if (logo) logo.style.display = '';
                        }
                    }
                    window.addEventListener('resize', ensureResponsiveState);
                    document.addEventListener('DOMContentLoaded', ensureResponsiveState);
                })();
            </script>

            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                <i class="fas fa-bars "></i>
            </button>
        </div>
    </header>
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
                            fetch('./backend/php/get_user_by_email.php?email=' + encodeURIComponent(displayEmail))
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
    <section class="hero1section">
        <!-- FONDO PLEXUS CON PUNTOS Y LÍNEAS (limitado al hero) -->
        <div id="anime-bg">
            <canvas id="plexus-canvas"></canvas>
            <div class="hero-overlay"></div>
        </div>
        <section id="hero1section"
            class=" relative max-w-7xl mx-auto px-20 py-[150px] flex flex-col md:flex-row items-center md:items-start gap-12 ">

            <!--Contenido el mensaje que esta al lado del carnicero-->
            <div class="md:w-1/2 flex flex-col justify-center space-y-4 animate-fade-in-left ">
                <br><br><br>
                <!--Modal para colocar distintas img de fondos con js-->
                <h1 class="text-6xl md:text-7xl font-extrabold leading-tight animate-slide-in-down ">
                    CARNE FRESCA, SEGURA Y DE CALIDAD
                </h1>
                <p class="text-gray-300 text-lg md:text-xl max-w-md animate-fade-in-up ">
                    <span id="typedSub"></span><span class="typed-caret typed-caret--sm" aria-hidden="true"></span>
                </p>
                <button onclick="window.location.href='productos.php'" style="background-color: red;"
                    class="bg-red-700 hover:bg-red-800 transition flex items-center space-x-2 text-white font-semibold px-4 py-2 rounded w-max ">
                    <i class="fas fa-shopping-cart "></i><span>Comprar online</span>
                </button>
            </div>



            <!--imagen de carnicero navarro-->
            <div class="md:w-1/2 relative flex items-center justify-center animate-fade-in-right ">
                <img alt="Man in white uniform holding meat cleaver in butcher shop with blurred background lights "
                    class="w-full max-h-[600px] object-contain rounded-md ml-2 mr-4 mt--2 mb-4 -translate-y-4 "
                    src="./static/images/carnicero_navarro.png " />

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
            background: radial-gradient(ellipse at bottom, #1a0f0f 0%, #0a0505 100%);
        }

        #plexus-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 30% 50%, rgba(204, 0, 0, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 70% 70%, rgba(255, 51, 51, 0.1) 0%, transparent 50%);
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
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initPlexusNetwork();
        });

        function initPlexusNetwork() {
            const canvas = document.getElementById('plexus-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const container = document.querySelector('.hero1section');

            function resizeCanvas() {
                const rect = container ? container.getBoundingClientRect() : {
                    width: window.innerWidth,
                    height: window.innerHeight
                };
                canvas.width = rect.width;
                canvas.height = rect.height;
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);
            const config = {
                dotCount: 100,
                dotSize: 2,
                connectionDistance: 150,
                mouseDistance: 250,
                baseSpeed: 0.4,
            };
            const dots = [];
            for (let i = 0; i < config.dotCount; i++) {
                dots.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    vx: (Math.random() - 0.5) * config.baseSpeed,
                    vy: (Math.random() - 0.5) * config.baseSpeed,
                    size: config.dotSize + Math.random() * 1.5,
                    originalSize: config.dotSize + Math.random() * 1.5
                });
            }
            let mouseX = -1000,
                mouseY = -1000;
            document.addEventListener('mousemove', (e) => {
                const rect = canvas.getBoundingClientRect();
                mouseX = e.clientX - rect.left;
                mouseY = e.clientY - rect.top;
            });
            document.addEventListener('mouseleave', () => {
                mouseX = -1000;
                mouseY = -1000;
            });

            function getDistance(x1, y1, x2, y2) {
                const dx = x2 - x1;
                const dy = y2 - y1;
                return Math.sqrt(dx * dx + dy * dy);
            }

            function drawLine(x1, y1, x2, y2, opacity, width = 0.5) {
                const gradient = ctx.createLinearGradient(x1, y1, x2, y2);
                gradient.addColorStop(0, `rgba(220, 20, 60, ${opacity * 0.3})`);
                gradient.addColorStop(0.5, `rgba(255, 51, 51, ${opacity * 0.6})`);
                gradient.addColorStop(1, `rgba(220, 20, 60, ${opacity * 0.3})`);
                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
                ctx.strokeStyle = gradient;
                ctx.lineWidth = width;
                ctx.stroke();
            }

            function drawDot(dot) {
                const gradient = ctx.createRadialGradient(dot.x, dot.y, 0, dot.x, dot.y, dot.size * 4);
                gradient.addColorStop(0, 'rgba(255, 51, 51, 0.8)');
                gradient.addColorStop(0.5, 'rgba(220, 20, 60, 0.4)');
                gradient.addColorStop(1, 'transparent');
                ctx.beginPath();
                ctx.arc(dot.x, dot.y, dot.size * 4, 0, Math.PI * 2);
                ctx.fillStyle = gradient;
                ctx.fill();
                ctx.beginPath();
                ctx.arc(dot.x, dot.y, dot.size, 0, Math.PI * 2);
                ctx.fillStyle = '#ff3333';
                ctx.fill();
                ctx.beginPath();
                ctx.arc(dot.x - dot.size * 0.3, dot.y - dot.size * 0.3, dot.size * 0.4, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                ctx.fill();
            }
            let animationId;

            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                dots.forEach((dot, index) => {
                    dot.x += dot.vx;
                    dot.y += dot.vy;
                    if (dot.x < 0 || dot.x > canvas.width) dot.vx *= -1;
                    if (dot.y < 0 || dot.y > canvas.height) dot.vy *= -1;
                    const mouseDist = getDistance(dot.x, dot.y, mouseX, mouseY);
                    if (mouseDist < config.mouseDistance) {
                        const force = (config.mouseDistance - mouseDist) / config.mouseDistance;
                        const angle = Math.atan2(mouseY - dot.y, mouseX - dot.x);
                        dot.x += Math.cos(angle) * force * 1.5;
                        dot.y += Math.sin(angle) * force * 1.5;
                        dot.size = dot.originalSize * (1 + force * 1.5);
                    } else {
                        dot.size += (dot.originalSize - dot.size) * 0.05;
                    }
                    for (let j = index + 1; j < dots.length; j++) {
                        const otherDot = dots[j];
                        const distance = getDistance(dot.x, dot.y, otherDot.x, otherDot.y);
                        if (distance < config.connectionDistance) {
                            const opacity = 1 - (distance / config.connectionDistance);
                            const lineWidth = 0.3 + opacity * 0.8;
                            drawLine(dot.x, dot.y, otherDot.x, otherDot.y, opacity, lineWidth);
                        }
                    }
                    if (mouseDist < config.connectionDistance * 1.5) {
                        const opacity = 1 - (mouseDist / (config.connectionDistance * 1.5));
                        drawLine(dot.x, dot.y, mouseX, mouseY, opacity * 0.9, 1.2);
                    }
                    drawDot(dot);
                });
                animationId = requestAnimationFrame(animate);
            }
            animate();
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    cancelAnimationFrame(animationId);
                } else {
                    animate();
                }
            });
            document.addEventListener('click', (e) => {
                const rect = canvas.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const clickY = e.clientY - rect.top;
                for (let i = 0; i < 15; i++) {
                    const angle = (i / 15) * Math.PI * 2;
                    const particle = {
                        x: clickX,
                        y: clickY,
                        vx: Math.cos(angle) * 4,
                        vy: Math.sin(angle) * 4,
                        size: 3,
                        life: 1,
                        originalSize: 3
                    };
                    dots.push(particle);
                    setTimeout(() => {
                        const idx = dots.indexOf(particle);
                        if (idx > -1) dots.splice(idx, 1);
                    }, 800);
                }
            });
        }
    </script>

    <section class="brand-marquee" aria-label="Marcas y servicios">
        <style>
            .brand-marquee {
                background: #0b0b0b;
                border-top: 1px solid rgba(255, 255, 255, .06);
                border-bottom: 1px solid rgba(255, 255, 255, .06);
                overflow: hidden
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
                pointer-events: none
            }

            .brand-marquee__fade.left {
                left: 0;
                background: linear-gradient(90deg, #0b0b0b 0%, rgba(11, 11, 11, 0) 100%)
            }

            .brand-marquee__fade.right {
                right: 0;
                background: linear-gradient(270deg, #0b0b0b 0%, rgba(11, 11, 11, 0) 100%)
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
                color: #d7d7d7;
                text-decoration: none;
                white-space: nowrap;
                font-weight: 700;
                opacity: .9;
                transition: transform .2s ease, opacity .2s ease
            }

            .brand-item i {
                font-size: 1.4rem
            }

            .brand-item:hover {
                transform: scale(1.05);
                opacity: 1
            }

            .brand-item .is-red {
                color: #ff1f1f
            }

            @keyframes brandScroll {
                from {
                    transform: translateX(0)
                }

                to {
                    transform: translateX(-50%)
                }
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
                        onclick="window.open('https://wa.me/573108392866?text=Hola%2C%20quiero%20hacer%20un%20pedido%20de%20la%20carnicer%C3%ADa', '_blank')"
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
            <div style="flex:1 1 480px; min-width:300px;">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3976.6586502248737!2d-75.55148638476352!3d10.39697399240679!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8ef624e4b578f21f%3A0xa6156aaebc72a220!2sDistriCarnes!5e0!3m2!1es-419!2sco!4v1688330717498!5m2!1es-419!2sco"
                    width="100%" height="350" style="border:0; border-radius:8px;" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    aria-label="Ubicación de DistriCarnes en Cartagena, Colombia"
                    title="Mapa de localización de DistriCarnes"></iframe>
            </div>
            <div
                style="flex:1 1 320px; min-width:280px; background:#0b0b0b; border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:16px; color:#fff;">
                <h3 style="margin:0 0 8px; color:#ff0000; font-weight:800;">Ubícanos</h3>
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
                        style="background:#ff0000; color:#fff; padding:10px 14px; border-radius:999px; font-weight:700; text-decoration:none;">Ver
                        en Google Maps</a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=10.39697399240679,-75.55148638476352"
                        target="_blank" rel="noopener noreferrer"
                        style="background:#1f2937; color:#fff; padding:10px 14px; border-radius:999px; font-weight:700; text-decoration:none;">Cómo
                        llegar</a>
                    <a href="https://wa.me/573015210177?text=Hola%20DistriCarnes,%20quiero%20m%C3%A1s%20informaci%C3%B3n"
                        target="_blank" rel="noopener noreferrer"
                        style="background:#22c55e; color:#fff; padding:10px 14px; border-radius:999px; font-weight:700; text-decoration:none;">WhatsApp</a>
                    <button type="button"
                        onclick="(function(){var t='Olaya Herrera #34-71A-60, Cartagena de Indias, Colombia';navigator.clipboard&&navigator.clipboard.writeText(t).then(function(){try{Swal&&Swal.fire({icon:'success',title:'Dirección copiada',timer:1500,showConfirmButton:false});}catch(e){}});})()"
                        style="background:#374151; color:#fff; padding:10px 14px; border-radius:999px; font-weight:700; border:none; cursor:pointer;">Copiar
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
                }

                .contact-info {
                    color: #fff;
                    padding-right: 24px;
                    min-width: 0;
                }

                @media (max-width: 992px) {
                    .contact-section {
                        grid-template-columns: 1fr;
                    }

                    .contact-form {
                        margin-left: 0;
                        margin-right: 0;
                        max-width: 720px;
                        width: 100%;
                    }
                }

                @media (max-width: 768px) {
                    .contact-info {
                        padding-right: 0;
                        margin-bottom: 1rem;
                    }

                    .contact-form {
                        margin: 0 auto;
                        max-width: 100%;
                        width: 100%;
                    }
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
            <form id="contactForm" method="post" action="https://formspree.io/f/mnjbandn">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" placeholder="Ingresa un correo válido" required>
                </div>

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" placeholder="Ingresa tu nombre" required>
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <input type="text" id="address" placeholder="Ingresa tu dirección" required>
                </div>

                <div class="form-group">
                    <label for="message">Mensaje</label>
                    <textarea id="message" placeholder="Escribe tu mensaje aquí..." required></textarea>
                </div>

                <button style="background-color: red;padding: 10px 20px;border-radius: 5px "
                    type="submit">ENVIAR</button>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <script src="./static/js/header_actions.js"></script>
    <script src="./static/js/index.js"></script>
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
    <script>
        function ensureResponsiveState() {
            const userData = JSON.parse(localStorage.getItem('userData'));
            const isMobile = window.innerWidth <= 768;

            const authButtons = document.getElementById('authButtons');
            const userLoggedButtons = document.getElementById('userLoggedButtons');
            const drawerAuth = document.querySelector('.drawer-quicklinks');
            const drawerUser = document.getElementById('drawerUserLogged');

            if (userData) {
                // Usuario logueado
                if (authButtons) authButtons.style.display = 'none';
                if (userLoggedButtons) userLoggedButtons.style.display = 'flex';
                if (drawerAuth) drawerAuth.style.display = 'none';
                if (drawerUser) drawerUser.style.display = 'flex';
            } else {
                // Usuario no logueado
                if (authButtons) authButtons.style.display = 'flex';
                if (userLoggedButtons) userLoggedButtons.style.display = 'none';
                if (drawerAuth) drawerAuth.style.display = 'flex';
                if (drawerUser) drawerUser.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', ensureResponsiveState);
        window.addEventListener('resize', ensureResponsiveState);
    </script>
</body>

</html>