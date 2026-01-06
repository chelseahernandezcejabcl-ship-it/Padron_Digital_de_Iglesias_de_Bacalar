<?php
/**
 * Estado del Sistema - Información de Configuración
 * Solo accesible desde localhost
 */
session_start();

// Solo accesible desde localhost para seguridad
if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
    die('Esta página solo está disponible en desarrollo local.');
}

require_once 'includes/config_sistema.php';

$config = include 'includes/config_sistema.php';
$es_produccion = !$config['MODO_DESARROLLO'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Sistema - Configuración Actual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
        .status-card { border-left: 5px solid; }
        .status-produccion { border-left-color: #28a745; }
        .status-desarrollo { border-left-color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <!-- Estado Principal -->
                <div class="card status-card <?php echo $es_produccion ? 'status-produccion' : 'status-desarrollo'; ?> mb-4">
                    <div class="card-header <?php echo $es_produccion ? 'bg-success' : 'bg-warning'; ?> text-white">
                        <h4>
                            <?php if ($es_produccion): ?>
                                <i class="fas fa-shield-alt"></i> Sistema en Modo PRODUCCIÓN
                            <?php else: ?>
                                <i class="fas fa-code"></i> Sistema en Modo DESARROLLO
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-cog"></i> Configuración Actual:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Entorno:</strong> 
                                        <span class="badge <?php echo $es_produccion ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $es_produccion ? 'PRODUCCIÓN' : 'DESARROLLO'; ?>
                                        </span>
                                    </li>
                                    <li><strong>Host:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></li>
                                    <li><strong>Mostrar tokens:</strong> 
                                        <?php echo $config['MOSTRAR_TOKENS'] ? '✅ Sí' : '❌ No'; ?>
                                    </li>
                                    <li><strong>Debug de email:</strong> 
                                        <?php echo $config['EMAIL_DEBUG'] ? '✅ Activo' : '❌ Desactivado'; ?>
                                    </li>
                                    <li><strong>Logs detallados:</strong> 
                                        <?php echo $config['LOGS_DETALLADOS'] ? '✅ Activos' : '❌ Desactivados'; ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-shield-alt"></i> Configuración de Seguridad:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Expiración de tokens:</strong> <?php echo $config['TOKEN_EXPIRACION_HORAS']; ?> hora(s)</li>
                                    <li><strong>Máx. intentos/día:</strong> <?php echo $config['MAX_INTENTOS_RECUPERACION']; ?></li>
                                    <li><strong>Timeout email:</strong> <?php echo $config['EMAIL_TIMEOUT']; ?> segundos</li>
                                    <li><strong>Reintentos email:</strong> <?php echo $config['EMAIL_REINTENTOS']; ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Funciones Disponibles -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6><i class="fas fa-key"></i> Sistema de Recuperación</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="recuperar_password.php" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Recuperar Contraseña
                                    </a>
                                    <?php if (!$es_produccion): ?>
                                    <a href="test_envio_email.php" class="btn btn-outline-primary">
                                        <i class="fas fa-flask"></i> Probar Envío Email
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6><i class="fas fa-tools"></i> Herramientas</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="ver_logs.php" class="btn btn-info">
                                        <i class="fas fa-list"></i> Ver Logs
                                    </a>
                                    <?php if (!$es_produccion): ?>
                                    <a href="configurar_email.php" class="btn btn-outline-info">
                                        <i class="fas fa-cog"></i> Configurar Email
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Estado -->
                <?php if ($es_produccion): ?>
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle"></i> ✅ Sistema Listo para Producción</h6>
                    <ul class="mb-0">
                        <li>Los tokens no se muestran en pantalla</li>
                        <li>Solo se envían emails reales</li>
                        <li>Mensajes seguros al usuario</li>
                        <li>Configuración optimizada para seguridad</li>
                    </ul>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> ⚠️ Sistema en Modo Desarrollo</h6>
                    <ul class="mb-0">
                        <li>Se muestran tokens y enlaces directos</li>
                        <li>Logs detallados activados</li>
                        <li>Herramientas de debug disponibles</li>
                        <li>Perfecto para pruebas y desarrollo</li>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Archivos de Configuración -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h6><i class="fas fa-file-code"></i> Archivos de Configuración</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>📁 Archivos Principales:</h6>
                                <ul class="small">
                                    <li><code>includes/config_sistema.php</code> - Configuración automática</li>
                                    <li><code>public/recuperar_password.php</code> - Sistema de recuperación</li>
                                    <li><code>includes/phpmailer_sender.php</code> - Envío de emails</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>📋 Logs Generados:</h6>
                                <ul class="small">
                                    <li><code>logs/email_enviados.log</code> - Emails exitosos</li>
                                    <li><code>logs/email_errores.log</code> - Errores de envío</li>
                                    <li><code>logs/recuperacion_password.log</code> - Tokens generados</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información para Subir a Producción -->
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-rocket"></i> Para Subir a Producción</h6>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li><strong>Configurar email del servidor:</strong> Editar <code>includes/email_configuracion.php</code></li>
                            <li><strong>Subir archivos:</strong> Todo el sistema está listo</li>
                            <li><strong>Verificar dominio:</strong> El sistema detecta automáticamente producción vs desarrollo</li>
                            <li><strong>Probar:</strong> Realizar prueba de recuperación en el servidor real</li>
                        </ol>
                        <div class="alert alert-info mt-3 mb-0">
                            <strong>💡 Detección automática:</strong> El sistema detecta automáticamente si está en localhost (desarrollo) o en un dominio real (producción) y ajusta su comportamiento accordingly.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
