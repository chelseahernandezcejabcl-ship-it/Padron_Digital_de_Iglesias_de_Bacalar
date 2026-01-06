<?php
/**
 * Configurador de Email Personal
 * Página para configurar tus credenciales de email
 */
session_start();

// Solo accesible desde localhost para seguridad
if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
    die('Esta página solo está disponible en desarrollo local.');
}

$mensaje = '';
$tipo_mensaje = 'info';
$config_file = __DIR__ . '/includes/email_configuracion.php';

if ($_POST && isset($_POST['guardar_config'])) {
    $proveedor = $_POST['proveedor'];
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $mensaje = 'Por favor completa todos los campos.';
        $tipo_mensaje = 'danger';
    } else {
        // Generar archivo de configuración
        $config_content = "<?php\n";
        $config_content .= "/**\n * Configuración de Email - Generado automáticamente\n */\n\n";
        
        if ($proveedor === 'gmail') {
            $config_content .= "\$email_config = [\n";
            $config_content .= "    'host' => 'smtp.gmail.com',\n";
            $config_content .= "    'port' => 587,\n";
            $config_content .= "    'encryption' => 'tls',\n";
            $config_content .= "    'auth' => true,\n";
            $config_content .= "    'username' => '$email',\n";
            $config_content .= "    'password' => '$password',\n";
            $config_content .= "    'from_email' => '$email',\n";
            $config_content .= "    'from_name' => 'Sistema de Iglesias - Bacalar'\n";
            $config_content .= "];\n\n";
            $config_content .= "return \$email_config;\n";
        } elseif ($proveedor === 'outlook') {
            $config_content .= "\$email_config = [\n";
            $config_content .= "    'host' => 'smtp-mail.outlook.com',\n";
            $config_content .= "    'port' => 587,\n";
            $config_content .= "    'encryption' => 'tls',\n";
            $config_content .= "    'auth' => true,\n";
            $config_content .= "    'username' => '$email',\n";
            $config_content .= "    'password' => '$password',\n";
            $config_content .= "    'from_email' => '$email',\n";
            $config_content .= "    'from_name' => 'Sistema de Iglesias - Bacalar'\n";
            $config_content .= "];\n\n";
            $config_content .= "return \$email_config;\n";
        }
        
        if (file_put_contents($config_file, $config_content)) {
            $mensaje = '✅ Configuración guardada exitosamente. Ya puedes probar el envío de emails.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error al guardar la configuración.';
            $tipo_mensaje = 'danger';
        }
    }
}

// Verificar si ya existe configuración
$config_existe = file_exists($config_file);
$config_actual = '';
if ($config_existe) {
    $config_actual = file_get_contents($config_file);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Email Personal - Sistema de Iglesias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
        .card { box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; }
        .btn-gobierno { background: #611232; border-color: #611232; }
        .btn-gobierno:hover { background: #8b1538; border-color: #8b1538; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <!-- Header -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-envelope-open-text"></i> Configurar Tu Email Personal</h4>
                        <p class="mb-0">Configura tu email para recibir las recuperaciones de contraseña</p>
                    </div>
                </div>

                <!-- Mensajes -->
                <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                    <?php echo $mensaje; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Instrucciones -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-info-circle"></i> Instrucciones Importantes</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>📧 Para Gmail:</strong></h6>
                                <ol>
                                    <li>Ve a <a href="https://myaccount.google.com/security" target="_blank">Seguridad de Google</a></li>
                                    <li>Activa "Verificación en 2 pasos"</li>
                                    <li>Genera una "Contraseña de aplicación"</li>
                                    <li>Usa esa contraseña aquí (no tu contraseña normal)</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>📧 Para Outlook/Hotmail:</strong></h6>
                                <ol>
                                    <li>Ve a <a href="https://account.microsoft.com/security" target="_blank">Seguridad de Microsoft</a></li>
                                    <li>Activa "Verificación en dos pasos"</li>
                                    <li>Crea una "Contraseña de aplicación"</li>
                                    <li>Usa esa contraseña aquí</li>
                                </ol>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <strong>⚠️ Importante:</strong> Nunca uses tu contraseña normal del email. Siempre usa una "contraseña de aplicación" para mayor seguridad.
                        </div>
                    </div>
                </div>

                <!-- Formulario de Configuración -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-cog"></i> Configurar Credenciales</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="proveedor" class="form-label">Proveedor de Email</label>
                                    <select class="form-select" id="proveedor" name="proveedor" required>
                                        <option value="">Selecciona tu proveedor</option>
                                        <option value="gmail">📧 Gmail</option>
                                        <option value="outlook">📧 Outlook/Hotmail</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Tu Dirección de Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="tu-email@gmail.com">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña de Aplicación</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Tu contraseña de aplicación (no la normal)">
                                <div class="form-text">
                                    <i class="fas fa-shield-alt text-warning"></i> 
                                    Usa una contraseña de aplicación, NO tu contraseña normal del email.
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="guardar_config" class="btn btn-gobierno btn-lg">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estado Actual -->
                <?php if ($config_existe): ?>
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h6><i class="fas fa-check-circle"></i> Configuración Actual</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            ✅ Ya tienes una configuración de email guardada.
                        </div>
                        <div class="d-grid gap-2">
                            <a href="recuperar_password.php" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Probar Recuperación de Contraseña
                            </a>
                            <a href="test_envio_email.php" class="btn btn-outline-primary">
                                <i class="fas fa-flask"></i> Hacer Prueba de Envío
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Links Útiles -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6><i class="fas fa-link"></i> Enlaces Útiles:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><a href="https://support.google.com/accounts/answer/185833" target="_blank">🔗 Contraseñas de aplicación Gmail</a></li>
                                    <li><a href="https://support.microsoft.com/account-billing/using-app-passwords-with-apps-that-don-t-support-two-step-verification-5896ed9b-4263-e681-128a-a6f2979a7944" target="_blank">🔗 Contraseñas de aplicación Outlook</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><a href="ver_logs.php">📋 Ver Logs de Sistema</a></li>
                                    <li><a href="test_email_recovery.php">🧪 Panel de Pruebas</a></li>
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
