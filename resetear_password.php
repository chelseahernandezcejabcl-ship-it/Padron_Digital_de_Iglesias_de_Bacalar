<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$mensaje = '';
$tipo_mensaje = 'info';
$token_valido = false;
$usuario_info = null;

// Verificar token
if (isset($_GET['token']) && isset($_GET['categoria'])) {
    $token = $_GET['token'];
    $tipo_usuario = $_GET['categoria'];
    
    if (in_array($tipo_usuario, ['iglesia', 'admin'])) {
        // Verificar token y que no haya expirado
        if ($tipo_usuario === 'admin') {
            $sql = "SELECT id, correo, nombre_completo, token_expiracion 
                    FROM usuarios_admin 
                    WHERE token_recuperacion = ? AND token_expiracion > NOW()";
        } else {
            $sql = "SELECT id, correo, nombre_iglesia, token_expiracion 
                    FROM iglesias 
                    WHERE token_recuperacion = ? AND token_expiracion > NOW()";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $token_valido = true;
            $usuario_info = $result->fetch_assoc();
        } else {
            $mensaje = 'El enlace de recuperación es inválido o ha expirado. Solicita uno nuevo.';
            $tipo_mensaje = 'danger';
        }
    } else {
        $mensaje = 'Tipo de usuario inválido.';
        $tipo_mensaje = 'danger';
    }
} else {
    $mensaje = 'Enlace de recuperación inválido.';
    $tipo_mensaje = 'danger';
}

// Procesar nueva contraseña
if (isset($_POST['resetear']) && $token_valido) {
    $nueva_password = $_POST['nueva_password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    if (empty($nueva_password) || empty($confirmar_password)) {
        $mensaje = 'Por favor completa todos los campos.';
        $tipo_mensaje = 'warning';
    } elseif (strlen($nueva_password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
        $tipo_mensaje = 'warning';
    } elseif ($nueva_password !== $confirmar_password) {
        $mensaje = 'Las contraseñas no coinciden.';
        $tipo_mensaje = 'warning';
    } else {
        // Encriptar nueva contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña y limpiar token
        if ($tipo_usuario === 'admin') {
            $update_sql = "UPDATE usuarios_admin 
                          SET password = ?, token_recuperacion = NULL, token_expiracion = NULL 
                          WHERE token_recuperacion = ?";
        } else {
            $update_sql = "UPDATE iglesias 
                          SET password = ?, token_recuperacion = NULL, token_expiracion = NULL 
                          WHERE token_recuperacion = ?";
        }
        
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param('ss', $password_hash, $token);
        
        if ($stmt_update->execute()) {
            $mensaje = 'Tu contraseña ha sido actualizada exitosamente. Ya puedes iniciar sesión.';
            $tipo_mensaje = 'success';
            $token_valido = false; // Deshabilitar el formulario
        } else {
            $mensaje = 'Error al actualizar la contraseña. Inténtalo de nuevo.';
            $tipo_mensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetear Contraseña - Sistema de Iglesias Bacalar</title>
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
        
        .reset-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .reset-card {
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
        
        .user-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--color-gobierno);
        }
        
        .password-requirements {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .password-strength {
            margin-top: 10px;
        }
        
        .strength-meter {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 4px;
        }
        
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #fd7e14; width: 50%; }
        .strength-good { background: #ffc107; width: 75%; }
        .strength-strong { background: #198754; width: 100%; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="card reset-card">
            <div class="card-header">
                <i class="fas fa-lock"></i>
                <h3 class="mb-0">Resetear Contraseña</h3>
                <p class="mb-0 mt-2 opacity-75">Crea una nueva contraseña segura</p>
            </div>
            
            <div class="card-body">
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="alert">
                        <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : ($tipo_mensaje === 'warning' ? 'exclamation-triangle' : ($tipo_mensaje === 'danger' ? 'times-circle' : 'info-circle')); ?> me-2"></i>
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($token_valido && $usuario_info): ?>
                    <div class="user-info">
                        <h6><i class="fas fa-user me-2"></i>Restableciendo contraseña para:</h6>
                        <p class="mb-1"><strong>
                            <?php echo $tipo_usuario === 'admin' ? $usuario_info['nombre_completo'] : $usuario_info['nombre_iglesia']; ?>
                        </strong></p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-envelope me-1"></i><?php echo $usuario_info['correo']; ?>
                        </p>
                    </div>
                    
                    <div class="password-requirements">
                        <h6><i class="fas fa-shield-alt me-2"></i>Requisitos de contraseña:</h6>
                        <ul class="mb-0 small">
                            <li>Mínimo 6 caracteres</li>
                            <li>Se recomienda usar mayúsculas, minúsculas y números</li>
                            <li>Evita información personal o datos obvios</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="" id="resetForm">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($tipo_usuario); ?>">
                        
                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control" 
                                   id="nueva_password" 
                                   name="nueva_password" 
                                   placeholder="Nueva contraseña"
                                   minlength="6"
                                   required>
                            <label for="nueva_password">
                                <i class="fas fa-key me-2"></i>Nueva contraseña
                            </label>
                        </div>
                        
                        <div class="password-strength">
                            <small class="text-muted">Fuerza de la contraseña:</small>
                            <div class="strength-meter">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <small id="strengthText" class="text-muted">Ingresa una contraseña</small>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control" 
                                   id="confirmar_password" 
                                   name="confirmar_password" 
                                   placeholder="Confirmar contraseña"
                                   required>
                            <label for="confirmar_password">
                                <i class="fas fa-check-double me-2"></i>Confirmar contraseña
                            </label>
                        </div>
                        
                        <div id="passwordMatch" class="small mt-2"></div>
                        
                        <button type="submit" name="resetear" class="btn btn-gobierno" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Actualizar contraseña
                        </button>
                    </form>
                <?php elseif ($tipo_mensaje === 'success'): ?>
                    <div class="text-center">
                        <div class="back-link">
                            <a href="login.php" class="btn btn-gobierno">
                                <i class="fas fa-sign-in-alt me-1"></i>Ir a iniciar sesión
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="back-link">
                    <a href="login.php">
                        <i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de contraseña en tiempo real
        const passwordInput = document.getElementById('nueva_password');
        const confirmInput = document.getElementById('confirmar_password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const passwordMatch = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = calculatePasswordStrength(password);
                updateStrengthMeter(strength);
                checkPasswordMatch();
            });
        }
        
        if (confirmInput) {
            confirmInput.addEventListener('input', checkPasswordMatch);
        }
        
        function calculatePasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 6) score += 25;
            if (password.length >= 8) score += 25;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score += 25;
            if (/\d/.test(password)) score += 25;
            if (/[^A-Za-z0-9]/.test(password)) score += 25;
            
            return Math.min(score, 100);
        }
        
        function updateStrengthMeter(strength) {
            strengthFill.className = 'strength-fill';
            
            if (strength < 25) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Muy débil';
                strengthText.className = 'text-danger';
            } else if (strength < 50) {
                strengthFill.classList.add('strength-fair');
                strengthText.textContent = 'Débil';
                strengthText.className = 'text-warning';
            } else if (strength < 75) {
                strengthFill.classList.add('strength-good');
                strengthText.textContent = 'Buena';
                strengthText.className = 'text-info';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Fuerte';
                strengthText.className = 'text-success';
            }
        }
        
        function checkPasswordMatch() {
            const password = passwordInput ? passwordInput.value : '';
            const confirm = confirmInput ? confirmInput.value : '';
            
            if (confirm && password) {
                if (password === confirm) {
                    passwordMatch.innerHTML = '<i class="fas fa-check text-success me-1"></i><span class="text-success">Las contraseñas coinciden</span>';
                    submitBtn.disabled = false;
                } else {
                    passwordMatch.innerHTML = '<i class="fas fa-times text-danger me-1"></i><span class="text-danger">Las contraseñas no coinciden</span>';
                    submitBtn.disabled = true;
                }
            } else {
                passwordMatch.innerHTML = '';
                submitBtn.disabled = !password || !confirm;
            }
        }
    </script>
</body>
</html>
