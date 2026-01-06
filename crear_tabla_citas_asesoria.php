<?php
/**
 * Crear tabla citas_asesoria si no existe
 */
require_once 'includes/config.php';

// Verificar si la tabla existe
$table_check = $conn->query("SHOW TABLES LIKE 'citas_asesoria'");

if ($table_check->num_rows == 0) {
    echo "Creando tabla citas_asesoria...<br>";
    
    $create_table_sql = "
    CREATE TABLE `citas_asesoria` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tramite_id` int(11) NOT NULL,
        `fecha_cita` date NOT NULL,
        `hora_cita` time NOT NULL,
        `duracion_minutos` int(11) DEFAULT 60,
        `estado` enum('programada','completada','cancelada') DEFAULT 'programada',
        `observaciones` text,
        `admin_id` int(11) NOT NULL,
        `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `tramite_id` (`tramite_id`),
        KEY `admin_id` (`admin_id`),
        FOREIGN KEY (`tramite_id`) REFERENCES `tramites_solicitudes` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`admin_id`) REFERENCES `usuarios_admin` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($conn->query($create_table_sql)) {
        echo "✅ Tabla citas_asesoria creada exitosamente.<br>";
    } else {
        echo "❌ Error al crear tabla: " . $conn->error . "<br>";
    }
} else {
    echo "✅ La tabla citas_asesoria ya existe.<br>";
    
    // Verificar estructura
    $columns = $conn->query("SHOW COLUMNS FROM citas_asesoria");
    echo "Columnas encontradas:<br>";
    while ($column = $columns->fetch_assoc()) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
}

echo "<br><a href='verificar_asesoria.php'>← Volver a verificación</a>";
?>
