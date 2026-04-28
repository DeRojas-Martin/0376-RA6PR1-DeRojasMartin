<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$usuariId = $_SESSION['usuari_id'];

$sql = "SELECT f.data, f.hora_entrada, f.hora_sortida, f.total_minuts, f.estat
        FROM fitxatges f
        WHERE f.usuari_id = :usuari_id
        AND f.data = CURDATE()";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $usuariId
]);

$fitxatgeAvui = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Inici</title>
</head>
<body>
    <h1>Inici</h1>

    <p>Hola, <?= htmlspecialchars($_SESSION['nom']) ?></p>
    <p>Rol: <?= htmlspecialchars($_SESSION['rol']) ?></p>

    <hr>

    <h2>Estat d'avui</h2>

    <?php if (!$fitxatgeAvui): ?>
        <p>Encara no has fitxat l'entrada.</p>
        <a href="fichar_entrada.php">Fitxar entrada</a>
    <?php else: ?>
        <p>Entrada: <?= htmlspecialchars($fitxatgeAvui['hora_entrada']) ?></p>
        <p>Sortida: <?= htmlspecialchars($fitxatgeAvui['hora_sortida'] ?? '--') ?></p>
        <p>Estat: <?= htmlspecialchars($fitxatgeAvui['estat']) ?></p>
        <p>Total minuts: <?= htmlspecialchars($fitxatgeAvui['total_minuts']) ?></p>

        <?php if (!$fitxatgeAvui['hora_sortida']): ?>
            <a href="fichar_salida.php">Fitxar sortida</a>
        <?php else: ?>
            <p>Jornada finalitzada.</p>
        <?php endif; ?>
    <?php endif; ?>

    <hr>

    <nav>
        <a href="dashboard.php">Inici</a> |
        <a href="registrar_tiempo.php">Registrar temps</a> |
        <a href="historial.php">Historial</a> |
        <a href="incidencias.php">Incidències</a> |

        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin.php">Panell admin</a> |
        <?php endif; ?>

        <a href="logout.php">Tancar sessió</a>
    </nav>
</body>
</html>
