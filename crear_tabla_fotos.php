<?php
require_once 'includes/config.php';

echo "<h3>Verificación de Tablas</h3>\n";

// Mostrar todas las tablas
$result = $conn->query("SHOW TABLES");
echo "<h4>Tablas existentes:</h4>\n";
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "<br>\n";
}

// Verificar si existe la tabla fotos_iglesias
$check_table = $conn->query("SHOW TABLES LIKE 'fotos_iglesias'");
if ($check_table->num_rows == 0) {
    echo "<h4>❌ Tabla 'fotos_iglesias' no existe. Creándola...</h4>\n";
    
    $sql_create = "CREATE TABLE fotos_iglesias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        iglesia_id INT NOT NULL,
        ruta_archivo VARCHAR(255) NOT NULL,
        descripcion TEXT,
        fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (iglesia_id) REFERENCES iglesias(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql_create)) {
        echo "<p style='color: green;'>✅ Tabla 'fotos_iglesias' creada exitosamente</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Error al crear tabla: " . $conn->error . "</p>\n";
    }
} else {
    echo "<h4>✅ Tabla 'fotos_iglesias' ya existe</h4>\n";
}

// Verificar estructura de la tabla iglesias para asegurar compatibilidad
echo "<h4>Estructura de tabla 'iglesias':</h4>\n";
$result = $conn->query("DESCRIBE iglesias");
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>\n";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>\n";
}
echo "</table>\n";
?>
