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
    <title>Contacto - <?php echo htmlspecialchars($config['titulo_sistema']); ?></title>
    
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        .page-header {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
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

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .contact-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-2px);
        }

        .contact-card h3 {
            color: var(--color-gobierno);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .contact-item i {
            color: var(--color-gobierno);
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .contact-item a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-item a:hover {
            color: var(--color-gobierno);
        }

        .map-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .map-container {
            height: 300px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }

        .hours-section {
            background: var(--color-gobierno);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .hours-item {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
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

        .department-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .department-card h4 {
            color: var(--color-gobierno);
            font-weight: 600;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .container-main {
                padding: 20px 10px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .hours-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                padding: 20px;
            }

            .page-title {
                font-size: 2rem;
            }

            .contact-card {
                padding: 20px;
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
                    <a class="nav-link active" href="<?php echo generarUrl('contacto.php'); ?>">
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
        <h1 class="page-title">Contacto</h1>
        <p class="page-subtitle">
            Coordinación de Asuntos Religiosos - H. Ayuntamiento de Bacalar
        </p>
    </div>

    <!-- Información de contacto principal -->
    <div class="contact-grid">
        <div class="contact-card">
            <h3>
                <i class="fas fa-building"></i>
                Información General
            </h3>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <strong>Dirección:</strong><br>
                    Av. 7 entre calles 1 y 3<br>
                    Col. Centro, C.P. 77930<br>
                    Bacalar, Quintana Roo
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <div>
                    <strong>Teléfono:</strong><br>
                    <a href="tel:+529838342748">(983) 834 2748</a>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <strong>Email General:</strong><br>
                    <a href="mailto:contacto@bacalar.gob.mx">contacto@bacalar.gob.mx</a>
                </div>
            </div>
        </div>

        <div class="contact-card">
            <h3>
                <i class="fas fa-church"></i>
                Asuntos Religiosos
            </h3>
            <div class="contact-item">
                <i class="fas fa-user-tie"></i>
                <div>
                    <strong>Coordinación:</strong><br>
                    Asuntos Religiosos
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <strong>Email Especializado:</strong><br>
                    <a href="mailto:iglesias@bacalar.gob.mx">iglesias@bacalar.gob.mx</a>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-file-alt"></i>
                <div>
                    <strong>Trámites:</strong><br>
                    Registro y gestión de iglesias
                </div>
            </div>
        </div>
    </div>

    <!-- Horarios de atención -->
    <div class="hours-section">
        <h3>
            <i class="fas fa-clock me-2"></i>
            Horarios de Atención
        </h3>
        <div class="hours-grid">
            <div class="hours-item">
                <h5><i class="fas fa-calendar-week me-2"></i>Lunes a Viernes</h5>
                <p class="mb-0">8:00 AM - 3:00 PM</p>
                <small>Atención presencial y telefónica</small>
            </div>
            <div class="hours-item">
                <h5><i class="fas fa-calendar-alt me-2"></i>Sábados</h5>
                <p class="mb-0">9:00 AM - 1:00 PM</p>
                <small>Solo citas programadas</small>
            </div>
        </div>
        <div class="text-center mt-3">
            <small><i class="fas fa-info-circle me-1"></i>Para atención especializada, se recomienda agendar cita previa</small>
        </div>
    </div>

    <!-- Mapa y ubicación -->
    <div class="map-section">
        <h3>
            <i class="fas fa-map me-2"></i>
            Ubicación
        </h3>
        <p>Nuestras oficinas se encuentran en el centro de Bacalar, fácilmente accesibles desde cualquier punto de la ciudad.</p>
        <div class="map-container">
            <div class="text-center">
                <i class="fas fa-map-marked-alt fa-3x text-muted mb-2"></i>
                <p class="text-muted mb-0">Mapa interactivo próximamente</p>
                <small class="text-muted">Av. 7 entre calles 1 y 3, Col. Centro, Bacalar</small>
            </div>
        </div>
    </div>

    <!-- Departamentos relacionados -->
    <div class="department-card">
        <h4><i class="fas fa-sitemap me-2"></i>Otros Departamentos de Interés</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="contact-item">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>Transparencia:</strong><br>
                        <a href="mailto:transparencia@bacalar.gob.mx">transparencia@bacalar.gob.mx</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Denuncias:</strong><br>
                        <a href="mailto:denuncias@bacalar.gob.mx">denuncias@bacalar.gob.mx</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-item">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Información:</strong><br>
                        <a href="mailto:info@bacalar.gob.mx">info@bacalar.gob.mx</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Redes sociales -->
    <div class="contact-card">
        <h3>
            <i class="fas fa-share-alt"></i>
            Síguenos en Redes Sociales
        </h3>
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-3">
                <a href="https://www.facebook.com/MunicipioBacalar" target="_blank" class="text-decoration-none">
                    <div class="p-3 border rounded">
                        <i class="fab fa-facebook-f fa-2x" style="color: #1877F2;"></i>
                        <p class="mt-2 mb-0">Facebook</p>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="https://twitter.com/BacalarOficial" target="_blank" class="text-decoration-none">
                    <div class="p-3 border rounded">
                        <i class="fab fa-twitter fa-2x" style="color: #1DA1F2;"></i>
                        <p class="mt-2 mb-0">Twitter</p>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="https://www.instagram.com/bacalar_oficial" target="_blank" class="text-decoration-none">
                    <div class="p-3 border rounded">
                        <i class="fab fa-instagram fa-2x" style="color: #E4405F;"></i>
                        <p class="mt-2 mb-0">Instagram</p>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="https://www.youtube.com/channel/UC_BacalarOficial" target="_blank" class="text-decoration-none">
                    <div class="p-3 border rounded">
                        <i class="fab fa-youtube fa-2x" style="color: #FF0000;"></i>
                        <p class="mt-2 mb-0">YouTube</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
