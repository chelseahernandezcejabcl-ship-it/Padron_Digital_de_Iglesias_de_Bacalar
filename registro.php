<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$mensaje = '';
$tipo_mensaje = '';

if (isset($_POST['registrar'])) {
    // Recoger y limpiar datos básicos
    $nombre_iglesia = trim($_POST['nombre_iglesia']);
    $denominacion_id = $_POST['denominacion_id']; // No convertir a int todavía
    $denominacion_personalizada = trim($_POST['denominacion_personalizada'] ?? '');
    $ministro_encargado = trim($_POST['ministro_encargado']);
    $direccion = trim($_POST['direccion']);
    $ciudad = trim($_POST['ciudad']);
    $latitud = floatval($_POST['latitud']);
    $longitud = floatval($_POST['longitud']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Datos adicionales
    $anio_fundacion = !empty($_POST['anio_fundacion']) ? intval($_POST['anio_fundacion']) : null;
    $numero_miembros = !empty($_POST['numero_miembros']) ? intval($_POST['numero_miembros']) : 0;
    $descripcion = trim($_POST['descripcion']);
    $tipo_construccion = trim($_POST['tipo_construccion']);
    $horarios_servicio = trim($_POST['horarios_servicio']);
    $pagina_web = trim($_POST['pagina_web']);
    $facebook = trim($_POST['facebook']);
    $refugio_anticiclonico = isset($_POST['refugio_anticiclonico']) ? $_POST['refugio_anticiclonico'] : 'no';
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre_iglesia)) $errores[] = "El nombre de la iglesia es obligatorio.";
    if (empty($ministro_encargado)) $errores[] = "El nombre del ministro es obligatorio.";
    if (empty($direccion)) $errores[] = "La dirección es obligatoria.";
    if (empty($email)) $errores[] = "El email es obligatorio.";
    if (empty($tipo_construccion)) $errores[] = "El tipo de construcción es obligatorio.";
    if (!in_array($refugio_anticiclonico, ['si', 'no'])) $errores[] = "Debe especificar si puede servir como refugio anticiclónico.";
    if ($latitud == 0 || $longitud == 0) $errores[] = "Debe seleccionar la ubicación en el mapa.";
    
    if ($password !== $confirm_password) {
        $errores[] = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    
    // Verificar que el email no exista
    if (empty($errores)) {
        $sql = "SELECT id FROM iglesias WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = 'Ya existe una iglesia registrada con este correo electrónico.';
        }
        $stmt->close();
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Crear directorio para fotos si no existe
        $upload_dir = "uploads/fotos/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Manejar denominación personalizada
        $final_denominacion_id = null;
        $final_denominacion_personalizada = null;
        
        if ($denominacion_id === 'otra') {
            // Validar que se especificó una denominación personalizada
            if (empty($denominacion_personalizada)) {
                $error_message = "Por favor especifique la denominación personalizada.";
            } else {
                $final_denominacion_id = null; // No hay ID para denominación personalizada
                $final_denominacion_personalizada = $denominacion_personalizada;
            }
        } else {
            $final_denominacion_id = intval($denominacion_id);
            $final_denominacion_personalizada = null;
        }
        
        if (!isset($error_message)) {
            try {
                $conn->begin_transaction();
                
                // Insertar iglesia (estado pendiente por defecto)
                $sql = "INSERT INTO iglesias (nombre_iglesia, denominacion_id, denominacion_personalizada, ministro_encargado, direccion, ciudad, latitud, longitud, telefono, correo, password, anio_fundacion, numero_miembros, descripcion, tipo_construccion, horarios_servicio, pagina_web, facebook, refugio_anticiclonico, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $estado = 'pendiente';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sissssddsssiisssssss", $nombre_iglesia, $final_denominacion_id, $final_denominacion_personalizada, $ministro_encargado, $direccion, $ciudad, $latitud, $longitud, $telefono, $email, $password_hash, $anio_fundacion, $numero_miembros, $descripcion, $tipo_construccion, $horarios_servicio, $pagina_web, $facebook, $refugio_anticiclonico, $estado);
                
                if ($stmt->execute()) {
                    $iglesia_id = $conn->insert_id;
                    
                    // Si se especificó una denominación personalizada, crear propuesta para revisión del admin
                    if (!empty($final_denominacion_personalizada)) {
                        // Verificar si ya existe una propuesta igual pendiente
                        $check_propuesta = "SELECT id FROM denominaciones_propuestas WHERE nombre_propuesto = ? AND estado = 'pendiente'";
                        $check_stmt = $conn->prepare($check_propuesta);
                        $check_stmt->bind_param("s", $final_denominacion_personalizada);
                        $check_stmt->execute();
                        $propuesta_existe = $check_stmt->get_result()->num_rows > 0;
                        
                        if (!$propuesta_existe) {
                            // Crear nueva propuesta de denominación
                            $propuesta_sql = "INSERT INTO denominaciones_propuestas (nombre_propuesto, iglesia_id) VALUES (?, ?)";
                            $propuesta_stmt = $conn->prepare($propuesta_sql);
                            $propuesta_stmt->bind_param("si", $final_denominacion_personalizada, $iglesia_id);
                            $propuesta_stmt->execute();
                        }
                    }
                    
                    // Procesar fotos subidas
                    if (!empty($_FILES['fotos']['name'][0])) {
                for ($i = 0; $i < count($_FILES['fotos']['name']); $i++) {
                    if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
                        $file_tmp = $_FILES['fotos']['tmp_name'][$i];
                        $file_name = $_FILES['fotos']['name'][$i];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        // Validar extensión
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($file_ext, $allowed_extensions)) {
                            $new_filename = $iglesia_id . "_" . time() . "_" . $file_name;
                            $upload_path = $upload_dir . $new_filename;
                            
                            if (move_uploaded_file($file_tmp, $upload_path)) {
                                // Insertar en tabla fotos_iglesia con la ruta correcta
                                $ruta_foto = "/uploads/fotos/" . $new_filename;
                                $sql_foto = "INSERT INTO fotos_iglesia (iglesia_id, ruta, es_principal) VALUES (?, ?, 0)";
                                $stmt_foto = $conn->prepare($sql_foto);
                                $stmt_foto->bind_param("is", $iglesia_id, $ruta_foto);
                                $stmt_foto->execute();
                                $stmt_foto->close();
                            }
                        }
                    }
                }
            }
            
            // Confirmar transacción
            $conn->commit();
            
            // Redirigir con mensaje de éxito
            header("Location: index.php?registro=exitoso");
            exit();
            
        } else {
            $conn->rollback();
            $mensaje = 'Error al registrar la iglesia: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
        $stmt->close();
        
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = 'Error al registrar la iglesia: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
        } // Cierre del if(!isset($error_message))
    } else {
        $mensaje = implode('<br>', $errores);
        $tipo_mensaje = 'danger';
    }
}

// Obtener denominaciones para el select
$denominaciones = [];
$result = $conn->query("SELECT * FROM denominaciones ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $denominaciones[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Iglesia - H. Ayuntamiento de Bacalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root { 
            --color-gobierno: #611232; 
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            margin-top: 0px;
            padding: 2rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .register-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: none;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--color-gobierno) 0%, #8b1538 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .section-header {
            background-color: var(--color-gobierno);
            color: white;
            padding: 15px 20px;
            margin: 30px 0 20px 0;
            border-radius: 10px;
            font-weight: 600;
            border-left: 5px solid #8b1538;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-gobierno);
            box-shadow: 0 0 0 0.2rem rgba(97, 18, 50, 0.25);
        }
        
        .btn-primary {
            background-color: var(--color-gobierno);
            border-color: var(--color-gobierno);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #8b1538;
            border-color: #8b1538;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(97, 18, 50, 0.3);
        }
        
        .btn-secondary {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
        }
        
        #map {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .step-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--color-gobierno);
        }
        
        .btn-bacalar { 
            background-color: var(--color-gobierno); 
            border-color: var(--color-gobierno); 
            color: white; 
        }
        .btn-bacalar:hover { 
            background-color: #8b1538; 
            border-color: #8b1538; 
            color: white;
        }
        
        .form-control:focus { 
            border-color: var(--bacalar-accent); 
            box-shadow: 0 0 0 0.2rem rgba(97, 18, 50, 0.25); 
        }
        
        #map { 
            height: 300px; 
            width: 100%; 
            border-radius: 8px;
        }
        
        .photo-preview { 
            max-width: 100px; 
            max-height: 100px; 
            object-fit: cover; 
            margin: 5px; 
            border-radius: 5px; 
            border: 2px solid #e9ecef;
        }
        
        .file-upload-area {
            border: 2px dashed var(--color-gobierno);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .file-upload-area:hover {
            background-color: #f8f9fa;
            border-color: #8b1538;
        }
        
        .required { color: red; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-content">
        <div class="container register-container">
            <div class="register-card">
                <div class="register-header">
                    <h1 class="mb-3">
                        <i class="fas fa-church me-2"></i>
                        Registro de Iglesia
                    </h1>
                    <p class="mb-0">H. Ayuntamiento de Bacalar</p>
                    <small>Complete todos los campos para solicitar su registro</small>
                </div>
                
                <div class="p-4">
                <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensaje; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="registroForm">
                    <!-- INFORMACIÓN BÁSICA -->
                    <div class="section-header">
                        <i class="fas fa-info-circle me-2"></i>
                        Información Básica
                    </div>
                    
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre_iglesia" class="form-label">
                                    Nombre de la Iglesia <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre_iglesia" name="nombre_iglesia" 
                                       value="<?php echo htmlspecialchars($_POST['nombre_iglesia'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="denominacion_id" class="form-label">
                                    Denominación <span class="required">*</span>
                                </label>
                                <select class="form-select" id="denominacion_id" name="denominacion_id" required>
                                    <option value="">Seleccione una denominación</option>
                                    <?php foreach ($denominaciones as $denom): ?>
                                        <option value="<?php echo $denom['id']; ?>" 
                                                <?php echo (($_POST['denominacion_id'] ?? '') == $denom['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($denom['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="otra" <?php echo (($_POST['denominacion_id'] ?? '') == 'otra') ? 'selected' : ''; ?>>
                                        🔧 Otra denominación (especificar)
                                    </option>
                                </select>
                                
                                <!-- Campo para especificar otra denominación -->
                                <div id="otra-denominacion" class="mt-2" style="display: none;">
                                    <input type="text" class="form-control" name="denominacion_personalizada" 
                                           placeholder="Especifique la denominación..."
                                           value="<?php echo htmlspecialchars($_POST['denominacion_personalizada'] ?? ''); ?>">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Esta denominación será revisada por el administrador
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ministro_encargado" class="form-label">
                                    Ministro/Pastor Encargado <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="ministro_encargado" name="ministro_encargado" 
                                       value="<?php echo htmlspecialchars($_POST['ministro_encargado'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    Correo Electrónico <span class="required">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="anio_fundacion" class="form-label">Año de Fundación</label>
                                <input type="number" class="form-control" id="anio_fundacion" name="anio_fundacion" 
                                       min="1800" max="<?php echo date('Y'); ?>"
                                       value="<?php echo htmlspecialchars($_POST['anio_fundacion'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- UBICACIÓN -->
                    <div class="section-header">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Ubicación
                    </div>
                    
                    <div class="form-section">
                        <div class="mb-3">
                            <label for="direccion" class="form-label">
                                Dirección Completa <span class="required">*</span>
                            </label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2" 
                                      placeholder="Calle, número, colonia, referencias..." required><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ciudad" class="form-label">
                                Ciudad/Localidad <span class="text-muted">(Opcional)</span>
                            </label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                   placeholder="Ej: Bacalar Centro, Maya Balam, Buenavista..." 
                                   value="<?php echo htmlspecialchars($_POST['ciudad'] ?? ''); ?>">
                            <small class="form-text text-muted">
                                Especifique la localidad dentro del municipio de Bacalar
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                Ubicación en el Mapa <span class="required">*</span>
                            </label>
                            <div id="map"></div>
                            <small class="form-text text-muted">
                                Haga clic en el mapa para marcar la ubicación exacta de su iglesia
                            </small>
                            <input type="hidden" id="latitud" name="latitud" value="<?php echo $_POST['latitud'] ?? ''; ?>">
                            <input type="hidden" id="longitud" name="longitud" value="<?php echo $_POST['longitud'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <!-- INFORMACIÓN ADICIONAL -->
                    <div class="section-header">
                        <i class="fas fa-users me-2"></i>
                        Información Adicional
                    </div>
                    
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="numero_miembros" class="form-label">Número Aproximado de Miembros</label>
                                <input type="number" class="form-control" id="numero_miembros" name="numero_miembros" 
                                       min="0" value="<?php echo htmlspecialchars($_POST['numero_miembros'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_construccion" class="form-label">
                                    Tipo de Construcción <span class="required">*</span>
                                </label>
                                <select class="form-select" id="tipo_construccion" name="tipo_construccion" required>
                                    <option value="">Seleccione el tipo de construcción</option>
                                    <option value="adobe" <?php echo (($_POST['tipo_construccion'] ?? '') == 'adobe') ? 'selected' : ''; ?>>Adobe</option>
                                    <option value="block_con_castillo" <?php echo (($_POST['tipo_construccion'] ?? '') == 'block_con_castillo') ? 'selected' : ''; ?>>Block con Castillo</option>
                                    <option value="block_sin_castillo" <?php echo (($_POST['tipo_construccion'] ?? '') == 'block_sin_castillo') ? 'selected' : ''; ?>>Block sin Castillo</option>
                                    <option value="concreto_armado" <?php echo (($_POST['tipo_construccion'] ?? '') == 'concreto_armado') ? 'selected' : ''; ?>>Concreto Armado</option>
                                    <option value="concreto_block" <?php echo (($_POST['tipo_construccion'] ?? '') == 'concreto_block') ? 'selected' : ''; ?>>Concreto Block</option>
                                    <option value="ladrillo" <?php echo (($_POST['tipo_construccion'] ?? '') == 'ladrillo') ? 'selected' : ''; ?>>Ladrillo</option>
                                    <option value="madera" <?php echo (($_POST['tipo_construccion'] ?? '') == 'madera') ? 'selected' : ''; ?>>Madera</option>
                                    <option value="mamposteria" <?php echo (($_POST['tipo_construccion'] ?? '') == 'mamposteria') ? 'selected' : ''; ?>>Mampostería</option>
                                    <option value="piedra" <?php echo (($_POST['tipo_construccion'] ?? '') == 'piedra') ? 'selected' : ''; ?>>Piedra</option>
                                    <option value="prefabricada" <?php echo (($_POST['tipo_construccion'] ?? '') == 'prefabricada') ? 'selected' : ''; ?>>Prefabricada</option>
                                    <option value="estructura_metalica" <?php echo (($_POST['tipo_construccion'] ?? '') == 'estructura_metalica') ? 'selected' : ''; ?>>Estructura Metálica</option>
                                    <option value="bambu" <?php echo (($_POST['tipo_construccion'] ?? '') == 'bambu') ? 'selected' : ''; ?>>Bambú</option>
                                    <option value="bajareque" <?php echo (($_POST['tipo_construccion'] ?? '') == 'bajareque') ? 'selected' : ''; ?>>Bajareque</option>
                                    <option value="mixta" <?php echo (($_POST['tipo_construccion'] ?? '') == 'mixta') ? 'selected' : ''; ?>>Mixta</option>
                                    <option value="otro" <?php echo (($_POST['tipo_construccion'] ?? '') == 'otro') ? 'selected' : ''; ?>>Otro</option>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Seleccione el material principal de construcción del templo
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-shield-alt me-2"></i>¿Puede servir como refugio anticiclónico?
                                <span class="text-danger">*</span>
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="refugio_anticiclonico" id="refugio_si" value="si" 
                                       <?php echo (($_POST['refugio_anticiclonico'] ?? 'no') == 'si') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="refugio_si">
                                    <i class="fas fa-check-circle text-success me-1"></i>Sí, puede servir como refugio
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="refugio_anticiclonico" id="refugio_no" value="no" 
                                       <?php echo (($_POST['refugio_anticiclonico'] ?? 'no') == 'no') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="refugio_no">
                                    <i class="fas fa-times-circle text-danger me-1"></i>No puede servir como refugio
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Esta información es importante para casos de emergencia por fenómenos meteorológicos
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción de la Iglesia</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                      placeholder="Breve descripción de la historia, misión y actividades de su iglesia..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="horarios_servicio" class="form-label">Horarios de Servicios</label>
                            <textarea class="form-control" id="horarios_servicio" name="horarios_servicio" rows="2"
                                      placeholder="Ej: Domingo 10:00 AM y 6:00 PM, Miércoles 7:00 PM..."><?php echo htmlspecialchars($_POST['horarios_servicio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pagina_web" class="form-label">Página Web (opcional)</label>
                                <input type="url" class="form-control" id="pagina_web" name="pagina_web" 
                                       value="<?php echo htmlspecialchars($_POST['pagina_web'] ?? ''); ?>"
                                       placeholder="https://...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="facebook" class="form-label">Facebook (opcional)</label>
                                <input type="url" class="form-control" id="facebook" name="facebook" 
                                       value="<?php echo htmlspecialchars($_POST['facebook'] ?? ''); ?>"
                                       placeholder="https://facebook.com/...">
                            </div>
                        </div>
                    </div>
                    
                    <!-- FOTOGRAFÍAS -->
                    <div class="section-header">
                        <i class="fas fa-camera me-2"></i>
                        Fotografías de la Iglesia
                    </div>
                    
                    <div class="form-section">
                        <div class="file-upload-area mb-3">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                            <p class="mb-2">Seleccione fotografías de su iglesia</p>
                            <input type="file" class="form-control" id="fotos" name="fotos[]" 
                                   multiple accept="image/*" onchange="previewImages(this)">
                            <small class="text-muted">
                                Puede seleccionar múltiples imágenes (JPG, PNG, GIF). 
                                También podrá agregar más fotos después del registro.
                            </small>
                        </div>
                        <div id="imagePreview" class="d-flex flex-wrap"></div>
                    </div>
                    
                    <!-- CONTRASEÑA -->
                    <div class="section-header">
                        <i class="fas fa-lock me-2"></i>
                        Configurar Acceso
                    </div>
                    
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Contraseña <span class="required">*</span>
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">Mínimo 6 caracteres</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">
                                    Confirmar Contraseña <span class="required">*</span>
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="registrar" class="btn btn-bacalar btn-lg px-5">
                            <i class="fas fa-paper-plane me-2"></i>
                            Enviar Solicitud de Registro
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p class="text-muted">
                            Su solicitud será revisada por el administrador municipal.<br>
                            Recibirá una notificación cuando sea aprobada.
                        </p>
                        <a href="login.php" class="text-decoration-none">¿Ya tiene una cuenta? Iniciar sesión</a>
                    </div>
                </form>
            </div>
        </div>
    </div> <!-- Cierre de register-container -->
</div> <!-- Cierre de main-content -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Mapa de Leaflet
        var map = L.map('map').setView([18.6791, -88.3896], 13); // Bacalar coordinates
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        var marker = null;
        
        // Al hacer clic en el mapa
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            
            // Remover marker anterior si existe
            if (marker) {
                map.removeLayer(marker);
            }
            
            // Agregar nuevo marker
            marker = L.marker([lat, lng]).addTo(map);
            
            // Actualizar campos ocultos
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;
        });
        
        // Si hay coordenadas previas (en caso de error de validación)
        var lat = parseFloat(document.getElementById('latitud').value);
        var lng = parseFloat(document.getElementById('longitud').value);
        if (lat && lng) {
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 15);
        }
        
        // Preview de imágenes
        function previewImages(input) {
            var preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files) {
                for (var i = 0; i < input.files.length; i++) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'photo-preview';
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(input.files[i]);
                }
            }
        }
        
        // Validación de contraseñas
        document.getElementById('confirm_password').addEventListener('blur', function() {
            var password = document.getElementById('password').value;
            var confirm = this.value;
            
            if (password && confirm && password !== confirm) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Manejar campo de denominación personalizada
        (function() {
            const denominacionSelect = document.getElementById('denominacion_id');
            const otraDiv = document.getElementById('otra-denominacion');
            
            if (denominacionSelect && otraDiv) {
                const otraInput = otraDiv.querySelector('input[name="denominacion_personalizada"]');
                
                if (otraInput) {
                    // Función para manejar el cambio
                    function toggleOtraDenominacion() {
                        console.log('Toggle ejecutado, valor:', denominacionSelect.value);
                        
                        if (denominacionSelect.value === 'otra') {
                            otraDiv.style.display = 'block';
                            otraInput.required = true;
                            setTimeout(() => otraInput.focus(), 100);
                            console.log('Campo mostrado');
                        } else {
                            otraDiv.style.display = 'none';
                            otraInput.required = false;
                            otraInput.value = '';
                            console.log('Campo ocultado');
                        }
                    }
                    
                    // Agregar event listener
                    denominacionSelect.addEventListener('change', toggleOtraDenominacion);
                    
                    // Verificar estado inicial
                    toggleOtraDenominacion();
                    
                    console.log('JavaScript de denominación personalizada inicializado');
                } else {
                    console.error('No se encontró el input de denominación personalizada');
                }
            } else {
                console.error('No se encontró el select o div de denominación personalizada');
            }
        })();
    </script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
