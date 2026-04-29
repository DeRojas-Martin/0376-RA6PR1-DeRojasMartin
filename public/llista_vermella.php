<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();
require_role(['admin']);

$sql = "SELECT u.nom, u.email, i.tipus, i.descripcio, i.data, i.estat
        FROM incidencies i
        JOIN usuaris u ON i.usuari_id = u.id
        WHERE i.tipus IN ('Arribada tard', 'Sortida anticipada', 'Menys hores treballades', 'No ha fitxat sortida')
        ORDER BY i.data DESC, i.id DESC";

$stmt = $pdo->query($sql);
$incidencies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Llista vermella</title>
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
        <a href="admin.php">Panell admin</a>
        <a href="llista_vermella.php" class="active">Llista vermella</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Llista vermella d’incompliments</h1>
            <p>Consulta els usuaris amb incidències rellevants de compliment horari.</p>
        </div>

        <div class="table-card">
            <h2>Incompliments detectats</h2>

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
