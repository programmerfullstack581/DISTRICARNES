<?php
require_once __DIR__ . '/config/bootstrap.php';

require_once __DIR__ . '/backend/php/core/conexion.php';

function getActiveOffers($db) {
    try {
        $sql = "SELECT * FROM ofertas WHERE estado = 'active' ORDER BY created_at DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

$ROOT_DIR = __DIR__;
function base_prefix_root_promos(): string {
    global $basePath;
    return $basePath;
}
function apply_base_prefix_promos(string $webPath): string {
    global $basePath;
    $p = str_replace('\\','/',$webPath);
    if ($p !== '' && $p[0] !== '/') $p = '/'.$p;
    $base = base_prefix_root_promos();
    if ($base && strpos($p, $base . '/') !== 0) $p = $base . $p;
    return $p;
}
function normalize_web_path_promos(string $fsPath, string $rootDir): string {
    $p = str_replace('\\','/',$fsPath);
    $root = str_replace('\\','/',$rootDir);
    if (strpos($p, $root) === 0) { $p = substr($p, strlen($root)); }
    if ($p !== '' && $p[0] !== '/') $p = '/'.$p;
    return apply_base_prefix_promos($p);
}
function promo_image_src(array $row, string $rootDir): string {
    $fallback = '/static/images/image.png';
    $val = isset($row['imagen']) ? trim((string)$row['imagen']) : '';
    if ($val === '') return $fallback;
    // Absoluta http(s)
    if (preg_match('#^https?://#i', $val)) return $val;
    // Normalizar separadores
    $val = str_replace('\\','/',$val);
    // Si contiene 'static/images'
    $pos = strpos($val, 'static/images');
    if ($pos !== false) {
        $rel = substr($val, $pos); // 'static/images/...'
        $fs = $rootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (file_exists($fs)) return apply_base_prefix_promos('/'.$rel);
    }
    // Si comienza con '/', comprobar en disco relativo a raíz del proyecto
    if ($val[0] === '/') {
        $fs = $rootDir . DIRECTORY_SEPARATOR . ltrim(str_replace('/','\\',$val),'\\');
        if (file_exists($fs)) return apply_base_prefix_promos($val);
    }
    // Probar por nombre de archivo en directorios conocidos
    $base = basename($val);
    $try1 = $rootDir . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $base;
    $try2 = $rootDir . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $base;
    if (file_exists($try1)) return normalize_web_path_promos($try1, $rootDir);
    if (file_exists($try2)) return normalize_web_path_promos($try2, $rootDir);
    // Fallback por categoría o genérico (simple: usar genérico)
    return apply_base_prefix_promos($fallback);
}

$ofertas = getActiveOffers($conexion);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Promociones</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <!-- <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/promociones.css" />  Comentado para usar los nuevos estilos -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <!-- <link rel="stylesheet" href="<?php echo $basePath; ?>/css/ofertas.css"> Comentado para evitar conflictos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/tailwind.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/theme.css" />
    <script src="<?php echo $basePath; ?>/static/js/theme.js"></script>


    <!-- Nuevos Estilos Integrados -->
    <style>
        :root{
            --palette-red:#B80707;
            --palette-white:#FFFFFF;
            --palette-black:#050304;
        }
        /* Estilos específicos para la sección de promociones nueva */
        .promociones-page-content {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--palette-white);
            line-height: 1.6;
        }

        /* Hero Section */
        .hero-promo {
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-bottom: 3px solid var(--palette-red);
            margin-top: 0; 
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: var(--palette-red);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            font-weight: bold;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        /* Promociones Section */
        .promociones-section {
            padding: 4rem 5%;
            margin: 0 auto;
        }

        .section-title-promo {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            position: relative;
            color: var(--palette-white);
        }

        .section-title-promo::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--palette-red);
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        @media (min-width: 1600px) {
            .promo-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 2.5rem;
            }
            .promo-info {
                padding: 1.25rem;
            }
            .promo-title {
                font-size: 1.15rem;
            }
            .promo-desc {
                font-size: 0.85rem;
            }
            .new-price {
                font-size: 1.4rem;
            }
        }

        @media (min-width: 1200px) and (max-width: 1599px) {
            .promo-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .promo-title {
                font-size: 1.3rem;
            }
            .new-price {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 767px) {
            .promo-grid {
                gap: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .promo-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .promo-title {
                font-size: 1.5rem;
            }
            .new-price {
                font-size: 2rem;
            }
        }

        /* Nueva Tarjeta de Promoción */
        .promo-card {
            background: #111111;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #333;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .promo-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(184, 7, 7, 0.25);
            border-color: var(--palette-red);
        }

        .promo-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--palette-red);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
            z-index: 6;
        }

        .promo-card:hover::before {
            transform: scaleX(1);
        }

        .promo-img {
            aspect-ratio: 4 / 3;
            height: auto;
            overflow: hidden;
            position: relative;
        }

        .promo-img::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.5), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .promo-card:hover .promo-img::after {
            opacity: 1;
        }

        .promo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .promo-card:hover .promo-img img {
            transform: scale(1.1);
        }

        .promo-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--palette-red);
            color: var(--palette-white);
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            z-index: 2;
        }

        .stock-badge {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: #4ade80;
            padding: 0.25rem 0.75rem;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #4ade80;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stock-badge::before {
            content: '●';
            font-size: 0.8rem;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ffc107;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .rating-number {
            color: #888;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .promo-info {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .promo-title {
            font-size: 1.4rem;
            color: var(--palette-white);
            margin-bottom: 0.5rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .promo-desc {
            color: #aaaaaa;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #888;
        }

        .product-meta span {
            background: #1a1a1a;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid #333;
        }

        .promo-price {
            display: flex;
            align-items: baseline;
            gap: 1rem;
            margin-bottom: 1.5rem;
            margin-top: auto;
        }

        .old-price {
            color: #666;
            text-decoration: line-through;
            font-size: 1rem;
        }

        .new-price {
            color: var(--palette-red);
            font-size: 1.8rem;
            font-weight: 800;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1a1a1a;
            border-radius: 8px;
            border: 1px solid #333;
            margin-bottom: 1rem;
            padding: 5px;
            max-width: 150px;
        }

        .qty-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 1.2rem;
            width: 30px;
            height: 30px;
            cursor: pointer;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            color: var(--palette-red);
        }

        .quantity-selector input {
            background: none;
            border: none;
            color: #ffffff;
            text-align: center;
            width: 40px;
            font-weight: bold;
            font-size: 1rem;
            -moz-appearance: textfield;
        }
        
        .quantity-selector input::-webkit-outer-spin-button,
        .quantity-selector input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .promo-btn {
            background: var(--palette-red);
            color: var(--palette-white);
            border: none;
            padding: 1rem;
            width: 100%;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .promo-btn:hover {
            background: #950505;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(184, 7, 7, 0.35);
        }
        
        .promo-btn:active {
            transform: translateY(0);
        }

        .view-details {
            display: block;
            text-align: center;
            color: #888;
            margin-top: 1rem;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .view-details:hover {
            color: var(--palette-red);
        }

        /* Ofertas Especiales */
        .ofertas-especiales {
            background: #111111;
            padding: 4rem 5%;
            border-top: 3px solid var(--palette-red);
            border-bottom: 3px solid var(--palette-red);
        }

        .oferta-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .oferta-item {
            text-align: center;
            padding: 2rem;
            background: #000000;
            border-radius: 10px;
            border: 1px solid #333;
            transition: border-color 0.3s;
        }

        .oferta-item:hover {
            border-color: var(--palette-red);
        }

        .oferta-icon {
            font-size: 3rem;
            color: var(--palette-red);
            margin-bottom: 1rem;
        }

        .oferta-item h3 {
            color: var(--palette-red);
            margin-bottom: 0.5rem;
            font-weight: bold;
            font-size: 1.3rem;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--palette-black) 0%, #1a0000 100%);
            padding: 4rem 5%;
            text-align: center;
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            color: var(--palette-red);
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .cta-content p {
            margin-bottom: 2rem;
        }

        .cta-btn {
            background: var(--palette-red);
            color: var(--palette-white);
            border: none;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .cta-btn:hover {
            background: #950505;
            transform: scale(1.05);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
             
            .section-title-promo {
                font-size: 2rem;
            }
        }
    </style>
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
                    <a href="./promociones.php" class="active">Ofertas</a>
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
                <a href="./promociones.php" class="active">Ofertas</a>
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
   

    <!-- Contenido Principal - Promociones -->
    <div class="promociones-page-content">
        <section class="hero-promo">
            <div class="hero-content">
                <h1 class="hero-title" style="line-height:1.05;"><span id="typedPromo"></span><span class="typed-caret" aria-hidden="true"></span></h1>
                <p>La mejor calidad al mejor precio, solo por tiempo limitado</p>
            </div>
        </section>

        <section class="promociones-section">
            <h2 class="section-title-promo">Promociones de la Semana</h2>

            <div class="promo-grid">
                <?php if (count($ofertas) > 0): ?>
                    <?php foreach ($ofertas as $oferta): ?>
                        <div class="promo-card">
                            <div class="promo-img">
                                <img src="<?php echo htmlspecialchars(promo_image_src($oferta, $ROOT_DIR)); ?>" alt="<?php echo htmlspecialchars($oferta['nombre']); ?>">
                                <?php 
                                    $tag = '';
                                    $precio_actual = 0;
                                    $precio_antes_mostrar = '';
                                    $precio_actual_mostrar = '';
                                    
                                    // Lógica básica de precios
                                    if ($oferta['tipo'] === 'percentage') {
                                        $tag = '-' . intval($oferta['valor_descuento']) . '%';
                                        // Simular precio base si no hay producto real (para demo visual)
                                        $precio_base_ficticio = 25000; 
                                        $descuento = ($precio_base_ficticio * $oferta['valor_descuento']) / 100;
                                        $precio_actual = $precio_base_ficticio - $descuento;
                                        
                                        $precio_antes_mostrar = '$' . number_format($precio_base_ficticio, 0, ',', '.');
                                        $precio_actual_mostrar = '$' . number_format($precio_actual, 0, ',', '.');
                                        
                                    } elseif ($oferta['tipo'] === 'bogo') {
                                        $tag = '2x1';
                                        $precio_actual = $oferta['valor_descuento'] > 0 ? $oferta['valor_descuento'] : 15000; // Fallback
                                        $precio_antes_mostrar = ''; // No aplica "antes" en 2x1 usualmente, o es el precio de 2
                                        $precio_actual_mostrar = '$' . number_format($precio_actual, 0, ',', '.') . ' (Lleva 2)';
                                        
                                    } elseif ($oferta['tipo'] === 'fixed') {
                                        $tag = 'OFERTA';
                                        $precio_actual = $oferta['valor_descuento'];
                                        // Simular un precio "antes" mayor
                                        $precio_antes_mostrar = '$' . number_format($precio_actual * 1.2, 0, ',', '.');
                                        $precio_actual_mostrar = '$' . number_format($precio_actual, 0, ',', '.');
                                    }
                                ?>
                                <span class="promo-tag"><?php echo $tag; ?></span>
                                <span class="stock-badge">En stock</span>
                            </div>
                            
                            <div class="promo-info">
                                <h3 class="promo-title"><?php echo htmlspecialchars($oferta['nombre']); ?></h3>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    <span class="rating-number">(5.0)</span>
                                </div>
                                <p class="promo-desc"><?php echo htmlspecialchars($oferta['descripcion'] ?? 'Corte seleccionado de alta calidad.'); ?></p>
                                
                                <div class="product-meta">
                                    <span class="weight"><i class="fas fa-weight-hanging"></i> Peso aprox: 500g - 1kg</span>
                                    <span class="origin"><i class="fas fa-map-marker-alt"></i> Origen: Nacional</span>
                                </div>
                                
                                <div class="promo-price">
                                    <?php if($precio_antes_mostrar && $oferta['tipo'] !== 'bogo'): ?>
                                        <span class="old-price"><?php echo $precio_antes_mostrar; ?></span>
                                    <?php endif; ?>
                                    <span class="new-price"><?php echo $precio_actual_mostrar; ?></span>
                                </div>
                                
                                <div class="quantity-selector">
                                    <button class="qty-btn minus" onclick="updateQty(this, -1)">-</button>
                                    <input type="number" class="quantity-input" value="1" min="1" max="10" readonly>
                                    <button class="qty-btn plus" onclick="updateQty(this, 1)">+</button>
                                </div>
                                
                                <!-- Botón Añadir al Carrito -->
                                <!-- Usamos las clases que cart_utils.js reconoce automáticamente: add-to-cart-btn -->
                                <!-- Pasamos los datos necesarios vía data attributes -->
                                <button class="promo-btn add-to-cart-btn" 
                                        data-id="promo-<?php echo $oferta['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($oferta['nombre']); ?>"
                                        data-price="<?php echo $precio_actual; ?>"
                                        data-image="<?php echo htmlspecialchars(promo_image_src($oferta, $ROOT_DIR)); ?>"
                                        data-qty="1">
                                    <i class="fas fa-shopping-cart"></i> Añadir al Carrito
                                </button>
                                
                                <a href="./productos.php" class="view-details">Ver detalles del producto →</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center">
                        <p class="text-xl">No hay promociones activas en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Script para manejar la cantidad localmente antes de añadir al carrito -->
        <script>
            function updateQty(btn, change) {
                const container = btn.closest('.quantity-selector');
                const input = container.querySelector('.quantity-input');
                let val = parseInt(input.value);
                val += change;
                if (val < 1) val = 1;
                if (val > 10) val = 10;
                input.value = val;
                
                // Actualizar el data-qty del botón de añadir al carrito correspondiente
                const card = btn.closest('.promo-card');
                const addBtn = card.querySelector('.add-to-cart-btn');
                if(addBtn) {
                    addBtn.dataset.qty = val;
                }
            }
        </script>

        <section class="ofertas-especiales">
            <h2 class="section-title-promo">Ofertas Especiales</h2>

            <div class="oferta-container">
                <div class="oferta-item">
                    <div class="oferta-icon">🥩</div>
                    <h3>Combo Asado</h3>
                    <p>Vacío + Chorizo + Costillar</p>
                    <p style="color: #ff0000; font-size: 1.5rem; font-weight: bold;">$29.990</p>
                    <p style="color: #999; text-decoration: line-through;">$39.990</p>
                </div>

                <div class="oferta-item">
                    <div class="oferta-icon">🍖</div>
                    <h3>Pack Familiar</h3>
                    <p>Pollo Entero + Carne Molida</p>
                    <p style="color: #ff0000; font-size: 1.5rem; font-weight: bold;">$12.990</p>
                    <p style="color: #999; text-decoration: line-through;">$16.980</p>
                </div>

                <div class="oferta-item">
                    <div class="oferta-icon">🥓</div>
                    <h3>Promo Fin de Semana</h3>
                    <p>Lomo Fino + Entraña</p>
                    <p style="color: #ff0000; font-size: 1.5rem; font-weight: bold;">$24.990</p>
                    <p style="color: #999; text-decoration: line-through;">$32.980</p>
                </div>

                <div class="oferta-item">
                    <div class="oferta-icon">🔥</div>
                    <h3>Combo Parrillero</h3>
                    <p>Todos los cortes para 6 personas</p>
                    <p style="color: #ff0000; font-size: 1.5rem; font-weight: bold;">$49.990</p>
                    <p style="color: #999; text-decoration: line-through;">$65.990</p>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="cta-content">
                <h2>¡No te pierdas estas ofertas!</h2>
                <p>Suscríbete a nuestro newsletter y recibe promociones exclusivas directamente en tu correo</p>
                <button class="cta-btn">Suscribirme</button>
            </div>
        </section>
    </div>


    <!--Footer Original-->
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
                    <button type="submit" style="background-color: #ff0000;">SUSCRÍBETE</button> </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AuthSystem && typeof AuthSystem.checkUserSession === 'function') {
                AuthSystem.checkUserSession();
            }
        });
    </script>
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
    <script src="./static/js/cart_utils.js"></script>
    <script src="./static/js/loader.js" defer></script>
    <script src="./static/js/session_guard.js" defer></script>
    <script src="./static/js/network_guard.js" defer>
    </script>
    <!-- Global Auth Utilities -->
    <script src="./static/js/auth_utils.js" defer></script>

    <!-- NOTA: Ya no necesitamos promociones.js para cargar las ofertas porque las cargamos con PHP -->
    <!-- <script src="./static/js/promociones.js" defer></script> -->
    <!-- Java script para las animaciones del carrusel de las imagenes -->
    <script>
      (function(){
        const el = document.getElementById('typedPromo');
        if(!el) return;
        const phrases = [
          '¡PROMOCIONES IMPERDIBLES!',
          'DESCUENTOS REALES EN CORTES SELECCIONADOS',
          'APROVECHA HOY: STOCK LIMITADO'
        ];
        const typeSpeed = 48, deleteSpeed = 32, hold = 1200;
        function colorize(text){
          const parts = text.split(/(\s+)/); let i=0;
          return parts.map(p=>/\s+/.test(p)?p:'<span class="'+((i++%2===0)?'typed-word-white':'typed-word-red')+'">'+p+'</span>').join('');
        }
        let pi=0, ci=0, typing=true;
        (function tick(){
          const ph = phrases[pi];
          if(typing){
            ci = Math.min(ci+1, ph.length);
            el.innerHTML = colorize(ph.slice(0,ci));
            if(ci===ph.length){ typing=false; return setTimeout(tick, hold); }
            return setTimeout(tick, typeSpeed);
          } else {
            ci = Math.max(0, ci-1);
            el.innerHTML = colorize(ph.slice(0,ci));
            if(ci===0){ typing=true; pi=(pi+1)%phrases.length; return setTimeout(tick, typeSpeed); }
            return setTimeout(tick, deleteSpeed);
          }
        })();
      })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
