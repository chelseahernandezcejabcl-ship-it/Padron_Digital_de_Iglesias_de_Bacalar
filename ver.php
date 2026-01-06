<?php
require_once __DIR__ . '/includes/funciones.php';
$id = $_GET['id'] ?? 0;
$libro = obtenerLibroPorId($id);
if (!$libro) {
    die("Libro no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($libro['titulo']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <h2><?php echo htmlspecialchars($libro['titulo']); ?></h2>
  <p><em><?php echo htmlspecialchars($libro['autor']); ?></em></p>
  <iframe src="uploads/pdfs/<?php echo $libro['archivo_pdf']; ?>" width="100%" height="600px"></iframe>
  <a href="uploads/pdfs/<?php echo $libro['archivo_pdf']; ?>" download class="btn btn-success mt-3">Descargar PDF</a>
</body>
</html>
