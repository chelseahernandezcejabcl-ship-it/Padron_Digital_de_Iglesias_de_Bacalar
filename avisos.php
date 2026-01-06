<?php
// MIDDLEWARE: Evitar acceso de iglesias logueadas a vista pública
require_once __DIR__ . '/includes/middleware_publico.php';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funciones.php';

// Eliminar avisos vencidos automáticamente
$fecha_actual = date('Y-m-d');
$conn->query("UPDATE avisos_municipales SET activo = 0 WHERE fecha_vencimiento < '$fecha_actual' AND activo = 1");

// Configuración de paginación
$avisos_por_pagina = 5; // Número de avisos por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina_actual = max(1, $pagina_actual); // Asegurar que sea al menos 1

// Obtener total de avisos para calcular páginas (TODOS los activos, sin filtro de dirigido_a)
$sql_total = "SELECT COUNT(*) as total FROM avisos_municipales 
              WHERE activo = 1 
              AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())";
$result_total = $conn->query($sql_total);
$total_avisos = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_avisos / $avisos_por_pagina);

// Calcular offset para la consulta
$offset = ($pagina_actual - 1) * $avisos_por_pagina;

// Obtener avisos municipales activos con paginación (TODOS, sin filtro de dirigido_a)
$sql = "SELECT * FROM avisos_municipales 
        WHERE activo = 1 
        AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
        ORDER BY tipo_aviso DESC, fecha_publicacion DESC
        LIMIT $avisos_por_pagina OFFSET $offset";

$avisos = [];
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $avisos[] = $row;
    }
}

// Obtener configuración del sistema
$config = obtenerConfiguracionSistema();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Avisos Municipales - <?php echo $config['nombre_municipio'] ?? 'Bacalar'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Header styles -->
    <link rel="stylesheet" href="<?php echo generarUrl('assets/css/header-styles.css'); ?>">
    
    <style>
        :root {
            --color-gobierno: #611232;
            --color-gobierno-claro: rgba(97, 18, 50, 0.1);
            --color-secundario: #D2B48C;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            max-width: 800px; /* Más estrecho para avisos verticales */
            margin: 0 auto !important;
            padding: 2rem 1rem;
            padding-top: 120px; /* Espacio fijo para el header */
            flex: 1; /* Para sticky footer */
        }
        
        .hero-title {
            color: #611232;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        
        .hero-title i {
            color: #611232;
            font-size: 2rem;
        }
        
        .hero-subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        /* Diseño de avisos verticales */
        .avisos-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            padding-bottom: 3rem;
        }
        
        .aviso-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            border-left: 6px solid #611232;
            will-change: transform, box-shadow;
            z-index: 1;
            margin-bottom: 2rem;
        }
        
        .aviso-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        
        .aviso-header {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            padding: 1.5rem;
        }
        
        .aviso-imagen {
            flex-shrink: 0;
            width: 200px;
            height: 150px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            order: 2; /* Mueve la imagen a la derecha */
        }
        
        .aviso-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .aviso-content {
            flex: 1;
            min-width: 0;
            order: 1; /* Mantiene el contenido a la izquierda */
        }
        
        .aviso-tipo {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--color-gobierno);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .aviso-titulo {
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            line-height: 1.3;
        }
        
        .aviso-descripcion {
            color: #6c757d;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .aviso-descripcion p {
            margin-bottom: 0.8rem;
        }
        
        .aviso-descripcion p:last-child {
            margin-bottom: 0;
        }
        
        .aviso-footer {
            padding: 0 1.5rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            border-top: 1px solid #f1f3f4;
            margin-top: 1rem;
            padding-top: 1rem;
        }
        
        .aviso-fecha {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-gobierno);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .aviso-fecha i {
            color: var(--color-gobierno);
        }
        
        /* Sin imagen */
        .aviso-sin-imagen .aviso-header {
            padding: 1.5rem;
        }
        
        .aviso-sin-imagen .aviso-content {
            max-width: 100%;
        }
        
        /* Estado de avisos */
        .aviso-urgente {
            border-left-color: #dc3545 !important;
        }
        
        .aviso-urgente .aviso-tipo {
            background: #dc3545;
        }
        
        .aviso-evento {
            border-left-color: #28a745 !important;
        }
        
        .aviso-evento .aviso-tipo {
            background: #28a745;
        }
        
        .aviso-informativo {
            border-left-color: #17a2b8 !important;
        }
        
        .aviso-informativo .aviso-tipo {
            background: #17a2b8;
        }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .main-content {
                max-width: 100%;
                padding-top: 100px; /* Menos espacio en móvil */
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .hero-title {
                font-size: 2rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .aviso-header {
                flex-direction: column;
                gap: 1rem;
                padding: 1.5rem;
            }
            
            .aviso-imagen {
                width: 100%;
                height: 200px;
                align-self: center;
                max-width: 300px;
            }
            
            .aviso-footer {
                padding: 0 1.5rem 1.5rem 1.5rem;
                flex-direction: column;
                gap: 0.8rem;
                align-items: flex-start;
            }
        }
        
        .aviso-evento::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #28a745, #51cf66);
            z-index: 1;
        }
        
        .aviso-general::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #611232, #8b1538);
            z-index: 1;
        }
        
        .aviso-normativo::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ffc107, #ffdb4d);
            z-index: 1;
        }
        
        /* Responsive para móviles */
        @media (max-width: 768px) {
            .aviso-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .aviso-imagen {
                order: 1; /* En móvil, imagen arriba */
                width: 100%;
                height: 180px;
                align-self: stretch;
            }
            
            .aviso-content {
                order: 2; /* En móvil, contenido abajo */
            }
            
            .hero-title {
                font-size: 2rem;
            }
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #dee2e6;
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }
        
        .card-body {
            padding: 1.75rem;
        }
        
        .card-title {
            color: #611232;
            font-weight: 600;
            font-size: 1.35rem;
            margin-bottom: 1.2rem;
            line-height: 1.3;
        }
        
        .card-text {
            color: #495057;
            line-height: 1.7;
            font-size: 1rem;
        }
        
        .card-footer {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-top: 2px solid #dee2e6;
            padding: 1.25rem 1.5rem;
        }
        
        .badge {
            font-size: 0.9rem;
            padding: 0.6rem 0.9rem;
            font-weight: 600;
            border-radius: 12px;
        }
        
        .alert-info {
            background: linear-gradient(135deg, rgba(97, 18, 50, 0.08) 0%, rgba(97, 18, 50, 0.12) 100%);
            border: 1px solid rgba(97, 18, 50, 0.2);
            color: #611232;
            border-radius: 12px;
        }
        
        .btn {
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.7rem 1.3rem;
            border-width: 2px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn.active {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            transform: translateY(-2px);
        }
        
        /* Animaciones y efectos */
        .aviso-item {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .aviso-item:nth-child(1) { animation-delay: 0.1s; }
        .aviso-item:nth-child(2) { animation-delay: 0.2s; }
        .aviso-item:nth-child(3) { animation-delay: 0.3s; }
        .aviso-item:nth-child(4) { animation-delay: 0.4s; }
        .aviso-item:nth-child(5) { animation-delay: 0.5s; }
        .aviso-item:nth-child(6) { animation-delay: 0.6s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .main-content {
                padding: 1.5rem 0.75rem;
                padding-top: 100px; /* Espacio consistente para el header */
            }
            
            .filtros-container {
                padding: 1.5rem 1rem;
                margin-bottom: 2rem;
            }
            
            .d-flex.gap-2 {
                gap: 0.5rem !important;
            }
            
            .btn {
                font-size: 0.9rem;
                padding: 0.6rem 1rem;
            }
            
            .aviso-imagen {
                height: 180px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.9rem;
                letter-spacing: 0.5px;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .btn {
                font-size: 0.85rem;
                padding: 0.5rem 0.9rem;
            }
            
            .filtros-container {
                padding: 1.25rem 0.75rem;
            }
            
            .filtros-container h5 {
                font-size: 1.1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .card-header {
                padding: 1.25rem;
            }
        }
        
        /* Estilos para paginación */
        .pagination .page-link {
            color: var(--color-gobierno);
            border-color: #dee2e6;
            padding: 0.75rem 1rem;
            font-weight: 500;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--color-gobierno);
            border-color: var(--color-gobierno);
            color: white;
            font-weight: 600;
        }
        
        .pagination .page-link:hover {
            background-color: var(--color-gobierno-claro);
            color: var(--color-gobierno);
            border-color: var(--color-gobierno);
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
        }
        
        .pagination-lg .page-link {
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
        }
        
        /* Responsive para paginación */
        @media (max-width: 576px) {
            .pagination-lg .page-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
            
            .pagination .page-item:not(.active):not(.disabled) {
                display: none;
            }
            
            .pagination .page-item.active,
            .pagination .page-item.disabled,
            .pagination .page-item:first-child,
            .pagination .page-item:last-child {
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="main-content">
        <!-- Título principal -->
        <h1 class="hero-title">
            <i class="fas fa-bullhorn"></i>
            Avisos importantes
        </h1>
        <p class="hero-subtitle">
            Información oficial, eventos y comunicados importantes del H. Ayuntamiento de <?php echo $config['nombre_municipio'] ?? 'Bacalar'; ?>
        </p>
        
        
        <!-- Lista de avisos en diseño vertical -->
        <?php if (!empty($avisos)): ?>
            <div class="avisos-container">
                <?php foreach ($avisos as $aviso): 
                    // Determinar el tipo y icono
                    $tipo_class = 'aviso-' . $aviso['tipo_aviso'];
                    $icono = '';
                    switch($aviso['tipo_aviso']) {
                        case 'urgente':
                            $icono = 'fas fa-exclamation-triangle';
                            break;
                        case 'evento':
                            $icono = 'fas fa-calendar-alt';
                            break;
                        case 'general':
                            $icono = 'fas fa-info-circle';
                            break;
                        case 'normativo':
                            $icono = 'fas fa-gavel';
                            break;
                        default:
                            $icono = 'fas fa-bullhorn';
                    }
                ?>
                <div class="aviso-card <?php echo $tipo_class; ?> <?php echo empty($aviso['imagen']) ? 'aviso-sin-imagen' : ''; ?>">
                    <div class="aviso-header">
                        <!-- Imagen del aviso (si existe) -->
                        <?php if (!empty($aviso['imagen'])): ?>
                            <?php 
                            $imagen_path = __DIR__ . '/uploads/avisos/' . $aviso['imagen'];
                            // Generar URL correcta para acceso desde cualquier dispositivo en red local
                            $imagen_url = generarUrl('uploads/avisos/' . rawurlencode($aviso['imagen']));
                            ?>
                            <?php if (file_exists($imagen_path)): ?>
                            <div class="aviso-imagen">
                                <img src="<?php echo $imagen_url; ?>" 
                                     alt="<?php echo htmlspecialchars($aviso['titulo']); ?>"
                                     loading="lazy">
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="aviso-content">
                            <!-- Tipo de aviso -->
                            <span class="aviso-tipo">
                                <i class="<?php echo $icono; ?>"></i>
                                <?php echo ucfirst($aviso['tipo_aviso']); ?>
                            </span>
                            
                            <!-- Título -->
                            <h3 class="aviso-titulo">
                                <?php echo htmlspecialchars($aviso['titulo']); ?>
                            </h3>
                            
                            <!-- Descripción -->
                            <div class="aviso-descripcion">
                                <?php 
                                // Permitir HTML básico pero limpiar contenido peligroso
                                $contenido_limpio = strip_tags($aviso['contenido'], '<p><b><strong><i><em><u><br><ul><ol><li>');
                                echo $contenido_limpio;
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="aviso-footer">
                        <div class="aviso-fecha">
                            <i class="fas fa-calendar"></i>
                            <span>Publicado: <?php echo date('j \d\e F \d\e Y', strtotime($aviso['fecha_publicacion'])); ?></span>
                        </div>
                        <?php if (!empty($aviso['fecha_vencimiento'])): ?>
                        <div class="aviso-fecha">
                            <i class="fas fa-clock"></i>
                            <span>Vigente hasta: <?php echo date('j \d\e F \d\e Y', strtotime($aviso['fecha_vencimiento'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="Paginación de avisos">
                    <ul class="pagination pagination-lg">
                        <!-- Botón Anterior -->
                        <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </span>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Números de página -->
                        <?php
                        // Mostrar máximo 5 páginas alrededor de la actual
                        $inicio = max(1, $pagina_actual - 2);
                        $fin = min($total_paginas, $pagina_actual + 2);
                        
                        // Ajustar si estamos cerca del inicio o final
                        if ($fin - $inicio < 4) {
                            if ($inicio == 1) {
                                $fin = min($total_paginas, $inicio + 4);
                            } else {
                                $inicio = max(1, $fin - 4);
                            }
                        }
                        
                        // Primera página si no está en el rango
                        if ($inicio > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=1">1</a>
                        </li>
                        <?php if ($inicio > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Páginas en el rango -->
                        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                        <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <!-- Última página si no está en el rango -->
                        <?php if ($fin < $total_paginas): ?>
                        <?php if ($fin < $total_paginas - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>"><?php echo $total_paginas; ?></a>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Botón Siguiente -->
                        <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            
            <!-- Información de paginación -->
            <div class="text-center mt-3 mb-4">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Mostrando <?php echo min($avisos_por_pagina, count($avisos)); ?> de <?php echo $total_avisos; ?> avisos
                    (Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?>)
                </p>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-inbox fa-4x text-muted"></i>
                </div>
                <h3 class="text-muted mb-3">No hay avisos disponibles</h3>
                <p class="text-muted">
                    Actualmente no hay avisos municipales publicados. 
                    Por favor, vuelve a consultar más tarde.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
