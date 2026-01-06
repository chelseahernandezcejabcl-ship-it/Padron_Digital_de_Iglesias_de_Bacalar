<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/funciones.php';
require_once 'includes/config_sistema.php';

$mensaje = '';
$tipo_mensaje = 'info';

// Cargar configuración del sistema
$config_sistema = include 'includes/config_sistema.php';
$MODO_DESARROLLO = $config_sistema['MODO_DESARROLLO'];
$MOSTRAR_TOKENS = $config_sistema['MOSTRAR_TOKENS'];

// Función para generar token seguro
function generarToken() {
    return bin2hex(random_bytes(32));
}

// Función para enviar email real con PHPMailer
function enviarEmailRecuperacion($correo, $nombre, $token, $tipo_usuario = 'iglesia') {
    global $MODO_DESARROLLO;
    
    // Incluir el sistema de PHPMailer
    require_once 'includes/phpmailer_sender.php';
    
    if ($MODO_DESARROLLO) {
        // En desarrollo: guardar en log Y enviar email real
        $enlace = generarUrl("resetear_password.php?token=" . $token . "&tipo=" . $tipo_usuario);
        $log = "[" . date('Y-m-d H:i:s') . "] Recuperación para: $correo\n";
        $log .= "Nombre: $nombre\n";
        $log .= "Enlace: $enlace\n";
        $log .= "Token: $token\n";
        $log .= "Expira: " . date('Y-m-d H:i:s', strtotime('+1 hour')) . "\n\n";
        
        // Asegurar que la carpeta logs existe
        $logs_dir = __DIR__ . '/logs';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }
        
        file_put_contents($logs_dir . '/recuperacion_password.log', $log, FILE_APPEND | LOCK_EX);
    }
    
    // Intentar enviar email real con PHPMailer
    return enviarEmailRecuperacionReal($correo, $nombre, $token, $tipo_usuario);
}

if (isset($_POST['recuperar'])) {
    $correo = trim($_POST['correo']);
    $tipo_usuario = isset($_POST['tipo_usuario']) ? $_POST['tipo_usuario'] : 'iglesia';
    
    if (empty($correo)) {
        $mensaje = 'Por favor ingresa tu correo electrónico.';
        $tipo_mensaje = 'warning';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Por favor ingresa un correo electrónico válido.';
        $tipo_mensaje = 'warning';
    } else {
        // Verificar si el correo existe según el tipo de usuario
        if ($tipo_usuario === 'admin') {
            $sql = "SELECT id, nombre_completo FROM usuarios_admin WHERE correo = ? AND activo = 1";
        } else {
            $sql = "SELECT id, nombre_iglesia FROM iglesias WHERE correo = ? AND estado = 'aprobada'";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            $token = generarToken();
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora
            
            // Actualizar token en la base de datos
            if ($tipo_usuario === 'admin') {
                $update_sql = "UPDATE usuarios_admin SET token_recuperacion = ?, token_expiracion = ? WHERE correo = ?";
                $nombre = $usuario['nombre_completo'];
            } else {
                $update_sql = "UPDATE iglesias SET token_recuperacion = ?, token_expiracion = ? WHERE correo = ?";
                $nombre = $usuario['nombre_iglesia'];
            }
            
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param('sss', $token, $expiracion, $correo);
            
            if ($stmt_update->execute()) {
                // Enviar email con el enlace de recuperación
                if (enviarEmailRecuperacion($correo, $nombre, $token, $tipo_usuario)) {
                    // Mensaje uniforme para producción - no revelar información sensible
                    $mensaje = 'Se ha enviado un enlace de recuperación a tu correo electrónico. Revisa tu bandeja de entrada y la carpeta de spam.';
                    $mensaje .= '<br><br>';
                    $mensaje .= '<div class="alert alert-info mt-3">';
                    $mensaje .= '<h6><i class="fas fa-info-circle me-2"></i>Información importante:</h6>';
                    $mensaje .= '<ul class="mb-0">';
                    $mensaje .= '<li>El enlace expira en <strong>1 hora</strong></li>';
                    $mensaje .= '<li>Solo puede utilizarse <strong>una vez</strong></li>';
                    $mensaje .= '<li>Si no recibes el correo, revisa tu carpeta de spam</li>';
                    $mensaje .= '<li>El correo puede tardar unos minutos en llegar</li>';
                    $mensaje .= '</ul>';
                    $mensaje .= '</div>';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al enviar el correo. Por favor contacta al administrador.';
                    $tipo_mensaje = 'danger';
                }
            } else {
                $mensaje = 'Error al procesar la solicitud. Inténtalo de nuevo.';
                $tipo_mensaje = 'danger';
            }
        } else {
            // Por seguridad, no revelar si el correo existe o no
            $mensaje = 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación.';
            $tipo_mensaje = 'info';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sistema de Iglesias Bacalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --color-gobierno: #611232;
            --color-gobierno-light: #8B1538;
            --color-gobierno-dark: #4A0E27;
        }
        
        body {
            background: linear-gradient(135deg, var(--color-gobierno) 0%, var(--color-gobierno-light) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .recovery-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .recovery-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: none;
        }
        
        .card-header {
            background: var(--color-gobierno);
            color: white;
            text-align: center;
            padding: 30px 20px;
            border: none;
        }
        
        .card-header i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--color-gobierno);
            box-shadow: 0 0 0 0.25rem rgba(97, 18, 50, 0.25);
        }
        
        .btn-gobierno {
            background: var(--color-gobierno);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-gobierno:hover {
            background: var(--color-gobierno-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(97, 18, 50, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: var(--color-gobierno);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            color: var(--color-gobierno-dark);
            text-decoration: underline;
        }
        
        .tipo-usuario-selector {
            margin-bottom: 25px;
        }
        
        .form-check {
            margin-bottom: 10px;
        }
        
        .form-check-input:checked {
            background-color: var(--color-gobierno);
            border-color: var(--color-gobierno);
        }
        
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--color-gobierno);
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="card recovery-card">
            <div class="card-header">
                <i class="fas fa-key"></i>
                <h3 class="mb-0">Recuperar Contraseña</h3>
                <p class="mb-0 mt-2 opacity-75">Ingresa tu correo para recibir un enlace de recuperación</p>
            </div>
            
            <div class="card-body">
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="alert">
                        <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : ($tipo_mensaje === 'warning' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <div class="info-box">
                    <h6><i class="fas fa-info-circle me-2"></i>¿Cómo funciona?</h6>
                    <ul class="mb-0 small">
                        <li>Ingresa tu correo electrónico registrado</li>
                        <li>Selecciona si eres iglesia o administrador</li>
                        <li>Recibirás un enlace válido por 1 hora</li>
                        <li>Usa el enlace para crear una nueva contraseña</li>
                    </ul>
                </div>
                
                <?php if ($MODO_DESARROLLO): ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-code me-2"></i>Modo Desarrollo Activo</h6>
                    <p class="mb-0 small">Los enlaces de recuperación se mostrarán directamente en pantalla. En producción, se enviarán por email.</p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="tipo-usuario-selector">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-tag me-2"></i>Tipo de usuario:
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_usuario" id="tipo_iglesia" value="iglesia" checked>
                            <label class="form-check-label" for="tipo_iglesia">
                                <i class="fas fa-church me-1"></i> Iglesia
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_usuario" id="tipo_admin" value="admin">
                            <label class="form-check-label" for="tipo_admin">
                                <i class="fas fa-user-shield me-1"></i> Administrador
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="email" 
                               class="form-control" 
                               id="correo" 
                               name="correo" 
                               placeholder="correo@ejemplo.com"
                               value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
                               required>
                        <label for="correo">
                            <i class="fas fa-envelope me-2"></i>Correo electrónico
                        </label>
                    </div>
                    
                    <button type="submit" name="recuperar" class="btn btn-gobierno">
                        <i class="fas fa-paper-plane me-2"></i>Enviar enlace de recuperación
                    </button>
                </form>
                
                <div class="back-link">
                    <a href="login.php">
                        <i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
