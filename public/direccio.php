<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();
require_role(['admin', 'direccio']);

$totalFitxatges = $pdo->query("SELECT COUNT(*) AS total FROM fitxatges")->fetch()['total'];
$totalIncidencies = $pdo->query("SELECT COUNT(*) AS total FROM incidencies")->fetch()['total'];
$totalProjectes = $pdo->query("SELECT COUNT(*) AS total FROM projectes")->fetch()['total'];
$jornadesObertes = $pdo->query("
    SELECT COUNT(*) AS total
    FROM fitxatges
    WHERE data = CURDATE()
    AND hora_entrada IS NOT NULL
    AND hora_sortida IS NULL
")->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell Direcció</title>
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
        <a href="direccio.php" class="active">Direcció</a>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Panell de Direcció</h1>
            <p>Resum executiu de l’activitat general.</p>
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
                <span>Projectes</span>
                <strong><?= $totalProjectes ?></strong>
            </div>
            <div class="stat-box">
                <span>Jornades obertes avui</span>
                <strong><?= $jornadesObertes ?></strong>
            </div>
        </div>

        <div class="table-card">
            <h2>Accés ràpid</h2>
            <div class="quick-access">
                <a href="reports.php" class="quick-card">Veure reports</a>
                <a href="llista_vermella.php" class="quick-card">Veure llista vermella</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
