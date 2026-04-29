<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$sql = "SELECT tipus, descripcio, data, estat
        FROM incidencies
        WHERE usuari_id = :usuari_id
        ORDER BY data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $_SESSION['usuari_id']
]);

$incidencies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Incidències</title>
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
        <a href="incidencias.php" class="active">Incidències</a>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin.php">Panell admin</a>
        <?php endif; ?>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Pantalla d’incidències de l’empleat</h1>
            <p>Consulta les incidències detectades pel sistema.</p>
        </div>

        <div class="table-card">
            <h2>Les meves incidències</h2>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipus</th>
                        <th>Descripció</th>
                        <th>Estat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidencies as $incidencia): ?>
                        <tr>
                            <td><?= htmlspecialchars($incidencia['data']) ?></td>
                            <td><?= htmlspecialchars($incidencia['tipus']) ?></td>
                            <td><?= htmlspecialchars($incidencia['descripcio']) ?></td>
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
    </main>
</div>

</body>
</html>
