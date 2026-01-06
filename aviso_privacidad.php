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
    <title>Avisos de Privacidad - <?php echo htmlspecialchars($config['titulo_sistema']); ?></title>
    
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
            max-width: 900px;
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

        .content-section {
            background: white;
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            color: var(--color-gobierno);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-gobierno);
        }

        .section-content {
            color: #444;
            line-height: 1.8;
        }

        .section-content p {
            margin-bottom: 15px;
        }

        .section-content ul {
            margin-bottom: 20px;
        }

        .section-content li {
            margin-bottom: 8px;
        }

        .highlight-box {
            background: #f8f9fa;
            border-left: 4px solid var(--color-gobierno);
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }

        .contact-info {
            background: var(--color-gobierno);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .contact-info h4 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .contact-item i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
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

        @media (max-width: 768px) {
            .container-main {
                padding: 20px 10px;
            }

            .page-header {
                padding: 20px;
            }

            .page-title {
                font-size: 2rem;
            }

            .content-section {
                padding: 25px;
            }

            .contact-info {
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
        <h1 class="page-title">Avisos de Privacidad</h1>
        <p class="page-subtitle">
            Información sobre el tratamiento y protección de datos personales
        </p>
    </div>

    <!-- Contenido principal -->
    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-shield-alt me-2"></i>
            Aviso de Privacidad
        </h2>
        <div class="section-content">
            <p>
                El <strong>H. Ayuntamiento de Bacalar</strong>, con domicilio en la Av. 7 entre calles 1 y 3, 
                Colonia Centro, C.P. 77930, Bacalar, Quintana Roo, México, es el responsable del tratamiento 
                de los datos personales que nos proporcione, los cuales serán protegidos conforme a lo 
                dispuesto por la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados.
            </p>
        </div>
    </div>

    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-database me-2"></i>
            Datos Personales que Recabamos
        </h2>
        <div class="section-content">
            <p>Para las finalidades señaladas en el presente aviso de privacidad, podemos recabar sus datos personales de distintas formas:</p>
            <ul>
                <li>Cuando usted nos los proporciona directamente</li>
                <li>Cuando visita nuestro sitio web o utiliza nuestros servicios en línea</li>
                <li>Cuando obtienen de otras fuentes permitidas por la ley</li>
            </ul>
            
            <div class="highlight-box">
                <strong>Los datos personales que recabamos incluyen:</strong>
                <ul class="mt-2 mb-0">
                    <li>Datos de identificación (nombre, apellidos, edad, nacionalidad)</li>
                    <li>Datos de contacto (domicilio, teléfono, correo electrónico)</li>
                    <li>Datos patrimoniales y/o financieros cuando sea necesario</li>
                    <li>Datos relacionados con la actividad religiosa de su organización</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-clipboard-list me-2"></i>
            Finalidades del Tratamiento
        </h2>
        <div class="section-content">
            <p>Los datos personales serán utilizados para las siguientes finalidades:</p>
            <ul>
                <li>Registro y administración del padrón de iglesias y organizaciones religiosas</li>
                <li>Procesamiento de trámites y solicitudes administrativas</li>
                <li>Comunicación oficial sobre asuntos relacionados con su organización</li>
                <li>Cumplimiento de obligaciones legales y regulatorias</li>
                <li>Estadísticas y análisis para mejorar los servicios municipales</li>
                <li>Atención de requerimientos de información de autoridades competentes</li>
            </ul>
        </div>
    </div>

    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-user-shield me-2"></i>
            Derechos ARCO
        </h2>
        <div class="section-content">
            <p>Usted tiene derecho a conocer qué datos personales tenemos de usted, para qué los utilizamos y las condiciones del uso que les damos (Acceso). Asimismo, es su derecho solicitar la corrección de su información personal en caso de que esté desactualizada, sea inexacta o incompleta (Rectificación); que la eliminemos de nuestros registros o bases de datos cuando considere que la misma no está siendo utilizada conforme a los principios, deberes y obligaciones previstas en la normativa (Cancelación); así como oponerse al uso de sus datos personales para fines específicos (Oposición).</p>
            
            <div class="highlight-box">
                <strong>Para ejercer cualquiera de estos derechos, usted deberá presentar la solicitud respectiva a través de los siguientes medios:</strong>
                <ul class="mt-2 mb-0">
                    <li>Por escrito en las oficinas del Ayuntamiento</li>
                    <li>A través del correo electrónico: transparencia@bacalar.gob.mx</li>
                    <li>En el portal de transparencia del municipio</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-exchange-alt me-2"></i>
            Transferencias de Datos
        </h2>
        <div class="section-content">
            <p>
                Sus datos personales pueden ser transferidos y tratados dentro y fuera del país, 
                por personas distintas a esta institución. En ese sentido, su información puede 
                ser compartida con autoridades federales, estatales y municipales, exclusivamente 
                para el cumplimiento de las finalidades previstas en este aviso de privacidad y 
                conforme a las disposiciones legales aplicables.
            </p>
        </div>
    </div>

    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-edit me-2"></i>
            Modificaciones al Aviso de Privacidad
        </h2>
        <div class="section-content">
            <p>
                Nos reservamos el derecho de efectuar en cualquier momento modificaciones o 
                actualizaciones al presente aviso de privacidad, para la atención de novedades 
                legislativas, políticas internas o nuevos requerimientos para la prestación u 
                ofrecimiento de nuestros servicios o productos.
            </p>
            <p>
                Estas modificaciones estarán disponibles al público a través de nuestro sitio 
                web o en las oficinas del H. Ayuntamiento de Bacalar.
            </p>
        </div>
    </div>

    <!-- Información de contacto -->
    <div class="contact-info">
        <h4>
            <i class="fas fa-phone me-2"></i>
            Información de Contacto
        </h4>
        <div class="row">
            <div class="col-md-6">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Av. 7 entre calles 1 y 3, Col. Centro<br>C.P. 77930, Bacalar, Quintana Roo</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>(983) 834 2748</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>transparencia@bacalar.gob.mx</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Lunes a Viernes: 8:00 - 15:00 hrs</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
