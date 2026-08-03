<?php
// =============================================
// Esquema centralizado para órdenes y ventas
// =============================================
// Este archivo garantiza que las tablas orders_pg y order_items_pg
// tengan TODAS las columnas necesarias, sin importar qué archivo
// PHP haya creado la tabla primero.
// Incluir con require_once en cualquier endpoint que use orders_pg.
// =============================================

if (!function_exists('ensure_orders_schema')) {
    function ensure_orders_schema(PDO $db): void {
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS orders_pg (
                    id SERIAL PRIMARY KEY,
                    user_id INT NULL,
                    paypal_id VARCHAR(255) NULL,
                    user_email VARCHAR(255) NULL,
                    user_name VARCHAR(255) NULL,
                    status VARCHAR(32) NOT NULL,
                    total NUMERIC(12,2) NOT NULL DEFAULT 0,
                    delivery_method VARCHAR(32) NOT NULL DEFAULT 'domicilio',
                    pay_method VARCHAR(32) NULL,
                    address_json JSONB NULL,
                    schedule_json JSONB NULL,
                    factus_invoice_id VARCHAR(255) NULL,
                    factus_number VARCHAR(255) NULL,
                    factus_status VARCHAR(32) NULL,
                    factus_pdf_url TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (Throwable $e) {
            error_log('orders_schema.php CREATE orders_pg: ' . $e->getMessage());
        }

        // Asegurar columnas que pueden faltar si la tabla fue creada por otro archivo
        $columns = [
            'user_id'          => 'INT NULL',
            'paypal_id'        => 'VARCHAR(255) NULL',
            'user_email'       => 'VARCHAR(255) NULL',
            'user_name'        => 'VARCHAR(255) NULL',
            'status'           => "VARCHAR(32) NOT NULL DEFAULT 'PENDING'",
            'total'            => 'NUMERIC(12,2) NOT NULL DEFAULT 0',
            'delivery_method'  => "VARCHAR(32) NOT NULL DEFAULT 'domicilio'",
            'pay_method'       => 'VARCHAR(32) NULL',
            'address_json'     => 'JSONB NULL',
            'schedule_json'    => 'JSONB NULL',
            'factus_invoice_id'=> 'VARCHAR(255) NULL',
            'factus_number'    => 'VARCHAR(255) NULL',
            'factus_status'    => 'VARCHAR(32) NULL',
            'factus_pdf_url'   => 'TEXT NULL',
            'created_at'       => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        ];

        foreach ($columns as $col => $def) {
            try {
                $db->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS \"$col\" $def");
            } catch (Throwable $e) {
                error_log("orders_schema.php ALTER $col: " . $e->getMessage());
            }
        }

        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS order_items_pg (
                    id SERIAL PRIMARY KEY,
                    order_id INT NOT NULL REFERENCES orders_pg(id) ON DELETE CASCADE,
                    title VARCHAR(255),
                    price NUMERIC(12,2) NOT NULL DEFAULT 0,
                    qty INT NOT NULL DEFAULT 1,
                    image TEXT NULL
                )
            ");
        } catch (Throwable $e) {
            error_log('orders_schema.php CREATE order_items_pg: ' . $e->getMessage());
        }

        // Índices para acelerar consultas frecuentes
        try { $db->exec("CREATE INDEX IF NOT EXISTS idx_orders_pg_status ON orders_pg(status)"); } catch (Throwable $_) {}
        try { $db->exec("CREATE INDEX IF NOT EXISTS idx_orders_pg_created_at ON orders_pg(created_at)"); } catch (Throwable $_) {}
        try { $db->exec("CREATE INDEX IF NOT EXISTS idx_orders_pg_user_email ON orders_pg(user_email)"); } catch (Throwable $_) {}
        try { $db->exec("CREATE INDEX IF NOT EXISTS idx_order_items_pg_order_id ON order_items_pg(order_id)"); } catch (Throwable $_) {}
    }
}

if (!function_exists('table_exists')) {
    function table_exists(PDO $db, string $name): bool {
        static $cache = [];
        if (array_key_exists($name, $cache)) return $cache[$name];
        try {
            $stmt = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_name = ? LIMIT 1");
            $stmt->execute([$name]);
            $cache[$name] = (bool)$stmt->fetch();
            $stmt->closeCursor();
        } catch (Throwable $e) {
            $cache[$name] = false;
        }
        return $cache[$name];
    }
}

if (!function_exists('col_exists')) {
    function col_exists(PDO $db, string $table, string $col): bool {
        static $cache = [];
        $key = $table . '.' . $col;
        if (array_key_exists($key, $cache)) return $cache[$key];
        try {
            $stmt = $db->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = ? AND column_name = ? LIMIT 1");
            $stmt->execute([$table, $col]);
            $cache[$key] = (bool)$stmt->fetch();
            $stmt->closeCursor();
        } catch (Throwable $e) {
            $cache[$key] = false;
        }
        return $cache[$key];
    }
}

if (!function_exists('ensure_notificaciones_schema')) {
    function ensure_notificaciones_schema(PDO $db): void {
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS notificaciones_admin (
                    id SERIAL PRIMARY KEY,
                    type VARCHAR(32) NOT NULL DEFAULT 'order',
                    title TEXT NOT NULL,
                    message TEXT,
                    order_id INT NULL,
                    ref_id VARCHAR(255) NULL,
                    leida BOOLEAN NOT NULL DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (Throwable $e) {
            error_log('orders_schema.php CREATE notificaciones_admin: ' . $e->getMessage());
        }

        $columns = [
            'type'     => "VARCHAR(32) NOT NULL DEFAULT 'order'",
            'title'    => 'TEXT NOT NULL',
            'message'  => 'TEXT NULL',
            'order_id' => 'INT NULL',
            'ref_id'   => 'VARCHAR(255) NULL',
            'leida'    => 'BOOLEAN NOT NULL DEFAULT FALSE',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        ];

        foreach ($columns as $col => $def) {
            try {
                $db->exec("ALTER TABLE notificaciones_admin ADD COLUMN IF NOT EXISTS \"$col\" $def");
            } catch (Throwable $e) {
                error_log("orders_schema.php ALTER notif $col: " . $e->getMessage());
            }
        }

        try { $db->exec("CREATE INDEX IF NOT EXISTS idx_notif_admin_created_at ON notificaciones_admin(created_at DESC)"); } catch (Throwable $_) {}
        try { $db->exec("CREATE INDEX IF NOT EXISTS idx_notif_admin_leida ON notificaciones_admin(leida)"); } catch (Throwable $_) {}
        try { $db->exec("CREATE INDEX IF NOT EXISTS idx_notif_admin_order_id ON notificaciones_admin(order_id)"); } catch (Throwable $_) {}
    }
}

if (!function_exists('record_notificacion')) {
    function record_notificacion(PDO $db, string $type, string $title, ?string $message = null, ?int $orderId = null, ?string $refId = null): bool {
        try {
            $stmt = $db->prepare("INSERT INTO notificaciones_admin (type, title, message, order_id, ref_id) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$type, $title, $message, $orderId, $refId]);
        } catch (Throwable $e) {
            error_log('record_notificacion: ' . $e->getMessage());
            return false;
        }
    }
}
