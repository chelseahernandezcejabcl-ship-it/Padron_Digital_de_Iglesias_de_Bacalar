<?php
// MIDDLEWARE: Evitar acceso de iglesias logueadas a vista p�blica
require_once __DIR__ . '/includes/middleware_publico.php';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funciones.php';

// Obtener tipos de documento desde la base de datos (detecci�n autom�tica)
$tipos_documento = [];
$category_field = null;

// Usar directamente el campo 'categoria' que existe en la tabla
$category_field = 'categoria';

// Si encontramos un campo v�lido, obtener los tipos
if ($category_field) {
    $tipos_result = $conn->query("SELECT DISTINCT $category_field FROM libros WHERE $category_field IS NOT NULL AND $category_field != '' ORDER BY $category_field ASC");
    if ($tipos_result) {
        while ($row = $tipos_result->fetch_assoc()) {
            $tipos_documento[] = $row[$category_field];
        }
    }
}

$busqueda = $_GET['busqueda'] ?? '';
$tipo_documento = $_GET['categoria'] ?? '';

// Paginaci�n
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Asegurar que sea al menos 1
$limit = 12; // Documentos por p�gina
$offset = ($page - 1) * $limit;

// Construir consulta con filtros (usando el campo correcto detectado)
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

// Usar el campo de categor�a correcto si existe
if ($tipo_documento && $category_field) {
    $where[] = "$category_field = ?";
    $params[] = $tipo_documento;
    $types .= 's';
}

// Consulta para contar total de documentos
$count_sql = 'SELECT COUNT(*) as total FROM libros';
if ($where) $count_sql .= ' WHERE ' . implode(' AND ', $where);

$total_documentos = 0;
if ($types && !empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_documentos = $count_result->fetch_assoc()['total'];
} else {
    $count_result = $conn->query($count_sql);
    if ($count_result) {
        $total_documentos = $count_result->fetch_assoc()['total'];
    }
}

$total_pages = ceil($total_documentos / $limit);

// Consulta principal con paginación (usar campos b�sicos)
$sql = 'SELECT l.*, ua.nombre_completo as subido_por_nombre 
        FROM libros l 
        LEFT JOIN usuarios_admin ua ON l.subido_por = ua.id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY l.id DESC LIMIT ? OFFSET ?';

$documentos = [];
if ($types) {
    $stmt = $conn->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
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

// Obtener configuración del sistema
$config = obtenerConfiguracionSistema();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca Digital - <?php echo $config['titulo_sistema'] ?? 'Padrón de Iglesias'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo generarUrl('assets/css/header-styles.css'); ?>">
    <style>
        /* Estilos específicos para evitar interferir con el header */
        .gob-header-unificado {
            position: relative !important;
            z-index: 1000 !important;
        }
        
        :root {
            --color-gobierno: #611232;
            --color-gobierno-claro: rgba(97, 18, 50, 0.1);
            --color-secundario: #D2B48C;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto !important;
            padding: 2rem 1rem;
            padding-top: 120px; /* Espacio fijo para el header */
            padding-bottom: 5rem; /* Espacio para evitar superposici�n del footer */
            min-height: calc(100vh - 200px);
        }
        
        .hero-title {
            color: #611232;
            font-size: 2.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #611232 0%, #8B4513 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 1.2rem;
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        
        .btn-outline-secondary {
            border: 2px solid #6c757d !important;
            color: #6c757d !important;
            background: transparent;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 45px;
        }
        
        .btn-outline-secondary:hover {
            background: #6c757d !important;
            color: white !important;
            border-color: #6c757d !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
            text-decoration: none !important;
        }
        /* Estilos para las estadísticas */
        .stats-card {
            background: linear-gradient(135deg, var(--color-gobierno) 0%, rgba(97, 18, 50, 0.9) 100%);
            color: white;
            border-radius: 12px;
            padding: 1.8rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(97, 18, 50, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(97, 18, 50, 0.4);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            line-height: 1;
        }
        
        .stats-label {
            opacity: 0.95;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.2;
        }
        
        .filtros-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            position: relative;
            overflow: hidden;
        }
        
        .filtros-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-gobierno), #8B4513, var(--color-gobierno));
        }
        
        .filtros-container h5 {
            color: #611232;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .filtros-activos .badge {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            animation: fadeInUp 0.3s ease;
        }
        
        .resultados-header {
            background: linear-gradient(135deg, rgba(97, 18, 50, 0.05) 0%, rgba(139, 69, 19, 0.05) 100%);
            border-radius: 15px;
            padding: 1.5rem;
            border-left: 4px solid var(--color-gobierno);
            margin-bottom: 2rem;
            animation: slideInLeft 0.5s ease;
        }
        
        .form-control-lg {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control-lg:focus {
            border-color: var(--color-gobierno);
            box-shadow: 0 0 0 0.2rem rgba(97, 18, 50, 0.25);
            transform: translateY(-1px);
        }
        
        .btn-gobierno {
            background: linear-gradient(135deg, var(--color-gobierno) 0%, #4a0d28 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 45px;
        }
        
        .btn-gobierno::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-gobierno:hover::before {
            left: 100%;
        }
        
        .btn-gobierno:hover {
            background: linear-gradient(135deg, #4a0d28 0%, var(--color-gobierno) 100%);
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(97, 18, 50, 0.3);
            text-decoration: none !important;
        }
        
        .btn-outline-success {
            border: 2px solid #28a745 !important;
            color: #28a745 !important;
            background: transparent;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
        }
        
        .btn-outline-success:hover {
            background: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            text-decoration: none !important;
        }
        
        .document-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            max-height: 450px;
        }
        
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        /* Estilos para cards de documentos - SIMPLIFICADOS */
        .document-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            max-height: 450px;
        }
        
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        /* Imagen de portada */
        .card-img-top-container {
            position: relative;
            height: 150px;
            overflow: hidden;
        }
        
        .card-img-top {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .card-img-placeholder {
            height: 100%;
            background: linear-gradient(135deg, var(--color-gobierno) 0%, #4a0d28 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .card-img-placeholder i {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        /* Badge de categoría */
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(97, 18, 50, 0.9);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* Cuerpo de la card */
        .card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 150px); /* Altura total menos imagen */
        }
        
        .card-title {
            color: var(--color-gobierno);
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .card-text {
            color: #555;
            font-size: 0.85rem;
            line-height: 1.4;
            margin-bottom: 0.75rem;
            flex-grow: 1;
        }
        
        .document-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--color-gobierno-claro) 0%, rgba(139, 69, 19, 0.1) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1.5rem auto 1rem;
            color: var(--color-gobierno);
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(97, 18, 50, 0.2);
        }
        
        .document-type {
            background: linear-gradient(135deg, var(--color-gobierno) 0%, #8B4513 100%);
            color: white;
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 1rem;
            text-transform: capitalize;
            font-weight: 600;
        }
        
        .document-date {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .document-author {
            color: var(--color-gobierno);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--color-gobierno-claro);
        }
        
        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .main-content {
                padding-top: 100px; /* Menos espacio en m�vil */
                padding-left: 1rem;
                padding-right: 1rem;
                padding-bottom: 4rem; /* M�s espacio en m�vil para evitar superposici�n */
            }
            
            .filtros-container {
                padding: 1rem;
            }
            
            .btn-gobierno, .btn-outline-secondary {
                margin-bottom: 0.5rem;
            }
            
            .document-card {
                max-height: 380px;
            }
            
            .card-img-top-container {
                height: 120px;
            }
        }
        
        /* Forzar footer a ancho completo escapando del contenedor */
        footer {
            position: relative !important;
            left: 50% !important;
            right: 50% !important;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            width: 100vw !important;
            max-width: 100vw !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        /* Prevenir overflow horizontal y espacios extra */
        html, body {
            overflow-x: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Eliminar espacios del contenedor principal */
        html {
            height: 100% !important;
        }
        
        /* Estilos para la paginaci�n - evitar superposici�n del footer */
        .pagination {
            margin-bottom: 3rem !important;
        }
        
        @media (max-width: 768px) {
            .pagination {
                margin-bottom: 2rem !important;
            }
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/includes/header.php'; ?>
    
    <div class="main-content">
        <!-- Título principal -->
        <h1 class="hero-title">BIBLIOTECA DIGITAL</h1>
        <p class="hero-subtitle">
            Accede a documentos, reglamentos y recursos oficiales del Padrón Digital de Iglesias de Bacalar.
            <br><strong>Portal exclusivo para iglesias</strong>
        </p>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-file-alt fa-2x text-white"></i>
                        </div>
                        <div>
                            <div class="stats-number"><?php echo $total_documentos; ?></div>
                            <div class="stats-label">Documentos</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-folder-open fa-2x text-white"></i>
                        </div>
                        <div>
                            <div class="stats-number"><?php echo count($tipos_documento); ?></div>
                            <div class="stats-label">Categorías</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-search fa-lg me-2"></i>
                            <i class="fas fa-book-open fa-lg me-2"></i>
                            <i class="fas fa-download fa-lg"></i>
                            <div class="stats-label mt-2">Busca     Lee     Descarga</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Buscador y filtros -->
        <div class="filtros-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de búsqueda</h5>
            </div>
            
            <!-- Mostrar filtros activos -->
            <?php if ($busqueda || $tipo_documento): ?>
                <div class="filtros-activos mb-3">
                    <small class="text-muted me-2">Filtros activos:</small>
                    <?php if ($busqueda): ?>
                        <span class="badge bg-primary me-2">
                            <i class="fas fa-search me-1"></i>Búsqueda: "<?php echo htmlspecialchars($busqueda); ?>"
                        </span>
                    <?php endif; ?>
                    <?php if ($tipo_documento): ?>
                        <span class="badge bg-success me-2">
                            <i class="fas fa-tag me-1"></i>Tipo: <?php echo ucfirst($tipo_documento); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form method="get" id="filtrosForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="busqueda" class="form-label fw-bold">
                            <i class="fas fa-search me-1"></i>Buscar documento:
                        </label>
                        <input type="text" id="busqueda" name="busqueda" class="form-control form-control-lg" 
                               placeholder="Título, autor o contenido..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="categoria" class="form-label fw-bold">
                            <i class="fas fa-tags me-1"></i>Categoría:
                        </label>
                        <select id="categoria" name="categoria" class="form-control form-control-lg">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($tipos_documento as $tipo): ?>
                                <option value="<?php echo $tipo; ?>" <?php if ($tipo_documento == $tipo) echo 'selected'; ?>>
                                    <?php echo ucfirst($tipo); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-gobierno btn-lg w-100">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold" style="visibility: hidden;">Acción:</label>
                        <button type="button" id="btnLimpiar" class="btn btn-outline-secondary btn-lg w-100">
                            <i class="fas fa-eraser me-2"></i>Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Contador de resultados -->
        <div class="resultados-header mb-4">
            <div class="resultados-info">
                <h4 class="mb-1">
                    <i class="fas fa-books me-2 text-primary"></i>
                    Documentos encontrados
                </h4>
                <p class="text-muted mb-0">
                    <?php 
                    $total_docs = count($documentos);
                    echo $total_docs === 1 ? '1 documento encontrado' : "$total_docs documentos encontrados";
                    if ($busqueda || $tipo_documento) {
                        echo ' con los filtros aplicados';
                    }
                    ?>
                </p>
            </div>
        </div>
        
        <!-- Resultados -->
        <div id="loading-indicator" style="display: none;" class="text-center my-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Buscando documentos...</p>
        </div>
        
        <div id="resultados-container">
            <?php if ($documentos): ?>
                <div class="row">
                    <?php foreach ($documentos as $documento): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="document-card h-100">
                            <!-- Imagen de portada -->
                            <div class="card-img-top-container">
                                <?php if ($documento['imagen_portada'] && file_exists(__DIR__ . '/uploads/portadas/' . $documento['imagen_portada'])): ?>
                                    <img src="<?php echo BASE_URL; ?>uploads/portadas/<?php echo $documento['imagen_portada']; ?>" 
                                         class="card-img-top" alt="Portada de <?php echo htmlspecialchars($documento['titulo']); ?>">
                                <?php else: ?>
                                    <div class="card-img-placeholder">
                                        <?php
                                        $icons = [
                                            'Libros' => 'fas fa-book',
                                            'Reglamentos' => 'fas fa-gavel',
                                            'Guías' => 'fas fa-book-open',
                                            'Manuales' => 'fas fa-book-reader',
                                            'Protocolos' => 'fas fa-clipboard-list',
                                            'Normativas' => 'fas fa-file-contract',
                                            'Folletos' => 'fas fa-file-alt',
                                            'Informativo' => 'fas fa-info-circle',
                                            'Otros' => 'fas fa-file'
                                        ];
                                        // Usar �cono basado en la categor�a real
                                        $doc_category = '';
                                        if ($category_field && isset($documento[$category_field])) {
                                            $doc_category = $documento[$category_field];
                                        }
                                        $icon = $icons[$doc_category] ?? 'fas fa-file-alt';
                                        ?>
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                <?php endif; ?>
                                <!-- Badge de categoría -->
                                <span class="category-badge">
                                    <?php 
                                    if ($category_field && isset($documento[$category_field]) && !empty($documento[$category_field])) {
                                        echo htmlspecialchars(ucfirst($documento[$category_field]));
                                    } else {
                                        echo 'Documento';
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <!-- Título principal -->
                                <h5 class="card-title"><?php echo htmlspecialchars($documento['titulo']); ?></h5>
                                
                                <!-- Descripción -->
                                <?php if ($documento['descripcion']): ?>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars(substr($documento['descripcion'], 0, 80)); ?>
                                        <?php if (strlen($documento['descripcion']) > 80) echo '...'; ?>
                                    </p>
                                <?php else: ?>
                                    <p class="card-text text-muted fst-italic">
                                        Sin descripción disponible
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Botones de acción - SIEMPRE VISIBLES -->
                                <div class="mt-auto pt-2" style="flex-shrink: 0;">
                                    <div class="d-grid gap-2 mb-2">
                                        <a href="leer_documento.php?id=<?php echo $documento['id']; ?>" 
                                           class="btn btn-gobierno">
                                            <i class="fas fa-eye me-2"></i>Leer documento
                                        </a>
                                        <a href="descargar_documento.php?id=<?php echo $documento['id']; ?>" 
                                           class="btn btn-outline-success" 
                                           target="_blank">
                                            <i class="fas fa-download me-2"></i>Descargar PDF
                                        </a>
                                    </div>
                                    <!-- Información adicional pequeña -->
                                    <small class="text-muted d-block text-center">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($documento['fecha_subida'])); ?>
                                        <span class="ms-2">
                                            <i class="fas fa-download me-1"></i>
                                            <?php echo isset($documento['descargas']) ? $documento['descargas'] : 0; ?> descargas
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h5>No se encontraron documentos</h5>
                <p class="mb-3">Intenta con otros términos de búsqueda o selecciona un tipo de documento diferente.</p>
                <?php if ($busqueda || $tipo_documento): ?>
                    <button class="btn btn-outline-primary" onclick="limpiarFiltros()">
                        <i class="fas fa-refresh me-2"></i>Ver todos los documentos
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-container">
            <?php if ($total_pages > 1): ?>
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-12">
                <nav aria-label="Navegación de biblioteca">
                    <ul class="pagination justify-content-center">
                        <!-- Botón Anterior -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo $tipo_documento ? '&categoria=' . urlencode($tipo_documento) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Anterior
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-left"></i> Anterior</span>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Números de página -->
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1<?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo $tipo_documento ? '&categoria=' . urlencode($tipo_documento) : ''; ?>">1</a>
                            </li>
                            <?php if ($start > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif;
                        endif;
                        
                        for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo $tipo_documento ? '&categoria=' . urlencode($tipo_documento) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor;
                        
                        if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo $tipo_documento ? '&categoria=' . urlencode($tipo_documento) : ''; ?>"><?php echo $total_pages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Botón Siguiente -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo $tipo_documento ? '&categoria=' . urlencode($tipo_documento) : ''; ?>">
                                    Siguiente <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Siguiente <i class="fas fa-chevron-right"></i></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <!-- Información de paginación -->
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Mostrando <?php echo (($page - 1) * $limit) + 1; ?> - <?php echo min($page * $limit, $total_documentos); ?> 
                        de <?php echo $total_documentos; ?> documentos
                        <?php if ($total_pages > 1): ?>
                            (Página <?php echo $page; ?> de <?php echo $total_pages; ?>)
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    </div>
    
    <?php include_once __DIR__ . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales para el JavaScript - usar la ruta exacta que funciona en PHP
        window.BIBLIOTECA_BASE_URL = '<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>';
        window.UPLOADS_PATH = window.BIBLIOTECA_BASE_URL + 'uploads/portadas/';
    </script>
    <script src="assets/js/biblioteca-ajax.js"></script>
    <script>
        // Inicializar variables con datos del servidor
        currentBusqueda = '<?php echo htmlspecialchars($busqueda); ?>';
        currentCategoria = '<?php echo htmlspecialchars($tipo_documento); ?>';
        currentPage = <?php echo $page; ?>;
        
        console.log('🚀 Biblioteca inicializada con:', { currentBusqueda, currentCategoria, currentPage });
    </script>
</body>
</html>
