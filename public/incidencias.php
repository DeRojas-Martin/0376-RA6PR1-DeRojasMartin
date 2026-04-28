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
</head>
<body>
    <h1>Les meves incidències</h1>

    <table border="1" cellpadding="8">
        <tr>
            <th>Data</th>
            <th>Tipus</th>
            <th>Descripció</th>
            <th>Estat</th>
        </tr>

        <?php foreach ($incidencies as $incidencia): ?>
            <tr>
                <td><?= htmlspecialchars($incidencia['data']) ?></td>
                <td><?= htmlspecialchars($incidencia['tipus']) ?></td>
                <td><?= htmlspecialchars($incidencia['descripcio']) ?></td>
                <td><?= htmlspecialchars($incidencia['estat']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="dashboard.php">Tornar</a>
</body>
</html>
