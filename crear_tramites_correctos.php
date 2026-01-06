<?php
require_once 'includes/config.php';

echo "<h2>Creando trámites con estructura correcta</h2>";

// Primero crear algunas iglesias de ejemplo si no existen
echo "<h3>1. Verificando/creando iglesias base...</h3>";

// Verificar si existe tabla de iglesias
$check_iglesias = $conn->query("SHOW TABLES LIKE 'iglesias'");
if ($check_iglesias->num_rows == 0) {
    // Crear tabla iglesias si no existe
    $create_iglesias = "
    CREATE TABLE iglesias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        representante VARCHAR(255) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(255),
        direccion TEXT,
        denominacion VARCHAR(100),
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado ENUM('activa', 'inactiva', 'suspendida') DEFAULT 'activa'
    )";
    
    if ($conn->query($create_iglesias)) {
        echo "✓ Tabla iglesias creada<br>";
        
        // Insertar iglesias de ejemplo
        $iglesias = [
            ['Iglesia Evangélica Emanuel', 'Pastor Juan Carlos Mendoza', '983-123-4567', 'emanuel@gmail.com', 'Calle Laguna #45', 'Evangélica'],
            ['Iglesia Católica San Joaquín', 'Padre Miguel Torres', '983-234-5678', 'san.joaquin@catolica.org', 'Av. Costera #78', 'Católica'],
            ['Iglesia Pentecostés Renacer', 'Pastora María Vásquez', '983-345-6789', 'renacer@hotmail.com', 'Calle 7 de Agosto #123', 'Pentecostés']
        ];
        
        foreach ($iglesias as $iglesia) {
            $sql = "INSERT INTO iglesias (nombre, representante, telefono, email, direccion, denominacion) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $iglesia[0], $iglesia[1], $iglesia[2], $iglesia[3], $iglesia[4], $iglesia[5]);
            if ($stmt->execute()) {
                echo "✓ Iglesia agregada: " . $iglesia[0] . "<br>";
            }
            $stmt->close();
        }
    }
}

// Obtener IDs de iglesias existentes
$iglesias_result = $conn->query("SELECT id, nombre FROM iglesias LIMIT 5");
$iglesias_ids = [];
if ($iglesias_result) {
    while ($row = $iglesias_result->fetch_assoc()) {
        $iglesias_ids[] = ['id' => $row['id'], 'nombre' => $row['nombre']];
    }
}

echo "<h3>2. Creando trámites...</h3>";

// Usar la estructura real que encontremos
$result = $conn->query("DESCRIBE tramites_solicitudes");
$columns = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

// Crear trámites adaptándose a la estructura real
if (!empty($iglesias_ids)) {
    foreach ($iglesias_ids as $index => $iglesia) {
        $tipos = ['registro_nuevo', 'actualizacion_datos', 'cambio_representante'];
        $estados = ['pendiente', 'en_revision', 'aprobado'];
        
        $tipo = $tipos[$index % 3];
        $estado = $estados[$index % 3];
        
        // Construir INSERT dinámicamente según columnas disponibles
        $campos = ['tipo_tramite', 'estado', 'fecha_solicitud'];
        $valores = ['?, ?, NOW()'];
        $params = [$tipo, $estado];
        $types = 'ss';
        
        // Agregar iglesia_id si existe la columna
        if (in_array('iglesia_id', $columns)) {
            array_unshift($campos, 'iglesia_id');
            array_unshift($valores, '?');
            array_unshift($params, $iglesia['id']);
            $types = 'i' . $types;
        }
        
        // Agregar descripción si existe la columna
        if (in_array('descripcion', $columns)) {
            $campos[] = 'descripcion';
            $valores[] = '?';
            $params[] = "Trámite de " . $tipo . " para " . $iglesia['nombre'];
            $types .= 's';
        }
        
        $sql = "INSERT INTO tramites_solicitudes (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                echo "✓ Trámite creado para: " . $iglesia['nombre'] . " ($tipo - $estado)<br>";
            } else {
                echo "✗ Error: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }
    }
} else {
    echo "No se encontraron iglesias para crear trámites<br>";
}

echo "<h3>✅ Proceso completado</h3>";
echo "<a href='admin/tramites.php' style='background:#611232;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Ver Trámites</a>";
?>
