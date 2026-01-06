<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funciones.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Detectar el campo de categoría automáticamente
    $category_field = null;
    $fields_to_try = ['categoria', 'categoria', 'tipo_documento', 'classification', 'type'];
    foreach ($fields_to_try as $field) {
        $test_query = "SELECT DISTINCT $field FROM libros WHERE $field IS NOT NULL AND $field != '' LIMIT 1";
        $test_result = $conn->query($test_query);
        if ($test_result && $test_result->num_rows > 0) {
            $category_field = $field;
            break;
        }
    }

    // Obtener parámetros
    $busqueda = $_GET['busqueda'] ?? '';
    $tipo_documento = $_GET['categoria'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page);
    $limit = 12;
    $offset = ($page - 1) * $limit;

    // Construir consulta con filtros (usando detección automática)
    $where = [];
    $params = [];
    $types = '';

    if ($busqueda) {
        $where[] = "(titulo LIKE ? OR autor LIKE ? OR descripcion LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $types .= 'sss';
    }
    
    // Usar el campo de categoría correcto si existe
    if ($tipo_documento && $category_field) {
        $where[] = "$category_field = ?";
        $params[] = $tipo_documento;
        $types .= 's';
    }

    // Consulta para contar total de documentos
    $count_sql = 'SELECT COUNT(*) as total FROM libros';
    if ($where) $count_sql .= ' WHERE ' . implode(' AND ', $where);

    $total_documentos = 0;
    if ($types) {
        $count_stmt = $conn->prepare($count_sql);
        if (!empty($params)) {
            $count_stmt->bind_param($types, ...$params);
        }
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_documentos = $count_result->fetch_assoc()['total'];
    } else {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_documentos = $count_result->fetch_assoc()['total'];
    }

    $total_pages = ceil($total_documentos / $limit);

    // Consulta principal con paginación
    $sql = 'SELECT l.*, ua.nombre_completo as subido_por_nombre
            FROM libros l 
            LEFT JOIN usuarios_admin ua ON l.subido_por = ua.id';
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY l.fecha_subida DESC LIMIT ? OFFSET ?';

    $documentos = [];
    if ($types) {
        $stmt = $conn->prepare($sql);
        $all_params = array_merge($params, [$limit, $offset]);
        $all_types = $types . 'ii';
        $stmt->bind_param($all_types, ...$all_params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $documentos[] = $row;
        }
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $documentos[] = $row;
        }
    }

    // Preparar respuesta
    $response = [
        'documentos' => $documentos,
        'pagination' => [
            'page' => $page,
            'total_pages' => $total_pages,
            'total_documentos' => $total_documentos,
            'limit' => $limit,
            'start' => (($page - 1) * $limit) + 1,
            'end' => min($page * $limit, $total_documentos)
        ],
        'success' => true
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Error en la búsqueda: ' . $e->getMessage(),
        'documentos' => [],
        'pagination' => [
            'page' => 1,
            'total_pages' => 1,
            'total_documentos' => 0,
            'limit' => 12,
            'start' => 0,
            'end' => 0
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
