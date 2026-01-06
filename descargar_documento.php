<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funciones.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('ID de documento inválido');
}

$documento_id = intval($_GET['id']);

// Obtener información del documento
$sql = "SELECT * FROM libros WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $documento_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die('Documento no encontrado');
}

$documento = $result->fetch_assoc();

// Verificar que el archivo existe
$archivo_nombre = $documento['archivo_pdf'];
$ruta_archivo = __DIR__ . '/uploads/libros/' . $archivo_nombre;

if (!file_exists($ruta_archivo) || !$archivo_nombre) {
    http_response_code(404);
    die('Archivo no encontrado en el servidor');
}

// Registrar descarga (opcional - para estadísticas)
$sql_descarga = "UPDATE libros SET descargas = descargas + 1 WHERE id = ?";
$stmt_descarga = $conn->prepare($sql_descarga);
$stmt_descarga->bind_param("i", $documento_id);
$stmt_descarga->execute();

// Generar nombre de archivo limpio para descarga
$nombre_archivo = sanitizarNombreArchivo($documento['titulo']) . '.pdf';

// Configurar headers para descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Cache-Control: private');
header('Pragma: private');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Limpiar cualquier salida anterior
if (ob_get_level()) {
    ob_end_clean();
}

// Enviar el archivo
readfile($ruta_archivo);
exit;

/**
 * Función para sanitizar nombres de archivo
 */
function sanitizarNombreArchivo($nombre) {
    // Remover caracteres especiales y espacios
    $nombre = preg_replace('/[^a-zA-Z0-9\s\-_áéíóúñÁÉÍÓÚÑ]/', '', $nombre);
    // Reemplazar espacios múltiples con guión bajo
    $nombre = preg_replace('/\s+/', '_', trim($nombre));
    // Limitar longitud
    $nombre = substr($nombre, 0, 100);
    return $nombre;
}
?>
