<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	echo 'Usuario no encontrado.';
	require_once 'includes/footer.php';
	exit;
}
$id = intval($_GET['id']);
$sql = "SELECT u.*, GROUP_CONCAT(f.ruta) AS fotos FROM users u LEFT JOIN fotos_usuario f ON u.id = f.user_id WHERE u.id = ? AND u.estado = 'aprobado' GROUP BY u.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
	// Mostrar perfil
} else {
	echo 'Usuario no encontrado o no aprobado.';
	require_once 'includes/footer.php';
	exit;
}
?>
	<h2>Perfil de <?= htmlspecialchars($row['nombre_completo']) ?></h2>
	<p><b>Institución:</b> <?= htmlspecialchars($row['institucion']) ?></p>
	<p><b>Sexo:</b> <?= htmlspecialchars($row['sexo']) ?></p>
	<p><b>Año:</b> <?= htmlspecialchars($row['anio']) ?></p>
	<p><b>Edad:</b> <?= htmlspecialchars($row['edad']) ?></p>
	<p><b>Tipo de documento:</b> <?= htmlspecialchars($row['tipo_documento']) ?></p>
	<p><b>Dirección:</b> <?= htmlspecialchars($row['direccion']) ?></p>
	<p><b>Descripción:</b> <?= nl2br(htmlspecialchars($row['descripcion'])) ?></p>
	<p><b>Teléfono:</b> <?= htmlspecialchars($row['telefono']) ?></p>
	<p><b>Ubicación:</b> Lat <?= htmlspecialchars($row['latitud']) ?>, Lng <?= htmlspecialchars($row['longitud']) ?></p>
	<?php if ($row['fotos']): ?>
		<div>
		<?php foreach (explode(',', $row['fotos']) as $foto): ?>
			<img src="<?= '../' . htmlspecialchars($foto) ?>" alt="foto" width="100" style="margin:4px;">
		<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<p><a href="index.php">Volver al mapa</a></p>
<?php require_once 'includes/footer.php'; ?>
