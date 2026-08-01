-- Tabla de Reseñas para Productos
CREATE TABLE IF NOT EXISTS resenas (
    id_resena SERIAL PRIMARY KEY,
    id_producto INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    calificacion INTEGER NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
    titulo VARCHAR(100),
    comentario TEXT,
    estado BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índice para búsquedas rápidas de reseñas por producto
CREATE INDEX IF NOT EXISTS idx_resenas_producto ON resenas(id_producto);

-- Índice para búsquedas rápidas de reseñas por usuario
CREATE INDEX IF NOT EXISTS idx_resenas_usuario ON resenas(id_usuario);
