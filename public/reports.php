<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();
require_role(['admin', 'comptabilitat', 'direccio']);

$sql = "SELECT p.id, p.nom, p.client, p.hores_estimades,
               COALESCE(SUM(t.total_minuts), 0) AS minuts_reals
        FROM projectes p
        LEFT JOIN tasques t ON p.id = t.projecte_id
        GROUP BY p.id, p.nom, p.client, p.hores_estimades
        ORDER BY p.nom";

$stmt = $pdo->query($sql);
$projectes = $stmt->fetchAll();

$labels = [];
$horesEstimadesData = [];
$horesRealsData = [];

foreach ($projectes as $projecte) {
    $labels[] = $projecte['nom'];
    $horesEstimadesData[] = (float)$projecte['hores_estimades'];
    $horesRealsData[] = round($projecte['minuts_reals'] / 60, 2);
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Reports de projectes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="llista_vermella.php">Llista vermella</a>
        <?php endif; ?>
        <a href="reports.php" class="active">Reports</a>
        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'rrhh'): ?>
            <a href="rrhh.php">RRHH</a>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'comptabilitat'): ?>
            <a href="comptabilitat.php">Comptabilitat</a>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'direccio'): ?>
            <a href="direccio.php">Direcció</a>
        <?php endif; ?>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Reports de projectes</h1>
            <p>Consulta la comparació entre hores estimades i hores reals.</p>
        </div>

        <div class="table-card" style="margin-bottom: 25px;">
            <h2>Gràfic comparatiu</h2>
            <canvas id="projectesChart" style="max-width: 100%;"></canvas>
        </div>

        <div class="table-card">
            <h2>Resum de projectes</h2>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Projecte</th>
                        <th>Client</th>
                        <th>Hores estimades</th>
                        <th>Hores reals</th>
                        <th>Diferència</th>
                        <th>Estat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projectes as $projecte): ?>
                        <?php
                            $horesReals = round($projecte['minuts_reals'] / 60, 2);
                            $horesEstimades = (float)$projecte['hores_estimades'];
                            $diferencia = round($horesReals - $horesEstimades, 2);

                            if ($horesReals > $horesEstimades) {
                                $estat = 'Sobrepassat';
                                $badgeClass = 'badge-danger';
                            } elseif ($horesReals >= ($horesEstimades * 0.8)) {
                                $estat = 'En risc';
                                $badgeClass = 'badge-warning';
                            } else {
                                $estat = 'Correcte';
                                $badgeClass = 'badge-success';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($projecte['nom']) ?></td>
                            <td><?= htmlspecialchars($projecte['client']) ?></td>
                            <td><?= htmlspecialchars($horesEstimades) ?></td>
                            <td><?= htmlspecialchars($horesReals) ?></td>
                            <td><?= htmlspecialchars($diferencia) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $estat ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
const ctx = document.getElementById('projectesChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            {
                label: 'Hores estimades',
                data: <?= json_encode($horesEstimadesData) ?>
            },
            {
                label: 'Hores reals',
                data: <?= json_encode($horesRealsData) ?>
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>
