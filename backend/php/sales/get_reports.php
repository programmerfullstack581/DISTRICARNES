<?php
require __DIR__ . '/../core/conexion.php';

header('Content-Type: application/json');

// Parámetros de filtro
$range = $_GET['range'] ?? 'week';
$start = $_GET['start'] ?? null;
$end   = $_GET['end']   ?? null;

// Calcular fechas
$today = date('Y-m-d');
$startDate = $start;
$endDate = $end ?: $today;

if (!$start) {
    if ($range === 'today') {
        $startDate = $today;
    } elseif ($range === 'week') {
        $startDate = date('Y-m-d', strtotime('-1 week'));
    } elseif ($range === 'month') {
        $startDate = date('Y-m-01');
    } elseif ($range === 'year') {
        $startDate = date('Y-01-01');
    } else {
        $startDate = date('Y-m-d', strtotime('-1 week'));
    }
}

// Helper para detectar columnas
function colExists($pdo, $table, $col) {
    try {
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = ? AND column_name = ?");
        $stmt->execute([$table, $col]);
        return $stmt->fetch() !== false;
    } catch (Exception $e) { return false; }
}

// Helper para detectar tablas
function tableExists($pdo, $table) {
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_name = ?");
        $stmt->execute([$table]);
        return $stmt->fetch() !== false;
    } catch (Exception $e) { return false; }
}

// Detectar nombres de columnas clave
$userDateCol = colExists($conexion, 'usuario', 'created_at') ? 'created_at' : 'fecha_registro';
$userRoleCol = colExists($conexion, 'usuario', 'rol') ? 'rol' : 'role';

// 1. VENTAS
$salesData = [];
$totalSales = 0;
$totalOrders = 0;

try {
    // Usamos orders_pg
    $stmt = $conexion->prepare("
        SELECT DATE(created_at) as fecha, SUM(total) as total, COUNT(*) as count 
        FROM orders_pg 
        WHERE created_at BETWEEN :start AND :end AND status != 'CANCELLED'
        GROUP BY DATE(created_at) 
        ORDER BY fecha ASC
    ");
    $stmt->execute(['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']);
    $salesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($salesRows as $row) {
        $totalSales += $row['total'];
        $totalOrders += $row['count'];
    }
    
    // Rellenar días
    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day')
    );
    
    $labels = [];
    $dataPoints = [];
    
    foreach ($period as $dt) {
        $d = $dt->format('Y-m-d');
        $found = false;
        foreach ($salesRows as $r) {
            if ($r['fecha'] === $d) {
                $labels[] = $dt->format('D d');
                $dataPoints[] = $r['total'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $labels[] = $dt->format('D d');
            $dataPoints[] = 0;
        }
    }
    
    $salesData = [
        'labels' => $labels,
        'data' => $dataPoints,
        'total' => $totalSales,
        'orders' => $totalOrders,
        'avg' => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0
    ];

} catch (Exception $e) {
    $salesData = ['error' => $e->getMessage(), 'labels' => [], 'data' => [], 'total' => 0, 'orders' => 0, 'avg' => 0];
}

// 2. PRODUCTOS
$productsData = [];
try {
    // Detectar columna de categoría en producto y existencia de tabla categorías
    $catIdCol = colExists($conexion, 'producto', 'id_categoria') ? 'id_categoria' : (colExists($conexion, 'producto', 'categoria_id') ? 'categoria_id' : null);
    $catTable = null;
    foreach (['categorias', 'categoria', 'categories', 'category'] as $t) {
        if (tableExists($conexion, $t)) { $catTable = $t; break; }
    }

    // Construir filas de categorías
    if ($catTable && $catIdCol) {
        $catNameCol = colExists($conexion, $catTable, 'nombre_categoria') ? 'nombre_categoria' : (
                        colExists($conexion, $catTable, 'nombre') ? 'nombre' : (
                        colExists($conexion, $catTable, 'name') ? 'name' : null));
        $catPkCol   = colExists($conexion, $catTable, 'id_categoria') ? 'id_categoria' : (colExists($conexion, $catTable, 'id') ? 'id' : null);
        if ($catNameCol && $catPkCol) {
            $stmt = $conexion->query("
                SELECT c.\"$catNameCol\" as cat, COUNT(p.\"$catIdCol\") as count 
                FROM $catTable c 
                LEFT JOIN producto p ON p.\"$catIdCol\" = c.\"$catPkCol\" 
                GROUP BY c.\"$catPkCol\", c.\"$catNameCol\"
            ");
            $catRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $catRows = [];
        }
    } else {
        // Fallback: agrupar por columna de texto en producto si existe
        $catTextCol = colExists($conexion, 'producto', 'categoria') ? 'categoria' : (colExists($conexion, 'producto', 'categoria_nombre') ? 'categoria_nombre' : null);
        if ($catTextCol) {
            $stmt = $conexion->query("SELECT \"$catTextCol\" as cat, COUNT(*) as count FROM producto GROUP BY \"$catTextCol\"");
            $catRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Último recurso: contar distintas categorías por ID si existe catIdCol
            if ($catIdCol) {
                $stmt = $conexion->query("SELECT COALESCE(CAST(\"$catIdCol\" AS TEXT), 'Sin Categoría') as cat, COUNT(*) as count FROM producto GROUP BY \"$catIdCol\"");
                $catRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $catRows = [];
            }
        }
    }
    
    $catLabels = [];
    $catData = [];
    $totalCats = count($catRows);
    
    foreach ($catRows as $r) {
        $catLabels[] = $r['cat'] ?: 'Sin Categoría';
        $catData[] = $r['count'];
    }
    
    // Stock Bajo
    // Detectar columnas de stock y stock_minimo
    $stockCol = colExists($conexion, 'producto', 'stock') ? 'stock' : (colExists($conexion, 'producto', 'existencias') ? 'existencias' : (colExists($conexion, 'producto', 'cantidad') ? 'cantidad' : null));
    $stockMinCol = colExists($conexion, 'producto', 'stock_minimo') ? 'stock_minimo' : null;
    $lowStock = 0;
    if ($stockCol) {
        if ($stockMinCol) {
            $stmt = $conexion->query("SELECT COUNT(*) as low FROM producto WHERE \"$stockCol\" <= \"$stockMinCol\"");
            $lowStock = $stmt->fetch(PDO::FETCH_ASSOC)['low'];
        } else {
            $stmt = $conexion->query("SELECT COUNT(*) as low FROM producto WHERE \"$stockCol\" <= 5");
            $lowStock = $stmt->fetch(PDO::FETCH_ASSOC)['low'];
        }
    } else {
        $lowStock = 0;
    }
    
    // Top Producto (filtrado por rango, join con orders_pg)
    $stmt = $conexion->prepare("
        SELECT oi.title as nombre, SUM(oi.qty) as sold
        FROM order_items_pg oi
        JOIN orders_pg o ON o.id = oi.order_id
        WHERE o.created_at BETWEEN :start AND :end AND o.status != 'CANCELLED'
        GROUP BY oi.title
        ORDER BY sold DESC
        LIMIT 1
    ");
    $stmt->execute(['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']);
    $topProd = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $productsData = [
        'catLabels' => $catLabels,
        'catData' => $catData,
        'totalCats' => $totalCats,
        'lowStock' => $lowStock,
        'topProduct' => $topProd ? $topProd['nombre'] : 'N/A',
        'topSold' => $topProd ? $topProd['sold'] : 0
    ];

} catch (Exception $e) {
    $productsData = ['error' => $e->getMessage(), 'catLabels' => [], 'catData' => [], 'totalCats' => 0, 'lowStock' => 0, 'topProduct' => 'N/A', 'topSold' => 0];
}

// 3. CLIENTES
$customersData = [];
try {
    // Nuevos
    $stmt = $conexion->prepare("SELECT COUNT(*) as count FROM usuario WHERE \"$userDateCol\" BETWEEN :start AND :end AND \"$userRoleCol\" != 'admin'");
    $stmt->execute(['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']);
    $newCust = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Recurrentes (por email en orders_pg ya que user_id no siempre está)
    $stmt = $conexion->query("
        SELECT COUNT(*) as count FROM (
            SELECT user_email FROM orders_pg WHERE user_email IS NOT NULL GROUP BY user_email HAVING COUNT(*) > 1
        ) as recurring
    ");
    $recurringCust = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total clientes
    $stmt = $conexion->query("SELECT COUNT(*) as count FROM usuario WHERE \"$userRoleCol\" != 'admin'");
    $totalCust = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $retention = $totalCust > 0 ? round(($recurringCust / $totalCust) * 100, 1) : 0;
    
    $customersData = [
        'new' => $newCust,
        'recurring' => $recurringCust,
        'vip' => 0,
        'retention' => $retention
    ];
} catch (Exception $e) {
    $customersData = ['error' => $e->getMessage(), 'new' => 0, 'recurring' => 0, 'vip' => 0, 'retention' => 0];
}

// 4. INVENTARIO
$inventoryData = [];
try {
    // Detectar columna stock
    $stockCol = colExists($conexion, 'producto', 'stock') ? 'stock' : (colExists($conexion, 'producto', 'existencias') ? 'existencias' : 'cantidad');
    
    // Valor total (precio_compra_lote o precio_compra * stock)
    $buyPriceCol = colExists($conexion, 'producto', 'precio_compra_lote') ? 'precio_compra_lote' : (colExists($conexion, 'producto', 'precio_compra') ? 'precio_compra' : 'precio');
    
    $stmt = $conexion->query("SELECT SUM(\"$buyPriceCol\" * \"$stockCol\") as total_val, COUNT(*) as total_items FROM producto");
    $invRow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conexion->query("
        SELECT 
            SUM(CASE WHEN \"$stockCol\" >= 20 THEN 1 ELSE 0 END) as high,
            SUM(CASE WHEN \"$stockCol\" >= 10 AND \"$stockCol\" < 20 THEN 1 ELSE 0 END) as medium,
            SUM(CASE WHEN \"$stockCol\" >= 5 AND \"$stockCol\" < 10 THEN 1 ELSE 0 END) as low,
            SUM(CASE WHEN \"$stockCol\" < 5 THEN 1 ELSE 0 END) as critical
        FROM producto
    ");
    $invStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $inventoryData = [
        'value' => $invRow['total_val'] ?: 0,
        'items' => $invRow['total_items'] ?: 0,
        'status' => [
            $invStatus['high'] ?? 0,
            $invStatus['medium'] ?? 0,
            $invStatus['low'] ?? 0,
            $invStatus['critical'] ?? 0,
            0
        ]
    ];
} catch (Exception $e) {
    $inventoryData = ['error' => $e->getMessage(), 'value' => 0, 'items' => 0, 'status' => [0,0,0,0,0]];
}

    // 5. TOP PRODUCTOS TABLA
    $topProducts = [];
    try {
        $sdObj = new DateTimeImmutable($startDate);
        $edObj = new DateTimeImmutable($endDate);
        $days = $sdObj->diff($edObj)->days + 1;
        $prevStartObj = $sdObj->modify('-' . $days . ' days');
        $prevEndObj = $edObj->modify('-' . $days . ' days');
        $prevStartDate = $prevStartObj->format('Y-m-d');
        $prevEndDate = $prevEndObj->format('Y-m-d');
        $prodNameCol = colExists($conexion, 'producto', 'nombre_producto') ? 'nombre_producto' : (
                        colExists($conexion, 'producto', 'nombre') ? 'nombre' : (
                        colExists($conexion, 'producto', 'title') ? 'title' : 'name'));
        $stockColTop = colExists($conexion, 'producto', 'stock') ? 'stock' : (colExists($conexion, 'producto', 'existencias') ? 'existencias' : (colExists($conexion, 'producto', 'cantidad') ? 'cantidad' : null));
        $catIdColTop = colExists($conexion, 'producto', 'id_categoria') ? 'id_categoria' : (colExists($conexion, 'producto', 'categoria_id') ? 'categoria_id' : null);
        $catTableTop = null;
        foreach (['categorias', 'categoria', 'categories', 'category'] as $t) {
            if (tableExists($conexion, $t)) { $catTableTop = $t; break; }
        }

        // Detectar la tabla y columna de categoría correcta
        $catTableFound = null;
        $catIdFound = null;
        $catNameFound = null;
        
        // Buscar tabla de categorías
        foreach (['categorias', 'categoria', 'categories', 'category'] as $t) {
            if (tableExists($conexion, $t)) { 
                $catTableFound = $t;
                // Buscar columna de ID
                $catIdFound = colExists($conexion, $t, 'id_categoria') ? 'id_categoria' : 
                              (colExists($conexion, $t, 'categoria_id') ? 'categoria_id' : 
                              (colExists($conexion, $t, 'id') ? 'id' : null));
                // Buscar columna de nombre
                $catNameFound = colExists($conexion, $t, 'nombre_categoria') ? 'nombre_categoria' : 
                                (colExists($conexion, $t, 'categoria_nombre') ? 'categoria_nombre' : 
                                (colExists($conexion, $t, 'nombre') ? 'nombre' : 
                                (colExists($conexion, $t, 'name') ? 'name' : null)));
                break;
            }
        }
        
        // Si encontramos la tabla de categorías, usarla
        $prodCatCol = 'id_categoria'; // La tabla producto tiene id_categoria
        
        if ($catTableFound && $catIdFound && $catNameFound) {
            // Construir el JOIN correctamente
            $joinCategoria = "LEFT JOIN $catTableFound cat ON cat.\"$catIdFound\" = p.\"$prodCatCol\"";
            $categoriaSelect = "COALESCE(MAX(cat.\"$catNameFound\"), 'Sin Cat')";
        } elseif (tableExists($conexion, 'categoria')) {
            // Forzar uso de tabla 'categoria' que existe en la estructura
            $joinCategoria = "LEFT JOIN categoria cat ON cat.categoria_id = p.id_categoria";
            $categoriaSelect = "COALESCE(MAX(cat.categoria_nombre), 'Sin Cat')";
        } else {
            // Fallback: intentar obtener categoría del producto directamente
            $categoriaSelect = colExists($conexion, 'producto', 'categoria') ? "MAX(p.\"categoria\")" : "'Sin Cat'";
            $joinCategoria = "";
        }

        $stockSelect = $stockColTop ? "MAX(p.\"$stockColTop\")" : "0";

        $sql = "
            WITH cur AS (
                SELECT oi.title, SUM(oi.qty) AS unidades, SUM(oi.price * oi.qty) AS ingresos
                FROM order_items_pg oi
                JOIN orders_pg o ON o.id = oi.order_id
                WHERE o.created_at BETWEEN :start AND :end AND o.status != 'CANCELLED'
                GROUP BY oi.title
            ),
            prev AS (
                SELECT oi.title, SUM(oi.qty) AS unidades
                FROM order_items_pg oi
                JOIN orders_pg o ON o.id = oi.order_id
                WHERE o.created_at BETWEEN :pstart AND :pend AND o.status != 'CANCELLED'
                GROUP BY oi.title
            )
            SELECT 
                cur.title AS nombre,
                $categoriaSelect AS categoria,
                cur.unidades,
                cur.ingresos,
                $stockSelect AS stock,
                CASE 
                    WHEN COALESCE(prev.unidades,0) = 0 THEN 0
                    ELSE ROUND(((cur.unidades - prev.unidades)::numeric / prev.unidades) * 100, 1)
                END AS tendencia
            FROM cur
            LEFT JOIN prev ON LOWER(TRIM(prev.title)) = LOWER(TRIM(cur.title))
            LEFT JOIN producto p 
                ON 
                -- Igualdad directa (normalizada)
                LOWER(translate(trim(p.\"$prodNameCol\"), 'áéíóúÁÉÍÓÚñÑ', 'aeiouAEIOUnN')) = 
                LOWER(translate(trim(cur.title), 'áéíóúÁÉÍÓÚñÑ', 'aeiouAEIOUnN'))
                -- Coincidencia parcial: producto contiene el título
                OR LOWER(translate(p.\"$prodNameCol\", 'áéíóúÁÉÍÓÚñÑ', 'aeiouAEIOUnN')) 
                    LIKE '%' || LOWER(translate(trim(cur.title), 'áéíóúÁÉÍÓÚñÑ', 'aeiouAEIOUnN')) || '%'
                -- Coincidencia parcial: título contiene el producto
                OR LOWER(translate(trim(cur.title), 'áéíóúÁÉÍÓÚñÑ', 'aeiouAEIOUnN')) 
                    LIKE '%' || LOWER(translate(p.\"$prodNameCol\", 'áéíóúÁÉÍÓÚñÑ', 'aeiouAEIOUnN')) || '%'
            $joinCategoria
            ORDER BY cur.ingresos DESC
            LIMIT 5
        ";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            'start' => $startDate . ' 00:00:00',
            'end' => $endDate . ' 23:59:59',
            'pstart' => $prevStartDate . ' 00:00:00',
            'pend' => $prevEndDate . ' 23:59:59'
        ]);
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: Si no hay resultados pero hay ventas, intentar consulta simple (sin joins complejos)
        if (empty($topProducts) && $totalSales > 0) {
            $sqlSimple = "
                SELECT 
                    oi.title as nombre,
                    'Sin Cat' as categoria,
                    SUM(oi.qty) as unidades,
                    SUM(oi.price * oi.qty) as ingresos,
                     0 as stock,
                     NULL as tendencia
                FROM order_items_pg oi
                JOIN orders_pg o ON o.id = oi.order_id
                WHERE o.created_at BETWEEN :start AND :end AND o.status != 'CANCELLED'
                GROUP BY oi.title
                ORDER BY ingresos DESC
                LIMIT 5
            ";
            $stmt = $conexion->prepare($sqlSimple);
            $stmt->execute(['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']);
            $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // Intentar fallback simple en caso de error SQL
        try {
            $sqlSimple = "
                SELECT 
                    oi.title as nombre,
                    'Sin Cat' as categoria,
                    SUM(oi.qty) as unidades,
                    SUM(oi.price * oi.qty) as ingresos,
                     0 as stock,
                     NULL as tendencia
                FROM order_items_pg oi
                JOIN orders_pg o ON o.id = oi.order_id
                WHERE o.created_at BETWEEN :start AND :end AND o.status != 'CANCELLED'
                GROUP BY oi.title
                ORDER BY ingresos DESC
                LIMIT 5
            ";
            $stmt = $conexion->prepare($sqlSimple);
            $stmt->execute(['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']);
            $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e2) {
            $topProducts = [];
        }
    }

echo json_encode([
    'ok' => true,
    'range' => $range,
    'sales' => $salesData,
    'products' => $productsData,
    'customers' => $customersData,
    'inventory' => $inventoryData,
    'topTable' => $topProducts
]);
?>
