<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout – Dirección y Pago | DISTRICARNES</title>
    <link rel="stylesheet" href="../static/css/nav_pills.css" />
    <link rel="icon" href="../assets/icon/image-removebg-preview sin fondo (1).ico" />
    <link rel="stylesheet" href="../static/css/header_en_general.css" />
    <link rel="stylesheet" href="../static/css/base.css" />
    <link rel="stylesheet" href="../static/css/cart.css" />
    <link rel="stylesheet" href="../static/css/Estilo_direccion_compra.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../static/css/responsive.css" />
    <script src="../static/js/auth_utils.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Carga dinámica del SDK de PayPal (se inyecta al elegir PayPal) -->
    <script src="../static/js/paypal_loader.js"></script>
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

            @media (max-width:992px) {
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

            @media (min-width:993px) {

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
                <a href="../index.php" aria-label="Inicio">
                    <img src="../assets/icon/LOGO-DISTRICARNES.png" alt="DistriCarnes">
                </a>
            </div>
            <div class="mh-right">
                <a href="../carrito-de-compras/index.php" class="mh-icon mh-cart" aria-label="Carrito">
                    <i class="bi bi-cart"></i>
                    <span class="mh-badge" id="mhCartCount">0</span>
                </a>
                <a href="../login/login.php" class="mh-icon" id="mhUserLink" aria-label="Perfil o Iniciar sesión">
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
                </style>
                <!-- Navegación 
                <nav class="drawer-nav" style="display:flex;flex-direction:column;align-items:stretch;padding:10px 12px;gap:8px">
                    <a href="../index.php">Inicio</a>
                    <a href="../productos.php">Productos</a>
                    <a href="../promociones.php">Ofertas</a>
                    <a href="../contacto.php" class="active">Contacto</a>
                    <a href="../sobre_nosotros.php">Quienes Somos</a>
                </nav>-->
                <div id="drawerAuthButtons" class="drawer-quicklinks" style="padding:8px 12px;gap:10px;display:flex;flex-direction:column;align-items:stretch">
                    <a href="./login/login.php" style="background:#ff0000;color:#fff;border:1px solid #ff0000;border-radius:999px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="bi bi-box-arrow-in-right"></i> INICIAR SESIÓN</a>
                    <a href="./login/register.php" style="background:#ff0000;color:#fff;border:1px solid #ff0000;border-radius:999px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="bi bi-person-plus-fill"></i> REGISTRARSE</a>
                </div>
                <div id="drawerUserLogged" style="display:none;padding:8px 12px;gap:10px;flex-direction:column;align-items:stretch">
                    <a href="../perfil.php" style="background:#111;color:#fff;border:1px solid #222;border-radius:10px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="fas fa-user"></i> Mi Perfil</a>
                    <a href="#" onclick="logout()" style="background:#111;color:#ff6b6b;border:1px solid #222;border-radius:10px;padding:10px 14px;text-decoration:none;font-weight:700;display:block;width:100%;text-align:center"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </aside>
        </div>
        <div class="header-content ">
            <div class="logo ">
                <a href="../index.php">
                    <img src="../assets/icon/LOGO-DISTRICARNES.png" alt="DISTRICARNES Logo" style="cursor: pointer;">
                </a>
            </div>

            <!-- Buscador central estilo ML y pill promocional -->
            <div class="ml-search">
                <form action="./productos.php" method="get">
                    <input type="search" name="q" id="site-search" placeholder="Buscar productos, marcas y más…" />
                    <button type="submit" aria-label="Buscar"><i class="fas fa-search"></i></button>
                </form>
            </div>


            <!-- Enlaces rápidos + botón de carrito (siempre visibles) -->
            <div id="quickLinks" class="ml-actions">
                <a id="cartButton" class="ml-icon-btn ml-icon-bounce" href="../carrito-de-compras/index.php"
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

                <!-- Botones de navegación 
                <a href="../index.php" class="active">Inicio</a>
                <a href="../productos.php">Productos</a>
                <a href="../promociones.php">Ofertas</a>
                <a href="../contacto.php">Contacto</a>
                <a href="../sobre_nosotros.php">Quienes Somos</a>
                -->



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
                        text-transform: none !important;
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
                        text-transform: none !important;
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

                        function toTitleCase(str) {
                            if (!str) return '';
                            return str.toLowerCase().split(' ').map(function(word) {
                                return (word.charAt(0).toUpperCase() + word.slice(1));
                            }).join(' ');
                        }

                        const nameRaw = userData.name || userData.nombres_completos || userData.nombre || 'Usuario';
                        const name = toTitleCase(nameRaw);
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
                        var isMobile = window.matchMedia('(max-width: 992px)').matches;
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


    <main class="container">
        <div class="checkout-progress" id="checkoutProgress">
            <div class="line"><span class="line-fill"></span></div>
            <ul class="progress-steps">
                <li class="progress-step" data-id="stepDelivery"><span class="circle">1</span><span class="label">Entrega</span></li>
                <li class="progress-step" data-id="stepAddress"><span class="circle">2</span><span class="label">Dirección</span></li>
                <li class="progress-step" data-id="stepMap"><span class="circle">3</span><span class="label">Mapa</span></li>
                <li class="progress-step" data-id="stepSchedule"><span class="circle">4</span><span class="label">Horario</span></li>
                <li class="progress-step" data-id="stepPayment"><span class="circle">5</span><span class="label">Pago</span></li>
            </ul>
        </div>
        <!-- Paso 1: Forma de entrega -->
        <section class="card step active" id="stepDelivery">
            <header style="color: #000000;">Elige la forma de entrega</header>
            <div class="content">
                <div class="option">
                    <label class="radio">
                        <input type="radio" name="delivery" value="domicilio" checked />
                        <div>
                            <div class="title" style="color: #000000;">Enviar a domicilio</div>
                            <div class="desc" id="deliveryAddressPreview">Selecciona una direccion de entrega
                            </div>
                            <div class="desc">Residencial</div>
                            <a href="#" class="muted-link" id="btnEditAddress"
                                style="color: #000; transition: color 0.2s ease;"
                                onmouseover="this.style.color='#ff0000'" onmouseout="this.style.color='#000'">Modificar
                                domicilio o elegir otro</a>
                        </div>
                    </label>
                    <div class="price-tag">Gratis</div>
                </div>
                <!-- segunda opcion para elegir el punto de entrega del pedido 
                 
                    <div class="option">
                        <label class="radio">
                            <input type="radio" name="delivery" value="punto" />
                            <div>
                                <div class="title" style="color: #000000;">Retiro en un punto de entrega</div>
                                <div class="desc">Agencia Mercado Libre – EFACTY PASEO DE BOLÍVAR – CRA 17 51-43 – Paseo De
                                    Bolívar</div>
                                <div class="desc">Lu a Sá: 8:30 a 12:30 hs. Lu a Vi: 14:30 a 19:30 hs. Sá: 14:30 a 17 hs.
                                    Do: 10 a 13 hs.
                                </div>
                                <a href="#" class="muted-link" style="color: #000; transition: color 0.2s ease;"
                                    onmouseover="this.style.color='#ff0000'" onmouseout="this.style.color='#000'">Ver punto
                                    en el mapa o elegir otro</a>
                            </div>
                        </label>
                        <div class="price-tag">Gratis</div>
                    </div>
                -->
            </div>
            <div class="actions">
                <button class="btn btn-primary" id="toStep2" onclick="window.__goStep && window.__goStep('stepAddress')"
                    style="background-color: #ff0000; color: #ffffff; transition: background-color 0.3s ease, transform 0.2s ease;"
                    onmouseover="this.style.backgroundColor='#cc0000'; this.style.transform='scale(1.05)'"
                    onmouseout="this.style.backgroundColor='#ff0000'; this.style.transform='scale(1)'">Continuar</button>
            </div>
        </section>

        <!-- Paso 2: Elegir dirección -->
        <section class="card step" id="stepAddress">
            <header style="color: #000000;">Elige dónde recibir tu compra</header>
            <div class="content">
                <div style="color: #000000;" class="address-list" id="addressList">
                    <!-- Direcciones cargadas dinámicamente -->
                </div>
                <div class="actions" style="justify-content:flex-start;">
                    <button class="btn" id="backToStep1" style="background:#333; color:#fff;">Atrás</button>
                    <button class="btn" id="btnAddAddress">Agregar nuevo domicilio</button>
                    <button class="btn btn-primary" id="toStep3"
                        style="background-color: #ff0000; color: #ffffff; transition: background-color 0.3s ease, transform 0.2s ease;"
                        onmouseover="this.style.backgroundColor='#cc0000'; this.style.transform='scale(1.05)'"
                        onmouseout="this.style.backgroundColor='#ff0000'; this.style.transform='scale(1)'">Continuar</button>
                </div>

                <!-- Formulario nuevo domicilio -->
                <style>
                    #newAddressForm .content input {
                        width: 100%;
                        padding: 12px 15px;
                        margin-bottom: 15px;
                        border: 1px solid #444;
                        border-radius: 8px;
                        background: #1a1a1a;
                        color: #fff;
                        font-family: 'Montserrat', sans-serif;
                        box-sizing: border-box;
                        transition: border-color 0.3s;
                    }

                    #newAddressForm .content input:focus {
                        border-color: #ff0000;
                        outline: none;
                        box-shadow: 0 0 5px rgba(255, 0, 0, 0.5);
                    }

                    #newAddressForm .inline {
                        display: flex;
                        gap: 15px;
                    }

                    @media (max-width: 600px) {
                        #newAddressForm .inline {
                            flex-direction: column;
                            gap: 0;
                        }
                    }
                </style>
                <div id="newAddressForm" class="card hidden"
                    style="margin-top:15px; border: 1px solid #333; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                    <header
                        style="background: #222; padding: 15px 20px; font-weight: bold; border-bottom: 1px solid #444; color: #fff;">
                        Agregar Nuevo Domicilio</header>
                    <div class="content" style="padding: 20px; background: #0a0a0a;">
                        <div class="inline">
                            <input type="text" id="addrStreet" placeholder="Calle y número (Ej: Cra 45 # 12-34)" />
                            <input type="text" id="addrCity" placeholder="Ciudad (Ej: Cartagena)" />
                        </div>
                        <div class="inline">
                            <input type="text" id="addrDept" placeholder="Departamento (Ej: Bolívar)" />
                            <input type="text" id="addrZip" placeholder="Código postal (Opcional)" />
                        </div>
                        <input type="text" id="addrNotes"
                            placeholder="Indicaciones adicionales (Ej: Casa de dos pisos, frente a parque...)"
                            style="margin-bottom:0;" />
                    </div>
                    <div class="actions"
                        style="background: #111; padding: 15px 20px; border-top: 1px solid #444; justify-content: flex-end;">
                        <button class="btn" id="cancelNewAddress"
                            style="background: #333; color: white;">Cancelar</button>
                        <button class="btn btn-primary" id="saveNewAddress"
                            style="background-color: #ff0000; border: none; font-weight: bold; transition: background 0.3s;">Guardar
                            y continuar</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Paso 3: Revisar dirección con mapa -->
        <section class="card step" id="stepMap">
            <header style="color: #000000;">Revisa la dirección</header>
            <div class="content">
                <div class="map-box">
                    <iframe id="mapFrame" width="100%" height="100%" style="border:0;" loading="lazy" allowfullscreen
                        src="https://maps.google.com/maps?q=Cartagena%20De%20Indias&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
                </div>
                <div class="desc" style="color: #000000;">Asegúrate de que el pin esté en el lugar correcto</div>
                <div style="margin-top:8px;">
                    <button class="btn" id="useMyLocation">Usar mi ubicación</button>
                </div>
            </div>
            <div class="actions">
                <button class="btn" id="backToStep2" style="background:#333; color:#fff;">Atrás</button>
                <button class="btn" id="skipMap">Omitir</button>
                <button class="btn btn-primary" id="toStep4"
                    style="background-color: #ff0000; color: #ffffff; transition: background-color 0.3s ease, transform 0.2s ease;"
                    onmouseover="this.style.backgroundColor='#cc0000'; this.style.transform='scale(1.05)'"
                    onmouseout="this.style.backgroundColor='#ff0000'; this.style.transform='scale(1)'">Guardar
                    dirección</button>
            </div>
        </section>

        <!-- Paso 4: Calendario de entrega -->
        <section class="card step" id="stepSchedule">
            <header style="color: #000000;">Revisa cuándo llega tu compra</header>
            <div style="color: #000000;" class="content" id="deliveryScheduleContent">
                <div style="color: #000000;" class="schedule-item">
                    <label class="radio">
                        <input type="radio" name="schedule" value="standard" checked />
                        <div>
                            <strong>Envío Único</strong> <span
                                style="color:#00c853; font-weight:700; margin-left:6px;">EXPRESS</span>
                            <div class="desc" id="deliveryDateLabel">Calculando fecha...</div>
                        </div>
                    </label>
                    <div class="price-tag">Gratis</div>
                </div>
            </div>
            <div class="actions">
                <button class="btn" id="backToStep3" style="background:#333; color:#fff;">Atrás</button>
                <button class="btn btn-primary" id="toStep5"
                    style="background-color: #ff0000; color: #ffffff; transition: background-color 0.3s ease, transform 0.2s ease;"
                    onmouseover="this.style.backgroundColor='#cc0000'; this.style.transform='scale(1.05)'"
                    onmouseout="this.style.backgroundColor='#ff0000'; this.style.transform='scale(1)'">Continuar</button>
            </div>
        </section>

        <!-- Paso 5: Pago -->
        <section class="card step" id="stepPayment">
            <header style="color: #000000;">Elige cómo pagar</header>
            <div style="color: #000000;" class="content">
                <div style="font-weight:700; margin:8px 0 6px 0;">Pago instantáneo</div>
                
                <div class="payment-method">
                    <input type="radio" name="pay" value="paypal" id="payPaypal" />
                    <i class="fab fa-paypal" style="font-size:22px; margin-right:8px; color:#003087;"></i>
                    <div>
                        <div class="title">PayPal</div>
                        <small class="muted" style="display:block;color:#666">Pago seguro y rápido</small>
                    </div>
                </div>
                <!--<div class="payment-method">
                    <input type="radio" name="pay" value="gpay" id="payGPay" />
                    <i class="fab fa-google" style="font-size:22px; margin-right:8px; color:#4285F4;"></i>
                    <div>
                        <div class="title">Google Pay</div>
                        <small class="muted" style="display:block;color:#666">Paga con G&nbsp;Pay</small>
                    </div>
                </div>-->

                <div id="paypalButtons" class="hidden" style="margin-top:12px;"></div>

                <div style="font-weight:700; margin:14px 0 6px 0;">1 día hábil</div>
                <div class="payment-method" id="nequiMethod">
                    <input type="radio" name="pay" value="nequi" id="payNequi" />
                    <i class="fas fa-wallet" style="font-size:22px; margin-right:8px; color:#6C2BD9;"></i>
                    <div style="flex:1">
                        <div class="title" style="display:flex; align-items:center; gap:6px;">
                            Nequi
                        </div>
                        <div id="nequiPanel" class="hidden" style="margin-top:8px;">
                            <label for="nequiPhone"
                                style="display:block; font-size:.9rem; color:#444; margin-bottom:6px;">Teléfono asociado
                                a Nequi</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input id="nequiPhone" type="tel" inputmode="numeric" placeholder="3XXXXXXXXX"
                                    style="flex:1; border:1px solid #eaeaea; border-radius:10px; padding:10px 12px;">
                                <button id="nequiSend" type="button" class="btn btn-primary"
                                    style="background:#6C2BD9; border-color:#6C2BD9;">Enviar pago</button>
                            </div>
                            <small style="display:block; color:#666; margin-top:6px;">Este método puede tardar hasta 24h
                                en confirmarse.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="actions">
                <button class="btn" id="backToStep4" style="background:#333; color:#fff;">Atrás</button>
                <button class="btn btn-primary" id="finishCheckout"
                    style="background-color: #ff0000; color: #ffffff; transition: background-color 0.3s ease, transform 0.2s ease;"
                    onmouseover="this.style.backgroundColor='#cc0000'; this.style.transform='scale(1.05)'"
                    onmouseout="this.style.backgroundColor='#ff0000'; this.style.transform='scale(1)'">Continuar</button>
            </div>
        </section>

        <!-- Resumen -->
        <aside class="summary">
            <h3 style="color: #000000ef;">Resumen de compra</h3>
            <div class="summary-row">
                <span style="color:#000;">Productos (<span id="itemsCount" style="color:#000;">0</span>)</span>
                <span style="color:#000;" id="summarySubtotal">$0</span>
            </div>
            <div class="summary-row">
                <span style="color:#000;">IVA (incluido)</span>
                <span style="color:#000;" id="summaryTax">$0</span>
            </div>
            <div class="summary-row">
                <span style="color:#000;">Envío</span>
                <span style="color:#000;" id="summaryShipping">Gratis</span>
            </div>
            <div id="freeShippingMsgSummary" style="font-size:0.75rem; color:#00c853; text-align:right; margin-top:-10px; margin-bottom:10px; display:none;">¡Envío gratis aplicado!</div>
            <div class="summary-row">
                <span style="color:#000;">Total</span>
                <span style="color:#000;" id="summaryTotal">$0</span>
            </div>
        </aside>
    </main>

    <script>
        // Fallback global para navegación de pasos (por si un error impide registrar listeners)
        window.__goStep = function(id) {
            try {
                document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
                const el = document.getElementById(id);
                if (el) {
                    el.classList.add('active');
                    el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                try {
                    updateProgressByStepId(id);
                } catch (e) {}
            } catch (e) {}
        };
        // Utilidad de moneda
        function formatCurrency(n) {
            try {
                return Number(n || 0).toLocaleString('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    maximumFractionDigits: 0
                });
            } catch (e) {
                return '$' + Number(n || 0).toLocaleString('es-CO');
            }
        }

        function safeParsePrice(str) {
            if (typeof str === 'number') return Number(str) || 0;
            if (typeof str !== 'string') return 0;
            const num = Number(str.replace(/[^0-9.,-]/g, '').replace(',', '.'));
            return isNaN(num) ? 0 : num;
        }

        // Leer carrito (persistencia por usuario)
        function getUserKey() {
            // 1) Intentar sistema global si existe
            try {
                if (window.AuthSystem) {
                    const u = (typeof window.AuthSystem.getCurrentUser === 'function') ? window.AuthSystem.getCurrentUser() : null;
                    if (u) {
                        const email = (u.correo_electronico || u.email) || '';
                        const id = (u.id_usuario || u.id) || '';
                        const key = email || String(id || '').trim();
                        if (key) return key;
                    }
                }
            } catch (e) {}
            // 2) Fallback a userData / currentSession
            try {
                const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                if (rawStr) {
                    const raw = JSON.parse(rawStr);
                    const user = raw && raw.user ? raw.user : raw;
                    const email = (user && (user.correo_electronico || user.email)) || '';
                    const id = (user && (user.id_usuario || user.id)) || '';
                    const key = email || String(id || '').trim();
                    if (key) return key;
                }
            } catch (e) {}
            // 3) Último recurso: detectar primer carrito existente en localStorage
            try {
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (k && k.startsWith('cart_items:')) {
                        return k.substring('cart_items:'.length);
                    }
                }
            } catch (e) {}
            return null;
        }

        function getCartKey() {
            const userKey = getUserKey();
            return userKey ? ('cart_items:' + userKey) : 'cart_items:guest';
        }

        function getCart() {
            let key = getCartKey();
            if (!key) {
                try {
                    for (let i = 0; i < localStorage.length; i++) {
                        const k = localStorage.key(i);
                        if (k && k.startsWith('cart_items:')) {
                            key = k;
                            break;
                        }
                    }
                } catch (e) {}
            }
            if (!key) return [];
            try {
                const raw = localStorage.getItem(key);
                return raw ? (JSON.parse(raw) || []) : [];
            } catch (e) {
                return [];
            }
        }

        // Estado de checkout (en memoria)
        const CheckoutState = {
            delivery: 'domicilio',
            address: {
                street: 'Avenida Calle cartagena SN 23',
                city: 'Cartagena De Indias',
                dept: 'Bolívar',
                zip: '',
                notes: ''
            },
            schedule: {
                fecha: 'calculando...'
            },
            payMethod: null,
            orderTotal: 0
        };

        // Navegación de pasos
        const STEP_ORDER = ['stepDelivery', 'stepAddress', 'stepMap', 'stepSchedule', 'stepPayment'];
        let CURRENT_STEP_IDX = 0;

        function updateProgressByStepId(id) {
            try {
                const idx = Math.max(0, STEP_ORDER.indexOf(id));
                CURRENT_STEP_IDX = idx;
                const steps = document.querySelectorAll('#checkoutProgress .progress-step');
                steps.forEach((li, i) => {
                    li.classList.remove('completed', 'current', 'upcoming');
                    const circle = li.querySelector('.circle');
                    if (i < idx) {
                        li.classList.add('completed');
                        if (circle) circle.textContent = '✓';
                    } else if (i === idx) {
                        li.classList.add('current');
                        if (circle) circle.textContent = String(i + 1);
                    } else {
                        li.classList.add('upcoming');
                        if (circle) circle.textContent = String(i + 1);
                    }
                });
                const fill = document.querySelector('#checkoutProgress .line .line-fill');
                if (fill) {
                    const denom = Math.max(1, (STEP_ORDER.length - 1));
                    const pct = (idx / denom) * 100;
                    fill.style.width = pct + '%';
                }
            } catch (e) {}
        }

        function markProgressComplete() {
            try {
                const steps = document.querySelectorAll('#checkoutProgress .progress-step');
                steps.forEach(li => {
                    li.classList.add('completed');
                    li.classList.remove('current', 'upcoming');
                    const c = li.querySelector('.circle');
                    if (c) c.textContent = '✓';
                });
                const fill = document.querySelector('#checkoutProgress .line .line-fill');
                if (fill) {
                    fill.style.width = '100%';
                }
            } catch (e) {}
        }

        function showStep(id) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            updateProgressByStepId(id);
        }

        // Utilidad: verificar si el usuario inició sesión
        function isLoggedIn() {
            try {
                const userData = localStorage.getItem('userData');
                const sessionData = sessionStorage.getItem('currentSession');
                const raw = userData ? JSON.parse(userData) : (sessionData ? JSON.parse(sessionData) : null);
                if (!raw) return false;
                return !!(raw.isLoggedIn || raw.user || raw.email || raw.correo_electronico);
            } catch (e) {
                return false;
            }
        }

        // Cargar direcciones existentes
        async function loadAddresses() {
            const list = document.getElementById('addressList');
            list.innerHTML = '';
            let addresses = [];
            try {
                const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                const raw = rawStr ? JSON.parse(rawStr) : null;
                const u = raw && raw.user ? raw.user : raw;
                const userEmail = (u && (u.correo_electronico || u.email)) || '';
                if (userEmail) {
                    const res = await fetch('../backend/php/address_list.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_email: userEmail
                        })
                    });
                    const out = await res.json();
                    if (out && out.ok && Array.isArray(out.addresses)) addresses = out.addresses;
                }
            } catch (e) {}
            if (!addresses.length) {
                addresses = [Object.assign({
                    id: null,
                    is_default: true
                }, CheckoutState.address)];
            }
            addresses.forEach((addr, idx) => {
                const id = 'addrOpt' + idx;
                const el = document.createElement('label');
                el.className = 'address';
                el.innerHTML = `
            <input type="radio" name="address" id="${id}" ${addr.is_default || idx === 0 ? 'checked' : ''}/>
            <div>
                <strong>${addr.street}</strong>
                <div>${addr.city}, ${addr.dept}${addr.zip ? (', ' + addr.zip) : ''}</div>
                <div class="desc">${addr.notes || ''}</div>
            </div>`;
                el.addEventListener('change', () => {
                    CheckoutState.address = {
                        street: addr.street,
                        city: addr.city,
                        dept: addr.dept,
                        zip: addr.zip || '',
                        notes: addr.notes || '',
                        lat: addr.lat || null,
                        lng: addr.lng || null
                    };
                    document.getElementById('deliveryAddressPreview').textContent = `${addr.street} - ${addr.city}, ${addr.dept}`;
                    refreshMap();
                });
                list.appendChild(el);
                if ((addr.is_default || idx === 0) && idx === 0) {
                    CheckoutState.address = {
                        street: addr.street,
                        city: addr.city,
                        dept: addr.dept,
                        zip: addr.zip || '',
                        notes: addr.notes || '',
                        lat: addr.lat || null,
                        lng: addr.lng || null
                    };
                }
            });
        }

        // Totales
        async function recalcSummary() {
            const items = getCart();
            const subtotal = items.reduce((sum, i) => {
                const price = (typeof i.price === 'string') ? safeParsePrice(i.price) : Number(i.price || 0);
                const qty = Number(i.qty || i.quantity || 1);
                return sum + (price * qty);
            }, 0);
            // IVA incluido (asumimos precios con IVA): 19%
            const IVA_RATE = 0.19;
            const base = subtotal / (1 + IVA_RATE);
            const tax = Math.max(0, subtotal - base);
            // Cotizar envío en backend según reglas
            let shipping = 0;
            try {
                const res = await fetch('../backend/php/shipping_quote.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        items,
                        delivery: CheckoutState.delivery
                    })
                });
                const q = await res.json();
                if (q && q.ok) shipping = Number(q.cost || 0);
            } catch (e) {}
            const total = subtotal + shipping;
            document.getElementById('itemsCount').textContent = String(items.length);
            document.getElementById('summarySubtotal').textContent = formatCurrency(subtotal);
            const taxEl = document.getElementById('summaryTax');
            if (taxEl) taxEl.textContent = formatCurrency(tax);
            const shipEl = document.getElementById('summaryShipping');
            if (shipEl) shipEl.textContent = shipping > 0 ? formatCurrency(shipping) : 'Gratis';
            const freeMsg = document.getElementById('freeShippingMsgSummary');
            if (freeMsg) {
                if (subtotal >= 10) {
                    freeMsg.style.display = 'block';
                } else {
                    freeMsg.style.display = 'none';
                }
            }
            document.getElementById('summaryTotal').textContent = formatCurrency(total);
            CheckoutState.orderTotal = total;
        }

        // Map URL según dirección
        function refreshMap() {
            const a = CheckoutState.address;
            const q = encodeURIComponent(`${a.street}, ${a.city}, ${a.dept}`);
            document.getElementById('mapFrame').src = `https://maps.google.com/maps?q=${q}&t=&z=14&ie=UTF8&iwloc=&output=embed`;
        }

        // PayPal: usa endpoints backend para crear/capturar y guardar pedido
        async function loadPayPalSdk() {
            if (window.paypal) return true;
            if (window.__paypalLoading) return new Promise((res) => {
                const i = setInterval(() => {
                    if (window.paypal) {
                        clearInterval(i);
                        res(true);
                    }
                }, 120);
                setTimeout(() => {
                    clearInterval(i);
                    res(!!window.paypal);
                }, 6000);
            });
            try {
                window.__paypalLoading = true;
                const confRes = await fetch('../backend/php/get_paypal_client.php');
                const conf = await confRes.json();
                const clientId = conf.client_id;
                const currency = conf.currency || 'USD';
                const components = conf.components || 'buttons';
                const funding = conf.enable_funding || 'card,venmo,paylater';
                const script = document.createElement('script');
                const params = new URLSearchParams({
                    'client-id': clientId,
                    currency,
                    components,
                    'enable-funding': funding,
                    intent: 'capture'
                });
                script.src = `https://www.paypal.com/sdk/js?${params.toString()}`;
                script.async = true;
                script.setAttribute('data-namespace', 'paypal');
                document.head.appendChild(script);
                await new Promise((resolve, reject) => {
                    script.onload = () => resolve(true);
                    script.onerror = () => reject(new Error('SDK PayPal no cargó'));
                });
                return !!window.paypal;
            } catch (e) {
                return false;
            }
        }
        async function saveOrder(capture) {
            try {
                const items = getCart();
                const payload = {
                    paypal_id: (capture && capture.id) || null,
                    status: (capture && capture.status) || 'COMPLETED',
                    total: CheckoutState.orderTotal,
                    delivery: CheckoutState.delivery,
                    address: CheckoutState.address,
                    schedule: CheckoutState.schedule,
                    items,
                    user: (function() {
                        try {
                            const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                            if (!rawStr) return null;
                            const raw = JSON.parse(rawStr);
                            const u = raw && raw.user ? raw.user : raw;
                            return {
                                email: u.correo_electronico || u.email || '',
                                name: u.nombres_completos || u.nombre || u.name || ''
                            };
                        } catch (e) {
                            return null;
                        }
                    })()
                };
                const res = await fetch('../backend/php/orders_save.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                return data;
            } catch (e) {
                return {
                    ok: false,
                    error: String(e)
                };
            }
        }

        function ensurePayPal() {
            const el = document.getElementById('paypalButtons');
            el.classList.remove('hidden');
            if (el.dataset.rendered === '1') return;
            // Cargar SDK si no está presente
            (async function() {
                const ok = await loadPayPalSdk();
                if (!ok || !window.paypal) {
                    const isBrave = (navigator.brave && (await navigator.brave.isBrave())) || /Brave/i.test(navigator.userAgent);
                    const text = isBrave ?
                        'Tu navegador está bloqueando el SDK. En Brave desactiva Shields para localhost o añade una excepción para paypal.com.' :
                        'Sin conexión o Client ID inválido. Verifica tu Internet y credenciales.';
                    Swal.fire({
                        icon: 'error',
                        title: 'PayPal no cargó',
                        text
                    });
                    return;
                }
                paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color: 'blue',
                        shape: 'pill',
                        label: 'paypal'
                    },
                    createOrder: async function() {
                        try {
                            const res = await fetch('../backend/php/create_paypal_order.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    amount: (CheckoutState.orderTotal || 0).toFixed(2)
                                })
                            });
                            const out = await res.json();
                            if (out && out.id) return out.id;
                            throw new Error((out && out.error) || 'No se pudo crear la orden');
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error creando la orden',
                                text: String(err.message || err)
                            });
                            throw err;
                        }
                    },
                    onApprove: async function(data) {
                        try {
                            const res = await fetch('../backend/php/capture_paypal_order.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    orderID: data.orderID
                                })
                            });
                            const out = await res.json();
                            if (out && (out.status === 'COMPLETED' || (out.raw && out.raw.status === 'COMPLETED'))) {
                                const saved = await saveOrder(out.raw || {
                                    id: data.orderID,
                                    status: 'COMPLETED'
                                });
                                if (!saved || saved.ok !== true) {
                                    const msg = (saved && (saved.error || saved.message)) ? (saved.error || saved.message) : 'No se pudo guardar el pedido.';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Pago completado, pedido no registrado',
                                        text: msg
                                    });
                                    return;
                                }
                                const orderId = (saved && saved.order_id) ? saved.order_id : null;
                                // Enviar factura al correo del usuario (si hay email)
                                try {
                                    const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                                    const raw = rawStr ? JSON.parse(rawStr) : null;
                                    const u = raw && raw.user ? raw.user : raw;
                                    const email = (u && (u.correo_electronico || u.email)) || null;
                                    if (orderId && email) {
                                        await fetch('../backend/php/send_invoice_email.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                order_id: orderId,
                                                to: email
                                            })
                                        });
                                    }
                                } catch (e) {}

                                // Mostrar mensaje de éxito y redirigir al inicio
                                if (orderId) {
                                    try {
                                        const key = getCartKey();
                                        if (key) {
                                            localStorage.setItem(key, JSON.stringify([]));
                                            window.dispatchEvent(new CustomEvent('cart:updated', {
                                                detail: {
                                                    items: []
                                                }
                                            }));
                                        }
                                    } catch (e) {}

                                    try {
                                        markProgressComplete();
                                    } catch (e) {}

                                    // Enviar factura por correo
                                    try {
                                        const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                                        const raw = rawStr ? JSON.parse(rawStr) : null;
                                        const u = raw && raw.user ? raw.user : raw;
                                        const email = (u && (u.correo_electronico || u.email)) || null;
                                        if (email) {
                                            await fetch('../backend/php/send_invoice_email.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    order_id: orderId,
                                                    to: email
                                                })
                                            });
                                        }
                                    } catch (e) {
                                        /* opcional */
                                    }

                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Compra Exitosa!',
                                        text: 'Tu pedido ha sido procesado correctamente y tu factura fue enviada al correo.',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#ff0000',
                                        allowOutsideClick: false
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = 'https://districarnes-83qm.onrender.com/index.php';
                                        }
                                    });
                                    return;
                                }
                                try {
                                    if (window.PurchaseHistoryStore && orderId) {
                                        window.PurchaseHistoryStore.record({
                                            id: orderId,
                                            method: 'paypal',
                                            total: CheckoutState.orderTotal,
                                            items: (getCart() || []).map(i => ({
                                                title: i.title || i.name,
                                                price: i.price,
                                                qty: i.qty || i.quantity || 1
                                            }))
                                        });
                                    }
                                } catch (e) {}
                                try {
                                    const key = getCartKey();
                                    if (key) {
                                        localStorage.setItem(key, JSON.stringify([]));
                                        window.dispatchEvent(new CustomEvent('cart:updated', {
                                            detail: {
                                                items: []
                                            }
                                        }));
                                    }
                                } catch (e) {}
                            } else {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Pago no completado',
                                    text: 'Verifica tu método de pago.'
                                });
                            }
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al capturar pago',
                                text: String(err.message || err)
                            });
                        }
                    },
                    onError: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de PayPal',
                            text: String(err)
                        });
                    }
                }).render('#paypalButtons');
                el.dataset.rendered = '1';
            })();
        }

        // Eventos
        document.addEventListener('DOMContentLoaded', function() {
            updateProgressByStepId('stepDelivery');
            recalcSummary();
            loadAddresses();
            refreshMap();
            // Escuchar cambios del carrito en tiempo real
            window.addEventListener('cart:updated', recalcSummary);
            window.addEventListener('storage', function(e) {
                if (e && e.key && e.key.startsWith('cart_items')) {
                    recalcSummary();
                }
            });

            // Método de entrega: guardar selección (domicilio | punto)
            (function() {
                const radios = document.querySelectorAll('input[name="delivery"]');
                const preview = document.getElementById('deliveryAddressPreview');
                const setDelivery = function() {
                    const checked = document.querySelector('input[name="delivery"]:checked');
                    CheckoutState.delivery = checked ? checked.value : 'domicilio';
                    // Opcional: atenuar vista de dirección si es retiro en punto
                    if (preview) {
                        if (CheckoutState.delivery === 'punto') {
                            preview.style.opacity = '0.6';
                        } else {
                            preview.style.opacity = '1';
                        }
                    }
                    recalcSummary();
                };
                radios.forEach(r => r.addEventListener('change', setDelivery));
                setDelivery(); // inicial
            })();

            // Habilitar clic en pasos previos de la barra de progreso
            document.querySelectorAll('#checkoutProgress .progress-step').forEach((li, i) => {
                li.style.cursor = 'pointer';
                li.addEventListener('click', () => {
                    if (i <= CURRENT_STEP_IDX) showStep(STEP_ORDER[i]);
                });
            });

            // Paso 1
            document.getElementById('toStep2').addEventListener('click', () => {
                showStep('stepAddress');
            });
            document.getElementById('btnEditAddress').addEventListener('click', (e) => {
                e.preventDefault();
                showStep('stepAddress');
            });
            // Volver a paso 1
            document.getElementById('backToStep1').addEventListener('click', () => {
                showStep('stepDelivery');
            });

            // Paso 2
            document.getElementById('btnAddAddress').addEventListener('click', () => {
                document.getElementById('newAddressForm').classList.remove('hidden');
            });
            document.getElementById('cancelNewAddress').addEventListener('click', () => {
                document.getElementById('newAddressForm').classList.add('hidden');
            });
            document.getElementById('saveNewAddress').addEventListener('click', async () => {
                const street = document.getElementById('addrStreet').value.trim();
                const city = document.getElementById('addrCity').value.trim();
                const dept = document.getElementById('addrDept').value.trim();
                const zip = document.getElementById('addrZip').value.trim();
                const notes = document.getElementById('addrNotes').value.trim();
                if (!street || !city || !dept) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Completa calle, ciudad y departamento'
                    });
                    return;
                }
                // Guardar en servidor
                try {
                    const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                    const raw = rawStr ? JSON.parse(rawStr) : null;
                    const u = raw && raw.user ? raw.user : raw;
                    const userEmail = (u && (u.correo_electronico || u.email)) || '';
                    const res = await fetch('../backend/php/address_upsert.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_email: userEmail,
                            street,
                            city,
                            dept,
                            zip,
                            notes,
                            is_default: true,
                            lat: CheckoutState.address.lat || null,
                            lng: CheckoutState.address.lng || null
                        })
                    });
                    const out = await res.json();
                    if (!out || !out.ok) {
                        throw new Error((out && out.error) || 'No se pudo guardar el domicilio');
                    }
                } catch (err) {
                    console.warn(err);
                }
                CheckoutState.address = {
                    street,
                    city,
                    dept,
                    zip,
                    notes,
                    lat: CheckoutState.address.lat || null,
                    lng: CheckoutState.address.lng || null
                };
                document.getElementById('deliveryAddressPreview').textContent = `${street} - ${city}, ${dept}`;
                loadAddresses();
                refreshMap();
                document.getElementById('newAddressForm').classList.add('hidden');
                showStep('stepMap');
            });
            document.getElementById('toStep3').addEventListener('click', () => {
                showStep('stepMap');
            });
            // Volver a paso 2
            document.getElementById('backToStep2').addEventListener('click', () => {
                showStep('stepAddress');
            });

            // Paso 3
            document.getElementById('skipMap').addEventListener('click', () => {
                // Calcular fecha de entrega: hoy + 1 día (o el siguiente día hábil)
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const deliveryDate = new Date();
                deliveryDate.setDate(deliveryDate.getDate() + 1); // Mañana
                
                // Si es domingo, pasar al lunes
                if (deliveryDate.getDay() === 0) {
                    deliveryDate.setDate(deliveryDate.getDate() + 1);
                }
                
                const dateStr = deliveryDate.toLocaleDateString('es-ES', options);
                const capitalizedDate = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
                
                CheckoutState.schedule.fecha = capitalizedDate;
                const label = document.getElementById('deliveryDateLabel');
                if (label) label.textContent = capitalizedDate;

                showStep('stepSchedule');
            });
            document.getElementById('toStep4').addEventListener('click', () => {
                // Calcular fecha de entrega: hoy + 1 día (o el siguiente día hábil)
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const deliveryDate = new Date();
                deliveryDate.setDate(deliveryDate.getDate() + 1); // Mañana
                
                // Si es domingo, pasar al lunes
                if (deliveryDate.getDay() === 0) {
                    deliveryDate.setDate(deliveryDate.getDate() + 1);
                }
                
                const dateStr = deliveryDate.toLocaleDateString('es-ES', options);
                const capitalizedDate = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
                
                CheckoutState.schedule.fecha = capitalizedDate;
                const label = document.getElementById('deliveryDateLabel');
                if (label) label.textContent = capitalizedDate;

                showStep('stepSchedule');
            });
            // Volver a paso 3
            document.getElementById('backToStep3').addEventListener('click', () => {
                showStep('stepMap');
            });
            document.getElementById('useMyLocation').addEventListener('click', () => {
                if (!navigator.geolocation) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Geolocalización no disponible'
                    });
                    return;
                }
                navigator.geolocation.getCurrentPosition((pos) => {
                    const {
                        latitude,
                        longitude
                    } = pos.coords;
                    CheckoutState.address.lat = latitude;
                    CheckoutState.address.lng = longitude;
                    document.getElementById('mapFrame').src = `https://maps.google.com/maps?q=${latitude},${longitude}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
                }, (err) => {
                    Swal.fire({
                        icon: 'info',
                        title: 'No se pudo obtener ubicación',
                        text: String(err.message || err)
                    });
                }, {
                    enableHighAccuracy: true,
                    timeout: 8000
                });
            });

            // Paso 4
            document.getElementById('toStep5').addEventListener('click', () => {
                if (!isLoggedIn()) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Debes iniciar sesión',
                        text: 'Para continuar con la compra, inicia sesión.',
                        confirmButtonText: 'Iniciar sesión'
                    }).then(() => {
                        sessionStorage.setItem('postLoginRedirect', window.location.pathname);
                        if (typeof window.openAuthModal === 'function') {
                            window.openAuthModal('login');
                        } else {
                            window.location.href = '../login/login.php?returnUrl=' + encodeURIComponent(window.location.pathname);
                        }
                    });
                    return;
                }
                showStep('stepPayment');
            });
            // Volver a paso 4
            document.getElementById('backToStep4').addEventListener('click', () => {
                showStep('stepSchedule');
            });

            // Paso 5 – selección de método
            document.querySelectorAll('input[name="pay"]').forEach(r => {
                r.addEventListener('change', () => {
                    CheckoutState.payMethod = r.value;
                    if (r.value === 'paypal') {
                        ensurePayPal();
                    } else {
                        document.getElementById('paypalButtons').classList.add('hidden');
                    }
                    const nequiPanel = document.getElementById('nequiPanel');
                    if (nequiPanel) {
                        if (r.value === 'nequi') {
                            nequiPanel.classList.remove('hidden');
                        } else {
                            nequiPanel.classList.add('hidden');
                        }
                    }
                });
            });
            const nequiSendBtn = document.getElementById('nequiSend');
            if (nequiSendBtn) {
                nequiSendBtn.addEventListener('click', () => {
                    const tel = (document.getElementById('nequiPhone') && document.getElementById('nequiPhone').value || '').replace(/\D/g, '');
                    if (tel.length < 9) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Ingresa un número Nequi válido'
                        });
                        return;
                    }
                    CheckoutState.payMethod = 'nequi';
                    CheckoutState.nequiPhone = tel;
                    const fin = document.getElementById('finishCheckout');
                    if (fin) fin.click();
                });
            }
            // Preselección de pago si viene desde el carrito
            try {
                const pref = sessionStorage.getItem('preferredPay');
                if (pref === 'paypal') {
                    const r = document.getElementById('payPaypal');
                    if (r) {
                        r.checked = true;
                        CheckoutState.payMethod = 'paypal';
                        ensurePayPal();
                    }
                    sessionStorage.removeItem('preferredPay');
                }
            } catch (e) {
                /* noop */
            }
            document.getElementById('finishCheckout').addEventListener('click', async () => {
                if (!isLoggedIn()) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Debes iniciar sesión',
                        text: 'Para finalizar la compra, inicia sesión.',
                        confirmButtonText: 'Iniciar sesión'
                    }).then(() => {
                        sessionStorage.setItem('postLoginRedirect', window.location.pathname);
                        if (typeof window.openAuthModal === 'function') {
                            window.openAuthModal('login');
                        } else {
                            window.location.href = '../login/login.php?returnUrl=' + encodeURIComponent(window.location.pathname);
                        }
                    });
                    return;
                }
                if (!CheckoutState.payMethod) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Selecciona un método de pago'
                    });
                    return;
                }
                if (CheckoutState.delivery !== 'punto' && CheckoutState.payMethod === 'efecty') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Efectivo solo en punto de entrega'
                    });
                    return;
                }
                if (CheckoutState.payMethod !== 'paypal') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pedido confirmado',
                        text: 'Generando factura…'
                    });
                    try {
                        const items = getCart();
                        const res = await fetch('../backend/php/orders_save_pending.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                total: CheckoutState.orderTotal,
                                delivery: CheckoutState.delivery,
                                address: CheckoutState.address,
                                schedule: CheckoutState.schedule,
                                pay: CheckoutState.payMethod,
                                nequi_phone: CheckoutState.nequiPhone || null,
                                items,
                                user: (function() {
                                    try {
                                        const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                                        if (!rawStr) return null;
                                        const raw = JSON.parse(rawStr);
                                        const u = raw && raw.user ? raw.user : raw;
                                        return {
                                            email: u.correo_electronico || u.email || '',
                                            name: u.nombres_completos || u.nombre || ''
                                        };
                                    } catch (e) {
                                        return null;
                                    }
                                })()
                            })
                        });
                        const out = await res.json();
                        if (out && out.ok && out.order_id) {
                            // Enviar factura por correo también en métodos no‑PayPal
                            try {
                                const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                                const raw = rawStr ? JSON.parse(rawStr) : null;
                                const u = raw && raw.user ? raw.user : raw;
                                const email = (u && (u.correo_electronico || u.email)) || null;
                                if (email) {
                                    await fetch('../backend/php/send_invoice_email.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            order_id: out.order_id,
                                            to: email
                                        })
                                    });
                                }
                            } catch (e) {
                                /* opcional */
                            }
                            try {
                                const key = getCartKey();
                                if (key) {
                                    localStorage.setItem(key, JSON.stringify([]));
                                    window.dispatchEvent(new CustomEvent('cart:updated', {
                                        detail: {
                                            items: []
                                        }
                                    }));
                                }
                            } catch (e) {}
                            try {
                                markProgressComplete();
                            } catch (e) {}
                            
                            Swal.fire({
                                icon: 'success',
                                title: '¡Compra Exitosa!',
                                text: 'Tu pedido ha sido procesado correctamente y tu factura fue enviada al correo.',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ff0000',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '../backend/php/order_invoice.php?order_id=' + encodeURIComponent(out.order_id) + '&print=1';
                                }
                            });
                            return;
                        } else {
                            if (out && out.code === 'email_not_verified') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Verifica tu correo',
                                    text: out.error || 'Debes verificar tu correo para poder comprar.'
                                });
                                return;
                            }
                            Swal.fire({
                                icon: 'info',
                                title: 'Pedido guardado',
                                html: (out && out.error) ? String(out.error) : 'No se pudo abrir la factura automáticamente. Puedes revisarla luego en <b>Historial de compra</b>.',
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pedido recibido',
                            text: 'Ocurrió un detalle al generar la factura. La compra quedó registrada como Pendiente.',
                        });
                    }
                    try {
                        window.PurchaseHistoryStore && window.PurchaseHistoryStore.record({
                            method: CheckoutState.payMethod,
                            total: CheckoutState.orderTotal
                        });
                    } catch (e) {}
                }
            });
        });
    </script>
    <!-- Script de acciones del header (sesión/visibilidad) -->
    <script src="../static/js/header_actions.js"></script>
    <script src="../static/js/auth_modal.js"></script>
    <script src="../js/auth.js"></script>
    <script src="../static/js/index.js"></script>
    <script src="../static/js/chatbot.js"></script>
    <script src="../static/js/cart_badge.js"></script>
    <script src="../static/js/history_favorites.js"></script>

    <!-- Footer (igual al de otras páginas) -->
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
    <i class="fab fa-cc-mastercard" title="Mastercard" style="font-size:28px;color:#EB001B;margin-right:8px;"></i>
    <i class="fab fa-cc-paypal" title="PayPal" style="font-size:28px;color:#003087;margin-right:8px;"></i>
    <i class="fab fa-cc-amex" title="American Express" style="font-size:28px;color:#2E77BC;margin-right:8px;"></i>
    <i class="fab fa-cc-discover" title="Discover" style="font-size:28px;color:#FF6000;margin-right:8px;"></i>
    </div>
    </div>

    </footer>
    <script src="../static/js/loader.js" defer></script>
    <script src="../static/js/session_guard.js" defer></script>
    <script src="../static/js/network_guard.js" defer></script>
</body>

</html>