<?php
require_once 'includes/config.php';

echo "<h2>Creando trámites de prueba</h2>";

// Verificar si existe la tabla tramites_solicitudes
$result = $conn->query("DESCRIBE tramites_solicitudes");
if (!$result) {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;'>";
    echo "<h3>❌ Tabla tramites_solicitudes no existe</h3>";
    echo "<p>Error: " . $conn->error . "</p>";
    echo "<p>Necesitamos crear la tabla primero.</p>";
    echo "</div>";
    
    // Crear la tabla tramites_solicitudes
    $create_table = "
    CREATE TABLE tramites_solicitudes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_iglesia VARCHAR(255) NOT NULL,
        representante VARCHAR(255) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(255),
        direccion TEXT,
        tipo_tramite ENUM('registro_nuevo', 'actualizacion_datos', 'cambio_representante', 'cancelacion', 'otro') NOT NULL,
        estado ENUM('pendiente', 'en_revision', 'aprobado', 'rechazado') DEFAULT 'pendiente',
        descripcion TEXT,
        documentos TEXT,
        fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        comentarios_admin TEXT,
        procesado_por INT,
        FOREIGN KEY (procesado_por) REFERENCES usuarios_admin(id)
    )";
    
    if ($conn->query($create_table)) {
        echo "<div style='background:#d4edda;padding:15px;border-radius:5px;color:#155724;'>";
        echo "<h3>✅ Tabla tramites_solicitudes creada exitosamente</h3>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;'>";
        echo "<h3>❌ Error creando tabla: " . $conn->error . "</h3>";
        echo "</div>";
        exit;
    }
} else {
    echo "<div style='background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;'>";
    echo "<h3>✅ Tabla tramites_solicitudes existe</h3>";
    echo "</div>";
}

// Insertar trámites de ejemplo
$tramites = [
    [
        'nombre_iglesia' => 'Iglesia Evangélica Emanuel',
        'representante' => 'Pastor Juan Carlos Mendoza',
        'telefono' => '983-123-4567',
        'email' => 'emanuel.bacalar@gmail.com',
        'direccion' => 'Calle Laguna #45, Centro, Bacalar',
        'tipo_tramite' => 'registro_nuevo',
        'estado' => 'pendiente',
        'descripcion' => 'Solicitud de registro de nueva iglesia evangélica en el centro de Bacalar'
    ],
    [
        'nombre_iglesia' => 'Iglesia Católica San Joaquín',
        'representante' => 'Padre Miguel Ángel Torres',
        'telefono' => '983-234-5678',
        'email' => 'san.joaquin.bacalar@catolica.org',
        'direccion' => 'Av. Costera #78, Bacalar Centro',
        'tipo_tramite' => 'actualizacion_datos',
        'estado' => 'aprobado',
        'descripcion' => 'Actualización de datos del representante legal y domicilio'
    ],
    [
        'nombre_iglesia' => 'Iglesia Pentecostés Renacer',
        'representante' => 'Pastora María Elena Vásquez',
        'telefono' => '983-345-6789',
        'email' => 'renacer.bacalar@hotmail.com',
        'direccion' => 'Calle 7 de Agosto #123, Col. Maya',
        'tipo_tramite' => 'cambio_representante',
        'estado' => 'en_revision',
        'descripcion' => 'Cambio de representante legal por jubilación del pastor anterior'
    ],
    [
        'nombre_iglesia' => 'Iglesia Adventista del Séptimo Día',
        'representante' => 'Anciano Roberto Herrera',
        'telefono' => '983-456-7890',
        'email' => 'adventista.bacalar@gmail.com',
        'direccion' => 'Calle Principal #234, Bacalar',
        'tipo_tramite' => 'registro_nuevo',
        'estado' => 'rechazado',
        'descripcion' => 'Solicitud de registro de congregación adventista'
    ],
    [
        'nombre_iglesia' => 'Iglesia Bautista Monte Sinaí',
        'representante' => 'Pastor David Ramírez',
        'telefono' => '983-567-8901',
        'email' => 'monte.sinai@bautista.org',
        'direccion' => 'Av. Insurgentes #567, Fraccionamiento Las Flores',
        'tipo_tramite' => 'otro',
        'estado' => 'pendiente',
        'descripcion' => 'Solicitud de permiso para construcción de nuevo templo'
    ]
];

echo "<h3>Insertando trámites de ejemplo...</h3>";
$success_count = 0;

foreach ($tramites as $tramite) {
    $sql = "INSERT INTO tramites_solicitudes (nombre_iglesia, representante, telefono, email, direccion, tipo_tramite, estado, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssssss", 
            $tramite['nombre_iglesia'],
            $tramite['representante'],
            $tramite['telefono'],
            $tramite['email'],
            $tramite['direccion'],
            $tramite['tipo_tramite'],
            $tramite['estado'],
            $tramite['descripcion']
        );
        
        if ($stmt->execute()) {
            $success_count++;
            echo "<div style='background:#d4edda;padding:10px;border-radius:5px;margin:5px 0;color:#155724;'>";
            echo "✓ Trámite agregado: " . $tramite['nombre_iglesia'] . " (" . $tramite['tipo_tramite'] . " - " . $tramite['estado'] . ")";
            echo "</div>";
        } else {
            echo "<div style='background:#f8d7da;padding:10px;border-radius:5px;margin:5px 0;color:#721c24;'>";
            echo "✗ Error: " . $stmt->error;
            echo "</div>";
        }
        $stmt->close();
    }
}

echo "<div style='background:#cce5ff;padding:15px;border-radius:5px;margin:20px 0;color:#004085;'>";
echo "<h3>🎉 ¡Trámites creados exitosamente!</h3>";
echo "<p><strong>Total de trámites insertados:</strong> $success_count</p>";
echo "<p>Ahora ya puedes ver los trámites en el panel de administración.</p>";
echo "</div>";

echo "<h3>🔗 Enlaces para verificar:</h3>";
echo "<div style='margin:20px 0;'>";
echo "<a href='admin/tramites.php' style='display:inline-block;background:#611232;color:white;padding:12px 20px;text-decoration:none;border-radius:5px;margin:5px;'>📋 Ver Trámites en Admin</a>";
echo "<a href='admin/index.php' style='display:inline-block;background:#28a745;color:white;padding:12px 20px;text-decoration:none;border-radius:5px;margin:5px;'>📊 Dashboard Principal</a>";
echo "</div>";

$conn->close();
?>
