<?php
require_once 'includes/config.php';

echo "<h2>Tablas disponibles en la base de datos:</h2>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Error: " . $conn->error;
}

// Verificar estructura de la tabla de trámites que sí existe
echo "<br><h2>Estructura de tramites_solicitudes:</h2>";
$result = $conn->query("DESCRIBE tramites_solicitudes");
if ($result) {
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}
?>
