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

?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell administrador</title>
</head>
<body>
    <h1>Panell administrador</h1>

    <a href="dashboard.php">Tornar</a> |
    <a href="logout.php">Tancar sessió</a>

    <h2>Fitxatges globals</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Usuari</th>
            <th>Email</th>
            <th>Data</th>
            <th>Entrada</th>
            <th>Sortida</th>
            <th>Total minuts</th>
            <th>Estat</th>
        </tr>

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
    </table>

    <h2>Incidències</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Usuari</th>
            <th>Tipus</th>
            <th>Descripció</th>
            <th>Data</th>
            <th>Estat</th>
        </tr>

        <?php foreach ($incidencies as $incidencia): ?>
            <tr>
                <td><?= htmlspecialchars($incidencia['nom']) ?></td>
                <td><?= htmlspecialchars($incidencia['tipus']) ?></td>
                <td><?= htmlspecialchars($incidencia['descripcio']) ?></td>
                <td><?= htmlspecialchars($incidencia['data']) ?></td>
                <td><?= htmlspecialchars($incidencia['estat']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Reports de projectes</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Projecte</th>
            <th>Client</th>
            <th>Hores estimades</th>
            <th>Hores reals</th>
        </tr>

        <?php foreach ($projectes as $projecte): ?>
            <tr>
                <td><?= htmlspecialchars($projecte['nom']) ?></td>
                <td><?= htmlspecialchars($projecte['client']) ?></td>
                <td><?= htmlspecialchars($projecte['hores_estimades']) ?></td>
                <td><?= round($projecte['minuts_reals'] / 60, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
