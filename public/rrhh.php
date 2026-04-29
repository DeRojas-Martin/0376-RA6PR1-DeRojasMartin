<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();
require_role(['admin', 'rrhh']);

$sqlFitxatges = "SELECT u.nom, u.email, f.data, f.hora_entrada, f.hora_sortida, f.total_minuts, f.estat
                 FROM fitxatges f
                 JOIN usuaris u ON f.usuari_id = u.id
                 ORDER BY f.data DESC";

$fitxatges = $pdo->query($sqlFitxatges)->fetchAll();

$sqlIncidencies = "SELECT u.nom, u.email, i.tipus, i.descripcio, i.data, i.estat
                   FROM incidencies i
                   JOIN usuaris u ON i.usuari_id = u.id
                   ORDER BY i.data DESC, i.id DESC";

$incidencies = $pdo->query($sqlIncidencies)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell RRHH</title>
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
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin.php">Panell admin</a>
        <?php endif; ?>
        <a href="rrhh.php" class="active">RRHH</a>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Panell de Recursos Humans</h1>
            <p>Consulta fitxatges i incidències dels empleats.</p>
        </div>

        <div class="table-card" style="margin-bottom: 25px;">
            <h2>Fitxatges</h2>
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
                            <td><?= htmlspecialchars($fitxatge['estat']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h2>Incidències</h2>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Usuari</th>
                        <th>Email</th>
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
                            <td><?= htmlspecialchars($incidencia['email']) ?></td>
                            <td><?= htmlspecialchars($incidencia['tipus']) ?></td>
                            <td><?= htmlspecialchars($incidencia['descripcio']) ?></td>
                            <td><?= htmlspecialchars($incidencia['data']) ?></td>
                            <td><?= htmlspecialchars($incidencia['estat']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
