<?php
// Demo del comprobante - datos de ejemplo
$iglesia_demo = [
    'id' => 1,
    'nombre_iglesia' => 'Iglesia Cristiana Emanuel',
    'ministro_encargado' => 'Pastor Juan Carlos Hernández',
    'denominacion_nombre' => 'Cristiana Evangélica',
    'direccion' => 'Calle Reforma #123',
    'ciudad' => 'Bacalar',
    'telefono' => '983-123-4567',
    'fecha_registro' => '2025-01-15'
];

$ministro = $iglesia_demo['ministro_encargado'];
$nombre_iglesia = $iglesia_demo['nombre_iglesia'];
$numero_registro = 'SGAR/' . str_pad($iglesia_demo['id'], 4, '0', STR_PAD_LEFT) . '/' . date('Y', strtotime($iglesia_demo['fecha_registro']));
$fecha_generacion = date('d/m/Y H:i:s');

// Crear el contenido HTML del comprobante
$html_content = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Registro - ' . htmlspecialchars($iglesia_demo['nombre_iglesia']) . '</title>
    <style>
        @page {
            margin: 0.5in;
            size: letter; /* Tamaño carta US Letter */
        }
        
        html, body {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        
        /* Container principal optimizado para una sola hoja */
        .document-container {
            width: 8.5in;
            max-width: 8.5in;
            margin: 10px auto;
            padding: 0.5in;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
            box-sizing: border-box;
            position: relative;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .logos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            height: 50px;
        }
        
        .logo-left, .logo-right {
            width: 45px;
            height: 45px;
            background: linear-gradient(145deg, #f0f0f0, #e0e0e0);
            border: 1px solid #8B4513;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6pt;
            font-weight: bold;
            color: #8B4513;
            text-align: center;
            line-height: 1.0;
            border-radius: 50%;
        }
        
        .logo-center {
            text-align: center;
            flex: 1;
        }
        
        .logo-center h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #8B4513;
            margin: 2px 0;
            letter-spacing: 1px;
        }
        
        .logo-center p {
            font-size: 10pt;
            margin: 0;
            color: #666;
            font-weight: 600;
        }
        
        .title {
            font-size: 11pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-align: center;
            line-height: 1.2;
        }
        
        .recipient {
            margin: 15px 0 10px 0;
            font-size: 11pt;
        }
        
        .content {
            text-align: justify;
            margin: 15px 0;
            line-height: 1.3;
            font-size: 10pt;
            text-indent: 0.5cm;
        }
        
        .data-section {
            margin: 30px 0;
            border-top: 2px solid #8B4513;
            padding-top: 20px;
        }
        
        .data-section h4 {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 15px;
            color: #8B4513;
        }
        
        .data-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 11pt;
        }
        
        .data-label {
            font-weight: bold;
            width: 180px;
            color: #333;
        }
        
        .data-value {
            flex: 1;
            color: #000;
        }
        
        .signature {
            text-align: center;
            margin-top: 60px;
            font-size: 13pt;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            width: 250px;
            margin: 50px auto 15px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        
        .note {
            font-style: italic;
            font-size: 10pt;
            color: #666;
            margin: 25px 0;
            text-align: center;
        }
        
        .demo-banner {
            background: linear-gradient(90deg, #ff6b6b, #ee5a24);
            color: white;
            text-align: center;
            padding: 15px;
            margin: -20px -20px 20px -20px;
            font-weight: bold;
            font-size: 12pt;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Botones de acción flotantes */
        .action-buttons {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .action-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11pt;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            min-width: 180px;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .action-btn.secondary {
            background: #6c757d;
        }
        
        .action-btn.success {
            background: #28a745;
        }
        
        /* Media queries para responsive */
        @media screen and (max-width: 1000px) {
            .document-container {
                width: 95%;
                max-width: none;
                margin: 10px auto;
                padding: 20px;
                box-shadow: none;
            }
            
            .action-buttons {
                position: relative;
                top: auto;
                right: auto;
                margin: 20px 0;
                flex-direction: row;
                justify-content: center;
                flex-wrap: wrap;
            }
        }
        
        /* Estilos específicos para impresión */
        @media print {
            html, body {
                margin: 0;
                padding: 0;
                font-size: 12pt;
                background: white;
            }
            
            .document-container {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1in;
                box-shadow: none;
                min-height: auto;
                background: white;
            }
            
            .demo-banner {
                display: none !important;
            }
            
            .action-buttons {
                display: none !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            /* Forzar salto de página si es necesario */
            .signature {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="document-container">
        <div class="demo-banner">
            🔬 VISTA DEMO - COMPROBANTE DE REGISTRO DIGITAL
        </div>
        
        <div class="header">
            <div class="logos">
                <div class="logo-left">ESCUDO<br>BACALAR</div>
                <div class="logo-center">
                    <h1>BACALAR</h1>
                    <p>MUNICIPIO</p>
                </div>
                <div class="logo-right">BACALAR<br>PUEBLO<br>MÁGICO</div>
            </div>
        </div>
        
        <div class="title">
            <strong>Coordinación de Atención Ciudadana y Asuntos Religiosos del Municipio de Bacalar</strong><br><br>
            <strong>Empadronamiento digital de iglesias del<br>
            municipio de Bacalar Quintana Roo.</strong>
        </div>
        
        <div class="recipient">
            <strong>C. ' . htmlspecialchars($ministro) . '</strong><br>
            <strong style="font-size: 12pt;">P R E S E N T E</strong>
        </div>
        
        <div class="content">
            La Coordinación de Atención Ciudadana y Asuntos Religiosos del Municipio de Bacalar, con fundamento en los artículos 24 y 130 de la Constitución Política de los Estados Unidos Mexicanos, y en observancia de la Ley de Asociaciones Religiosas y Culto Público, hace constar que el C. <strong>' . htmlspecialchars($ministro) . '</strong>, perteneciente a la asociación religiosa denominada "<strong>' . htmlspecialchars($nombre_iglesia) . '</strong>", con número de registro <strong>' . htmlspecialchars($numero_registro) . '</strong>, ha acreditado los requisitos para el Empadronamiento Digital de Iglesias del Municipio de Bacalar.
        </div>
        
        <div class="data-section">
            <h4>DATOS DE REGISTRO:</h4>
            <div class="data-row">
                <div class="data-label">Fecha de registro:</div>
                <div class="data-value">' . date('d \d\e F \d\e Y', strtotime($iglesia_demo['fecha_registro'])) . '</div>
            </div>
            <div class="data-row">
                <div class="data-label">Denominación:</div>
                <div class="data-value">' . htmlspecialchars($iglesia_demo['denominacion_nombre']) . '</div>
            </div>
            <div class="data-row">
                <div class="data-label">Dirección:</div>
                <div class="data-value">' . htmlspecialchars($iglesia_demo['direccion']) . ', ' . htmlspecialchars($iglesia_demo['ciudad']) . '</div>
            </div>
            <div class="data-row">
                <div class="data-label">Teléfono:</div>
                <div class="data-value">' . htmlspecialchars($iglesia_demo['telefono']) . '</div>
            </div>
        </div>
        
        <div class="note">
            * Este documento es un comprobante de registro digital. El documento oficial será emitido posteriormente.
        </div>
        
        <div class="signature">
            <strong>ATENTAMENTE</strong><br><br><br>
            <div class="signature-line"></div>
            <strong>Mtro. Manuel Jesús Tun Mendez</strong><br>
            Coordinación de Atención Ciudadana y Asuntos Religiosos del Municipio de Bacalar
        </div>
        
        <div class="footer">
            Documento generado automáticamente el ' . $fecha_generacion . '<br>
            Sistema de Padrón Digital de Iglesias - H. Ayuntamiento de Bacalar
        </div>
    </div>
    
    <div class="action-buttons no-print">
        <button onclick="window.print()" class="action-btn">
            📄 Imprimir/Guardar PDF
        </button>
        <button onclick="window.close()" class="action-btn secondary">
            ✖ Cerrar
        </button>
        <a href="user/iglesias.php" class="action-btn success">
            🏠 Panel Iglesia
        </a>
    </div>
</body>
</html>';

echo $html_content;
?>
