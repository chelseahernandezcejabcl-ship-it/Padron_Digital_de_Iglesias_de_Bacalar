<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pruebas - Sistema de Recuperación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --color-gobierno: #611232; }
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .test-card { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn-gobierno { background: var(--color-gobierno); border: none; color: white; }
        .btn-gobierno:hover { background: #4A0E27; color: white; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="fas fa-vial me-2"></i>Panel de Pruebas - Recuperación de Contraseña</h1>
                <p class="text-muted">Sistema configurado en modo desarrollo. Los tokens se mostrarán directamente en pantalla.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="test-card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-church me-2"></i>Probar Iglesias</h5>
                    </div>
                    <div class="card-body">
                        <h6>Correos de prueba disponibles:</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>emanuel@iglesias-bacalar.com</strong><br>
                                <small class="text-muted">Iglesia Cristiana Emanuel</small>
                            </li>
                            <li class="list-group-item">
                                <strong>gabs@gmail.com</strong><br>
                                <small class="text-muted">Gabriel de la Cruz</small>
                            </li>
                            <li class="list-group-item">
                                <strong>gabrielitogg@gmail.com</strong><br>
                                <small class="text-muted">prueba denominación</small>
                            </li>
                        </ul>
                        <div class="mt-3">
                            <a href="recuperar_password.php" class="btn btn-primary w-100">
                                <i class="fas fa-key me-2"></i>Probar Recuperación
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="test-card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-user-shield me-2"></i>Probar Administradores</h5>
                    </div>
                    <div class="card-body">
                        <h6>Para probar administradores:</h6>
                        <ol>
                            <li>Ve a <a href="recuperar_password.php">recuperar contraseña</a></li>
                            <li>Selecciona "Administrador"</li>
                            <li>Usa un correo de admin registrado</li>
                            <li>El token aparecerá en pantalla</li>
                        </ol>
                        <div class="alert alert-info mt-3">
                            <small><i class="fas fa-info-circle me-1"></i>Si no hay admins, créalos desde el panel de administración</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="test-card">
                    <div class="card-header bg-warning text-dark">
                        <h6><i class="fas fa-bug me-2"></i>Debug</h6>
                    </div>
                    <div class="card-body">
                        <a href="debug_recuperacion.php" class="btn btn-warning w-100">
                            <i class="fas fa-search me-2"></i>Debug Avanzado
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="test-card">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-file-alt me-2"></i>Logs</h6>
                    </div>
                    <div class="card-body">
                        <a href="ver_logs.php" class="btn btn-info w-100">
                            <i class="fas fa-eye me-2"></i>Ver Logs
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="test-card">
                    <div class="card-header bg-secondary text-white">
                        <h6><i class="fas fa-sign-in-alt me-2"></i>Login</h6>
                    </div>
                    <div class="card-body">
                        <a href="login.php" class="btn btn-secondary w-100">
                            <i class="fas fa-door-open me-2"></i>Ir al Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="test-card">
                    <div class="card-header bg-dark text-white">
                        <h5><i class="fas fa-cogs me-2"></i>Configuración del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>✅ Estado Actual:</h6>
                                <ul class="list-unstyled">
                                    <li>✅ Campos BD agregados</li>
                                    <li>✅ Modo desarrollo activo</li>
                                    <li>✅ Tokens en pantalla</li>
                                    <li>✅ Logs funcionando</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>🔧 Para Producción:</h6>
                                <ul class="list-unstyled">
                                    <li>📧 Configurar SMTP</li>
                                    <li>🔧 Cambiar MODO_DESARROLLO = false</li>
                                    <li>📮 Instalar PHPMailer</li>
                                    <li>🔒 Proteger logs</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
