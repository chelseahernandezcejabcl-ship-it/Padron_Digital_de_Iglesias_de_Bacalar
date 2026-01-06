<?php
require_once 'includes/config.php';

// Obtener avisos con filtros básicos
$sql = "SELECT a.*, 'Administrador Municipal' as admin_nombre 
        FROM avisos_municipales a 
        WHERE 1=1
        ORDER BY a.fecha_creacion DESC";

$result = $conn->query($sql);
$avisos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $avisos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Avisos Municipales - Panel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }
        
        .main-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #611232;
            margin: 0 0 10px 0;
        }
        
        .page-subtitle {
            color: #666;
            margin: 0;
        }
        
        .content-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 0 0 20px 0;
        }
        
        /* SISTEMA DE CARDS LIMPIO */
        .avisos-wrapper {
            width: 100%;
            overflow: hidden;
        }
        
        .avisos-grid {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }
        
        .aviso-item {
            width: 25%;
            padding: 10px;
        }
        
        .aviso-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .aviso-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .aviso-header {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            border-radius: 8px 8px 0 0;
        }
        
        .aviso-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #333;
            line-height: 1.3;
        }
        
        .aviso-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            color: white;
            margin-bottom: 5px;
        }
        
        .badge-activo { background: #28a745; }
        .badge-inactivo { background: #6c757d; }
        
        .aviso-tipo {
            font-size: 12px;
            color: #666;
        }
        
        .aviso-body {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .aviso-content {
            font-size: 14px;
            line-height: 1.4;
            color: #555;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        
        .aviso-dates {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .aviso-date {
            flex: 1;
        }
        
        .aviso-date strong {
            display: block;
            color: #333;
            margin-bottom: 2px;
        }
        
        .aviso-footer {
            padding: 12px 15px;
            background: #fafafa;
            border-top: 1px solid #e0e0e0;
            border-radius: 0 0 8px 8px;
        }
        
        .aviso-actions {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .aviso-btn {
            flex: 1;
            padding: 6px 8px;
            border: none;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            text-align: center;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-ver { background: #17a2b8; color: white; }
        .btn-ver:hover { background: #138496; color: white; }
        .btn-editar { background: #ffc107; color: #212529; }
        .btn-editar:hover { background: #e0a800; color: #212529; }
        .btn-eliminar { background: #dc3545; color: white; }
        .btn-eliminar:hover { background: #c82333; color: white; }
        
        .aviso-meta {
            font-size: 10px;
            color: #888;
            line-height: 1.3;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: #611232;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            background: #4a0d25;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 1199px) {
            .aviso-item { width: 33.333%; }
        }
        
        @media (max-width: 991px) {
            .aviso-item { width: 50%; }
        }
        
        @media (max-width: 767px) {
            .aviso-item { width: 100%; }
            .avisos-grid { margin: -5px; }
            .aviso-item { padding: 5px; }
            .main-container { padding: 10px; }
            .aviso-actions { flex-direction: column; gap: 3px; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-section">
            <h1 class="page-title">
                <i class="fas fa-bullhorn"></i> Gestión de Avisos Municipales
            </h1>
            <p class="page-subtitle">Administra los avisos públicos del municipio de Bacalar</p>
        </div>
        
        <div class="content-section">
            <h2 class="section-title">Avisos Publicados (<?php echo count($avisos); ?>)</h2>
            
            <?php if (empty($avisos)): ?>
                <div class="empty-state">
                    <i class="fas fa-bullhorn"></i>
                    <h3>No hay avisos publicados</h3>
                    <p>Comienza creando tu primer aviso municipal.</p>
                    <a href="#" class="btn-primary">
                        <i class="fas fa-plus"></i> Crear primer aviso
                    </a>
                </div>
            <?php else: ?>
                <div class="avisos-wrapper">
                    <div class="avisos-grid">
                        <?php foreach($avisos as $aviso): ?>
                        <div class="aviso-item">
                            <div class="aviso-card">
                                <div class="aviso-header">
                                    <div class="aviso-title"><?php echo htmlspecialchars($aviso['titulo']); ?></div>
                                    <div class="aviso-badge <?php echo $aviso['activo'] == 1 ? 'badge-activo' : 'badge-inactivo'; ?>">
                                        <?php echo $aviso['activo'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                    </div>
                                    <div class="aviso-tipo">
                                        <?php 
                                        $tipo_labels = [
                                            'general' => 'General',
                                            'urgente' => 'Urgente',
                                            'evento' => 'Evento',
                                            'normativo' => 'Normativo',
                                            'tramite' => 'Trámite'
                                        ];
                                        echo $tipo_labels[$aviso['tipo_aviso']] ?? ucfirst($aviso['tipo_aviso']); 
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="aviso-body">
                                    <div class="aviso-content">
                                        <?php 
                                        $contenido_limpio = strip_tags($aviso['contenido'], '<b><i><u><strong><em>');
                                        echo substr($contenido_limpio, 0, 150) . (strlen(strip_tags($aviso['contenido'])) > 150 ? '...' : ''); 
                                        ?>
                                    </div>
                                    
                                    <div class="aviso-dates">
                                        <div class="aviso-date">
                                            <strong>Desde:</strong>
                                            <?php echo $aviso['fecha_publicacion'] ? date('d/m/Y', strtotime($aviso['fecha_publicacion'])) : 'No definida'; ?>
                                        </div>
                                        <div class="aviso-date">
                                            <strong>Hasta:</strong>
                                            <?php echo $aviso['fecha_vencimiento'] ? date('d/m/Y', strtotime($aviso['fecha_vencimiento'])) : 'Sin límite'; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($aviso['imagen'])): ?>
                                    <div style="font-size: 11px; color: #666; margin-top: 5px;">
                                        <i class="fas fa-image"></i> Imagen adjunta
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="aviso-footer">
                                    <div class="aviso-actions">
                                        <button class="aviso-btn btn-ver" onclick="alert('Ver aviso <?php echo $aviso['id']; ?>')">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <button class="aviso-btn btn-editar" onclick="alert('Editar aviso <?php echo $aviso['id']; ?>')">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button class="aviso-btn btn-eliminar" onclick="alert('Eliminar aviso <?php echo $aviso['id']; ?>')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                    <div class="aviso-meta">
                                        Creado por: <?php echo htmlspecialchars($aviso['admin_nombre'] ?? 'Admin'); ?><br>
                                        <?php echo date('d/m/Y H:i', strtotime($aviso['fecha_creacion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
