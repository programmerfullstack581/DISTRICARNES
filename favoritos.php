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
    <header class="header ">
        <style>
            .header{background:#000; border-bottom:none !important; box-shadow:none !important}
            .mobile-header{display:none;align-items:center;justify-content:space-between;background:#000;border-bottom:none;padding:.4rem .5rem;position:sticky;top:0;z-index:2000;min-height:50px}
            .mh-left,.mh-right{display:flex;align-items:center;gap:10px}
            .mh-left{padding-left:.25rem}
            .mh-right{padding-right:.25rem}
            .mh-icon{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;background:#111;border:1px solid #222}
            .mh-icon i{font-size:1.2rem}
            .mh-cart{position:relative}
            .mh-badge{position:absolute;top:-4px;right:-4px;background:#ff0000;color:#fff;border-radius:999px;font-size:.65rem;padding:2px 6px;line-height:1}
            @media (max-width:992px){
                .mobile-header{display:flex}
                .ml-search{display:none}
                #quickLinks{display:none}
                #userLoggedButtons{display:none !important}
                .nav-menu{display:none}
                .header .logo{display:none !important}
                .header .mobile-toggle{display:none !important}
                .header .header-content{padding:0;margin:0}
            }
            .header-content,.nav-menu,.ml-search{border-bottom:none !important; box-shadow:none !important}
            .user-avatar.has-photo,.user-avatar-large.has-photo{background-color: transparent !important}
            @media (min-width:993px){
                .mobile-drawer,.mobile-drawer-overlay{display:none !important}
            }
        </style>
        <div class="mobile-header" id="mobileHeader">
            <style>
                .mh-center{position:absolute;left:50%;transform:translateX(-50%);display:flex;align-items:center}
                .mh-center img{height:26px;max-width:120px}
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
                    .mobile-drawer{background:#000;border:1px solid #111;border-radius:12px;box-shadow:0 10px 32px rgba(0,0,0,.6)}
                    .drawer-quicklinks a{margin:6px 0;font-weight:800;letter-spacing:.2px}
                    .drawer-nav{padding:10px 12px}
                    .drawer-nav a{display:block;background:#0d0d0d;border:1px solid #1a1a1a;color:#fff;text-decoration:none;padding:12px 14px;border-radius:12px;font-weight:700;text-align:center}
                    .drawer-nav a + a{margin-top:8px}
                    .drawer-nav a.active{background:#1a1a1a;border-color:#333;position:relative}
                    .drawer-nav a.active::after{content:"";display:block;height:2px;background:#ff0000;width:80%;margin:8px auto 0;border-radius:2px}
                </style>
                <nav class="drawer-nav" style="display:flex;flex-direction:column;align-items:stretch;padding:10px 12px;gap:8px">
                    <a href="./index.php">Inicio</a>
                    <a href="./productos.php">Productos</a>
                    <a href="./promociones.php">Ofertas</a>
                    <a href="./contacto.php" >Contacto</a>
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
                <a href="./index.php" >Inicio</a>
                <a href="./productos.php">Productos</a>
                <a href="./promociones.php">Ofertas</a>
                <a href="./contacto.php" >Contacto</a>
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

                    document.addEventListener('click', function (event) {
                        const container = document.querySelector('.user-profile-container');
                        if (!container.contains(event.target)) {
                            const dd = document.getElementById('userDropdown');
                            if (dd) dd.classList.remove('active');
                            const btn = document.querySelector('.menu-button');
                            if (btn) btn.setAttribute('aria-expanded', 'false');
                        }
                    });

                    document.addEventListener('keydown', function (event) {
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
                (function(){
                    function ensureResponsiveState(){
                        var isMobile = window.matchMedia('(max-width: 992px)').matches;
                        var ql = document.getElementById('quickLinks');
                        var nav = document.getElementById('navMenu');
                        var logo = document.querySelector('.header .logo');
                        if (isMobile) {
                            if (ql) ql.style.display='none';
                            if (nav) nav.style.display='none';
                            if (logo) logo.style.display='none';
                        } else {
                            document.body.classList.remove('drawer-open');
                            var ov=document.getElementById('drawerOverlay');
                            var md=document.getElementById('mobileDrawer');
                            if (ov) ov.style.display='none';
                            if (md) md.style.display='';
                            if (ql) ql.style.display='';
                            if (nav) nav.style.display='';
                            if (logo) logo.style.display='';
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
