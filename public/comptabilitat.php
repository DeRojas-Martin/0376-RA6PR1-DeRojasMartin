<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();
require_role(['admin', 'comptabilitat']);

$sql = "SELECT p.nom, p.client, p.hores_estimades,
               COALESCE(SUM(t.total_minuts), 0) AS minuts_reals
        FROM projectes p
        LEFT JOIN tasques t ON p.id = t.projecte_id
        GROUP BY p.id, p.nom, p.client, p.hores_estimades
        ORDER BY p.nom";

$projectes = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell Comptabilitat</title>
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
        <a href="reports.php">Reports</a>
        <a href="comptabilitat.php" class="active">Comptabilitat</a>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Panell de Comptabilitat</h1>
            <p>Consulta hores estimades i hores reals per projecte.</p>
        </div>

        <div class="table-card">
            <h2>Cost en hores per projecte</h2>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Projecte</th>
                        <th>Client</th>
                        <th>Hores estimades</th>
                        <th>Hores reals</th>
                        <th>Diferència</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projectes as $projecte): ?>
                        <?php
                            $horesReals = round($projecte['minuts_reals'] / 60, 2);
                            $horesEstimades = (float)$projecte['hores_estimades'];
                            $diferencia = round($horesReals - $horesEstimades, 2);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($projecte['nom']) ?></td>
                            <td><?= htmlspecialchars($projecte['client']) ?></td>
                            <td><?= htmlspecialchars($horesEstimades) ?></td>
                            <td><?= htmlspecialchars($horesReals) ?></td>
                            <td><?= htmlspecialchars($diferencia) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
