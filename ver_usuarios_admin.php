<?php
require_once 'includes/config.php';

echo "<h2>🔐 Usuarios Admin en la Base de Datos</h2>";

try {
    $sql = "SELECT id, username, email, nombre, apellido, fecha_creacion FROM admin_usuarios ORDER BY fecha_creacion DESC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Nombre</th><th>Apellido</th><th>Fecha Creación</th></tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td><strong>" . $row["username"] . "</strong></td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["nombre"] . "</td>";
            echo "<td>" . $row["apellido"] . "</td>";
            echo "<td>" . $row["fecha_creacion"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>💡 Usa cualquiera de estos usernames para iniciar sesión.</strong></p>";
        echo "<p>Si no recuerdas la contraseña, podemos crear un usuario temporal.</p>";
    } else {
        echo "<p>❌ No se encontraron usuarios admin.</p>";
        echo "<a href='crear_admin.php'>🆕 Crear usuario admin</a>";
    }
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
