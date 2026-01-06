<?php
// MIDDLEWARE: Evitar acceso de iglesias logueadas a vista pública
require_once __DIR__ . '/includes/middleware_publico.php';

session_start();

// Funciones auxiliares para generar URLs
function generarUrl($ruta) {
    $base_url = '';
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $base_url = $protocol . $_SERVER['HTTP_HOST'];
        $script_name = $_SERVER['SCRIPT_NAME'];
        $path = dirname(dirname($script_name));
        if ($path != '/') {
            $base_url .= $path;
        }
    }
    return $base_url . '/' . ltrim($ruta, '/');
}

function obtenerConfiguracionSistema() {
    return [
        'nombre_municipio' => 'Bacalar',
        'titulo_sistema' => 'Padrón Digital de Iglesias'
    ];
}

$config = obtenerConfiguracionSistema();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - <?php echo htmlspecialchars($config['titulo_sistema']); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-gobierno: #611232;
            --color-gobierno-light: #8B4B6B;
            --color-gobierno-dark: #4A0E26;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .navbar {
            background: var(--color-gobierno) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }

        .container-main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        .page-header {
            background: white;
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .page-title {
            color: var(--color-gobierno);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .service-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .service-icon {
            background: var(--color-gobierno);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 2rem;
        }

        .service-title {
            color: var(--color-gobierno);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .service-description {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .service-features {
            text-align: left;
            margin-bottom: 25px;
        }

        .service-features ul {
            list-style: none;
            padding: 0;
        }

        .service-features li {
            padding: 5px 0;
            color: #555;
        }

        .service-features li i {
            color: var(--color-gobierno);
            margin-right: 10px;
            width: 16px;
        }

        .btn-service {
            background: var(--color-gobierno);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-service:hover {
            background: var(--color-gobierno-dark);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-back {
            background: var(--color-gobierno);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .btn-back:hover {
            background: var(--color-gobierno-dark);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .benefits-section {
            background: white;
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            color: var(--color-gobierno);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .benefit-item {
            text-align: center;
            padding: 20px;
        }

        .benefit-icon {
            color: var(--color-gobierno);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .benefit-title {
            color: var(--color-gobierno);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .cta-section {
            background: var(--color-gobierno);
            color: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            margin-top: 40px;
        }

        .cta-section h3 {
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .cta-section .btn {
            background: white;
            color: var(--color-gobierno);
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-weight: 600;
            margin: 0 10px 10px 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .cta-section .btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            color: var(--color-gobierno);
        }

        @media (max-width: 768px) {
            .container-main {
                padding: 20px 10px;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .page-header {
                padding: 25px;
            }

            .page-title {
                font-size: 2rem;
            }

            .service-card {
                padding: 25px;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo generarUrl('index.php'); ?>">
            <i class="fas fa-church me-2"></i>
            <?php echo htmlspecialchars($config['titulo_sistema']); ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo generarUrl('index.php'); ?>">
                        <i class="fas fa-home me-1"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo generarUrl('avisos.php'); ?>">
                        <i class="fas fa-bullhorn me-1"></i> Publicaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo generarUrl('biblioteca.php'); ?>">
                        <i class="fas fa-book me-1"></i> Marco Jurídico
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo generarUrl('tramites.php'); ?>">
                        <i class="fas fa-file-alt me-1"></i> Trámites
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo generarUrl('servicios.php'); ?>">
                        <i class="fas fa-cogs me-1"></i> Servicios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo generarUrl('contacto.php'); ?>">
                        <i class="fas fa-envelope me-1"></i> Contacto
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo generarUrl('login.php'); ?>">
                        <i class="fas fa-sign-in-alt me-1"></i> Acceder
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-main">
    <!-- Botón Regresar -->
    <a href="<?php echo generarUrl('index.php'); ?>" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Regresar al Inicio
    </a>

    <!-- Header de la página -->
    <div class="page-header">
        <h1 class="page-title">Servicios</h1>
        <p class="page-subtitle">
            Servicios digitales y administrativos para organizaciones religiosas
        </p>
    </div>

    <!-- Servicios principales -->
    <div class="services-grid">
        <!-- Padrón Digital -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-database"></i>
            </div>
            <h3 class="service-title">Padrón Digital</h3>
            <p class="service-description">
                Sistema centralizado para el registro y gestión de iglesias y organizaciones religiosas en Bacalar.
            </p>
            <div class="service-features">
                <ul>
                    <li><i class="fas fa-check"></i> Registro en línea</li>
                    <li><i class="fas fa-check"></i> Actualización de datos</li>
                    <li><i class="fas fa-check"></i> Consulta de estatus</li>
                    <li><i class="fas fa-check"></i> Historial de actividades</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('registro.php'); ?>" class="btn-service">
                <i class="fas fa-user-plus"></i>
                Registrarse
            </a>
        </div>

        <!-- Gestión de Trámites -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3 class="service-title">Gestión de Trámites</h3>
            <p class="service-description">
                Plataforma digital para solicitar y dar seguimiento a todos sus trámites administrativos.
            </p>
            <div class="service-features">
                <ul>
                    <li><i class="fas fa-check"></i> Solicitudes en línea</li>
                    <li><i class="fas fa-check"></i> Seguimiento en tiempo real</li>
                    <li><i class="fas fa-check"></i> Notificaciones automáticas</li>
                    <li><i class="fas fa-check"></i> Historial completo</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('tramites.php'); ?>" class="btn-service">
                <i class="fas fa-arrow-right"></i>
                Ver Trámites
            </a>
        </div>

        <!-- Biblioteca Digital -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="service-title">Biblioteca Digital</h3>
            <p class="service-description">
                Acceso a documentos oficiales, marco jurídico y recursos para organizaciones religiosas.
            </p>
            <div class="service-features">
                <ul>
                    <li><i class="fas fa-check"></i> Marco jurídico actualizado</li>
                    <li><i class="fas fa-check"></i> Formularios oficiales</li>
                    <li><i class="fas fa-check"></i> Guías y manuales</li>
                    <li><i class="fas fa-check"></i> Descarga gratuita</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('biblioteca.php'); ?>" class="btn-service">
                <i class="fas fa-book"></i>
                Explorar Biblioteca
            </a>
        </div>

        <!-- Sistema de Avisos -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <h3 class="service-title">Sistema de Avisos</h3>
            <p class="service-description">
                Manténgase informado con las últimas publicaciones, anuncios y noticias municipales.
            </p>
            <div class="service-features">
                <ul>
                    <li><i class="fas fa-check"></i> Avisos oficiales</li>
                    <li><i class="fas fa-check"></i> Convocatorias</li>
                    <li><i class="fas fa-check"></i> Noticias relevantes</li>
                    <li><i class="fas fa-check"></i> Actualizaciones automáticas</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('avisos.php'); ?>" class="btn-service">
                <i class="fas fa-newspaper"></i>
                Ver Avisos
            </a>
        </div>

        <!-- Asesoría Especializada -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <h3 class="service-title">Asesoría Especializada</h3>
            <p class="service-description">
                Orientación personalizada de nuestros especialistas en asuntos religiosos y administrativos.
            </p>
            <div class="service-features">
                <ul>
                    <li><i class="fas fa-check"></i> Consulta jurídica</li>
                    <li><i class="fas fa-check"></i> Orientación administrativa</li>
                    <li><i class="fas fa-check"></i> Resolución de dudas</li>
                    <li><i class="fas fa-check"></i> Apoyo personalizado</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('contacto.php'); ?>" class="btn-service">
                <i class="fas fa-phone"></i>
                Solicitar Asesoría
            </a>
        </div>

        <!-- Soporte Técnico -->
        <div class="service-card">
            <div class="service-icon">
                <i class="fas fa-headset"></i>
            </div>
            <h3 class="service-title">Soporte Técnico</h3>
            <p class="service-description">
                Asistencia técnica para el uso del sistema digital y resolución de problemas técnicos.
            </p>
            <div class="service-features">
                <ul>
                    <li><i class="fas fa-check"></i> Ayuda técnica</li>
                    <li><i class="fas fa-check"></i> Capacitación de usuarios</li>
                    <li><i class="fas fa-check"></i> Resolución de problemas</li>
                    <li><i class="fas fa-check"></i> Soporte remoto</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('contacto.php'); ?>" class="btn-service">
                <i class="fas fa-question-circle"></i>
                Obtener Ayuda
            </a>
        </div>
    </div>

    <!-- Beneficios del sistema -->
    <div class="benefits-section">
        <h2 class="section-title">¿Por qué usar nuestros servicios digitales?</h2>
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h4 class="benefit-title">Ahorro de Tiempo</h4>
                <p>Realice sus trámites desde cualquier lugar, las 24 horas del día, sin necesidad de desplazarse.</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4 class="benefit-title">Seguridad</h4>
                <p>Sus datos están protegidos con los más altos estándares de seguridad y privacidad.</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h4 class="benefit-title">Transparencia</h4>
                <p>Seguimiento completo de sus solicitudes con actualizaciones en tiempo real.</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h4 class="benefit-title">Accesibilidad</h4>
                <p>Compatible con computadoras, tablets y teléfonos móviles para su comodidad.</p>
            </div>
        </div>
    </div>

    <!-- Llamada a la acción -->
    <div class="cta-section">
        <h3>¿Listo para comenzar?</h3>
        <p>Únase a las organizaciones que ya aprovechan los beneficios de nuestros servicios digitales</p>
        <a href="<?php echo generarUrl('registro.php'); ?>" class="btn">
            <i class="fas fa-user-plus me-2"></i>Registrar mi Iglesia
        </a>
        <a href="<?php echo generarUrl('login.php'); ?>" class="btn">
            <i class="fas fa-sign-in-alt me-2"></i>Acceder al Sistema
        </a>
        <a href="<?php echo generarUrl('contacto.php'); ?>" class="btn">
            <i class="fas fa-question-circle me-2"></i>Resolver Dudas
        </a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
