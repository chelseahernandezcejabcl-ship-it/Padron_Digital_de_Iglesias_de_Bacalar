
<?php
// MIDDLEWARE: Evitar acceso de iglesias logueadas a vista pública
require_once __DIR__ . '/includes/middleware_publico.php';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funciones.php';

// Verificar si hay un mensaje de registro exitoso
$mensaje_registro = '';
if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso') {
    $mensaje_registro = '
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
            <div>
                <h5 class="alert-heading mb-1">¡Registro Exitoso!</h5>
                <p class="mb-0">Su solicitud de registro ha sido enviada correctamente. Nuestro equipo administrativo la revisará en las próximas 24-48 horas.</p>
                <small class="text-muted">Recibirá una notificación por correo electrónico cuando su iglesia sea aprobada.</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// Obtener iglesias aprobadas para mostrar en el mapa
$iglesias = obtenerIglesiasParaMapa();

// Obtener avisos municipales para visitantes
$avisos = obtenerAvisosMunicipales('publico', 5);

// Obtener configuración del sistema
$config = obtenerConfiguracionSistema();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Padron Digital de Iglesias - Bacalar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema digital para el registro y gestión de iglesias en el municipio de Bacalar">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Leaflet CSS para el mapa -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Header styles -->
    <link rel="stylesheet" href="<?php echo generarUrl('assets/css/header-styles.css'); ?>">
    
    <style>
        :root {
            --color-gobierno: #611232;
            --color-gobierno-claro: rgba(97, 18, 50, 0.1);
            --color-secundario: #D2B48C;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto !important;
            padding: 2rem 1rem;
            padding-top: 120px; /* Espacio fijo para el header */
        }
        
        .hero-title {
            text-align: center;
            color: #333;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .left-panel {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .right-panel {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        
        .login-buttons {
            margin-bottom: 2rem;
        }
        
        .btn-iglesia {
            background-color: var(--color-gobierno);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-iglesia:hover {
            background-color: #4a0d28;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(97, 18, 50, 0.3);
        }
        
        .btn-registro {
            background-color: transparent;
            color: var(--color-gobierno);
            border: 2px solid var(--color-gobierno);
            padding: 12px 24px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-registro:hover {
            background-color: var(--color-gobierno);
            color: white;
            transform: translateY(-2px);
        }
        
        .leyenda-container {
            margin-top: 1rem;
        }
        
        .leyenda-item {
            display: flex;
            align-items: center;
            margin: 0.5rem 0;
        }
        
        .color-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-right: 0.75rem;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        
        /* Estilos para el buscador */
        .search-container {
            margin-bottom: 1rem;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid var(--color-gobierno);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            -webkit-appearance: none;
            appearance: none;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--color-gobierno);
            box-shadow: 0 0 10px rgba(97, 18, 50, 0.2);
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-gobierno);
            font-size: 1.1rem;
            pointer-events: none;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-height: 250px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .search-result-item {
            padding: 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .search-result-item:hover,
        .search-result-item:active {
            background-color: var(--color-gobierno-claro);
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-name {
            font-weight: 600;
            color: var(--color-gobierno);
            font-size: 1rem;
            line-height: 1.3;
        }
        
        .search-result-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 4px;
            line-height: 1.2;
        }
        
        /* Mejoras para móvil */
        @media (max-width: 768px) {
            .search-input {
                font-size: 16px; /* Previene zoom en iOS */
                padding: 14px 50px 14px 16px;
            }
            
            .search-icon {
                right: 16px;
                font-size: 1.2rem;
            }
            
            .search-results {
                max-height: 200px;
                border-radius: 12px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }
            
            .search-result-item {
                padding: 16px;
                min-height: 70px;
                -webkit-tap-highlight-color: rgba(97, 18, 50, 0.1);
            }
            
            .search-result-name {
                font-size: 1.1rem;
            }
            
            .search-result-info {
                font-size: 0.95rem;
                margin-top: 5px;
            }
            
            /* Evitar zoom automático en dispositivos pequeños */
            .search-input:focus {
                transform: none;
            }
            
            /* Altura del mapa optimizada para móvil - más cuadrada */
            #mapa-iglesias {
                height: 70vh; /* 70% de la altura de la ventana */
                min-height: 400px; /* Altura mínima para pantallas muy pequeñas */
                max-height: 600px; /* Altura máxima para evitar que sea demasiado grande */
            }
        }
        
        /* Mejoras para pantallas táctiles */
        @media (hover: none) and (pointer: coarse) {
            .search-result-item {
                padding: 18px 16px;
                min-height: 75px;
            }
            
            .search-result-item:active {
                background-color: var(--color-gobierno-claro);
                transform: scale(0.98);
            }
        }
        
        #mapa-iglesias {
            height: 450px;
            width: 100%;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            z-index: 1;
            position: relative;
        }
        
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }
        
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        
        .info-card h5 {
            color: var(--color-gobierno);
            margin-bottom: 0.5rem;
        }
        
        .info-card p {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            /* Optimizaciones para el mapa en móvil - dimensiones más cuadradas */
            #mapa-iglesias {
                height: 70vh; /* Usar viewport height para mejor adaptación */
                min-height: 400px; /* Mínimo para usabilidad */
                max-height: 600px; /* Máximo para no ser excesivo */
                border-radius: 12px;
            }
            
            .right-panel {
                order: -1; /* Mover mapa arriba en móvil */
                padding: 1rem; /* Reducir padding en móvil */
            }
            
            .left-panel {
                order: 1;
            }
            
            /* Leyenda más compacta en móvil */
            .leyenda-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
                margin-top: 1rem;
            }
            
            .leyenda-item {
                margin: 0.25rem 0;
                font-size: 0.9rem;
            }
            
            .color-dot {
                width: 14px;
                height: 14px;
                margin-right: 0.5rem;
            }
        }
        
        /* Estilos específicos para dispositivos muy pequeños */
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.75rem;
                line-height: 1.2;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
                line-height: 1.4;
            }
            
            /* Mapa aún más optimizado para móviles pequeños */
            #mapa-iglesias {
                height: 75vh; /* Aumentar a 75% en pantallas muy pequeñas */
                min-height: 350px;
                max-height: 500px;
            }
            
            .main-container {
                padding: 0.75rem;
            }
            
            .right-panel {
                padding: 0.75rem; /* Menos padding en pantallas pequeñas */
            }
            
            .search-input {
                padding: 16px 55px 16px 18px;
                font-size: 16px; /* Importante para evitar zoom en iOS */
            }
            
            .search-results {
                border-radius: 16px;
                max-height: 180px;
            }
        }
        
        /* Media query específica para dispositivos en orientación landscape */
        @media (max-width: 768px) and (orientation: landscape) {
            #mapa-iglesias {
                height: 85vh; /* En landscape, usar más altura disponible */
                min-height: 300px;
                max-height: 450px;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="main-content">
<div class="main-container">
    <?php echo $mensaje_registro; ?>
    
    <!-- Título principal -->
    <h1 class="hero-title">PADRÓN DIGITAL DE IGLESIAS DE BACALAR</h1>
    <p class="hero-subtitle">
        "Bienvenido al Padrón Digital de Iglesias de Bacalar — una plataforma creada para digitalizar y conectar a las iglesias del municipio, 
        permitiendo su registro oficial y facilitando la gestión de trámites de manera rápida, transparente y segura."
    </p>
    
    <!-- Contenido principal -->
    <div class="content-grid">
        <!-- Panel izquierdo -->
        <div class="left-panel">
            <!-- Sección de acciones para iglesias -->
            <h6 class="fw-bold mb-3">Acciones</h6>
            <div class="login-buttons mb-4">
                <div class="d-grid gap-2">
                    <a href="<?php echo generarUrl('login.php'); ?>" class="btn btn-iglesia btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Iniciar sesión como iglesia
                    </a>
                    <a href="<?php echo generarUrl('registro.php'); ?>" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-user-plus me-2"></i>
                        Registrarse como iglesia
                    </a>
                </div>
            </div>
            
            <!-- Leyenda de denominaciones -->
            <h6 class="fw-bold mb-3">Leyenda del mapa</h6>
            <div class="leyenda-container">
                <?php 
                $denominaciones = obtenerDenominaciones();
                foreach ($denominaciones as $denom): 
                ?>
                <div class="leyenda-item">
                    <div class="color-dot" style="background-color: <?php echo $denom['color_mapa']; ?>"></div>
                    <span><?php echo htmlspecialchars($denom['nombre']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Panel derecho - Mapa -->
        <div class="right-panel">
            <!-- Buscador de iglesias -->
            <div class="search-container">
                <input type="text" 
                       id="buscador-iglesias" 
                       class="search-input" 
                       placeholder="Buscar iglesia por nombre..."
                       autocomplete="off">
                <i class="fas fa-search search-icon"></i>
                <div id="resultados-busqueda" class="search-results"></div>
            </div>
            
            <div id="mapa-iglesias"></div>
        </div>
    </div>
    
    <!-- Cards informativas -->
    <div class="info-cards">
        <div class="info-card">
            <i class="fas fa-question-circle fa-2x text-primary mb-3"></i>
            <h5>¿Qué es?</h5>
            <p>Es el registro digital oficial de iglesias en Bacalar para consulta pública y trámites municipales</p>
        </div>
        
        <div class="info-card">
            <i class="fas fa-chart-line fa-2x text-success mb-3"></i>
            <h5>Beneficios</h5>
            <p>Gestión digital eficiente, transparencia en trámites y acceso rápido a servicios municipales</p>
        </div>
        
        <div class="info-card">
            <i class="fas fa-shield-alt fa-2x text-danger mb-3"></i>
            <h5>Seguridad</h5>
            <p>Protección de datos y verificación de información para garantizar confianza y transparencia</p>
        </div>
    </div>
</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Script del mapa -->
<script>
// Datos de las iglesias
const iglesias = <?php echo json_encode($iglesias); ?>;
const marcadores = []; // Array para almacenar los marcadores

console.log('Iniciando mapa...');
console.log('Iglesias cargadas:', iglesias.length);
console.log('Datos de iglesias:', iglesias);

// Verificar que el contenedor del mapa existe
const mapaContainer = document.getElementById('mapa-iglesias');
if (!mapaContainer) {
    console.error('No se encontró el contenedor del mapa #mapa-iglesias');
} else {
    console.log('Contenedor del mapa encontrado:', mapaContainer);
}

// Inicializar el mapa centrado en Bacalar
const mapa = L.map('mapa-iglesias').setView([18.6769, -88.3912], 13);

console.log('Mapa inicializado:', mapa);

// Agregar capa de OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(mapa);

// Agregar marcadores de iglesias
iglesias.forEach((iglesia, index) => {
    if (iglesia.latitud && iglesia.longitud) {
        // Crear marcador con color según denominación
        const marker = L.circleMarker([parseFloat(iglesia.latitud), parseFloat(iglesia.longitud)], {
            color: '#fff',
            fillColor: iglesia.color_mapa || '#3388ff',
            fillOpacity: 0.8,
            radius: 12,
            weight: 3
        }).addTo(mapa);
        
        // Agregar referencia del índice al marcador
        marker.iglesiaIndex = index;
        
        // Contenido del popup
        const popupContent = `
            <div class="text-center" style="min-width: 200px;">
                <h6 class="fw-bold mb-2">${iglesia.nombre_iglesia}</h6>
                <p class="mb-1"><i class="fas fa-cross text-primary me-1"></i> ${iglesia.denominacion || 'Sin denominación'}</p>
                <p class="mb-1"><i class="fas fa-user text-secondary me-1"></i> ${iglesia.ministro_encargado}</p>
                <p class="mb-1"><i class="fas fa-map-marker-alt text-danger me-1"></i> ${iglesia.direccion}</p>
                ${iglesia.telefono ? `<p class="mb-1"><i class="fas fa-phone text-success me-1"></i> ${iglesia.telefono}</p>` : ''}
                ${iglesia.anio_fundacion ? `<p class="mb-1"><i class="fas fa-calendar text-info me-1"></i> Fundada: ${iglesia.anio_fundacion}</p>` : ''}
                ${iglesia.numero_miembros ? `<p class="mb-1"><i class="fas fa-users text-warning me-1"></i> Miembros: ${iglesia.numero_miembros}</p>` : ''}
                ${iglesia.horarios_servicio ? `<hr><p class="mb-0 text-muted small">${iglesia.horarios_servicio}</p>` : ''}
            </div>
        `;
        
        marker.bindPopup(popupContent);
        
        // Agregar efecto hover
        marker.on('mouseover', function() {
            this.setStyle({
                radius: 15,
                weight: 4
            });
        });
        
        marker.on('mouseout', function() {
            this.setStyle({
                radius: 12,
                weight: 3
            });
        });
        
        // Guardar marcador en el array
        marcadores.push(marker);
    }
});

// Funcionalidad del buscador
document.addEventListener('DOMContentLoaded', function() {
    const buscadorInput = document.getElementById('buscador-iglesias');
    const resultadosDiv = document.getElementById('resultados-busqueda');
    let timeoutId = null;
    
    // Detectar si es dispositivo móvil
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // Función para buscar iglesias con debounce
    function buscarIglesias(termino) {
        // Limpiar timeout anterior
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        
        timeoutId = setTimeout(() => {
            if (!termino || termino.length < 2) {
                resultadosDiv.style.display = 'none';
                return;
            }
            
            const terminoLower = termino.toLowerCase();
            const resultados = iglesias.filter(iglesia => 
                iglesia.nombre_iglesia.toLowerCase().includes(terminoLower) ||
                (iglesia.denominacion && iglesia.denominacion.toLowerCase().includes(terminoLower)) ||
                (iglesia.ministro_encargado && iglesia.ministro_encargado.toLowerCase().includes(terminoLower))
            );
            
            mostrarResultados(resultados);
        }, isMobile ? 300 : 150); // Mayor delay en móvil
    }
    
    // Función para mostrar resultados
    function mostrarResultados(resultados) {
        if (resultados.length === 0) {
            resultadosDiv.innerHTML = '<div class="search-result-item">No se encontraron iglesias</div>';
            resultadosDiv.style.display = 'block';
            return;
        }
        
        // Limitar resultados en móvil para mejor rendimiento
        const maxResultados = isMobile ? 5 : 8;
        const resultadosLimitados = resultados.slice(0, maxResultados);
        
        const html = resultadosLimitados.map(iglesia => `
            <div class="search-result-item" data-iglesia="${iglesias.indexOf(iglesia)}">
                <div class="search-result-name">${iglesia.nombre_iglesia}</div>
                <div class="search-result-info">
                    ${iglesia.denominacion || 'Sin denominación'} • ${iglesia.ministro_encargado}
                </div>
            </div>
        `).join('');
        
        // Agregar indicador si hay más resultados
        const htmlFinal = html + (resultados.length > maxResultados ? 
            `<div class="search-result-item" style="background-color: #f8f9fa; cursor: default;">
                <div class="search-result-info text-center">
                    Mostrando ${maxResultados} de ${resultados.length} resultados
                </div>
            </div>` : '');
        
        resultadosDiv.innerHTML = htmlFinal;
        resultadosDiv.style.display = 'block';
        
        // Agregar event listeners a los resultados
        resultadosDiv.querySelectorAll('.search-result-item[data-iglesia]').forEach(item => {
            // Event listener para click/touch
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const iglesiaIndex = parseInt(this.dataset.iglesia);
                if (!isNaN(iglesiaIndex)) {
                    mostrarIglesiaEnMapa(iglesiaIndex);
                }
            });
            
            // Event listener para touch en móviles
            if (isMobile) {
                item.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    const iglesiaIndex = parseInt(this.dataset.iglesia);
                    if (!isNaN(iglesiaIndex)) {
                        mostrarIglesiaEnMapa(iglesiaIndex);
                    }
                });
            }
        });
    }
    
    // Función para mostrar iglesia en el mapa
    function mostrarIglesiaEnMapa(index) {
        const iglesia = iglesias[index];
        const marcador = marcadores[index];
        
        if (iglesia && marcador && iglesia.latitud && iglesia.longitud) {
            // Centrar el mapa en la iglesia (zoom más cercano en móvil)
            const zoomLevel = isMobile ? 17 : 16;
            mapa.setView([parseFloat(iglesia.latitud), parseFloat(iglesia.longitud)], zoomLevel);
            
            // Abrir el popup con delay para mejor UX en móvil
            setTimeout(() => {
                marcador.openPopup();
            }, isMobile ? 500 : 200);
            
            // Efecto de destacar marcador temporalmente
            marcador.setStyle({
                radius: isMobile ? 18 : 20,
                weight: 5,
                color: '#ff0000'
            });
            
            setTimeout(() => {
                marcador.setStyle({
                    radius: 12,
                    weight: 3,
                    color: '#fff'
                });
            }, 2500);
            
            // Ocultar resultados y limpiar buscador
            resultadosDiv.style.display = 'none';
            buscadorInput.value = iglesia.nombre_iglesia;
            
            // Quitar focus en móvil para ocultar teclado
            if (isMobile) {
                buscadorInput.blur();
            }
        }
    }
    
    // Event listeners del buscador
    buscadorInput.addEventListener('input', function() {
        buscarIglesias(this.value);
    });
    
    buscadorInput.addEventListener('focus', function() {
        if (this.value.length >= 2) {
            buscarIglesias(this.value);
        }
    });
    
    // Ocultar resultados al hacer click/touch fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            resultadosDiv.style.display = 'none';
        }
    });
    
    // Event listener adicional para touch en móviles
    if (isMobile) {
        document.addEventListener('touchstart', function(e) {
            if (!e.target.closest('.search-container')) {
                resultadosDiv.style.display = 'none';
            }
        });
    }
    
    // Búsqueda con Enter (mejorada para móvil)
    buscadorInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevenir envío de formulario
            const primerResultado = resultadosDiv.querySelector('.search-result-item[data-iglesia]');
            if (primerResultado) {
                const iglesiaIndex = parseInt(primerResultado.dataset.iglesia);
                if (!isNaN(iglesiaIndex)) {
                    mostrarIglesiaEnMapa(iglesiaIndex);
                }
            }
        }
    });
    
    // Prevenir zoom automático en iOS al enfocar input
    if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
        buscadorInput.addEventListener('focus', function() {
            this.style.fontSize = '16px';
        });
        
        buscadorInput.addEventListener('blur', function() {
            this.style.fontSize = '';
        });
    }
});

// Si no hay iglesias, mostrar mensaje
if (iglesias.length === 0) {
    L.popup()
        .setLatLng([18.6769, -88.3912])
        .setContent('<div class="text-center"><p>No hay iglesias registradas aún.</p><a href="registro.php" class="btn btn-primary btn-sm">Registrar Primera Iglesia</a></div>')
        .openOn(mapa);
}

// Ajustar vista del mapa a todos los marcadores
if (iglesias.length > 0) {
    const group = new L.featureGroup(mapa._layers);
    if (Object.keys(group._layers).length > 0) {
        mapa.fitBounds(group.getBounds().pad(0.1));
    }
}

// Forzar redibujado del mapa después de cargar
setTimeout(function() {
    console.log('Invalidando tamaño del mapa...');
    mapa.invalidateSize();
}, 100);

// También forzar redibujado cuando la ventana cambie de tamaño
window.addEventListener('resize', function() {
    setTimeout(function() {
        mapa.invalidateSize();
    }, 200);
});
</script>

</body>
</html>
