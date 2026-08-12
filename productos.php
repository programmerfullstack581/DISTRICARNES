<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/backend/php/core/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DISTRICARNES - Productos-Categorias </title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/nav_pills.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/productos.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/tailwind.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/theme.css" />
    <script src="<?php echo $basePath; ?>/static/js/theme.js"></script>

</head>

<body class="">
    <?php
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
    $subcategoria = isset($_GET['subcategoria']) ? trim($_GET['subcategoria']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 12;
    $offset = ($page - 1) * $perPage;
    $baseParams = [];
    if ($q !== '') $baseParams['q'] = $q;
    if ($categoria !== '') $baseParams['categoria'] = $categoria;
    if ($subcategoria !== '') $baseParams['subcategoria'] = $subcategoria;

    // Utilidades para derivar categorías y normalizar texto
    function norm($s)
    {
        return mb_strtolower(trim((string)$s), 'UTF-8');
    }
    function deriveCategoryFromName($name)
    {
        $n = norm($name);
        if (preg_match('/(res|vaca|ternera|carne\s*de\s*res)/i', $n)) return 'res';
        if (preg_match('/(cerdo|puerco|chancho)/i', $n)) return 'cerdo';
        if (preg_match('/(pollo|gallina|pechuga|muslo)/i', $n)) return 'pollo';
        if (preg_match('/(pescado|robalo|bagre|mojarra|tilapia)/i', $n)) return 'pescado';
        return 'otros';
    }
    function imageForCategory($cat)
    {
        // Fallback a una imagen por categoría
        switch ($cat) {
            case 'cerdo':
                return base_prefix() . '/static/images/lomo_de_cerdo.jpeg';
            case 'res':
                return base_prefix() . '/static/images/lomo fresco.jpg';
            case 'pollo':
                return base_prefix() . '/static/images/imagenhero1.jpeg';
            case 'pescado':
                return base_prefix() . '/static/images/filete_de_robalo.jpg';
            default:
                return base_prefix() . '/static/images/image.png';
        }
    }

    // Normalización de rutas a formato web
    function base_prefix()
    {
        $script = isset($_SERVER['SCRIPT_NAME']) ? (string)$_SERVER['SCRIPT_NAME'] : '';
        $base = rtrim(dirname($script), '/');
        return ($base && $base !== '/') ? $base : '';
    }
    function normalize_web_path($fsPath, $rootDir)
    {
        $p = str_replace('\\', '/', (string)$fsPath);
        $root = str_replace('\\', '/', (string)$rootDir);
        if (strpos($p, $root) === 0) {
            $p = substr($p, strlen($root));
        }
        if ($p !== '' && $p[0] !== '/') {
            $p = '/' . $p;
        }
        $prefix = base_prefix();
        if ($prefix && strpos($p, $prefix . '/') !== 0) {
            $p = $prefix . $p;
        }
        return $p;
    }

    // Intentar encontrar imagen por coincidencia de nombre en static/images/products o static/images
    function find_fallback_image($name, $imagesDir, $imagesProductsDir, $rootDir)
    {
        $lower = trim(mb_strtolower((string)$name));
        if ($lower === '') return normalize_web_path($imagesDir . '/image.png', $rootDir);
        $strip = preg_replace('/[^a-z0-9]+/i', '', $lower);
        $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $dirs = [$imagesProductsDir, $imagesDir];
        foreach ($dirs as $dir) {
            foreach ($exts as $ext) {
                foreach (glob($dir . DIRECTORY_SEPARATOR . '*.' . $ext) as $file) {
                    $base = mb_strtolower(pathinfo($file, PATHINFO_FILENAME));
                    $baseStripped = preg_replace('/[^a-z0-9]+/i', '', $base);
                    if ($baseStripped === $strip || strpos($baseStripped, $strip) !== false) {
                        return normalize_web_path($file, $rootDir);
                    }
                }
            }
        }
        $generic = $imagesDir . DIRECTORY_SEPARATOR . 'image.png';
        return file_exists($generic) ? normalize_web_path($generic, $rootDir) : imageForCategory('otros');
    }
    // Normaliza y obtiene la imagen desde la fila de BD, si existe
    function imageFromRow(array $row): ?string
    {
        $candidates = ['imagen', 'image', 'imagen_url', 'image_url', 'foto', 'imagen_producto', 'url_imagen'];
        $img = null;
        foreach ($candidates as $c) {
            if (isset($row[$c]) && trim((string)$row[$c]) !== '') {
                $img = (string)$row[$c];
                break;
            }
        }
        if ($img === null) return null;
        $img = str_replace('\\', '/', $img);
        // Si es URL absoluta, devolver tal cual
        if (preg_match('#^https?://#i', $img)) return $img;
        // Directorios base
        $rootDir = __DIR__;
        $imagesDir = $rootDir . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
        $imagesProductsDir = $imagesDir . DIRECTORY_SEPARATOR . 'products';

        // Caso 1: contiene 'static/images'
        $pos = strpos($img, 'static/images');
        if ($pos !== false) {
            $webPath = base_prefix() . '/' . substr($img, $pos);
            $fsCandidate = $rootDir . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
            if (file_exists($fsCandidate)) {
                return $webPath;
            }
            // Intentar con basename en directorios conocidos
            $base = basename($img);
            $try1 = $imagesProductsDir . DIRECTORY_SEPARATOR . $base;
            $try2 = $imagesDir . DIRECTORY_SEPARATOR . $base;
            if (file_exists($try1)) return normalize_web_path($try1, $rootDir);
            if (file_exists($try2)) return normalize_web_path($try2, $rootDir);
            return null;
        }

        // Caso 2: relativo o solo nombre de archivo
        $base = basename($img);
        $try1 = $imagesProductsDir . DIRECTORY_SEPARATOR . $base;
        $try2 = $imagesDir . DIRECTORY_SEPARATOR . $base;
        $try3 = $rootDir . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $img), DIRECTORY_SEPARATOR);
        if (file_exists($try1)) return normalize_web_path($try1, $rootDir);
        if (file_exists($try2)) return normalize_web_path($try2, $rootDir);
        if (file_exists($try3)) return normalize_web_path($try3, $rootDir);
        // No encontrado: devolver null para activar fallback
        return null;
    }

    // Cargar todos los productos (según esquema real de BD)
    require_once __DIR__ . '/backend/php/core/producto_caducidad.php';
    producto_caducidad_aplicar($conexion);
    $exclVencidos = producto_caducidad_filtro_excluir($conexion);

    $allProducts = [];
    $sqlProductos = "SELECT * FROM producto";
    if ($exclVencidos !== '') { $sqlProductos .= ' WHERE ' . $exclVencidos; }
    $sqlProductos .= " ORDER BY id_producto DESC";
    if ($res = $conexion->query($sqlProductos)) {
        while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
            $allProducts[] = $r;
        }
        $res->closeCursor();
    }

    // Derivar conteos de categorías y etiquetas desde nombres
    $categoryCounts = [];
    $tagCounts = [];
    foreach ($allProducts as $p) {
        $cat = deriveCategoryFromName($p['nombre'] ?? '');
        $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;

        $words = preg_split('/[\s\-_,.]+/', norm($p['nombre'] ?? ''));
        foreach ($words as $w) {
            if (strlen($w) >= 4 && !in_array($w, ['res', 'cerdo', 'pollo', 'pescado', 'otros'])) {
                $tagCounts[$w] = ($tagCounts[$w] ?? 0) + 1;
            }
        }
    }
    arsort($tagCounts);
    // Limitar a las 10 etiquetas más frecuentes
    $tagCounts = array_slice($tagCounts, 0, 10, true);

    // Filtrar en memoria según parámetros actuales
    $filtered = [];
    $qNorm = norm($q);
    $catNorm = norm($categoria);
    $subNorm = norm($subcategoria);
    foreach ($allProducts as $p) {
        $name = $p['nombre'] ?? '';
        $nameNorm = norm($name);
        if ($q !== '' && strpos($nameNorm, $qNorm) === false) continue;
        $cat = deriveCategoryFromName($name);
        if ($categoria !== '' && $cat !== $catNorm) continue;
        if ($subcategoria !== '' && strpos($nameNorm, $subNorm) === false) continue;
        $p['__cat'] = $cat;
        $filtered[] = $p;
    }

    $total = count($filtered);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $pageProducts = array_slice($filtered, $offset, $perPage);
    $startDisplay = ($total > 0) ? ($offset + 1) : 0;
    $endDisplay = min($total, $offset + count($pageProducts));
    ?>

    <!-- Header -->
<?php
$activeNav = 'productos';
include __DIR__ . '/includes/header.php';
?>


<?php include __DIR__ . '/includes/header-scripts.php'; ?>


    <!-- Sesion de productos  -->
    <!-- PAGE TITLE -->
    <section class="page-title">
        <h1 class="text-white font-extrabold text-xl md:text-2xl mb-12 text-center team-title ">
            TODOS LOS
            <span class="text-red-600 italic " style="color: red;">PRODUCTOS</span>
        </h1>
        <div class="breadcrumb">
            <a href="#" style="color: rgb(255, 0, 0);">Inicio</a> <span>›</span>
            <span>Todos los productos</span>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- SIDEBAR -->
        <aside class="sidebar" style="color: black;">
            <div class="sidebar-fixed-top">
                <div class="filter-section">
                    <h3 style="color: gray;">¿Qué buscas?</h3>
                    <form class="search-box" action="productos.php" method="get" style="margin-bottom:10px ; color: black;">
                        <input style="color: #000000;" type="search" name="q" id="site-search-sidebar" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        <button type="submit">Buscar</button>
                    </form>
                </div>
            </div>
            <div class="sidebar-scroll">
                <div class="filter-section" style="color: black;">
                    <h3 style="color: gray;">Categorías</h3>
                    <div class="filter-group" style="color: white;">
                        <?php
                        $selectedCat = isset($_GET['categoria']) ? strtolower(trim($_GET['categoria'])) : '';
                        foreach ($categoryCounts as $catName => $cnt) {
                            $label = strtoupper($catName);
                            $checked = ($selectedCat === $catName) ? 'checked' : '';
                            $paramsOn = $baseParams;
                            $paramsOn['categoria'] = $catName;
                            $paramsOn['page'] = 1;
                            $urlOn = 'productos.php?' . http_build_query($paramsOn);

                            $paramsOff = $baseParams;
                            unset($paramsOff['categoria']);
                            $paramsOff['page'] = 1;
                            $urlOff = 'productos.php?' . http_build_query($paramsOff);
                            echo '<label><input type="checkbox" data-category="' . htmlspecialchars($catName) . '" ' . $checked . ' onchange="window.location.href=this.checked?\'' . $urlOn . '\':\'' . $urlOff . '\'"> ' . htmlspecialchars($label) . ' <span class="count">(' . intval($cnt) . ')</span></label>';
                        }
                        ?>
                    </div>
                </div>

                <div class="filter-section" style="color: white;">
                    <h3 style="color: gray;">Etiquetas</h3>
                    <div class="filter-group" style="color: white;">
                        <?php
                        $selectedSub = isset($_GET['subcategoria']) ? strtolower(trim($_GET['subcategoria'])) : '';
                        foreach ($tagCounts as $tag => $cnt) {
                            $label = strtoupper($tag);
                            $checked = ($selectedSub === $tag) ? 'checked' : '';
                            $paramsOn = $baseParams;
                            $paramsOn['subcategoria'] = $tag;
                            $paramsOn['page'] = 1;
                            $urlOn = 'productos.php?' . http_build_query($paramsOn);

                            $paramsOff = $baseParams;
                            unset($paramsOff['subcategoria']);
                            $paramsOff['page'] = 1;
                            $urlOff = 'productos.php?' . http_build_query($paramsOff);
                            echo '<label><input type="checkbox" data-tag="' . htmlspecialchars($tag) . '" ' . $checked . ' onchange="window.location.href=this.checked?\'' . $urlOn . '\':\'' . $urlOff . '\'"> ' . htmlspecialchars($label) . ' <span class="count">(' . intval($cnt) . ')</span></label>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </aside>

        <!-- PRODUCTS -->
        <main class="products-grid" style="color: white;" id="main-content">
            <div class="products-header">
                <div class="products-count" style="color: white;">Mostrando <?php echo $startDisplay . '-' . $endDisplay . ' de ' . $total . ' resultados'; ?></div>
            </div>

            <!-- Estilos de Productos Mejorados (Inspirados en Promociones) -->
            <style>
                .products-grid-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(225px, 1fr));
                    gap: 2rem;
                    padding: 20px 0;
                }

                @media (min-width: 1700px) {
                    .products-grid-container .product-info {
                        padding: 1rem;
                    }
                    .products-grid-container .product-name {
                        font-size: 1.05rem;
                    }
                    .products-grid-container .product-price {
                        font-size: 1.2rem;
                    }
                    .products-grid-container .product-actions {
                        flex-direction: column;
                        margin-top: 12px;
                    }
                    .products-grid-container .btn {
                        font-size: 0.8rem;
                        padding: 0.6rem;
                    }
                }

                @media (min-width: 1400px) and (max-width: 1699px) {
                    .products-grid-container .product-info {
                        padding: 1.15rem;
                    }
                    .products-grid-container .product-name {
                        font-size: 1.1rem;
                    }
                    .products-grid-container .product-price {
                        font-size: 1.3rem;
                    }
                }

                @media (min-width: 1200px) and (max-width: 1399px) {
                    .products-grid-container .product-info {
                        padding: 1.25rem;
                    }
                    .products-grid-container .product-name {
                        font-size: 1.15rem;
                    }
                    .products-grid-container .product-price {
                        font-size: 1.35rem;
                    }
                }

                @media (max-width: 991px) {
                    .products-grid-container {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 1.25rem;
                    }
                }

                @media (max-width: 480px) {
                    .products-grid-container {
                        grid-template-columns: 1fr;
                        gap: 1.25rem;
                    }
                    .products-grid-container .product-name {
                        font-size: 1.4rem;
                    }
                    .products-grid-container .product-price {
                        font-size: 1.7rem;
                    }
                }

                .product-card {
                    background: #111111;
                    border-radius: 4px;
                    overflow: hidden;
                    transition: all 0.3s ease;
                    border: 1px solid #333;
                    display: flex;
                    flex-direction: column;
                    position: relative;
                    height: 100%;
                }

                .product-card:hover {
                    transform: translateY(-10px);
                    box-shadow: 0 15px 30px rgba(255, 0, 0, 0.15);
                    border-color: #ff0000;
                }

                .product-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: #ff0000;
                    transform: scaleX(0);
                    transform-origin: left;
                    transition: transform 0.3s ease;
                    z-index: 6;
                }

                .product-card:hover::before {
                    transform: scaleX(1);
                }

                .product-image {
                    overflow: hidden;
                    position: relative;
                    width: 100%;
                    aspect-ratio: 4 / 3;
                    border-top-left-radius: 4px;
                    border-top-right-radius: 4px;
                    background: #ffffff;
                }

                .product-image::after {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(to top, rgba(0, 0, 0, 0.5), transparent 60%);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                    pointer-events: none;
                }

                .product-card:hover .product-image::after {
                    opacity: 1;
                }

                .product-image img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    object-position: center;
                    display: block;
                    border-top-left-radius: 4px;
                    border-top-right-radius: 4px;
                    background: transparent;
                    transform: scale(1);
                    transition: transform 0.5s ease;
                }

                .product-card:hover .product-image img {
                    transform: scale(1.08);
                }

                .favorite-btn {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: rgba(0, 0, 0, 0.6);
                    border: none;
                    border-radius: 50%;
                    width: 36px;
                    height: 36px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    opacity: 0;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    z-index: 10;
                }

                .product-card:hover .favorite-btn {
                    opacity: 1;
                }

                .favorite-btn:hover {
                    background: #ff0000;
                    transform: scale(1.1);
                }

                .favorite-btn.favorited {
                    color: #ff2f2f;
                    opacity: 1;
                }

                .product-info {
                    padding: 1.5rem;
                    display: flex;
                    flex-direction: column;
                    flex-grow: 1;
                    gap: 10px;
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

                .product-category-tag {
                    font-size: 0.8rem;
                    color: #888;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    margin-bottom: 0;
                }

                .product-name {
                    font-size: 1.3rem;
                    color: #ffffff;
                    font-weight: 700;
                    line-height: 1.3;
                    margin: 0;
                }

                .product-price {
                    font-size: 1.6rem;
                    color: #ff0000;
                    font-weight: 800;
                    margin-top: auto;
                    white-space: nowrap;
                    /* Empuja el precio hacia abajo si hay espacio */
                }

                .product-actions {
                    display: flex;
                    gap: 10px;
                    margin-top: 15px;
                }

                .btn {
                    padding: 0.8rem;
                    border-radius: 8px;
                    font-weight: 700;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-align: center;
                    border: none;
                    font-size: 0.9rem;
                    flex: 1;
                    white-space: nowrap;
                }

                .btn-add {
                    background: #ff0000;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 5px;
                }

                .btn-add:hover {
                    background: #cc0000;
                    transform: translateY(-2px);
                }

                .btn-info {
                    background: transparent;
                    border: 1px solid #444;
                    color: #ccc;
                }

                .btn-info:hover {
                    border-color: #ff0000;
                    color: #ff0000;
                }

                .stock-badge {
                    position: absolute;
                    bottom: 10px;
                    left: 10px;
                    background: rgba(0, 0, 0, 0.8);
                    color: #4ade80;
                    padding: 0.25rem 0.6rem;
                    border-radius: 5px;
                    font-size: 0.8rem;
                    font-weight: 700;
                    border: 1px solid #4ade80;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }

                .stock-badge::before {
                    content: '●';
                    font-size: 0.8rem;
                }
            </style>
            <div class="products-grid-container" id="products-container" data-server-render="1">
                <?php
                // Renderizado basado en datos ya filtrados en memoria
                $shown = 0;
                // Preparar rutas para fallback por nombre
                $rootDir = __DIR__;
                $imagesDir = $rootDir . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
                $imagesProductsDir = $imagesDir . DIRECTORY_SEPARATOR . 'products';

                foreach ($pageProducts as $row) {
                    $shown++;
                    $id = htmlspecialchars($row['id_producto'] ?? '');
                    $name = htmlspecialchars($row['nombre'] ?? 'Producto');
                    $price = floatval($row['precio_venta'] ?? 0);
                    $cat = $row['__cat'] ?? deriveCategoryFromName($row['nombre'] ?? '');
                    $image = imageFromRow($row);
                    if (!$image) {
                        // Intentar encontrar una imagen que coincida con el nombre
                        $image = find_fallback_image(($row['nombre'] ?? ''), $imagesDir, $imagesProductsDir, $rootDir);
                        if (!$image) {
                            $image = imageForCategory($cat);
                        }
                    }

                    // Simular calificación
                    $rating = number_format(rand(40, 50) / 10, 1);
                    $reviews = rand(12, 150);

                    // Renderizado de tarjeta mejorada
                    echo '<div class="product-card">';

                    // Imagen
                    echo '  <div class="product-image">';
                    echo '    <a href="detalle_producto.php?id=' . $id . '">';
                    echo '      <img src="' . $image . '" alt="' . $name . '" loading="lazy">';
                    echo '    </a>';
                    $stockVal = 0;
                    if (isset($row['stock'])) {
                        $stockVal = intval($row['stock']);
                    } elseif (isset($row['existencia'])) {
                        $stockVal = intval($row['existencia']);
                    } elseif (isset($row['cantidad'])) {
                        $stockVal = intval($row['cantidad']);
                    }
                    if ($stockVal > 0) {
                        echo '    <span class="stock-badge">En stock</span>';
                    }
                    echo '    <button class="favorite-btn" aria-label="Añadir a favoritos" data-id="' . $id . '" data-name="' . $name . '" data-price="' . number_format($price, 2, '.', '') . '" data-image="' . $image . '"><i class="far fa-heart"></i></button>';
                    echo '  </div>';

                    // Info
                    echo '  <div class="product-info">';
                    echo '    <div class="product-rating">';
                    echo '      <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
                    echo '      ' . ($rating >= 4.8 ? '<i class="fas fa-star"></i>' : '<i class="fas fa-star-half-alt"></i>');
                    echo '      <span class="rating-number">(' . $rating . ')</span>';
                    echo '    </div>';
                    echo '    <span class="product-category-tag">' . htmlspecialchars(strtoupper($cat)) . '</span>';
                    echo '    <a href="detalle_producto.php?id=' . $id . '" style="text-decoration:none;">';
                    echo '      <h3 class="product-name">' . $name . '</h3>';
                    echo '    </a>';
                    echo '    <div class="product-price">$' . number_format($price, 0, ',', '.') . '</div>';

                    // Acciones
                    echo '    <div class="product-actions">';
                    // Usamos add-to-cart-btn para que cart_utils.js lo detecte
                    echo '      <button class="btn btn-add add-to-cart-btn" data-id="' . $id . '" data-title="' . $name . '" data-price="' . $price . '" data-image="' . $image . '">';
                    echo '        <i class="fas fa-cart-plus"></i> Agregar';
                    echo '      </button>';
                    echo '      <a href="detalle_producto.php?id=' . $id . '" class="btn btn-info">Ver Detalles</a>';
                    echo '    </div>';

                    echo '  </div>';
                    echo '</div>';
                }
                ?>
            </div>

            <!-- SECCIONES POR CATEGORÍAS -->
            <section id="category-sections" class="category-sections">
                <!-- Secciones de Res, Cerdo y Pollo se renderizan vía JS -->
            </section>

            <div class="pagination">
                <?php
                // Construcción de paginación
                $baseParams = [];
                if ($q !== '') $baseParams['q'] = $q;
                if ($categoria !== '') $baseParams['categoria'] = $categoria;
                if ($subcategoria !== '') $baseParams['subcategoria'] = $subcategoria;

                function buildUrl($page, $params)
                {
                    $params['page'] = $page;
                    return 'productos.php?' . http_build_query($params) . '#products-container';
                }

                $prevDisabled = ($page <= 1);
                $nextDisabled = ($page >= $totalPages);

                // Primera página
                echo '<a class="' . ($page <= 1 ? 'disabled' : '') . '" href="' . ($page <= 1 ? '#' : buildUrl(1, $baseParams)) . '">Primera</a>';
                // Página anterior
                echo '<a class="' . ($prevDisabled ? 'disabled' : '') . '" href="' . ($prevDisabled ? '#' : buildUrl($page - 1, $baseParams)) . '">‹ Anterior</a>';

                // Mostrar hasta 5 páginas centradas
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($p = $start; $p <= $end; $p++) {
                    $active = ($p === $page) ? 'active' : '';
                    echo '<a class="' . $active . '" href="' . buildUrl($p, $baseParams) . '">' . $p . '</a>';
                }

                // Página siguiente
                echo '<a class="' . ($nextDisabled ? 'disabled' : '') . '" href="' . ($nextDisabled ? '#' : buildUrl($page + 1, $baseParams)) . '">Siguiente ›</a>';
                // Última página
                echo '<a class="' . ($page >= $totalPages ? 'disabled' : '') . '" href="' . ($page >= $totalPages ? '#' : buildUrl($totalPages, $baseParams)) . '">Última</a>';
                ?>
            </div>
        </main>
    </div>
    <br>

    <!--PAGINADO-->



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
                <p>Suscríbete a nuestros boletines ahora y mantente al día con nuevas colecciones y ofertas exclusivas.</p>
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
    <div class="chatbot-toggle" title="Abrir chat DISTRICARNES" aria-label="Abrir chat DISTRICARNES">
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
            <button class="close-btn" aria-label="Cerrar chat">
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
                <input type="text" class="chat-input" id="userInput" placeholder="¿Qué deseas saber sobre nuestras carnes?" autocomplete="off" />
                <button class="voice-btn" title="Entrada de voz (No implementado)">
                    <i class="fas fa-microphone"></i>
                </button>
                <button class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="quick-actions">
                <button class="quick-action" data-action="productos">
                    <i class="fas fa-drumstick-bite"></i> Ver Productos
                </button>
                <button class="quick-action" data-action="horarios">
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
    <script>
        (function() {
            function hasUserSession() {
                try {
                    const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                    if (!rawStr) return false;
                    const raw = JSON.parse(rawStr);
                    const user = raw && raw.user ? raw.user : raw;
                    const email = (user && (user.correo_electronico || user.email));
                    const id = (user && (user.id_usuario || user.id));
                    return Boolean(email || id);
                } catch (e) {
                    return false;
                }
            }

            function setBtnState(btn, favorited) {
                const icon = btn.querySelector('i');
                if (favorited) {
                    btn.classList.add('favorited');
                    if (icon) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    }
                } else {
                    btn.classList.remove('favorited');
                    if (icon) {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                }
            }

            function initFavoritesUI() {
                const buttons = document.querySelectorAll('.favorite-btn');
                const list = (window.FavoritesStore && FavoritesStore.all()) || [];
                buttons.forEach(btn => {
                    const id = btn.dataset.id;
                    const exists = list.some(i => i && String(i.id) === String(id));
                    setBtnState(btn, exists);
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (!hasUserSession()) {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Inicia sesión para usar favoritos',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                            return;
                        }
                        const id = this.dataset.id;
                        const name = this.dataset.name || (this.closest('.product-card')?.querySelector('.product-name')?.textContent?.trim() || 'Producto');
                        const price = parseFloat(this.dataset.price || (this.closest('.product-card')?.querySelector('.product-price')?.textContent?.replace(/[^0-9.,]/g, '').replace(',', '.') || '0'));
                        const image = this.dataset.image || (this.closest('.product-card')?.querySelector('.product-image img')?.src || '');

                        const currentlyFavorited = this.classList.contains('favorited');
                        if (currentlyFavorited) {
                            if (window.FavoritesStore) {
                                FavoritesStore.remove(id);
                            }
                            setBtnState(this, false);
                        } else {
                            if (window.FavoritesStore) {
                                FavoritesStore.add({
                                    id,
                                    name,
                                    price,
                                    image
                                });
                            }
                            setBtnState(this, true);
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Añadido a favoritos',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 1200,
                                    showConfirmButton: false,
                                    background: '#28a745',
                                    color: '#fff'
                                });
                            }
                        }
                    });
                });

                // React a cambios desde otras páginas
                window.addEventListener('favorites:updated', function() {
                    const fresh = (window.FavoritesStore && FavoritesStore.all()) || [];
                    document.querySelectorAll('.favorite-btn').forEach(btn => {
                        const id = btn.dataset.id;
                        const exists = fresh.some(i => i && String(i.id) === String(id));
                        setBtnState(btn, exists);
                    });
                });

                // Al cerrar sesión, limpiar estados visuales de favoritos
                window.addEventListener('auth:loggedOut', function() {
                    document.querySelectorAll('.favorite-btn').forEach(btn => setBtnState(btn, false));
                });
            }

            document.addEventListener('DOMContentLoaded', initFavoritesUI);
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AuthSystem && typeof AuthSystem.checkUserSession === 'function') {
                AuthSystem.checkUserSession();
            }
        });
    </script>
    <script src="./static/js/cart_utils.js"></script>
    <script src="./static/js/index.js"></script>
    <script src="./static/js/chatbot.js"></script>
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
    <script src="./static/js/loader.js" defer></script>
    <script src="./static/js/session_guard.js" defer></script>
    <script src="./static/js/network_guard.js" defer></script>
    <script src="<?php echo $basePath; ?>/static/js/public_tour.js"></script>
</body>
<script src="./static/js/productos.js"></script>

</html>
