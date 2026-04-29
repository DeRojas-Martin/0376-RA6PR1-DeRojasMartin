<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$sql = "SELECT data, hora_entrada, hora_sortida, total_minuts, estat, retard_minuts
        FROM fitxatges
        WHERE usuari_id = :usuari_id
        ORDER BY data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $_SESSION['usuari_id']
]);

$fitxatges = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Historial</title>
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
        <a href="historial.php" class="active">Historial</a>
        <a href="incidencias.php">Incidències</a>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin.php">Panell admin</a>
        <?php endif; ?>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Pantalla d’historial de l’empleat</h1>
            <p>Consulta els teus fitxatges i el resum de la teva activitat.</p>
        </div>

        <div class="table-card">
            <h2>El meu historial</h2>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Sortida</th>
                        <th>Total minuts</th>
                        <th>Estat</th>
                        <th>Retard</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fitxatges as $fitxatge): ?>
                        <tr>
                            <td><?= htmlspecialchars($fitxatge['data']) ?></td>
                            <td><?= htmlspecialchars($fitxatge['hora_entrada']) ?></td>
                            <td><?= htmlspecialchars($fitxatge['hora_sortida'] ?? '--') ?></td>
                            <td><?= htmlspecialchars($fitxatge['total_minuts']) ?></td>
                            <td>
                                <?php if ($fitxatge['estat'] === 'tancat'): ?>
                                    <span class="badge badge-success">Tancat</span>
                                <?php elseif ($fitxatge['estat'] === 'obert'): ?>
                                    <span class="badge badge-warning">Obert</span>
                                <?php else: ?>
                                    <span class="badge badge-gray"><?= htmlspecialchars($fitxatge['estat']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($fitxatge['retard_minuts']) ?> min</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
