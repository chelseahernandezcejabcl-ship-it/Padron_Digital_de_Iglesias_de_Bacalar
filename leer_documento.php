<?php
// Vista de detalle y lector PDF para Biblioteca Digital - Padrón de Iglesias de Bacalar
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funciones.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    header('Location: biblioteca.php');
    exit;
}

// Obtener documento de la tabla libros
$res = $conn->query("SELECT bd.*, ua.nombre_completo as subido_por_nombre 
                     FROM libros bd 
                     LEFT JOIN usuarios_admin ua ON bd.subido_por = ua.id 
                     WHERE bd.id = $id");
$documento = $res ? $res->fetch_assoc() : null;

if (!$documento) {
    header('Location: biblioteca.php');
    exit;
}

// Ruta para verificar existencia del archivo
$pdf_path = 'uploads/libros/' . $documento['archivo_pdf'];
// URL completa para acceso desde red local
$pdf_url = generarUrl('uploads/libros/' . $documento['archivo_pdf']);

// Obtener configuración del sistema
$config = obtenerConfiguracionSistema();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($documento['titulo']); ?> - Biblioteca Digital</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo generarUrl('assets/css/header-styles.css'); ?>">
    <style>
        :root {
            --color-gobierno: #611232;
            --color-gobierno-claro: rgba(97, 18, 50, 0.1);
            --color-secundario: #D2B48C;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 100px auto 0 auto !important;
            padding: 2rem 1rem;
        }
        
        .document-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .document-icon {
            width: 80px;
            height: 80px;
            background-color: var(--color-gobierno-claro);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-gobierno);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .document-type {
            background-color: var(--color-gobierno);
            color: white;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            display: inline-block;
            margin-bottom: 1rem;
            text-transform: capitalize;
        }
        
        .btn-gobierno {
            background-color: var(--color-gobierno);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-gobierno:hover {
            background-color: #4a0d28;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(97, 18, 50, 0.3);
        }
        
        .pdf-viewer { 
            width: 100%; 
            height: 80vh; 
            border: 2px solid var(--color-gobierno);
            border-radius: 10px;
        }
        
        .viewer-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .document-meta {
            background-color: var(--color-gobierno-claro);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .meta-item:last-child {
            margin-bottom: 0;
        }
        
        .meta-item i {
            color: var(--color-gobierno);
            width: 20px;
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .document-header {
                padding: 1.5rem;
            }
            
            .pdf-viewer {
                height: 60vh;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="main-content">
        <!-- Botón de regreso -->
        <a href="biblioteca.php" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i>Volver al catálogo
        </a>
        
        <div class="row">
            <!-- Panel de información del documento -->
            <div class="col-lg-4 mb-4">
                <div class="document-header">
                    <!-- Icono según tipo de documento -->
                    <div class="document-icon mx-auto">
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
                        $icon = $icons[$documento['categoria']] ?? 'fas fa-file-alt';
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    
                    <div class="text-center">
                        <div class="document-type"><?php echo ucfirst($documento['categoria']); ?></div>
                        <h3 class="mb-3"><?php echo htmlspecialchars($documento['titulo']); ?></h3>
                        <p class="h5 text-muted">Por: <?php echo htmlspecialchars($documento['autor']); ?></p>
                    </div>
                    
                    <!-- Metadatos del documento -->
                    <div class="document-meta mt-4">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Fecha de publicación: <?php echo date('d/m/Y', strtotime($documento['fecha_subida'])); ?></span>
                        </div>
                        
                        <?php if ($documento['subido_por_nombre']): ?>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span>Subido por: <?php echo htmlspecialchars($documento['subido_por_nombre']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <i class="fas fa-file"></i>
                            <span>Tipo: <?php echo ucfirst($documento['categoria']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Descripción -->
                    <?php if ($documento['descripcion']): ?>
                    <div class="mt-3">
                        <h6 class="fw-bold">Descripción:</h6>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($documento['descripcion'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Botones de acción -->
                    <div class="d-grid gap-2 mt-4">
                        <?php if (!empty($documento['archivo_pdf']) && file_exists($pdf_path)): ?>
                            <a href="<?php echo $pdf_url; ?>" class="btn btn-gobierno" target="_blank">
                                <i class="fas fa-download me-2"></i>Descargar PDF
                            </a>
                            <button onclick="toggleFullscreen()" class="btn btn-outline-secondary">
                                <i class="fas fa-expand me-2"></i>Pantalla completa
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Visualizador de PDF -->
            <div class="col-lg-8">
                <div class="viewer-container">
                    <h5 class="mb-3">
                        <i class="fas fa-file-pdf text-danger me-2"></i>Vista del documento
                    </h5>
                    
                    <?php if (!empty($documento['archivo_pdf']) && file_exists($pdf_path)): ?>
                        <iframe id="pdf-viewer" class="pdf-viewer" 
                                src="<?php echo BASE_URL; ?>uploads/libros/<?php echo $documento['archivo_pdf']; ?>#toolbar=1&navpanes=1&scrollbar=1">
                        </iframe>
                        
                        <!-- Fallback para navegadores que no soportan PDFs embebidos -->
                        <div class="pdf-fallback text-center mt-3" style="display: none;">
                            <p class="mb-3">Su navegador no puede mostrar el PDF directamente.</p>
                            <a href="<?php echo BASE_URL; ?>uploads/libros/<?php echo $documento['archivo_pdf']; ?>" 
                               class="btn btn-gobierno" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>Abrir PDF en nueva pestaña
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h5>Archivo no disponible</h5>
                            <p class="mb-0">No hay archivo PDF disponible para este documento o el archivo no se encuentra en el servidor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once __DIR__ . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Detectar si el PDF se carga correctamente
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('pdf-viewer');
            const fallback = document.querySelector('.pdf-fallback');
            
            if (iframe && fallback) {
                iframe.onerror = function() {
                    iframe.style.display = 'none';
                    fallback.style.display = 'block';
                };
                
                // Timeout para mostrar fallback si el PDF no carga en 5 segundos
                setTimeout(function() {
                    if (!iframe.contentDocument || !iframe.contentDocument.body) {
                        try {
                            // Intentar acceder al contenido del iframe
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            if (!iframeDoc.body || iframeDoc.body.innerHTML === '') {
                                iframe.style.display = 'none';
                                fallback.style.display = 'block';
                            }
                        } catch(e) {
                            // Si hay errores de CORS, probablemente el PDF se está cargando correctamente
                            console.log('PDF cargándose normalmente');
                        }
                    }
                }, 3000);
            }
        });
        
        function toggleFullscreen() {
            const viewer = document.getElementById('pdf-viewer');
            if (viewer) {
                if (viewer.requestFullscreen) {
                    viewer.requestFullscreen();
                } else if (viewer.webkitRequestFullscreen) {
                    viewer.webkitRequestFullscreen();
                } else if (viewer.msRequestFullscreen) {
                    viewer.msRequestFullscreen();
                }
            }
        }
    </script>
</body>
</html>
