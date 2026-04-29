<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();
require_role(['admin']);

$sqlFitxatges = "SELECT u.nom, u.email, f.data, f.hora_entrada, f.hora_sortida, f.total_minuts, f.estat
                 FROM fitxatges f
                 JOIN usuaris u ON f.usuari_id = u.id
                 ORDER BY f.data DESC";

$stmtFitxatges = $pdo->query($sqlFitxatges);
$fitxatges = $stmtFitxatges->fetchAll();

$sqlIncidencies = "SELECT u.nom, i.tipus, i.descripcio, i.data, i.estat
                   FROM incidencies i
                   JOIN usuaris u ON i.usuari_id = u.id
                   ORDER BY i.data DESC, i.id DESC";

$stmtIncidencies = $pdo->query($sqlIncidencies);
$incidencies = $stmtIncidencies->fetchAll();

$sqlProjectes = "SELECT p.nom, p.client, p.hores_estimades,
                        COALESCE(SUM(t.total_minuts), 0) AS minuts_reals
                 FROM projectes p
                 LEFT JOIN tasques t ON p.id = t.projecte_id
                 GROUP BY p.id, p.nom, p.client, p.hores_estimades";

$stmtProjectes = $pdo->query($sqlProjectes);
$projectes = $stmtProjectes->fetchAll();

$jornadesObertes = $pdo->query("
    SELECT COUNT(*) AS total
    FROM fitxatges
    WHERE data = CURDATE()
    AND hora_entrada IS NOT NULL
    AND hora_sortida IS NULL
")->fetch()['total'];

$incidenciesPendents = $pdo->query("
    SELECT COUNT(*) AS total
    FROM incidencies
    WHERE estat = 'pendent'
")->fetch()['total'];

$arribadesTardAvui = $pdo->query("
    SELECT COUNT(*) AS total
    FROM incidencies
    WHERE data = CURDATE()
    AND tipus = 'Arribada tard'
")->fetch()['total'];

$sortidesAnticipadesAvui = $pdo->query("
    SELECT COUNT(*) AS total
    FROM incidencies
    WHERE data = CURDATE()
    AND tipus = 'Sortida anticipada'
")->fetch()['total'];

$menysHoresAvui = $pdo->query("
    SELECT COUNT(*) AS total
    FROM incidencies
    WHERE data = CURDATE()
    AND tipus = 'Menys hores treballades'
")->fetch()['total'];

$autoTancadesAvui = $pdo->query("
    SELECT COUNT(*) AS total
    FROM fitxatges
    WHERE data = CURDATE()
    AND estat = 'auto_tancat'
")->fetch()['total'];

$totalFitxatges = count($fitxatges);
$totalIncidencies = count($incidencies);
$totalProjectes = count($projectes);
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
        <a href="admin.php" class="active">Panell admin</a>
        <a href="llista_vermella.php">Llista vermella</a>
        <a href="reports.php">Reports</a>
        <a href="rrhh.php">RRHH</a>
        <a href="comptabilitat.php">Comptabilitat</a>
        <a href="direccio.php">Direcció</a>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Panell de l’administrador</h1>
            <p>Resum general del sistema de control horari.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <span>Fitxatges totals</span>
                <strong><?= $totalFitxatges ?></strong>
            </div>
            <div class="stat-box">
                <span>Incidències totals</span>
                <strong><?= $totalIncidencies ?></strong>
            </div>
            <div class="stat-box">
                <span>Incidències pendents</span>
                <strong><?= $incidenciesPendents ?></strong>
            </div>
            <div class="stat-box">
                <span>Jornades obertes avui</span>
                <strong><?= $jornadesObertes ?></strong>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <span>Arribades tard avui</span>
                <strong><?= $arribadesTardAvui ?></strong>
            </div>
            <div class="stat-box">
                <span>Sortides anticipades avui</span>
                <strong><?= $sortidesAnticipadesAvui ?></strong>
            </div>
            <div class="stat-box">
                <span>Menys hores avui</span>
                <strong><?= $menysHoresAvui ?></strong>
            </div>
            <div class="stat-box">
                <span>Auto tancades avui</span>
                <strong><?= $autoTancadesAvui ?></strong>
            </div>
        </div>

        <div class="actions" style="margin-bottom: 25px;">
            <a href="llista_vermella.php" class="btn-action btn-exit">Veure llista vermella</a>
            <a href="reports.php" class="btn-action btn-entry">Veure reports</a>
            <a href="auto_tancar_jornades.php" class="btn-action btn-secondary">Tancar jornades obertes</a>
        </div>

        <div class="table-card" style="margin-bottom: 25px;">
            <h2>Últimes incidències</h2>
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
                                <?php elseif ($fitxatge['estat'] === 'auto_tancat'): ?>
                                    <span class="badge badge-warning">Auto tancat</span>
                                <?php else: ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($fitxatge['estat']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
