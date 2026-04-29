<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$usuariId = $_SESSION['usuari_id'];

$sqlUsuari = "SELECT u.nom, u.rol, d.nom AS departament
              FROM usuaris u
              LEFT JOIN departaments d ON u.departament_id = d.id
              WHERE u.id = :usuari_id";

$stmtUsuari = $pdo->prepare($sqlUsuari);
$stmtUsuari->execute([
    ':usuari_id' => $usuariId
]);

$usuari = $stmtUsuari->fetch();

$sql = "SELECT f.data, f.hora_entrada, f.hora_sortida, f.total_minuts, f.estat
        FROM fitxatges f
        WHERE f.usuari_id = :usuari_id
        AND f.data = CURDATE()";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $usuariId
]);

$fitxatgeAvui = $stmt->fetch();

$sqlAvisos = "SELECT tipus, descripcio, estat
              FROM incidencies
              WHERE usuari_id = :usuari_id
              AND data = CURDATE()
              ORDER BY id DESC";

$stmtAvisos = $pdo->prepare($sqlAvisos);
$stmtAvisos->execute([
    ':usuari_id' => $usuariId
]);

$avisos = $stmtAvisos->fetchAll();

$entrada = $fitxatgeAvui['hora_entrada'] ?? '--';
$sortida = $fitxatgeAvui['hora_sortida'] ?? '--';
$estat = $fitxatgeAvui['estat'] ?? 'Sense fitxar';
$totalMinuts = $fitxatgeAvui['total_minuts'] ?? 0;

$hores = floor($totalMinuts / 60);
$minuts = $totalMinuts % 60;
$totalFormat = $hores . 'h ' . $minuts . 'min';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Inici - Control Horari</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">

<header class="topbar">
    <div class="logo">
        <div class="logo-box">CH</div>
        <span>Control Horari</span>
    </div>

    <div class="user-info">
        <div class="user-avatar">
            <?= strtoupper(substr($_SESSION['nom'], 0, 1)) ?>
        </div>
        <div>
            <strong><?= htmlspecialchars($_SESSION['nom']) ?></strong><br>
            <small><?= htmlspecialchars($_SESSION['rol']) ?></small>
        </div>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <a href="dashboard.php" class="active">Inici</a>
        <a href="registrar_tiempo.php">Registrar temps</a>
        <a href="historial.php">Historial</a>
        <a href="incidencias.php">Incidències</a>

        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin.php">Panell admin</a>
            <a href="llista_vermella.php">Llista vermella</a>
            <a href="reports.php">Reports</a>
        <?php endif; ?>

        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <h1 class="page-title">Pantalla d’inici de l’empleat</h1>

        <section class="welcome-card">
            <h2>Hola, <?= htmlspecialchars($_SESSION['nom']) ?></h2>
            <p>Departament: <?= htmlspecialchars($usuari['departament'] ?? 'Sense departament') ?></p>
        </section>

        <section class="status-grid">
            <div class="status-card">
                <span>Entrada</span>
                <strong><?= htmlspecialchars($entrada) ?></strong>
            </div>

            <div class="status-card">
                <span>Sortida</span>
                <strong><?= htmlspecialchars($sortida) ?></strong>
            </div>

            <div class="status-card">
                <span>Temps treballat</span>
                <strong><?= htmlspecialchars($totalFormat) ?></strong>
            </div>
        </section>

        <section class="main-panel">
            <h2>Estat d'avui</h2>

            <div class="info-row">
                <span class="info-label">Estat de la jornada</span>
                <span class="info-value"><?= htmlspecialchars($estat) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Hora d'entrada</span>
                <span class="info-value"><?= htmlspecialchars($entrada) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Hora de sortida</span>
                <span class="info-value"><?= htmlspecialchars($sortida) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Total treballat</span>
                <span class="info-value"><?= htmlspecialchars($totalFormat) ?></span>
            </div>

            <div class="actions">
                <?php if (!$fitxatgeAvui): ?>
                    <a href="fichar_entrada.php" class="btn-action btn-entry">Fitxar entrada</a>
                    <span class="btn-action btn-disabled">Fitxar sortida</span>
                <?php elseif (!$fitxatgeAvui['hora_sortida']): ?>
                    <span class="btn-action btn-disabled">Entrada ja fitxada</span>
                    <a href="fichar_salida.php" class="btn-action btn-exit">Fitxar sortida</a>
                <?php else: ?>
                    <span class="btn-action btn-disabled">Entrada ja fitxada</span>
                    <span class="btn-action btn-disabled">Sortida ja fitxada</span>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="notice-box">
                    <?php if ($_GET['error'] === 'ja_fitxat'): ?>
                        Ja has fitxat l’entrada avui.
                    <?php elseif ($_GET['error'] === 'sense_entrada'): ?>
                        No pots fitxar sortida sense haver fitxat entrada.
                    <?php elseif ($_GET['error'] === 'ja_sortida'): ?>
                        Ja has fitxat la sortida avui.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['ok']) && $_GET['ok'] === 'sortida'): ?>
                <div class="message-box">
                    Sortida registrada correctament.
                </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($avisos)): ?>
            <section class="main-panel">
                <h2>Avisos del sistema</h2>

                <?php foreach ($avisos as $aviso): ?>
                    <div class="notice-box" style="margin-bottom: 12px;">
                        <strong><?= htmlspecialchars($aviso['tipus']) ?>:</strong>
                        <?= htmlspecialchars($aviso['descripcio']) ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <section class="quick-access">
            <a href="historial.php" class="quick-card">Consultar historial</a>
            <a href="registrar_tiempo.php" class="quick-card">Registrar temps en projecte</a>
            <a href="incidencias.php" class="quick-card">Veure incidències</a>

            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="admin.php" class="quick-card">Panell administrador</a>
            <?php endif; ?>
        </section>
    </main>
</div>

</body>
</html>
