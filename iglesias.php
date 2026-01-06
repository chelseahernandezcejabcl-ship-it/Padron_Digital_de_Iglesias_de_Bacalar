<?php
// Verificar si se solicita ver la vista pública
if (isset($_GET['vista_publica']) && $_GET['vista_publica'] === '1') {
    // Incluir middleware para manejar sesiones
    require_once 'includes/middleware_publico.php';
    
    // Continuar con la vista pública real (será manejado por el middleware)
    // Si llegamos aquí, el admin tiene permiso para ver la vista pública
    require_once 'index.php';
    exit();
}

// Redirección automática al panel admin por defecto
header('Location: admin/iglesias.php');
exit();
?>