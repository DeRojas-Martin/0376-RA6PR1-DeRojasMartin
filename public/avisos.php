<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$sql = "SELECT tipus, email_desti, assumpte, missatge, data_enviament
        FROM avisos_email
        WHERE usuari_id = :usuari_id
        ORDER BY data_enviament DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $_SESSION['usuari_id']
]);

$avisos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Avisos</title>
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
        <a href="avisos.php" class="active">Avisos</a>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin.php">Panell admin</a>
            <a href="llista_vermella.php">Llista vermella</a>
            <a href="reports.php">Reports</a>
        <?php endif; ?>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Avisos</h1>
            <p>Consulta els avisos registrats pel sistema.</p>
        </div>

        <div class="table-card">
            <h2>Els meus avisos</h2>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Tipus</th>
                        <th>Correu</th>
                        <th>Assumpte</th>
                        <th>Missatge</th>
                        <th>Data enviament</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avisos as $aviso): ?>
                        <tr>
                            <td><?= htmlspecialchars($aviso['tipus']) ?></td>
                            <td><?= htmlspecialchars($aviso['email_desti']) ?></td>
                            <td><?= htmlspecialchars($aviso['assumpte']) ?></td>
                            <td><?= htmlspecialchars($aviso['missatge']) ?></td>
                            <td><?= htmlspecialchars($aviso['data_enviament']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
