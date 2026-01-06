<?php
// Archivo para ver los logs de recuperación de contraseña
$log_file = 'logs/recuperacion_password.log';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Recuperación - Sistema Iglesias Bacalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .log-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 600px;
            overflow-y: auto;
        }
        .log-entry {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .log-timestamp {
            color: #0066cc;
            font-weight: bold;
        }
        .log-email {
            color: #cc6600;
            font-weight: bold;
        }
        .log-link {
            color: #009900;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-file-alt me-2"></i>Logs de Recuperación de Contraseña</h2>
                    <div>
                        <a href="../recuperar_password.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Nueva Recuperación
                        </a>
                        <a href="../test_recuperacion.php" class="btn btn-secondary">
                            <i class="fas fa-cog me-1"></i>Panel de Pruebas
                        </a>
                    </div>
                </div>
                
                <?php if (file_exists($log_file)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Solicitudes de Recuperación
                                <small class="text-muted">(<?php echo $log_file; ?>)</small>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="log-container">
                                <?php
                                $content = file_get_contents($log_file);
                                if (!empty($content)) {
                                    // Formatear el contenido para mejor lectura
                                    $content = htmlspecialchars($content);
                                    
                                    // Resaltar diferentes elementos
                                    $content = preg_replace('/(\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/', '<span class="log-timestamp">$1</span>', $content);
                                    $content = preg_replace('/(Recuperación para: )([^\n]+)/', '$1<span class="log-email">$2</span>', $content);
                                    $content = preg_replace('/(Enlace: )(http[^\n]+)/', '$1<span class="log-link">$2</span>', $content);
                                    
                                    echo nl2br($content);
                                } else {
                                    echo '<p class="text-muted">El archivo de log está vacío.</p>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Los enlaces de recuperación expiran en 1 hora. En producción, estos logs se enviarían por email.
                            </small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>No hay logs disponibles</h5>
                        <p>Aún no se han realizado solicitudes de recuperación de contraseña.</p>
                        <p>Para generar logs, visita la <a href="../recuperar_password.php">página de recuperación</a> y realiza una prueba.</p>
                    </div>
                <?php endif; ?>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-question-circle me-2"></i>¿Cómo usar estos logs?</h6>
                            </div>
                            <div class="card-body">
                                <ol class="small">
                                    <li>Los usuarios solicitan recuperación desde login</li>
                                    <li>El sistema genera un token y lo guarda en BD</li>
                                    <li>El enlace se registra en este log</li>
                                    <li>Copia el enlace y úsalo para resetear</li>
                                    <li>En producción, se envía por email automáticamente</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-shield-alt me-2"></i>Seguridad</h6>
                            </div>
                            <div class="card-body">
                                <ul class="small">
                                    <li>Tokens expiran en 1 hora</li>
                                    <li>Se limpian al usar o expirar</li>
                                    <li>Solo un token activo por usuario</li>
                                    <li>Logs deben protegerse en producción</li>
                                    <li>Eliminar logs periódicamente</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
