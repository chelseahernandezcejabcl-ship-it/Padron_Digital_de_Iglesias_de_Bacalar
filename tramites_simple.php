<?php
require_once 'includes/config.php';

echo "<h2>Diagnóstico completo del sistema</h2>";

echo "<h3>1. Todas las tablas disponibles:</h3>";
$tables = $conn->query("SHOW TABLES");
echo "<ul>";
while ($table = $tables->fetch_array()) {
    echo "<li><strong>" . $table[0] . "</strong></li>";
}
echo "</ul>";

echo "<h3>2. Estructura de tramites_solicitudes:</h3>";
$desc = $conn->query("DESCRIBE tramites_solicitudes");
if ($desc) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th></tr>";
    while ($row = $desc->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
    }
    echo "</table>";
    
    // Contar registros existentes
    $count = $conn->query("SELECT COUNT(*) as total FROM tramites_solicitudes");
    $total = $count->fetch_assoc()['total'];
    echo "<p><strong>Registros actuales:</strong> $total</p>";
} else {
    echo "Error: " . $conn->error;
}

echo "<h3>3. Insertando trámites básicos sin dependencias:</h3>";

// Insertar datos muy básicos usando solo las columnas que sabemos que existen
$tramites_basicos = [
    ['registro_nuevo', 'pendiente', 'Solicitud de registro de nueva iglesia evangélica'],
    ['actualizacion_datos', 'aprobado', 'Actualización de datos administrativos'],
    ['cambio_representante', 'en_revision', 'Cambio de representante legal'],
    ['registro_nuevo', 'rechazado', 'Solicitud incompleta de registro'],
    ['otro', 'pendiente', 'Solicitud de permiso especial']
];

foreach ($tramites_basicos as $index => $tramite) {
    // Usar solo columnas básicas que probablemente existan
    $sql = "INSERT INTO tramites_solicitudes (tipo_tramite, estado, descripcion, fecha_solicitud) VALUES (?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $tramite[0], $tramite[1], $tramite[2]);
        if ($stmt->execute()) {
            echo "✓ Trámite " . ($index + 1) . " creado: " . $tramite[0] . " (" . $tramite[1] . ")<br>";
        } else {
            echo "✗ Error en trámite " . ($index + 1) . ": " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        echo "✗ Error preparando trámite " . ($index + 1) . ": " . $conn->error . "<br>";
    }
}

// Verificar cuántos se insertaron
$final_count = $conn->query("SELECT COUNT(*) as total FROM tramites_solicitudes");
$final_total = $final_count->fetch_assoc()['total'];
echo "<h3>📊 Resultado final:</h3>";
echo "<p><strong>Total de trámites ahora:</strong> $final_total</p>";

if ($final_total > 0) {
    echo "<div style='background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:20px 0;'>";
    echo "<h3>🎉 ¡Éxito! Trámites creados</h3>";
    echo "<p>Ya puedes ver los trámites en el panel de administración.</p>";
    echo "</div>";
    
    echo "<a href='admin/tramites.php' style='background:#611232;color:white;padding:12px 20px;text-decoration:none;border-radius:5px;margin:10px;'>📋 Ver Trámites</a>";
    echo "<a href='admin/index.php' style='background:#28a745;color:white;padding:12px 20px;text-decoration:none;border-radius:5px;margin:10px;'>📊 Dashboard</a>";
}
?>
