<?php
require_once 'includes/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Datos de Prueba - Sistema de Iglesias Bacalar</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        h1, h2 { color: #611232; }
        .btn { background: #611232; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
    </style>
</head>
<body>";

echo "<h1>Insertando datos de prueba para el Sistema de Iglesias de Bacalar</h1>";

$success_count = 0;
$error_count = 0;

// Array de consultas para insertar
$queries = [
    // Avisos municipales
    "INSERT INTO avisos_municipales (titulo, contenido, tipo_aviso, fecha_publicacion, fecha_vencimiento, activo, prioridad, autor_id, fecha_creacion) VALUES
    ('Nuevo Procedimiento para Registro de Iglesias', 
    '<p>Se informa a todas las organizaciones religiosas que a partir del 1 de noviembre de 2025, el proceso de registro de iglesias incluye nuevos requisitos documentales.</p><p><strong>Documentos requeridos:</strong></p><ul><li>Acta constitutiva de la organización religiosa</li><li>Comprobante de domicilio del templo o local</li><li>Identificación oficial del representante legal</li><li>Plan de actividades religiosas y comunitarias</li></ul><p>Para más información, acudir a la Coordinación de Atención Ciudadana y Asuntos Religiosos.</p>', 
    'tramite', '2025-10-01', '2025-12-31', 1, 'alta', 1, NOW())",
    
    "INSERT INTO avisos_municipales (titulo, contenido, tipo_aviso, fecha_publicacion, fecha_vencimiento, activo, prioridad, autor_id, fecha_creacion) VALUES
    ('Celebración del Día de Todos los Santos', 
    '<p>El H. Ayuntamiento de Bacalar invita a todas las iglesias y organizaciones religiosas a participar en las actividades conmemorativas del Día de Todos los Santos.</p><p><strong>Programa de actividades:</strong></p><ul><li><strong>1 de noviembre:</strong> Misa ecuménica en la Plaza Central - 10:00 AM</li><li><strong>2 de noviembre:</strong> Procesión al Cementerio Municipal - 5:00 PM</li><li><strong>2 de noviembre:</strong> Vigilia de oración - 8:00 PM</li></ul><p>Las iglesias interesadas en participar pueden inscribirse hasta el 25 de octubre.</p>', 
    'evento', '2025-10-15', '2025-11-03', 1, 'media', 1, NOW())",
    
    "INSERT INTO avisos_municipales (titulo, contenido, tipo_aviso, fecha_publicacion, fecha_vencimiento, activo, prioridad, autor_id, fecha_creacion) VALUES
    ('Capacitación en Gestión de Organizaciones Religiosas', 
    '<p>Se invita a los líderes y representantes de iglesias a participar en el taller de capacitación sobre gestión administrativa y legal de organizaciones religiosas.</p><p><strong>Fecha:</strong> 28 de octubre de 2025<br><strong>Hora:</strong> 9:00 AM - 2:00 PM<br><strong>Lugar:</strong> Salón de Usos Múltiples del Ayuntamiento</p><p><strong>Temas a tratar:</strong></p><ul><li>Marco legal de las asociaciones religiosas</li><li>Obligaciones fiscales y contables</li><li>Trámites municipales y estatales</li><li>Resolución de conflictos internos</li></ul><p>Inscripciones abiertas hasta el 25 de octubre. Cupo limitado a 30 participantes.</p>', 
    'general', '2025-10-10', '2025-10-28', 1, 'media', 1, NOW())",
    
    "INSERT INTO avisos_municipales (titulo, contenido, tipo_aviso, fecha_publicacion, fecha_vencimiento, activo, prioridad, autor_id, fecha_creacion) VALUES
    ('Mantenimiento del Sistema Digital - Programado', 
    '<p>Se informa que el sistema digital de trámites estará en mantenimiento por mejoras técnicas.</p><p><strong>Fecha:</strong> 22 de octubre de 2025<br><strong>Horario:</strong> 2:00 AM - 6:00 AM</p><p>Durante este periodo, el sistema no estará disponible para realizar trámites en línea. Se recomienda planificar las solicitudes con anticipación.</p><p>Disculpas por las molestias ocasionadas.</p>', 
    'urgente', '2025-10-18', '2025-10-23', 1, 'alta', 1, NOW())",
    
    // Asesorías jurídicas
    "INSERT INTO citas_asesoria (iglesia_id, tipo_consulta, descripcion_problema, fecha_solicitada, hora_solicitada, estado, prioridad, fecha_solicitud, contacto_nombre, contacto_telefono, contacto_email) VALUES
    (1, 'constitucion_asociacion', 
    'Necesitamos asesoría para constituir legalmente nuestra iglesia como asociación religiosa ante la Secretaría de Gobernación. Tenemos dudas sobre los documentos requeridos y el proceso.', 
    '2025-10-25', '10:00:00', 'programada', 'alta', '2025-10-12 16:20:00', 
    'Pastor Miguel Hernández', '9831234567', 'iglesia.esperanza@email.com')",
    
    "INSERT INTO citas_asesoria (iglesia_id, tipo_consulta, descripcion_problema, fecha_solicitada, hora_solicitada, estado, prioridad, fecha_solicitud, contacto_nombre, contacto_telefono, contacto_email) VALUES
    (2, 'registro_objeto_religioso', 
    'Queremos registrar una nueva imagen religiosa que llegó de donación desde Italia. Necesitamos conocer el procedimiento para el registro ante las autoridades competentes.', 
    '2025-10-28', '14:30:00', 'programada', 'media', '2025-10-14 10:45:00', 
    'Hermana Rosa María', '9831987654', 'san.joaquin.bacalar@email.com')",
    
    "INSERT INTO citas_asesoria (iglesia_id, tipo_consulta, descripcion_problema, fecha_solicitada, hora_solicitada, estado, prioridad, fecha_solicitud, contacto_nombre, contacto_telefono, contacto_email) VALUES
    (1, 'problema_organizacional', 
    'Tenemos un conflicto interno en la junta directiva de la iglesia. Algunos miembros no están de acuerdo con las decisiones financieras. Necesitamos mediación legal.', 
    '2025-10-30', '09:00:00', 'programada', 'alta', '2025-10-13 13:15:00', 
    'Administrador Juan Carlos', '9831333222', 'admin.pentecostal@email.com')",
    
    "INSERT INTO citas_asesoria (iglesia_id, tipo_consulta, descripcion_problema, fecha_solicitada, hora_solicitada, estado, prioridad, fecha_solicitud, contacto_nombre, contacto_telefono, contacto_email) VALUES
    (1, 'constitucion_asociacion', 
    'Consulta de seguimiento sobre documentos faltantes para completar el proceso de constitución como asociación religiosa.', 
    '2025-10-18', '11:00:00', 'completada', 'media', '2025-10-01 08:30:00', 
    'Pastor Miguel Hernández', '9831234567', 'iglesia.esperanza@email.com')"
];

echo "<h2>Ejecutando inserción de datos...</h2>";

foreach ($queries as $index => $query) {
    try {
        if ($conn->query($query)) {
            $success_count++;
            echo "<div class='success'>✓ Dato " . ($index + 1) . " insertado correctamente</div>";
        } else {
            $error_count++;
            echo "<div class='error'>✗ Error en dato " . ($index + 1) . ": " . $conn->error . "</div>";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<div class='error'>✗ Excepción en dato " . ($index + 1) . ": " . $e->getMessage() . "</div>";
    }
}

echo "<h2>Resumen de la inserción:</h2>";
echo "<div class='info'>";
echo "<p><strong>Datos insertados correctamente:</strong> $success_count</p>";
echo "<p><strong>Errores encontrados:</strong> $error_count</p>";
echo "</div>";

if ($error_count === 0) {
    echo "<div class='success'>";
    echo "<h3>¡Datos de prueba insertados exitosamente!</h3>";
    echo "<p>El sistema ahora cuenta con datos de demostración:</p>";
    echo "<ul>";
    echo "<li><strong>4 Avisos municipales</strong> con diferentes tipos y prioridades</li>";
    echo "<li><strong>4 Citas de asesoría jurídica</strong> con diferentes estados</li>";
    echo "<li>Datos realistas para pruebas del sistema</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>Enlaces para probar el sistema:</h3>";
    echo "<p>";
    echo "<a href='index.php' class='btn'>🏠 Página Principal</a>";
    echo "<a href='avisos.php' class='btn'>📢 Ver Avisos Públicos</a>";
    echo "<a href='admin/index.php' class='btn'>👑 Panel de Administración</a>";
    echo "<a href='admin/avisos.php' class='btn'>📋 Gestión de Avisos</a>";
    echo "<a href='admin/asesoria_juridica.php' class='btn'>⚖️ Asesorías Jurídicas</a>";
    echo "</p>";
} else {
    echo "<div class='error'>";
    echo "<h3>Se encontraron algunos errores</h3>";
    echo "<p>Revisa los mensajes de error arriba. Es posible que algunos datos ya existieran en la base de datos.</p>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h3>Credenciales de prueba:</h3>";
echo "<p><strong>Administrador:</strong><br>";
echo "Usuario: admin@bacalar.gob.mx<br>";
echo "Contraseña: admin123</p>";
echo "<p><strong>Iglesia de prueba:</strong><br>";
echo "Correo: iglesia.test@email.com<br>";
echo "Contraseña: iglesia123</p>";
echo "</div>";

$conn->close();

echo "</body></html>";
?>
