<?php
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Sobre Nosotros</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/sobre_nosotros.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/tailwind.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/theme.css" />
    <script src="<?php echo $basePath; ?>/static/js/theme.js"></script>
    <script src="<?php echo $basePath; ?>/static/js/auth_utils.js"></script>

</head>

<body class=" bg-black text-white ">
    <!-- Header -->
<?php
$activeNav = 'nosotros';
include __DIR__ . '/includes/header.php';
?>


<?php include __DIR__ . '/includes/header-scripts.php'; ?>
    
    <!-- Contenido Principal -->
    <main style="background-color: #000000;" class="main-content">
        <!-- Hero Section Ultra Moderno con Video -->
        <section class="hero-ultra-modern">
            <div class="hero-video-container">
                <video autoplay muted loop class="hero-video">
                    <source
                        src="https://player.vimeo.com/external/371433846.sd.mp4?s=236da2f3c0fd273d2c6d9a064f3ae35579b2bbdf&profile_id=139&oauth2_token_id=57447761"
                        type="video/mp4">
                </video>
                <div class="hero-video-overlay"></div>
            </div>

            <div class="hero-content-ultra">
                <div class="hero-badge-modern">
                    <i class="fas fa-crown"></i>
                    <span>QUIÉNES SOMOS</span>
                </div>

                <h1 class="hero-title-ultra">
                    <span class="title-line-1">DISTRICARNES</span>
                </h1>

                <div class="hero-subtitle-container">
                    <p class="hero-subtitle-ultra">Tradición familiar en carnes premium desde 1995. Conectamos la calidad artesanal con la innovación tecnológica para ofrecer la mejor experiencia cárnica de la región
                    </p>
                </div>

                <div class="hero-cta-buttons">
                    <button class="btn-primary-ultra">
                        <span>Conoce Nuestra Historia</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button class="btn-secondary-ultra">
                        <i class="fas fa-play"></i>
                        <span>Ver Video</span>
                    </button>
                </div>

                <!-- Floating Stats -->
                <div class="floating-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number" data-target="28">0</span>
                            <span class="stat-label">Años de Tradición</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number" data-target="8500">0</span>
                            <span class="stat-label">Familias Atendidas</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-meat"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number" data-target="100">0</span>
                            <span class="stat-label">% Carnes Premium</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div class="scroll-indicator">
                <div class="scroll-mouse">
                    <div class="scroll-wheel"></div>
                </div>
                <span>Scroll para descubrir más</span>
            </div>
        </section>

        
        

        <!-- Misión y Visión Profesional -->
        <section class="mission-vision-pro">
            <div class="container-pro">
                <div class="mv-wrapper">
                    <div class="mv-card-pro mission-card-pro">
                        <div class="mv-header">
                            <div class="mv-icon-pro">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h3 class="mv-title">Nuestra Misión</h3>
                            <div class="mv-line"></div>
                        </div>
                        <div class="mv-content">
                            <p>Proporcionar a hogares y empresas de nuestra comunidad <strong>productos cárnicos de
                                    excelencia</strong>, respaldados por un sistema integral de gestión que garantiza frescura, trazabilidad y una experiencia de compra excepcional.</p>
                            <ul class="mv-list">
                                <li><i class="fas fa-check"></i> Calidad garantizada en cada producto</li>
                                <li><i class="fas fa-check"></i> Servicio personalizado y profesional</li>
                                <li><i class="fas fa-check"></i> Innovación constante en procesos</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mv-divider-vertical"></div>

                    <div class="mv-card-pro vision-card-pro">
                        <div class="mv-header">
                            <div class="mv-icon-pro">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3 class="mv-title">Nuestra Visión</h3>
                            <div class="mv-line"></div>
                        </div>
                        <div class="mv-content">
                            <p>Ser reconocidos como <strong>la empresa líder en el sector cárnico regional</strong>, distinguiéndonos por nuestra capacidad de innovación, responsabilidad social y compromiso con la sostenibilidad.</p>
                            <ul class="mv-list">
                                <li><i class="fas fa-check"></i> Liderazgo en el mercado regional</li>
                                <li><i class="fas fa-check"></i> Innovación tecnológica continua</li>
                                <li><i class="fas fa-check"></i> Responsabilidad social empresarial</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        </div>
        </section>

        <!-- Valores Corporativos -->
        <section class="values-section-pro" style="background-color: #000000;">
            <div class="container-pro">
                <div class="section-header">
                    <div class="section-badge">
                        <i class="fas fa-gem"></i>
                        <span>Nuestros Valores</span>
                    </div>
                    <h2 class="section-title" style="color: #ffffff;">Principios que nos Guían</h2>
                    <div class="section-underline"></div>
                    <p class="section-description">Los valores fundamentales que definen nuestra cultura empresarial y compromiso con la excelencia</p>
                </div>

                <div class="values-grid-pro">
                    <div class="value-card-pro">
                        <div class="value-header">
                            <div class="value-icon-pro">
                                <i class="fas fa-award"></i>
                            </div>
                            <h4 class="value-title">Excelencia</h4>
                        </div>
                        <p class="value-description">Compromiso inquebrantable con la calidad superior en cada producto y servicio que ofrecemos</p>
                        <div class="value-features">
                            <span class="feature-tag">Productos Premium</span>
                            <span class="feature-tag">Procesos Certificados</span>
                        </div>
                    </div>

                    <div class="value-card-pro">
                        <div class="value-header">
                            <div class="value-icon-pro">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <h4 class="value-title">Innovación</h4>
                        </div>
                        <p class="value-description">Integración de tecnología avanzada para optimizar procesos y mejorar la experiencia del cliente</p>
                        <div class="value-features">
                            <span class="feature-tag">Tecnología Digital</span>
                            <span class="feature-tag">Mejora Continua</span>
                        </div>
                    </div>

                    <div class="value-card-pro">
                        <div class="value-header">
                            <div class="value-icon-pro">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h4 class="value-title">Confianza</h4>
                        </div>
                        <p class="value-description">Relaciones sólidas basadas en la transparencia, honestidad y compromiso con nuestros clientes</p>
                        <div class="value-features">
                            <span class="feature-tag">Transparencia Total</span>
                            <span class="feature-tag">Servicio Confiable</span>
                        </div>
                    </div>

                    <div class="value-card-pro">
                        <div class="value-header">
                            <div class="value-icon-pro">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4 class="value-title">Sostenibilidad</h4>
                        </div>
                        <p class="value-description">Compromiso con prácticas responsables que beneficien a nuestra comunidad y medio ambiente</p>
                        <div class="value-features">
                            <span class="feature-tag">Consumo Local</span>
                            <span class="feature-tag">Responsabilidad Social</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Historia Empresarial -->
        <section class="history-section-pro" style="background-color: #000000;">
            <div class="container-pro">
                <div class="history-layout">
                    <div class="history-content-pro">
                        <div class="section-header">
                            <div class="section-badge">
                                <i class="fas fa-history"></i>
                                <span>Nuestra Trayectoria</span>
                            </div>
                            <h2 class="section-title" style="color: #ffffff;">Una Historia de Éxito</h2>
                            <div class="section-underline"></div>
                        </div>

                        <div class="history-text-pro">
                            <p class="lead-text">Fundada por los <strong>Hermanos Navarro</strong>, nuestra empresa representa la perfecta fusión entre tradición familiar y visión empresarial moderna.</p>

                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Los Inicios</h4>
                                    <p>Comenzamos como una carnicería tradicional con el sueño de ofrecer productos de calidad excepcional a nuestra comunidad local.</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Evolución Tecnológica</h4>
                                    <p>Implementamos sistemas avanzados de gestión para optimizar procesos y mejorar la experiencia del cliente.</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Liderazgo Regional</h4>
                                    <p>Hoy somos reconocidos como referentes en calidad y servicio en el sector cárnico regional.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="history-features-pro">
                        <div class="feature-box-pro">
                            <div class="feature-icon-large">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h4>Sistema Integral</h4>
                            <div class="feature-list-pro">
                                <div class="feature-item-pro">
                                    <i class="fas fa-check"></i>
                                    <span>Control de inventario en tiempo real</span>
                                </div>
                                <div class="feature-item-pro">
                                    <i class="fas fa-check"></i>
                                    <span>Gestión avanzada de ventas</span>
                                </div>
                                <div class="feature-item-pro">
                                    <i class="fas fa-check"></i>
                                    <span>Trazabilidad completa</span>
                                </div>
                                <div class="feature-item-pro">
                                    <i class="fas fa-check"></i>
                                    <span>Servicio a domicilio optimizado</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive Stats Section Ultra Modern -->
        <section class="interactive-stats-ultra">
            <div class="stats-background-animated">
                <div class="animated-shapes">
                    <div class="shape shape-1"></div>
                    <div class="shape shape-2"></div>
                    <div class="shape shape-3"></div>
                    <div class="shape shape-4"></div>
                </div>
            </div>

            <div class="container">
                <div class="stats-header-ultra">
                    <div class="stats-badge-floating">
                        <i class="fas fa-meat"></i>
                        <span>¿Buscas Carnes Premium de Calidad?</span>
                    </div>
                </div>

                <div class="stats-grid-ultra">
                    <div class="stat-card-ultra card-green" data-aos="zoom-in" data-aos-delay="100">
                        <div class="stat-icon-ultra">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content-ultra">
                            <span class="stat-number-animated" data-target="500">0</span>
                            <span class="stat-label-ultra">Familias Atendidas</span>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" data-progress="85"></div>
                        </div>
                    </div>

                    <div class="stat-card-ultra card-blue" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-icon-ultra">
                            <i class="fas fa-meat"></i>
                        </div>
                        <div class="stat-content-ultra">
                            <span class="stat-number-animated" data-target="150">0</span>
                            <span class="stat-label-ultra">Productos Premium</span>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" data-progress="92"></div>
                        </div>
                    </div>

                    <div class="stat-card-ultra card-teal" data-aos="zoom-in" data-aos-delay="300">
                        <div class="stat-icon-ultra">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-content-ultra">
                            <span class="stat-number-animated" data-target="24">0</span>
                            <span class="stat-label-ultra">Horas Frescura</span>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" data-progress="78"></div>
                        </div>
                    </div>

                    <div class="stat-card-ultra card-orange" data-aos="zoom-in" data-aos-delay="400">
                        <div class="stat-icon-ultra">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="stat-content-ultra">
                            <span class="stat-number-animated" data-target="28">0</span>
                            <span class="stat-label-ultra">Años de Tradición</span>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" data-progress="95"></div>
                        </div>
                    </div>
                </div>

                <!-- Experience Section -->
                <div class="experience-section-ultra" data-aos="fade-up" data-aos-delay="500">
                    <div class="experience-content">
                        <h3 class="experience-title">Experiencia Única en Carnes</h3>
                        <p class="experience-description">Ofrecemos productos cárnicos premium con tradición familiar y un equipo altamente especializado en el sector.</p>

                        <div class="experience-features">
                            <div class="feature-ultra">
                                <i class="fas fa-truck"></i>
                                <span>Entrega Rápida</span>
                            </div>
                            <div class="feature-ultra">
                                <i class="fas fa-shield-alt"></i>
                                <span>Seguridad Garantizada</span>
                            </div>
                            <div class="feature-ultra">
                                <i class="fas fa-headset"></i>
                                <span>Soporte 24/7</span>
                            </div>
                            <div class="feature-ultra">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Diseño Responsivo</span>
                            </div>
                        </div>

                        <div class="experience-cta">
                            <button class="btn-experience-ultra">
                                <span>Descubre Más</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="advantages-section-pro" style="background-color: #000000;">
            <div class="container-pro">
                <div class="section-header">
                    <div class="section-badge">
                        <i class="fas fa-trophy"></i>
                        <span>Ventajas Competitivas</span>
                    </div>
                    <h2 class="section-title" style="color: #ffffff;">¿Por Qué Somos Diferentes?</h2>
                    <div class="section-underline"></div>
                    <p class="section-description">Características distintivas que nos posicionan como líderes en el sector
                    </p>
                </div>

                <div class="advantages-grid">
                    <div class="advantage-card">
                        <div class="advantage-number">01</div>
                        <div class="advantage-content">
                            <h4>Frescura Garantizada</h4>
                            <p>Productos frescos diariamente con control de calidad riguroso</p>
                        </div>
                        <div class="advantage-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>

                    <div class="advantage-card">
                        <div class="advantage-number">02</div>
                        <div class="advantage-content">
                            <h4>Servicio Personalizado</h4>
                            <p>Atención especializada y asesoramiento experto para cada cliente</p>
                        </div>
                        <div class="advantage-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>

                    <div class="advantage-card">
                        <div class="advantage-number">03</div>
                        <div class="advantage-content">
                            <h4>Tecnología Avanzada</h4>
                            <p>Sistemas digitales que optimizan cada aspecto del servicio</p>
                        </div>
                        <div class="advantage-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                    </div>

                    <div class="advantage-card">
                        <div class="advantage-number">04</div>
                        <div class="advantage-content">
                            <h4>Entrega Confiable</h4>
                            <p>Servicio a domicilio puntual con seguimiento en tiempo real</p>
                        </div>
                        <div class="advantage-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>

                    <div class="advantage-card">
                        <div class="advantage-number">05</div>
                        <div class="advantage-content">
                            <h4>Precios Competitivos</h4>
                            <p>Tarifas justas con ofertas personalizadas y programas de fidelización</p>
                        </div>
                        <div class="advantage-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>

                    <div class="advantage-card">
                        <div class="advantage-number">06</div>
                        <div class="advantage-content">
                            <h4>Experiencia Digital</h4>
                            <p>Plataforma online intuitiva con seguimiento completo de pedidos</p>
                        </div>
                        <div class="advantage-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Profesional -->
        <section class="cta-section-pro">
            <div class="cta-overlay"></div>
            <div class="container-pro">
                <div class="cta-content-pro">
                    <div class="cta-badge">
                        <i class="fas fa-star"></i>
                        <span>Calidad Premium</span>
                    </div>
                    <h3 class="cta-title">Experiencia la Diferencia de la Calidad Superior</h3>
                    <p class="cta-description">Descubre por qué miles de clientes confían en nosotros para sus necesidades cárnicas</p>
                    <div class="cta-buttons">
                        <a href="./productos.php" class="cta-button-primary">
                            <i class="fas fa-shopping-cart"></i> Ver Productos
                        </a>
                        <a href="./contacto.php" class="cta-button-secondary">
                            <i class="fas fa-phone"></i> Contactar
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Innovación y Futuro -->
        <section class="innovation-section-pro" style="background-color: #000000;">
            <div class="container-pro">
                <div class="section-header">
                    <div class="section-badge">
                        <i class="fas fa-rocket"></i>
                        <span>Innovación Continua</span>
                    </div>
                    <h2 class="section-title" style="color: #ffffff;">El Futuro de la Experiencia Cárnica</h2>
                    <div class="section-underline"></div>
                    <p class="section-description" >Proyectos en desarrollo que revolucionarán la forma de comprar productos cárnicos</p>
                </div>

                <div class="innovation-grid">
                    <div class="innovation-card">
                        <div class="innovation-icon">
                            <i class="fas fa-globe-americas"></i>
                        </div>
                        <div class="innovation-content">
                            <h4>Plataforma Digital Avanzada</h4>
                            <p>Sitio web interactivo con catálogo completo, promociones personalizadas y sistema de pedidos optimizado</p>
                            <div class="innovation-status">
                                <span class="status-badge in-development">En Desarrollo</span>
                            </div>
                        </div>
                    </div>

                    <div class="innovation-card">
                        <div class="innovation-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="innovation-content">
                            <h4>Aplicación Móvil</h4>
                            <p>App nativa para gestión completa de compras, historial de pedidos y preferencias personalizadas
                            </p>
                            <div class="innovation-status">
                                <span class="status-badge planned">Planificado</span>
                            </div>
                        </div>
                    </div>

                    <div class="innovation-card">
                        <div class="innovation-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="innovation-content">
                            <h4>Programa de Fidelización</h4>
                            <p>Sistema de recompensas inteligente con ofertas personalizadas basadas en preferencias de compra
                            </p>
                            <div class="innovation-status">
                                <span class="status-badge planned">Planificado</span>
                            </div>
                        </div>
                    </div>

                    <div class="innovation-card">
                        <div class="innovation-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div class="innovation-content">
                            <h4>Integración Total</h4>
                            <p>Ecosistema completo desde la gestión de inventario hasta la entrega final al cliente</p>
                            <div class="innovation-status">
                                <span class="status-badge active">Activo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONOCE AL EQUIPO Section -->
        <section class="team-section-pro">
            <div class="container-pro">
                <div class="section-header">
                    <h2 class="section-title" style="color: #ffffff;" >CONOCE AL EQUIPO</h2>
                    <div class="section-underline"></div>
                </div>

                <div class="team-carousel-container">
                    <!-- Navigation Arrows -->
                    <button class="carousel-arrow carousel-arrow-left" id="teamCarouselPrev" aria-label="Anterior">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="carousel-arrow carousel-arrow-right" id="teamCarouselNext" aria-label="Siguiente">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <!-- Carrusel de sobre nosotros en este caso los miembros del equipo -->
                    <div class="team-carousel-wrapper">
                        <div class="team-carousel" id="teamCarousel">
                            <!-- Team Member 1 -->
                            <div class="team-member-card active">
                                <div class="team-member-image">
                                    <img src="./assets/img-sobre-nosotros/cv jaider navarro.png" alt="Jaider Alberto Navarro" width="512" height="512" loading="lazy" decoding="async">
                                </div>
                                <div class="team-member-info">
                                    <h3 class="team-member-name">Jaider Alberto Navarro</h3>
                                    <p class="team-member-role">LIDER TECNICO/ JEFE DEPROYECTO </p>
                                    <p class="team-member-description">Coordina tareas, elimina obstáculos, facilita reuniones diarias y asegura estándares de código.</p>
                                </div>
                            </div>

                            <!-- Team Member 2 -->
                            <div class="team-member-card">
                                <div class="team-member-image">
                                    <img src="./assets/img-sobre-nosotros/cv jairo requena.png" alt="Team Member 2" width="512" height="512" loading="lazy" decoding="async">
                                </div>
                                
                                <div class="team-member-info">
                                    <h3 class="team-member-name">Jairo Requena Caraballo</h3>
                                    <p class="team-member-role">ANALISTA DE DATOS</p>
                                    <p class="team-member-description">Recopila y analiza necesidades del cliente, diseña y arquitectura.</p>
                                </div>
                            </div>

                            <!-- Team Member 3 -->
                            <div class="team-member-card">
                                <div class="team-member-image">
                                    <img src="./assets/img-sobre-nosotros/cv juan humberto .png" alt="Team Member 3" width="512" height="512" loading="lazy" decoding="async">
                                </div>
                                <div class="team-member-info">
                                    <h3 class="team-member-name">Juan humberto Vega Sanchez</h3>
                                    <p class="team-member-role">DESARROLLADOR FULLSTACK</p>
                                    <p class="team-member-description">Implementa backend (lógica, APIs, DB como Postgres) y frontend.</p>
                                </div>
                            </div>

                            <!-- Team Member 4 -->
                            <div class="team-member-card">
                                <div class="team-member-image">
                                    <img src="./assets/img-sobre-nosotros/cv diego andres cardona.png" alt="Team Member 4" width="512" height="512" loading="lazy" decoding="async">
                                </div>
                                <div class="team-member-info">
                                    <h3 class="team-member-name">Diego Andres Cardona Quintana </h3>
                                    <p class="team-member-role">DESARROLLADOR FRONTEND/BACKEND</p>
                                    <p class="team-member-description">Implementa frontend (HTML, CSS, JS, React) y backend (Node.js, Express) para crear aplicaciones web funcionales.</p>
                                </div>
                            </div>

                            <!-- Team Member 5 -->
                            <div class="team-member-card">
                                <div class="team-member-image">
                                    <img src="./assets/img-sobre-nosotros/cv francisco.png" alt="Team Member 4" width="512" height="512" loading="lazy" decoding="async">
                                </div>
                                <div class="team-member-info">
                                    <h3 class="team-member-name">Francisco Javier Sanz Ortiz </h3>
                                    <p class="team-member-role">ENCARGADO DE PRUEBAS  Y ENTREGA / TESTER</p>
                                    <p class="team-member-description">Encargado de garantizar la calidad y la entrega oportuna de los productos, asegurando la satisfacción del cliente.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination Dots -->
                    <div class="carousel-dots" id="teamCarouselDots">
                        <span class="dot active" data-index="0"></span>
                        <span class="dot" data-index="1"></span>
                        <span class="dot" data-index="2"></span>
                        <span class="dot" data-index="3"></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mensaje Final Profesional -->
        <section class="final-message-section-pro">
            <div class="container-pro">
                <div class="final-message-pro">
                    <div class="final-message-content">
                        <div class="final-badge">
                            <i class="fas fa-heart"></i>
                            <span>Nuestro Compromiso</span>
                        </div>
                        <h3 class="final-title">Districarnes Hermanos Navarro</h3>
                        <div class="final-subtitle">Más que una carnicería, una experiencia completa</div>
                        <div class="final-description">
                            <p>Somos la promesa de frescura que se cumple cada día, el abrazo familiar que acompaña cada compra, y el paso firme hacia el futuro de la alimentación local responsable.</p>
                        </div>
                        <div class="final-values">
                            <div class="final-value">
                                <i class="fas fa-leaf"></i>
                                <span>Frescura Garantizada</span>
                            </div>
                            <div class="final-value">
                                <i class="fas fa-heart"></i>
                                <span>Atención Familiar</span>
                            </div>
                            <div class="final-value">
                                <i class="fas fa-rocket"></i>
                                <span>Innovación Constante</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

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
    <script src="./static/js/header_actions.js"></script>
    <script src="./static/js/auth_modal.js?v=<?= filemtime(__DIR__ . '/static/js/auth_modal.js') ?>"></script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/history_favorites.js"></script>
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
