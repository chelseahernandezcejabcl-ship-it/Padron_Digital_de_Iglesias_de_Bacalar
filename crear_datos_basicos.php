<?php
require_once 'includes/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Datos de Prueba Básicos - Sistema de Iglesias Bacalar</title>
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

echo "<h1>Creando datos de prueba básicos</h1>";

$success_count = 0;
$error_count = 0;

// Primero verificar qué columnas existen en avisos_municipales
echo "<h2>Verificando estructura de avisos_municipales...</h2>";
$result = $conn->query("DESCRIBE avisos_municipales");
if ($result) {
    echo "<div class='info'>Columnas disponibles en avisos_municipales:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
    echo "</div>";
}

// Intentar insertar avisos con estructura básica
$avisos = [
    [
        'titulo' => 'Nuevo Procedimiento para Registro de Iglesias',
        'contenido' => '<p>Se informa a todas las organizaciones religiosas que a partir del 1 de noviembre de 2025, el proceso de registro de iglesias incluye nuevos requisitos documentales.</p><p><strong>Documentos requeridos:</strong></p><ul><li>Acta constitutiva de la organización religiosa</li><li>Comprobante de domicilio del templo o local</li><li>Identificación oficial del representante legal</li><li>Plan de actividades religiosas y comunitarias</li></ul>',
        'tipo_aviso' => 'tramite'
    ],
    [
        'titulo' => 'Celebración del Día de Todos los Santos',
        'contenido' => '<p>El H. Ayuntamiento de Bacalar invita a todas las iglesias y organizaciones religiosas a participar en las actividades conmemorativas del Día de Todos los Santos.</p><p><strong>Programa de actividades:</strong></p><ul><li><strong>1 de noviembre:</strong> Misa ecuménica en la Plaza Central - 10:00 AM</li><li><strong>2 de noviembre:</strong> Procesión al Cementerio Municipal - 5:00 PM</li></ul>',
        'tipo_aviso' => 'evento'
    ],
    [
        'titulo' => 'Capacitación en Gestión de Organizaciones Religiosas',
        'contenido' => '<p>Se invita a los líderes y representantes de iglesias a participar en el taller de capacitación sobre gestión administrativa y legal de organizaciones religiosas.</p><p><strong>Fecha:</strong> 28 de octubre de 2025</p><p><strong>Lugar:</strong> Salón de Usos Múltiples del Ayuntamiento</p>',
        'tipo_aviso' => 'general'
    ],
    [
        'titulo' => 'Mantenimiento del Sistema Digital',
        'contenido' => '<p>Se informa que el sistema digital de trámites estará en mantenimiento por mejoras técnicas.</p><p><strong>Fecha:</strong> 22 de octubre de 2025</p><p><strong>Horario:</strong> 2:00 AM - 6:00 AM</p>',
        'tipo_aviso' => 'urgente'
    ]
];

echo "<h2>Insertando avisos municipales...</h2>";

foreach ($avisos as $index => $aviso) {
    $sql = "INSERT INTO avisos_municipales (titulo, contenido, tipo_aviso, fecha_publicacion, activo, autor_id, fecha_creacion) VALUES (?, ?, ?, CURDATE(), 1, 1, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $aviso['titulo'], $aviso['contenido'], $aviso['tipo_aviso']);
        
        if ($stmt->execute()) {
            $success_count++;
            echo "<div class='success'>✓ Aviso " . ($index + 1) . " insertado: " . $aviso['titulo'] . "</div>";
        } else {
            $error_count++;
            echo "<div class='error'>✗ Error en aviso " . ($index + 1) . ": " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        $error_count++;
        echo "<div class='error'>✗ Error preparando consulta para aviso " . ($index + 1) . ": " . $conn->error . "</div>";
    }
}

// Verificar estructura de citas_asesoria
echo "<h2>Verificando estructura de citas_asesoria...</h2>";
$result = $conn->query("DESCRIBE citas_asesoria");
if ($result) {
    echo "<div class='info'>Columnas disponibles en citas_asesoria:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
    echo "</div>";
} else {
    echo "<div class='error'>No se pudo verificar la estructura de citas_asesoria</div>";
}

echo "<h2>Resumen final:</h2>";
echo "<div class='info'>";
echo "<p><strong>Datos insertados correctamente:</strong> $success_count</p>";
echo "<p><strong>Errores encontrados:</strong> $error_count</p>";
echo "</div>";

if ($success_count > 0) {
    echo "<div class='success'>";
    echo "<h3>¡Algunos datos fueron insertados exitosamente!</h3>";
    echo "<p>Ahora puedes ver:</p>";
    echo "<ul>";
    echo "<li>Avisos municipales en el sistema público</li>";
    echo "<li>Gestión de avisos en el panel de administración</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>Enlaces para probar:</h3>";
    echo "<p>";
    echo "<a href='index.php' class='btn'>🏠 Página Principal</a>";
    echo "<a href='avisos.php' class='btn'>📢 Ver Avisos Públicos</a>";
    echo "<a href='admin/avisos.php' class='btn'>📋 Gestión de Avisos (Admin)</a>";
    echo "</p>";
}

$conn->close();

echo "</body></html>";
?>
