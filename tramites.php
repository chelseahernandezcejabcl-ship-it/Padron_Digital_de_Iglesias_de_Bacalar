<?php
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
    <title>Trámites y Servicios - <?php echo htmlspecialchars($config['titulo_sistema']); ?></title>
    
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

        .tramites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .tramite-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--color-gobierno);
        }

        .tramite-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .tramite-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .tramite-icon {
            background: var(--color-gobierno);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }

        .tramite-title {
            color: var(--color-gobierno);
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }

        .tramite-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .requirements-list {
            margin-bottom: 20px;
        }

        .requirements-list h5 {
            color: var(--color-gobierno);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .requirements-list ul {
            margin: 0;
            padding-left: 20px;
        }

        .requirements-list li {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .btn-tramite {
            background: var(--color-gobierno);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-tramite:hover {
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

        .info-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            color: var(--color-gobierno);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .process-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .step-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .step-number {
            background: var(--color-gobierno);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-weight: 600;
        }

        .contact-banner {
            background: var(--color-gobierno);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-top: 30px;
        }

        .contact-banner h3 {
            margin-bottom: 15px;
        }

        .contact-banner .btn {
            background: white;
            color: var(--color-gobierno);
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .contact-banner .btn:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .container-main {
                padding: 20px 10px;
            }

            .tramites-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .page-header {
                padding: 20px;
            }

            .page-title {
                font-size: 2rem;
            }

            .tramite-card {
                padding: 20px;
            }

            .process-steps {
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
                    <a class="nav-link active" href="<?php echo generarUrl('tramites.php'); ?>">
                        <i class="fas fa-file-alt me-1"></i> Trámites
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
        <h1 class="page-title">Trámites y Servicios</h1>
        <p class="page-subtitle">
            Servicios disponibles para organizaciones religiosas en Bacalar
        </p>
    </div>

    <!-- Proceso general -->
    <div class="info-section">
        <h2 class="section-title">
            <i class="fas fa-route"></i>
            Proceso de Trámites
        </h2>
        <p>Para realizar cualquier trámite, debe estar registrado en nuestro sistema. El proceso es simple y transparente:</p>
        
        <div class="process-steps">
            <div class="step-item">
                <div class="step-number">1</div>
                <h5>Registro</h5>
                <p>Complete el formulario de registro de su iglesia</p>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <h5>Validación</h5>
                <p>Nuestro equipo revisa y valida la información</p>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <h5>Aprobación</h5>
                <p>Recibe credenciales de acceso al sistema</p>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <h5>Trámites</h5>
                <p>Puede solicitar todos los servicios disponibles</p>
            </div>
        </div>
    </div>

    <!-- Trámites disponibles -->
    <div class="tramites-grid">
        <!-- Asesoría Jurídica -->
        <div class="tramite-card">
            <div class="tramite-header">
                <div class="tramite-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h3 class="tramite-title">Asesoría Jurídica</h3>
            </div>
            <p class="tramite-description">
                Orientación legal especializada para organizaciones religiosas en temas de documentación, 
                regularización de predios, y aspectos jurídicos relacionados con la actividad religiosa.
            </p>
            <div class="requirements-list">
                <h5>Requisitos:</h5>
                <ul>
                    <li>Estar registrado en el sistema</li>
                    <li>Documentación de la organización</li>
                    <li>Situación actual del predio</li>
                    <li>Descripción del caso específico</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('registro.php'); ?>" class="btn-tramite">
                <i class="fas fa-user-plus"></i>
                Registrarse para Solicitar
            </a>
        </div>

        <!-- Solicitud de Apoyo -->
        <div class="tramite-card">
            <div class="tramite-header">
                <div class="tramite-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3 class="tramite-title">Solicitud de Apoyo</h3>
            </div>
            <p class="tramite-description">
                Solicite apoyo municipal para proyectos, eventos especiales, programas comunitarios 
                o necesidades específicas de su organización religiosa.
            </p>
            <div class="requirements-list">
                <h5>Requisitos:</h5>
                <ul>
                    <li>Registro vigente en el padrón</li>
                    <li>Descripción detallada del proyecto</li>
                    <li>Justificación de la necesidad</li>
                    <li>Cronograma de actividades</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('registro.php'); ?>" class="btn-tramite">
                <i class="fas fa-user-plus"></i>
                Registrarse para Solicitar
            </a>
        </div>

        <!-- Permiso para Eventos -->
        <div class="tramite-card">
            <div class="tramite-header">
                <div class="tramite-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="tramite-title">Permiso para Eventos</h3>
            </div>
            <p class="tramite-description">
                Tramite permisos para eventos religiosos, celebraciones, procesiones o actividades 
                que requieran autorización municipal o uso de espacios públicos.
            </p>
            <div class="requirements-list">
                <h5>Requisitos:</h5>
                <ul>
                    <li>Solicitud con 15 días de anticipación</li>
                    <li>Detalles del evento y programa</li>
                    <li>Número estimado de asistentes</li>
                    <li>Medidas de seguridad propuestas</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('registro.php'); ?>" class="btn-tramite">
                <i class="fas fa-user-plus"></i>
                Registrarse para Solicitar
            </a>
        </div>

        <!-- Registro de Eventos -->
        <div class="tramite-card">
            <div class="tramite-header">
                <div class="tramite-icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <h3 class="tramite-title">Registro de Eventos</h3>
            </div>
            <p class="tramite-description">
                Registre oficialmente eventos, ceremonias o actividades regulares de su organización 
                para mantener un historial actualizado con el municipio.
            </p>
            <div class="requirements-list">
                <h5>Requisitos:</h5>
                <ul>
                    <li>Registro activo en el sistema</li>
                    <li>Información básica del evento</li>
                    <li>Fecha y duración estimada</li>
                    <li>Tipo de actividad religiosa</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('registro.php'); ?>" class="btn-tramite">
                <i class="fas fa-user-plus"></i>
                Registrarse para Solicitar
            </a>
        </div>

        <!-- Registro de Actividades -->
        <div class="tramite-card">
            <div class="tramite-header">
                <div class="tramite-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="tramite-title">Registro de Actividades</h3>
            </div>
            <p class="tramite-description">
                Mantenga un registro oficial de las actividades regulares, programas comunitarios 
                y servicios que ofrece su organización a la comunidad.
            </p>
            <div class="requirements-list">
                <h5>Requisitos:</h5>
                <ul>
                    <li>Padrón vigente y actualizado</li>
                    <li>Descripción de actividades</li>
                    <li>Horarios y frecuencia</li>
                    <li>Beneficiarios estimados</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('registro.php'); ?>" class="btn-tramite">
                <i class="fas fa-user-plus"></i>
                Registrarse para Solicitar
            </a>
        </div>

        <!-- Actualización de Datos -->
        <div class="tramite-card">
            <div class="tramite-header">
                <div class="tramite-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <h3 class="tramite-title">Actualización de Datos</h3>
            </div>
            <p class="tramite-description">
                Mantenga actualizada la información de su organización: cambios de domicilio, 
                representantes legales, contactos o datos administrativos.
            </p>
            <div class="requirements-list">
                <h5>Requisitos:</h5>
                <ul>
                    <li>Acceso al sistema con credenciales</li>
                    <li>Documentación de cambios</li>
                    <li>Identificación del representante</li>
                    <li>Justificación de modificaciones</li>
                </ul>
            </div>
            <a href="<?php echo generarUrl('login.php'); ?>" class="btn-tramite">
                <i class="fas fa-sign-in-alt"></i>
                Acceder al Sistema
            </a>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="info-section">
        <h2 class="section-title">
            <i class="fas fa-info-circle"></i>
            Información Importante
        </h2>
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-clock me-2"></i>Tiempos de Respuesta</h5>
                <ul>
                    <li><strong>Asesoría Jurídica:</strong> 5-7 días hábiles</li>
                    <li><strong>Solicitudes de Apoyo:</strong> 10-15 días hábiles</li>
                    <li><strong>Permisos para Eventos:</strong> 3-5 días hábiles</li>
                    <li><strong>Registros:</strong> 1-3 días hábiles</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5><i class="fas fa-file-alt me-2"></i>Documentación General</h5>
                <ul>
                    <li>Acta constitutiva de la organización</li>
                    <li>Identificación del representante legal</li>
                    <li>Comprobante de domicilio del predio</li>
                    <li>Documentos específicos según el trámite</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Banner de contacto -->
    <div class="contact-banner">
        <h3><i class="fas fa-question-circle me-2"></i>¿Necesita Ayuda con su Trámite?</h3>
        <p>Nuestro equipo está disponible para orientarle en el proceso</p>
        <a href="<?php echo generarUrl('contacto.php'); ?>" class="btn">
            <i class="fas fa-phone me-2"></i>Contactar Ahora
        </a>
        <a href="mailto:iglesias@bacalar.gob.mx" class="btn">
            <i class="fas fa-envelope me-2"></i>Enviar Email
        </a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
