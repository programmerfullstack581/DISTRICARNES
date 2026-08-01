<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
$basePath = str_replace('\\', '/', $basePath);

require_once __DIR__ . '/backend/php/conexion.php';

// Obtener ID del producto
$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$producto = null;

if ($id_producto > 0) {
    $stmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto = ?");
    $stmt->execute([$id_producto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
}

// === LÓGICA DE RESEÑAS ===
$reseñas = [];
$promedio_calificacion = 0;
$total_reseñas = 0;

try {
    // Verificar si la tabla existe
    $tableExists = $conexion->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'resenas'")->fetch();
    
    if ($tableExists) {
        // Obtener reseñas del producto
        $reseñasStmt = $conexion->prepare("
            SELECT r.*, u.nombres_completos as usuario_nombre 
            FROM resenas r 
            LEFT JOIN usuario u ON r.id_usuario = u.id_usuario 
            WHERE r.id_producto = ? AND r.estado = true 
            ORDER BY r.created_at DESC
        ");
        $reseñasStmt->execute([$id_producto]);
        $reseñas = $reseñasStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular promedio
        if (count($reseñas) > 0) {
            $total_calificacion = array_sum(array_column($reseñas, 'calificacion'));
            $promedio_calificacion = round($total_calificacion / count($reseñas), 1);
            $total_reseñas = count($reseñas);
        }
    }
} catch (PDOException $e) {
    // Tabla no existe, ignorar
}

// Manejar envío de nueva reseña
$mensaje_resena = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'agregar_resena') {
    try {
        // Verificar si la tabla existe
        $tableExists = $conexion->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'resenas'")->fetch();
        
        if (!$tableExists) {
            // Crear tabla si no existe
            $conexion->exec("
                CREATE TABLE IF NOT EXISTS resenas (
                    id_resena SERIAL PRIMARY KEY,
                    id_producto INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
                    id_usuario INTEGER NOT NULL,
                    calificacion INTEGER NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
                    titulo VARCHAR(100),
                    comentario TEXT,
                    estado BOOLEAN DEFAULT true,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $conexion->exec("CREATE INDEX IF NOT EXISTS idx_resenas_producto ON resenas(id_producto)");
            $conexion->exec("CREATE INDEX IF NOT EXISTS idx_resenas_usuario ON resenas(id_usuario)");
        }
        
        // Obtener usuario de la sesión
        $id_usuario = null;
        if (isset($_SESSION['id_usuario'])) {
            $id_usuario = intval($_SESSION['id_usuario']);
        } elseif (isset($_POST['id_usuario'])) {
            $id_usuario = intval($_POST['id_usuario']);
        }
        
        if ($id_usuario && $id_producto > 0) {
            $calificacion = intval($_POST['calificacion']);
            $titulo = trim($_POST['titulo'] ?? '');
            $comentario = trim($_POST['comentario'] ?? '');
            
            if ($calificacion >= 1 && $calificacion <= 5) {
                $insertStmt = $conexion->prepare("
                    INSERT INTO resenas (id_producto, id_usuario, calificacion, titulo, comentario)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertStmt->execute([$id_producto, $id_usuario, $calificacion, $titulo, $comentario]);
                $mensaje_resena = '¡Gracias! Tu reseña ha sido publicada.';
                $tipo_mensaje = 'success';
                
                // Recargar reseñas
                $reseñasStmt = $conexion->prepare("
                    SELECT r.*, u.nombres_completos as usuario_nombre 
                    FROM resenas r 
                    LEFT JOIN usuario u ON r.id_usuario = u.id_usuario 
                    WHERE r.id_producto = ? AND r.estado = true 
                    ORDER BY r.created_at DESC
                ");
                $reseñasStmt->execute([$id_producto]);
                $reseñas = $reseñasStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($reseñas) > 0) {
                    $total_calificacion = array_sum(array_column($reseñas, 'calificacion'));
                    $promedio_calificacion = round($total_calificacion / count($reseñas), 1);
                    $total_reseñas = count($reseñas);
                }
            } else {
                $mensaje_resena = 'Por favor selecciona una calificación.';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje_resena = 'Debes iniciar sesión para dejar una reseña.';
            $tipo_mensaje = 'error';
        }
    } catch (PDOException $e) {
        $mensaje_resena = 'Error al guardar la reseña. Intenta de nuevo.';
        $tipo_mensaje = 'error';
    }
}

// Redirigir si no existe
if (!$producto) {
    header("Location: productos.php");
    exit;
}

// === LÓGICA DE IMÁGENES (Idéntica a productos.php) ===
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
function imageForCategory($cat)
{
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
function deriveCategoryFromName($name)
{
    $n = mb_strtolower(trim((string)$name), 'UTF-8');
    if (preg_match('/(res|vaca|ternera|carne\s*de\s*res)/i', $n))
        return 'res';
    if (preg_match('/(cerdo|puerco|chancho)/i', $n))
        return 'cerdo';
    if (preg_match('/(pollo|gallina|pechuga|muslo)/i', $n))
        return 'pollo';
    if (preg_match('/(pescado|robalo|bagre|mojarra|tilapia)/i', $n))
        return 'pescado';
    return 'otros';
}
function imageFromRow(array $row)
{
    $candidates = ['imagen', 'image', 'imagen_url', 'image_url', 'foto', 'imagen_producto', 'url_imagen'];
    $img = null;
    foreach ($candidates as $c) {
        if (isset($row[$c]) && trim((string)$row[$c]) !== '') {
            $img = (string)$row[$c];
            break;
        }
    }
    if ($img === null)
        return null;
    $img = str_replace('\\', '/', $img);
    if (preg_match('#^https?://#i', $img))
        return $img;

    $rootDir = __DIR__;
    $pos = strpos($img, 'static/images');
    if ($pos !== false) {
        return base_prefix() . '/' . substr($img, $pos);
    }
    return base_prefix() . '/' . ltrim($img, '/');
}

// Obtener imagen final
$cat = deriveCategoryFromName($producto['nombre']);
$imagen_producto = imageFromRow($producto);
if (!$imagen_producto) {
    $imagen_producto = imageForCategory($cat);
}

$modelsDirRel = 'assets/models/products/';
$glbRel = $modelsDirRel . $id_producto . '.glb';
$usdzRel = $modelsDirRel . $id_producto . '.usdz';
$glbAbs = __DIR__ . '/' . $glbRel;
$usdzAbs = __DIR__ . '/' . $usdzRel;
$model_glb_url = null;
$model_usdz_url = null;
if (file_exists($glbAbs)) {
    $model_glb_url = base_prefix() . '/' . $glbRel;
}
if (file_exists($usdzAbs)) {
    $model_usdz_url = base_prefix() . '/' . $usdzRel;
}

// === PRODUCTOS RELACIONADOS ===
$relatedProducts = [];
// Buscar productos de la misma categoría (basado en nombre o id_categoria si existiera)
// Usamos el nombre para derivar categoría
$relatedStmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto != ? AND (LOWER(nombre) LIKE ? OR LOWER(nombre) LIKE ?) LIMIT 4");
$catTerm = '%' . $cat . '%';
// Fallback simple: buscar por categoría derivada
$relatedStmt->execute([$id_producto, $catTerm, $catTerm]);
$relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

// Si hay pocos, rellenar con cualquiera
if (count($relatedProducts) < 4) {
    $moreStmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto != ? ORDER BY RANDOM() LIMIT " . (4 - count($relatedProducts)));
    $moreStmt->execute([$id_producto]);
    while ($r = $moreStmt->fetch(PDO::FETCH_ASSOC)) {
        // Evitar duplicados si ya estaba
        $exists = false;
        foreach ($relatedProducts as $rp)
            if ($rp['id_producto'] == $r['id_producto'])
                $exists = true;
        if (!$exists)
            $relatedProducts[] = $r;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - Detalles</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/header_en_general.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/responsive.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/base.css" />
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/chatbot.css" />
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/assets/icon/image-removebg-preview sin fondo (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/static/css/tailwind.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <style>
        body {
            background-color: #000000;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .product-detail-container {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 4rem;
            align-items: start;
        }

        /* Columna Izquierda: Imagen */
        .detail-image-wrapper {
            background: #111;
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid #333;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(20,20,20,0.8);
        }

        .viewer-tabs {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            z-index: 20;
        }
        .viewer-tab {
            padding: 6px 12px;
            border-radius: 8px;
            background: #1a1a1a;
            border: 1px solid #333;
            color: #ccc;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .viewer-tab.active {
            background: #ff0000;
            color: #fff;
            border-color: #ff0000;
        }
        .viewer-tab.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .detail-image-wrapper img {
            width: 100%;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
            max-height: 500px;
        }

        .detail-image-wrapper:hover img {
            transform: scale(1.05);
        }

        model-viewer.product-model {
            width: 100%;
            height: 500px;
            background: #111;
            border-radius: 20px;
            border: 1px solid #333;
            display: none;
        }

        /* Pseudo 3D (tilt) sobre imagen */
        .product-image-large.tilt-enabled {
            will-change: transform;
            transition: transform 80ms linear, box-shadow 200ms ease;
            cursor: grab;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        }
        .product-image-large.tilt-grabbing {
            cursor: grabbing;
        }

        .badges {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }

        .badge-category {
            background: #ff0000;
            color: white;
        }
        
        .badge-stock {
            background: #28a745;
            color: white;
        }

        /* Columna Derecha: Info */
        .product-info-detail {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .breadcrumb-detail {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: -10px;
        }

        .breadcrumb-detail a {
            color: #ff0000;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb-detail a:hover {
            color: #fff;
        }

        .product-title-large {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.1;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .product-price-large {
            font-size: 2.8rem;
            color: #ff0000;
            font-weight: bold;
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .iva-text {
            font-size: 1rem;
            color: #666;
            font-weight: normal;
        }

        .product-description-full {
            color: #ccc;
            line-height: 1.8;
            font-size: 1.1rem;
            border-bottom: 1px solid #333;
            padding-bottom: 1.5rem;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            background: #111;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #333;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #aaa;
        }

        .meta-item i {
            color: #ff0000;
            font-size: 1.2rem;
        }

        .meta-item strong {
            color: #fff;
        }

        /* Controles de Compra */
        .purchase-controls {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #0a0a0a;
            padding: 20px;
            border-radius: 15px;
            border: 1px solid #222;
        }

        .qty-selector-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .qty-label {
            font-weight: bold;
            color: #ccc;
        }

        .qty-selector-large {
            display: flex;
            align-items: center;
            background: #000;
            border: 1px solid #333;
            border-radius: 8px;
            width: fit-content;
        }

        .qty-btn-large {
            width: 40px;
            height: 40px;
            background: #1a1a1a;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .qty-btn-large:hover {
            background: #ff0000;
        }

        .qty-input-large {
            width: 50px;
            background: none;
            border: none;
            border-left: 1px solid #333;
            border-right: 1px solid #333;
            color: white;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .qty-input-large:focus {
            outline: none;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 15px;
        }

        .btn-large {
            padding: 1rem;
            border-radius: 8px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
        }

        .btn-add-cart-large {
            background: transparent;
            color: #fff;
            border: 2px solid #ff0000;
        }

        .btn-add-cart-large:hover {
            background: #ff0000;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }

        .btn-buy-now {
            background: #ff0000;
            color: white;
            border: 2px solid #ff0000;
        }

        .btn-buy-now:hover {
            background: #cc0000;
            border-color: #cc0000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.4);
        }

        /* Responsive Detalle Producto */
        @media (max-width: 992px) {
            .product-detail-container {
                grid-template-columns: 1fr;
                gap: 2rem;
                margin: 2rem auto;
            }
            .product-title-large {
                font-size: 2.5rem;
            }
            .detail-image-wrapper img, model-viewer.product-model {
                max-height: 400px;
            }
        }

        @media (max-width: 768px) {
            .product-title-large {
                font-size: 2rem;
            }
            .product-price-large {
                font-size: 2.2rem;
            }
            .meta-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                grid-template-columns: 1fr;
            }
            .detail-image-wrapper {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .product-title-large {
                font-size: 1.8rem;
            }
            .product-price-large {
                font-size: 2rem;
            }
            .qty-selector-wrapper {
                flex-direction: column;
                align-items: flex-start;
            }
            .badges {
                top: 10px;
                left: 10px;
            }
            .badge {
                font-size: 0.8rem;
                padding: 4px 8px;
            }
        }
        
        /* Productos Relacionados */
        .related-products {
            max-width: 1200px;
            margin: 0 auto 4rem auto;
            padding: 0 20px;
        }
        
        .related-title {
            font-size: 2rem;
            color: #fff;
            margin-bottom: 2rem;
            border-bottom: 2px solid #333;
            padding-bottom: 1rem;
            display: inline-block;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .related-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            border-color: #ff0000;
        }
        
        .related-img {
            height: 180px;
            width: 100%;
            object-fit: cover;
        }
        
        .related-info {
            padding: 1rem;
        }
        
        .related-name {
            font-size: 1.1rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .related-price {
            color: #ff0000;
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Responsivo */
        @media (max-width: 900px) {
            .product-detail-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .product-title-large {
                font-size: 2.5rem;
            }
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* === ESTILOS DE RESEÑAS === */
        .reviews-section {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 20px;
        }

        .reviews-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .reviews-title {
            font-size: 2rem;
            color: #fff;
            border-bottom: 2px solid #333;
            padding-bottom: 1rem;
        }

        .reviews-summary {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #111;
            padding: 15px 25px;
            border-radius: 10px;
            border: 1px solid #333;
        }

        .reviews-average {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff0000;
        }

        .reviews-stars {
            color: #ffc107;
            font-size: 1.3rem;
        }

        .reviews-count {
            color: #888;
            font-size: 0.9rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .review-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 1.5rem;
            transition: border-color 0.3s;
        }

        .review-card:hover {
            border-color: #ff0000;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 10px;
        }

        .review-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .review-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 1rem;
        }

        .review-user-name {
            color: #fff;
            font-weight: 600;
        }

        .review-date {
            color: #666;
            font-size: 0.85rem;
        }

        .review-rating {
            color: #ffc107;
            font-size: 1rem;
        }

        .review-title {
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .review-comment {
            color: #ccc;
            line-height: 1.6;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-reviews i {
            font-size: 3rem;
            color: #333;
            margin-bottom: 1rem;
        }

        /* Formulario de Reseñas */
        .review-form-container {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .review-form-title {
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #333;
            padding-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: #ccc;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            background: #000;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #ff0000;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .rating-select {
            display: flex;
            gap: 10px;
        }

        .rating-star {
            font-size: 2rem;
            color: #444;
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }

        .rating-star:hover,
        .rating-star.active {
            color: #ffc107;
            transform: scale(1.1);
        }

        .btn-submit-review {
            background: #ff0000;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit-review:hover {
            background: #cc0000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }

        .login-prompt {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin-top: 2rem;
        }

        .login-prompt p {
            color: #ccc;
            margin-bottom: 1rem;
        }

        .login-prompt a {
            color: #ff0000;
            text-decoration: none;
            font-weight: 600;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .reviews-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .reviews-summary {
                width: 100%;
                justify-content: center;
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
            @media (max-width:768px){
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
            @media (min-width:769px){
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
                    <a href="./productos.php" class="active">Productos</a>
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
                <a href="./productos.php" class="active">Productos</a>
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
                        if (!dropdown || !button) return;
                        
                        const isOpen = dropdown.classList.contains('active');

                        // Cerrar todos los dropdowns primero
                        document.querySelectorAll('.user-dropdown').forEach(d => d.classList.remove('active'));
                        document.querySelectorAll('.menu-button').forEach(b => b.setAttribute('aria-expanded', 'false'));

                        if (!isOpen) {
                            dropdown.classList.add('active');
                            dropdown.style.display = 'block';
                            button.setAttribute('aria-expanded', 'true');
                        } else {
                            dropdown.style.display = 'none';
                        }
                    }

                    document.addEventListener('click', function (event) {
                        const container = document.querySelector('.user-profile-container');
                        if (!container || !container.contains(event.target)) {
                            const dd = document.getElementById('userDropdown');
                            if (dd) {
                                dd.classList.remove('active');
                                dd.style.display = 'none';
                            }
                            const btn = document.querySelector('.menu-button');
                            if (btn) btn.setAttribute('aria-expanded', 'false');
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            const dd = document.getElementById('userDropdown');
                            if (dd) {
                                dd.classList.remove('active');
                                dd.style.display = 'none';
                            }
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
                        var isMobile = window.matchMedia('(max-width: 768px)').matches;
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
    

    <!-- Contenido Detalle -->
    <div class="product-detail-container product-detail"> <!-- clase product-detail añadida para cart_utils -->
        
        <!-- Columna Izquierda -->
        <div class="detail-image-wrapper">
            <div class="badges">
                <span class="badge badge-category"><?php echo htmlspecialchars(ucfirst($cat)); ?></span>
                <?php if ($producto['stock'] > 0): ?>
                    <span class="badge badge-stock">En Stock</span>
                <?php
else: ?>
                    <span class="badge" style="background: #666;">Agotado</span>
                <?php
endif; ?>
            </div>
            <div class="viewer-tabs">
                <button class="viewer-tab active" data-view="image">Imagen</button>
                <button class="viewer-tab" 
                        data-view="3d">
                    Vista 3D
                </button>
            </div>
            <img id="productImage" src="<?php echo htmlspecialchars($imagen_producto); ?>" class="product-image-large" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
            <?php if ($model_glb_url): ?>
            <model-viewer id="productModel"
                          class="product-model"
                          src="<?php echo htmlspecialchars($model_glb_url); ?>"
                          <?php if ($model_usdz_url) {
        echo 'ios-src="' . htmlspecialchars($model_usdz_url) . '"';
    }?>
                          shadow-intensity="1"
                          camera-controls
                          auto-rotate
                          ar
                          ar-modes="webxr scene-viewer quick-look"
                          exposure="1.0"
                          disable-zoom="false">
            </model-viewer>
            <?php
endif; ?>
        </div>

        <!-- Columna Derecha -->
        <div class="product-info-detail">
            <div class="breadcrumb-detail">
                <a href="index.php">Inicio</a> / <a href="productos.php">Productos</a> / <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
            </div>

            <h1 class="product-title-large product-title"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
            <!--estilos de las estrellas de calificacion -->
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="color: #ffc107; font-size: 1.2rem;">
                    <?php 
                    if ($promedio_calificacion > 0) {
                        $fullStars = floor($promedio_calificacion);
                        $hasHalf = ($promedio_calificacion - $fullStars) >= 0.5;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $fullStars) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i == $fullStars + 1 && $hasHalf) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                    } else {
                        echo '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
                    }
                    ?>
                </div>
                <span style="color: #888; font-size: 0.9rem;">(<?php echo $promedio_calificacion > 0 ? $promedio_calificacion : '0'; ?> de 5) • <?php echo $total_reseñas; ?> reseñas</span>
            </div>

            <div class="product-price-large product-price-detail">
                $<?php echo number_format($producto['precio_venta'], 0, ',', '.'); ?>
                <span class="iva-text">IVA incluido</span>
            </div>

            <div class="product-description-full">
                <?php echo nl2br(htmlspecialchars($producto['descripcion'] ?? 'Sin descripción detallada.')); ?>
                <br><br>
                <p class="text-gray-400 text-sm">
                    * Las imágenes son referenciales. El peso final puede variar ligeramente.
                </p>
            </div>

            <div class="meta-grid">
                <div class="meta-item">
                    <i class="fas fa-box-open"></i>
                    <div>
                        <span>Disponibilidad:</span>
                        <strong style="color: #4ade80;"><?php echo $producto['stock']; ?> unidades</strong>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-barcode"></i>
                    <div>
                        <span>Código:</span>
                        <strong><?php echo str_pad($producto['id_producto'], 6, '0', STR_PAD_LEFT); ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-weight-hanging"></i>
                    <div>
                        <span>Venta por:</span>
                        <strong>Unidad / Kg</strong>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-truck"></i>
                    <div>
                        <span>Entrega:</span>
                        <strong>Inmediata</strong>
                    </div>
                </div>
            </div>

            <div class="purchase-controls">
                <div class="action-buttons" style="grid-template-columns: 1fr;">
                    <button class="btn-large btn-add-cart-large add-to-cart" 
                            data-id="<?php echo $producto['id_producto']; ?>"
                            data-title="<?php echo htmlspecialchars($producto['nombre']); ?>"
                            data-price="<?php echo $producto['precio_venta']; ?>"
                            data-image="<?php echo htmlspecialchars($imagen_producto); ?>">
                        <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Reseñas -->
    <div class="reviews-section">
        <div class="reviews-header">
            <h2 class="reviews-title">Reseñas de Clientes</h2>
            <div class="reviews-summary">
                <span class="reviews-average"><?php echo $promedio_calificacion > 0 ? $promedio_calificacion : '0'; ?></span>
                <div>
                    <div class="reviews-stars">
                        <?php 
                        if ($promedio_calificacion > 0) {
                            $fullStars = floor($promedio_calificacion);
                            $hasHalf = ($promedio_calificacion - $fullStars) >= 0.5;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $fullStars) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i == $fullStars + 1 && $hasHalf) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                        } else {
                            echo '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <span class="reviews-count"><?php echo $total_reseñas; ?> reseñas</span>
                </div>
            </div>
        </div>

        <!-- Lista de Reseñas -->
        <div class="reviews-grid">
            <?php if (count($reseñas) > 0): ?>
                <?php foreach ($reseñas as $resena): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-user">
                                <div class="review-avatar">
                                    <?php echo strtoupper(substr($resena['usuario_nombre'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="review-user-name"><?php echo htmlspecialchars($resena['usuario_nombre'] ?? 'Usuario'); ?></div>
                                    <div class="review-date"><?php echo date('d M Y', strtotime($resena['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $resena['calificacion']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if (!empty($resena['titulo'])): ?>
                            <div class="review-title"><?php echo htmlspecialchars($resena['titulo']); ?></div>
                        <?php endif; ?>
                        <div class="review-comment"><?php echo nl2br(htmlspecialchars($resena['comentario'] ?? 'Sin comentario')); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-reviews">
                    <i class="fas fa-comments"></i>
                    <h3 style="color: #fff; margin-bottom: 0.5rem;">Aún no hay reseñas</h3>
                    <p>Sé el primero en dejar tu opinión sobre este producto</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Formulario de Reseña -->
        <?php 
        // Verificar si el usuario está logueado (se detectará con JavaScript en el cliente)
        $usuario_logueado = false;
        $id_usuario_actual = 0;
        ?>

        <?php 
        // Determinar si mostrar formulario o prompt de login usando JavaScript
        ?>
            <div class="review-form-container" id="reviewFormContainer" style="display: none;">
                <h3 class="review-form-title">Deja tu Reseña</h3>
                
                <?php if ($mensaje_resena): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                        <?php echo htmlspecialchars($mensaje_resena); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="resenaForm">
                    <input type="hidden" name="action" value="agregar_resena">
                    <input type="hidden" name="id_usuario" id="resenaIdUsuario" value="0">
                    
                    <div class="form-group">
                        <label class="form-label">Calificación *</label>
                        <div class="rating-select" id="ratingSelect">
                            <i class="rating-star fas fa-star" data-value="1"></i>
                            <i class="rating-star fas fa-star" data-value="2"></i>
                            <i class="rating-star fas fa-star" data-value="3"></i>
                            <i class="rating-star fas fa-star" data-value="4"></i>
                            <i class="rating-star fas fa-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="calificacion" id="calificacionInput" value="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Título de tu reseña</label>
                        <input type="text" name="titulo" class="form-input" placeholder="Ej: Excelente calidad de carne" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tu opinión *</label>
                        <textarea name="comentario" class="form-textarea" placeholder="Comparte tu experiencia con este producto..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit-review">
                        <i class="fas fa-paper-plane"></i> Publicar Reseña
                    </button>
                </form>
            </div>
            <div class="login-prompt" id="loginPromptDiv">
                <i class="fas fa-user-lock" style="font-size: 2rem; color: #ff0000; margin-bottom: 1rem;"></i>
                <p>Debes iniciar sesión para dejar una reseña</p>
                <a href="./login/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
                <span style="color: #666;"> | </span>
                <a href="./login/register.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                    Registrarse
                </a>
            </div>
    </div>

    <!-- Productos Relacionados -->
    <?php if (count($relatedProducts) > 0): ?>
    <div class="related-products">
        <h2 class="related-title">También te podría gustar</h2>
        <div class="related-grid">
            <?php foreach ($relatedProducts as $rel):
        $relImg = imageFromRow($rel);
        if (!$relImg) {
            $relCat = deriveCategoryFromName($rel['nombre']);
            $relImg = imageForCategory($relCat);
        }
?>
            <a href="detalle_producto.php?id=<?php echo $rel['id_producto']; ?>" class="related-card" style="text-decoration: none;">
                <img src="<?php echo htmlspecialchars($relImg); ?>" alt="<?php echo htmlspecialchars($rel['nombre']); ?>" class="related-img">
                <div class="related-info">
                    <div class="related-name"><?php echo htmlspecialchars($rel['nombre']); ?></div>
                    <div class="related-price">$<?php echo number_format($rel['precio_venta'], 0, ',', '.'); ?></div>
                </div>
            </a>
            <?php
    endforeach; ?>
        </div>
    </div>
    <?php
endif; ?>

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
                <input type="text" class="chat-input" id="userInput" placeholder="¿Qué deseas saber sobre nuestras carnes?"
                       onkeypress="handleKeyPress(event)" autocomplete="off" />
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

    <!-- Scripts Esenciales -->
    <script src="./static/js/header_actions.js"></script>
    <script src="./js/auth.js"></script>
    <script src="./static/js/cart_badge.js"></script>
    <script src="./static/js/auth_utils.js"></script>
    <script src="./static/js/cart_utils.js"></script>
    <script src="./static/js/product-3d-viewer.js"></script>
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
    <style>
      /* Desactivar sticky del header solo en detalle de producto */
      .header { position: static !important; top: auto !important; }
    </style>
    
    <script>
        function updateQty(change) {
            const input = document.querySelector('.qty-input-large');
            let val = parseInt(input.value);
            const max = parseInt(input.getAttribute('max')) || 100;
            val += change;
            if (val < 1) val = 1;
            if (val > max) val = max;
            input.value = val;
            
            // Actualizar data-qty del botón
            const btn = document.querySelector('.add-to-cart');
            if(btn) btn.dataset.qty = val;
        }

        function buyNow() {
            // Simular clic en agregar al carrito y luego redirigir
            const addBtn = document.querySelector('.add-to-cart');
            if(addBtn) {
                // Verificar sesión antes de acción
                const userData = localStorage.getItem('userData');
                const sessionData = sessionStorage.getItem('currentSession');
                
                if(!userData && !sessionData) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Debes iniciar sesión',
                        text: 'Para comprar, primero ingresa a tu cuenta.',
                        confirmButtonText: 'Ir a Login',
                        showCancelButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = './login/login.php';
                        }
                    });
                    return;
                }
                
                // Si hay sesión, agregar y redirigir
                addBtn.click();
                setTimeout(() => {
                     window.location.href = './carrito-de-compras/index.php';
                }, 800);
            }
        }
        
        // Verificar sesión al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const userData = localStorage.getItem('userData');
            const sessionData = sessionStorage.getItem('currentSession');
            
            const authBtns = document.getElementById('authButtons');
            const userBtns = document.getElementById('userLoggedButtons');
            
            if(userData || sessionData) {
                if(authBtns) authBtns.style.display = 'none';
                if(userBtns) userBtns.style.display = 'block';
                
                // Cargar nombre
                try {
                    const u = JSON.parse(userData || sessionData);
                    const name = u.nombre || u.nombres_completos || 'Usuario';
                    const initials = name.charAt(0).toUpperCase();
                    document.getElementById('userName').textContent = name;
                    document.getElementById('userAvatar').textContent = initials;
                } catch(e) {}
            } else {
                if(authBtns) authBtns.style.display = 'flex';
                if(userBtns) userBtns.style.display = 'none';
            }
        });
        
        // === FUNCIONALIDAD DE RESEÑAS ===
        document.addEventListener('DOMContentLoaded', function() {
            // Detectar usuario logueado y mostrar formulario
            const reviewFormContainer = document.getElementById('reviewFormContainer');
            const loginPromptDiv = document.getElementById('loginPromptDiv');
            
            let isLoggedIn = false;
            
            try {
                const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                if (rawStr) {
                    const raw = JSON.parse(rawStr);
                    const user = raw && raw.user ? raw.user : raw;
                    const userId = user && (user.id || user.id_usuario);
                    
                    if (userId) {
                        // Usuario logueado - mostrar formulario
                        isLoggedIn = true;
                        if (reviewFormContainer) reviewFormContainer.style.display = 'block';
                        if (loginPromptDiv) loginPromptDiv.style.display = 'none';
                        
                        // Llenar el input oculto con el ID del usuario
                        const idInput = document.getElementById('resenaIdUsuario');
                        if (idInput) idInput.value = userId;
                    } else {
                        // No hay usuario válido
                        if (reviewFormContainer) reviewFormContainer.style.display = 'none';
                        if (loginPromptDiv) loginPromptDiv.style.display = 'block';
                    }
                } else {
                    // No hay sesión
                    if (reviewFormContainer) reviewFormContainer.style.display = 'none';
                    if (loginPromptDiv) loginPromptDiv.style.display = 'block';
                }
            } catch (e) {
                if (reviewFormContainer) reviewFormContainer.style.display = 'none';
                if (loginPromptDiv) loginPromptDiv.style.display = 'block';
            }

            const ratingStars = document.querySelectorAll('.rating-star');
            const calificacionInput = document.getElementById('calificacionInput');
            
            if (ratingStars.length > 0) {
                ratingStars.forEach(star => {
                    star.addEventListener('click', function() {
                        const value = parseInt(this.dataset.value);
                        calificacionInput.value = value;
                        
                        ratingStars.forEach(s => {
                            if (parseInt(s.dataset.value) <= value) {
                                s.classList.add('active');
                            } else {
                                s.classList.remove('active');
                            }
                        });
                    });
                    
                    star.addEventListener('mouseenter', function() {
                        const value = parseInt(this.dataset.value);
                        ratingStars.forEach(s => {
                            if (parseInt(s.dataset.value) <= value) {
                                s.style.color = '#ffc107';
                            }
                        });
                    });
                    
                    star.addEventListener('mouseleave', function() {
                        ratingStars.forEach(s => {
                            if (!s.classList.contains('active')) {
                                s.style.color = '#444';
                            }
                        });
                    });
                });
            }

            // Validar formulario de reseñas
            const resenaForm = document.getElementById('resenaForm');
            if (resenaForm) {
                resenaForm.addEventListener('submit', function(e) {
                    const calificacion = parseInt(calificacionInput.value);
                    if (calificacion < 1 || isNaN(calificacion)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Calificación requerida',
                            text: 'Por favor selecciona una calificación de 1 a 5 estrellas',
                            confirmButtonColor: '#ff0000'
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>
