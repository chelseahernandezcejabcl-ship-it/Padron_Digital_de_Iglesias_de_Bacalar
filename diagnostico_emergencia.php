<?php
/**
 * DIAGNÓSTICO DE EMERGENCIA - MIGRACIÓN
 * Subir este archivo a public_html/ como diagnostico.php
 * Acceder via: https://padrondigitaldeiglesiasdebacalar-com-205229.hostingersite.com/diagnostico.php
 */

// Configuración para mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema - Padrón Digital</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0; }
        .info { color: #004085; background: #cce7ff; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Diagnóstico del Sistema - Padrón Digital de Iglesias</h1>
    <p><strong>URL:</strong> <?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></p>
    <p><strong>Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

    <div class="section">
        <h2>📊 Información del Servidor</h2>
        <table>
            <tr><th>Parámetro</th><th>Valor</th></tr>
            <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
            <tr><td>Servidor Web</td><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible'; ?></td></tr>
            <tr><td>Sistema Operativo</td><td><?php echo php_uname('s'); ?></td></tr>
            <tr><td>Document Root</td><td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'No disponible'; ?></td></tr>
            <tr><td>Script Path</td><td><?php echo __FILE__; ?></td></tr>
            <tr><td>HTTPS</td><td><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '✅ Activo' : '❌ Inactivo'; ?></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>🔧 Extensiones PHP Críticas</h2>
        <?php
        $required_extensions = ['mysqli', 'gd', 'session', 'json', 'mbstring', 'curl'];
        echo "<table><tr><th>Extensión</th><th>Estado</th></tr>";
        foreach ($required_extensions as $ext) {
            $loaded = extension_loaded($ext);
            $status = $loaded ? '<span style="color: green;">✅ Disponible</span>' : '<span style="color: red;">❌ Faltante</span>';
            echo "<tr><td>{$ext}</td><td>{$status}</td></tr>";
        }
        echo "</table>";
        ?>
    </div>

    <div class="section">
        <h2>📁 Estructura de Archivos</h2>
        <?php
        $critical_files = [
            'index.php' => 'Página principal',
            'login.php' => 'Página de login',
            'registro.php' => 'Registro de iglesias',
            'iglesias.php' => 'Redirector de iglesias',
            'includes/config.php' => 'Configuración principal',
            'includes/funciones.php' => 'Funciones del sistema',
            'includes/middleware_publico.php' => 'Middleware de acceso',
            'admin/index.php' => 'Panel administrativo',
            'user/iglesias.php' => 'Panel de iglesias',
            '.htaccess' => 'Configuración del servidor'
        ];
        
        echo "<table><tr><th>Archivo</th><th>Estado</th><th>Permisos</th><th>Descripción</th></tr>";
        foreach ($critical_files as $file => $description) {
            if (file_exists($file)) {
                $perms = substr(sprintf('%o', fileperms($file)), -4);
                $readable = is_readable($file) ? '✅' : '❌';
                echo "<tr><td>{$file}</td><td style='color: green;'>✅ Existe {$readable}</td><td>{$perms}</td><td>{$description}</td></tr>";
            } else {
                echo "<tr><td>{$file}</td><td style='color: red;'>❌ Faltante</td><td>-</td><td>{$description}</td></tr>";
            }
        }
        echo "</table>";
        ?>
    </div>

    <div class="section">
        <h2>📂 Directorios de Upload</h2>
        <?php
        $upload_dirs = ['uploads/', 'uploads/documentos/', 'uploads/fotos/', 'uploads/comprobantes/'];
        echo "<table><tr><th>Directorio</th><th>Existe</th><th>Escribible</th><th>Permisos</th></tr>";
        foreach ($upload_dirs as $dir) {
            if (is_dir($dir)) {
                $writable = is_writable($dir) ? '✅ Sí' : '❌ No';
                $perms = substr(sprintf('%o', fileperms($dir)), -4);
                echo "<tr><td>{$dir}</td><td style='color: green;'>✅ Sí</td><td>{$writable}</td><td>{$perms}</td></tr>";
            } else {
                echo "<tr><td>{$dir}</td><td style='color: red;'>❌ No</td><td>-</td><td>-</td></tr>";
            }
        }
        echo "</table>";
        ?>
    </div>

    <div class="section">
        <h2>🗄️ Conexión a Base de Datos</h2>
        <?php
        if (file_exists('includes/config.php')) {
            echo "<div class='info'>✅ Archivo config.php encontrado</div>";
            
            try {
                // Intentar incluir config.php
                ob_start();
                include_once 'includes/config.php';
                $config_errors = ob_get_clean();
                
                if ($config_errors) {
                    echo "<div class='error'>⚠️ Errores al cargar config.php:<br><pre>{$config_errors}</pre></div>";
                }
                
                // Verificar si las variables están definidas
                if (isset($DB_HOST, $DB_USER, $DB_NAME)) {
                    echo "<div class='info'>📋 Configuración de BD detectada:<br>";
                    echo "<strong>Host:</strong> {$DB_HOST}<br>";
                    echo "<strong>Usuario:</strong> {$DB_USER}<br>";
                    echo "<strong>Base de datos:</strong> {$DB_NAME}<br>";
                    echo "<strong>Password:</strong> " . (empty($DB_PASS) ? 'Vacío' : 'Configurado') . "</div>";
                    
                    // Intentar conexión
                    try {
                        $test_conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
                        
                        if ($test_conn->connect_error) {
                            echo "<div class='error'>❌ Error de conexión: " . $test_conn->connect_error . "</div>";
                            echo "<div class='warning'>💡 Posibles causas:<br>";
                            echo "• Credenciales incorrectas<br>";
                            echo "• Base de datos no existe<br>";
                            echo "• Usuario sin permisos<br>";
                            echo "• Servidor de BD inaccesible</div>";
                        } else {
                            echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
                            
                            // Verificar tablas críticas
                            $critical_tables = ['usuarios_admin', 'iglesias', 'denominaciones'];
                            echo "<table><tr><th>Tabla</th><th>Estado</th><th>Registros</th></tr>";
                            foreach ($critical_tables as $table) {
                                $result = $test_conn->query("SHOW TABLES LIKE '{$table}'");
                                if ($result && $result->num_rows > 0) {
                                    $count_result = $test_conn->query("SELECT COUNT(*) as total FROM {$table}");
                                    $count = $count_result ? $count_result->fetch_assoc()['total'] : 0;
                                    echo "<tr><td>{$table}</td><td style='color: green;'>✅ Existe</td><td>{$count}</td></tr>";
                                } else {
                                    echo "<tr><td>{$table}</td><td style='color: red;'>❌ Faltante</td><td>-</td></tr>";
                                }
                            }
                            echo "</table>";
                            
                            $test_conn->close();
                        }
                    } catch (Exception $e) {
                        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
                    }
                } else {
                    echo "<div class='error'>❌ Variables de configuración no encontradas en config.php</div>";
                    echo "<div class='warning'>Verificar que estén definidas: \$DB_HOST, \$DB_USER, \$DB_PASS, \$DB_NAME</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error al cargar config.php: " . $e->getMessage() . "</div>";
            }
            
        } else {
            echo "<div class='error'>❌ Archivo includes/config.php no encontrado</div>";
        }
        ?>
    </div>

    <div class="section">
        <h2>⚙️ Configuración PHP</h2>
        <?php
        $php_settings = [
            'upload_max_filesize' => ['valor' => ini_get('upload_max_filesize'), 'recomendado' => '10M+'],
            'post_max_size' => ['valor' => ini_get('post_max_size'), 'recomendado' => '10M+'],
            'memory_limit' => ['valor' => ini_get('memory_limit'), 'recomendado' => '256M+'],
            'max_execution_time' => ['valor' => ini_get('max_execution_time'), 'recomendado' => '300+'],
            'session.save_path' => ['valor' => ini_get('session.save_path'), 'recomendado' => 'Escribible'],
            'display_errors' => ['valor' => ini_get('display_errors') ? 'On' : 'Off', 'recomendado' => 'Off en producción']
        ];
        
        echo "<table><tr><th>Configuración</th><th>Valor Actual</th><th>Recomendado</th></tr>";
        foreach ($php_settings as $setting => $info) {
            echo "<tr><td>{$setting}</td><td>{$info['valor']}</td><td>{$info['recomendado']}</td></tr>";
        }
        echo "</table>";
        ?>
    </div>

    <div class="section">
        <h2>🌐 Pruebas de URL</h2>
        <?php
        $test_urls = [
            'index.php' => 'Vista pública',
            'login.php' => 'Página de login', 
            'registro.php' => 'Registro de iglesias',
            'admin/index.php' => 'Panel administrativo'
        ];
        
        echo "<table><tr><th>URL</th><th>Estado</th><th>Descripción</th></tr>";
        foreach ($test_urls as $url => $desc) {
            if (file_exists($url)) {
                $full_url = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $url;
                echo "<tr><td><a href='{$full_url}' target='_blank'>{$url}</a></td><td style='color: green;'>✅ Disponible</td><td>{$desc}</td></tr>";
            } else {
                echo "<tr><td>{$url}</td><td style='color: red;'>❌ Archivo no encontrado</td><td>{$desc}</td></tr>";
            }
        }
        echo "</table>";
        ?>
    </div>

    <div class="section">
        <h2>📋 Variables de Entorno</h2>
        <pre><?php 
        $server_vars = ['HTTP_HOST', 'SERVER_NAME', 'DOCUMENT_ROOT', 'REQUEST_URI', 'SCRIPT_NAME', 'PHP_SELF'];
        foreach ($server_vars as $var) {
            echo "{$var}: " . ($_SERVER[$var] ?? 'No definida') . "\n";
        }
        ?></pre>
    </div>

    <div class="section">
        <h2>🔍 Logs de Error Recientes</h2>
        <?php
        $error_logs = [
            'error_log',
            'logs/error.log',
            '../logs/error_log',
            '/tmp/php_errors.log'
        ];
        
        $found_log = false;
        foreach ($error_logs as $log_file) {
            if (file_exists($log_file) && is_readable($log_file)) {
                $found_log = true;
                echo "<h4>📄 {$log_file}</h4>";
                $lines = file($log_file);
                $recent_lines = array_slice($lines, -20); // Últimas 20 líneas
                echo "<pre>" . htmlspecialchars(implode('', $recent_lines)) . "</pre>";
                break;
            }
        }
        
        if (!$found_log) {
            echo "<div class='warning'>⚠️ No se encontraron logs de error accesibles</div>";
        }
        ?>
    </div>

    <div class="section">
        <h2>💡 Recomendaciones</h2>
        <div class="info">
            <h4>Próximos pasos según los resultados:</h4>
            <ol>
                <li><strong>Si faltan archivos:</strong> Verificar que se subieron todos los archivos del proyecto</li>
                <li><strong>Si hay errores de BD:</strong> Verificar credenciales en includes/config.php</li>
                <li><strong>Si faltan extensiones PHP:</strong> Contactar soporte del hosting</li>
                <li><strong>Si hay errores de permisos:</strong> Configurar chmod 755 para directorios</li>
                <li><strong>Si no hay errores visibles:</strong> Verificar configuración de dominio en el hosting</li>
            </ol>
        </div>
        
        <div class="warning">
            <strong>⚠️ Importante:</strong>
            <ul>
                <li>Eliminar este archivo después del diagnóstico</li>
                <li>Verificar que el dominio apunte al directorio correcto</li>
                <li>Contactar soporte de Hostinger si persisten problemas</li>
            </ul>
        </div>
    </div>

</div>
</body>
</html>