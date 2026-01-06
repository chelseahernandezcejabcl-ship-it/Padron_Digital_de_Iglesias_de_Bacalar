<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$mensaje = '';
$tipo_mensaje = 'danger'; // Por defecto danger para errores

if (isset($_POST['login'])) {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $tipo_login = isset($_POST['tipo_login']) ? $_POST['tipo_login'] : 'usuario';
    
    if ($tipo_login === 'admin') {
        // Login para administradores
        $sql = "SELECT id, password, activo, nombre_completo FROM usuarios_admin WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if ($admin['activo'] != 1) {
                $mensaje = 'Tu cuenta de administrador está inactiva.';
                $tipo_mensaje = 'warning';
            } elseif (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['tipo_usuario'] = 'admin';
                $_SESSION['nombre_completo'] = $admin['nombre_completo'];
                
                // Redirección al panel administrativo
                header('Location: admin/index.php');
                exit;
            } else {
                $mensaje = 'Contraseña incorrecta.';
                $tipo_mensaje = 'danger';
            }
        } else {
            $mensaje = 'Administrador no encontrado.';
            $tipo_mensaje = 'danger';
        }
    } else {
        // Login para usuarios (iglesias)
        $sql = "SELECT id, password, estado, nombre_iglesia FROM iglesias WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['estado'] === 'pendiente') {
                $mensaje = 'Su solicitud de registro está siendo procesada. Recibirá una notificación por correo cuando sea aprobada.';
                $tipo_mensaje = 'warning';
            } elseif ($user['estado'] === 'rechazada') {
                $mensaje = 'Su solicitud de registro ha sido rechazada. Contacte al administrador municipal para más información.';
                $tipo_mensaje = 'danger';
            } elseif ($user['estado'] === 'aprobada') {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['iglesia_id'] = $user['id'];
                    $_SESSION['user_id'] = $user['id']; // Mantener compatibilidad
                    $_SESSION['tipo_usuario'] = 'iglesia';
                    $_SESSION['nombre_iglesia'] = $user['nombre_iglesia'];
                    
                    // Redirección directa al panel de iglesias
                    header('Location: user/iglesias.php');
                    exit;
                } else {
                    $mensaje = 'Contraseña incorrecta.';
                    $tipo_mensaje = 'danger';
                }
            } else {
                $mensaje = 'Estado de iglesia no válido. Contacte al administrador.';
                $tipo_mensaje = 'danger';
            }
        } else {
            $mensaje = 'Iglesia no encontrada.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - H. Ayuntamiento de Bacalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --color-gobierno: #611232;
        }
        
        .main-content {
            margin-top: 0px;
            padding: 1rem 0;
            min-height: calc(100vh - 80px);
            background: white;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--color-gobierno) 0%, #8b1538 100%);
            color: white;
            text-align: center;
            padding: 1.5rem 2rem;
            border: none;
        }
        
        .btn-gobierno {
            background-color: var(--color-gobierno);
            border-color: var(--color-gobierno);
            color: white;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-gobierno:hover {
            background-color: #8b1538;
            border-color: #8b1538;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(97, 18, 50, 0.3);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--color-gobierno);
            box-shadow: 0 0 0 0.2rem rgba(97, 18, 50, 0.25);
        }
        
        .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
        }
        
        .form-select:focus {
            border-color: var(--color-gobierno);
            box-shadow: 0 0 0 0.2rem rgba(97, 18, 50, 0.25);
        }
        
        .logo-gobierno {
            max-width: 200px;
            height: auto;
            margin-bottom: 1rem;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.2));
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .system-title {
            text-align: center;
            color: var(--color-gobierno);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        /* Texto simple y limpio */
        .header-text {
            font-size: 1.4rem;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 0.2rem;
        }
        
        .footer-text {
            font-size: 1.5rem;
            font-weight: normal;
            color: #333;
            text-align: center;
            margin-top: 0.2rem;
            margin-bottom: 1.5rem;
        }
        
        .logo-gobierno {
            max-width: 200px;
            height: auto;
            margin-bottom: 0.2rem;
            margin-top: 0.2rem;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.2));
        }
        
        @media (max-width: 768px) {
            .header-text {
                font-size: 1.2rem;
            }
            .footer-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <!-- Logo y título del sistema fuera del formulario -->
                    <div class="logo-container">
                        <!-- Texto superior -->
                        <div class="header-text">PADRÓN DIGITAL DE IGLESIAS DE BACALAR</div>
                        
                        <!-- Logo -->
                        <img src="assets/img/login.png" alt="Gobierno Municipal de Bacalar" class="logo-gobierno">
                        
                        <!-- Texto inferior -->
                        <div class="text-center">
                            <div class="footer-text">Administración 2024 - 2027</div>
                        </div>
                    </div>
                    
                    <div class="login-card">
                        <div class="card-header">
                            <h2 class="text-white m-0">Inicio de Sesión</h2>
                        </div>
                    <div class="card-body p-4">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-<?php echo $tipo_mensaje === 'danger' ? 'exclamation-triangle' : 'info-circle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($mensaje); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="tipo_login" class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Tipo de Usuario
                                </label>
                                <select class="form-select" id="tipo_login" name="tipo_login" required>
                                    <option value="usuario" <?php echo (!isset($_POST['tipo_login']) || $_POST['tipo_login'] === 'usuario') ? 'selected' : ''; ?>>
                                        Iglesia/Organización
                                    </option>
                                    <option value="admin" <?php echo (isset($_POST['tipo_login']) && $_POST['tipo_login'] === 'admin') ? 'selected' : ''; ?>>
                                        Administrador Municipal
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="correo" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>" 
                                       required placeholder="ejemplo@correo.com">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-gobierno btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="recuperar_password.php" class="text-decoration-none">
                                <i class="fas fa-key me-1"></i>¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <p class="text-muted mb-2">¿No tienes cuenta?</p>
                            <a href="registro.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user-plus me-2"></i>Registrar Iglesia
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
