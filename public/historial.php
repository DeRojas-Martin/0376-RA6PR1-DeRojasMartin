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
</head>
<body>
    <h1>El meu historial</h1>

    <table border="1" cellpadding="8">
        <tr>
            <th>Data</th>
            <th>Entrada</th>
            <th>Sortida</th>
            <th>Total minuts</th>
            <th>Estat</th>
            <th>Retard</th>
        </tr>

        <?php foreach ($fitxatges as $fitxatge): ?>
            <tr>
                <td><?= htmlspecialchars($fitxatge['data']) ?></td>
                <td><?= htmlspecialchars($fitxatge['hora_entrada']) ?></td>
                <td><?= htmlspecialchars($fitxatge['hora_sortida'] ?? '--') ?></td>
                <td><?= htmlspecialchars($fitxatge['total_minuts']) ?></td>
                <td><?= htmlspecialchars($fitxatge['estat']) ?></td>
                <td><?= htmlspecialchars($fitxatge['retard_minuts']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="dashboard.php">Tornar</a>
</body>
</html>
