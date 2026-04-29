<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();
require_role(['admin']);

$sql = "SELECT u.nom, u.email, f.data, f.hora_entrada, f.hora_sortida, f.total_minuts, f.estat
        FROM fitxatges f
        JOIN usuaris u ON f.usuari_id = u.id
        ORDER BY f.data DESC";

$stmt = $pdo->query($sql);
$fitxatges = $stmt->fetchAll();

$sql = "SELECT u.nom, i.tipus, i.descripcio, i.data, i.estat
        FROM incidencies i
        JOIN usuaris u ON i.usuari_id = u.id
        ORDER BY i.data DESC";

$stmt = $pdo->query($sql);
$incidencies = $stmt->fetchAll();

$sql = "SELECT p.nom, p.client, p.hores_estimades,
               COALESCE(SUM(t.total_minuts), 0) AS minuts_reals
        FROM projectes p
        LEFT JOIN tasques t ON p.id = t.projecte_id
        GROUP BY p.id, p.nom, p.client, p.hores_estimades";

$stmt = $pdo->query($sql);
$projectes = $stmt->fetchAll();

$totalFitxatges = count($fitxatges);
$totalIncidencies = count($incidencies);
$totalProjectes = count($projectes);
$totalUsuarisActius = count(array_unique(array_column($fitxatges, 'email')));
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell administrador</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">

<header class="topbar">
    <div class="logo">
        <div class="logo-box">CH</div>
        <span>Control Horari</span>
    </div>

    <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nom'], 0, 1)) ?></div>
        <div>
            <strong><?= htmlspecialchars($_SESSION['nom']) ?></strong><br>
            <small><?= htmlspecialchars($_SESSION['rol']) ?></small>
        </div>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <a href="dashboard.php">Inici</a>
        <a href="registrar_tiempo.php">Registrar temps</a>
        <a href="historial.php">Historial</a>
        <a href="incidencias.php">Incidències</a>
        <a href="admin.php" class="active">Panell admin</a>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Panell de l’administrador</h1>
            <p>Vista global de fitxatges, incidències i projectes.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <span>Fitxatges</span>
                <strong><?= $totalFitxatges ?></strong>
            </div>
            <div class="stat-box">
                <span>Incidències</span>
                <strong><?= $totalIncidencies ?></strong>
            </div>
            <div class="stat-box">
                <span>Projectes</span>
                <strong><?= $totalProjectes ?></strong>
            </div>
            <div class="stat-box">
                <span>Usuaris amb activitat</span>
                <strong><?= $totalUsuarisActius ?></strong>
            </div>
        </div>

        <div class="table-card" style="margin-bottom: 25px;">
            <h2>Fitxatges globals</h2>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Usuari</th>
                        <th>Email</th>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Sortida</th>
                        <th>Total minuts</th>
                        <th>Estat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fitxatges as $fitxatge): ?>
                        <tr>
                            <td><?= htmlspecialchars($fitxatge['nom']) ?></td>
                            <td><?= htmlspecialchars($fitxatge['email']) ?></td>
                            <td><?= htmlspecialchars($fitxatge['data']) ?></td>
                            <td><?= htmlspecialchars($fitxatge['hora_entrada']) ?></td>
                            <td><?= htmlspecialchars($fitxatge['hora_sortida'] ?? '--') ?></td>
                            <td><?= htmlspecialchars($fitxatge['total_minuts']) ?></td>
                            <td>
                                <?php if ($fitxatge['estat'] === 'tancat'): ?>
                                    <span class="badge badge-success">Tancat</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= htmlspecialchars($fitxatge['estat']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card" style="margin-bottom: 25px;">
            <h2>Incidències</h2>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Usuari</th>
                        <th>Tipus</th>
                        <th>Descripció</th>
                        <th>Data</th>
                        <th>Estat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidencies as $incidencia): ?>
                        <tr>
                            <td><?= htmlspecialchars($incidencia['nom']) ?></td>
                            <td><?= htmlspecialchars($incidencia['tipus']) ?></td>
                            <td><?= htmlspecialchars($incidencia['descripcio']) ?></td>
                            <td><?= htmlspecialchars($incidencia['data']) ?></td>
                            <td>
                                <?php if ($incidencia['estat'] === 'pendent'): ?>
                                    <span class="badge badge-danger">Pendent</span>
                                <?php elseif ($incidencia['estat'] === 'revisada'): ?>
                                    <span class="badge badge-info">Revisada</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Resolta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h2>Reports de projectes</h2>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Projecte</th>
                        <th>Client</th>
                        <th>Hores estimades</th>
                        <th>Hores reals</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projectes as $projecte): ?>
                        <tr>
                            <td><?= htmlspecialchars($projecte['nom']) ?></td>
                            <td><?= htmlspecialchars($projecte['client']) ?></td>
                            <td><?= htmlspecialchars($projecte['hores_estimades']) ?></td>
                            <td><?= round($projecte['minuts_reals'] / 60, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
