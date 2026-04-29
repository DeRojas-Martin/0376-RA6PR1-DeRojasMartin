<?php

require_once __DIR__ . '/../app/config/database.php';

$sql = "SELECT f.id, f.usuari_id, f.hora_entrada, u.horari_id
        FROM fitxatges f
        JOIN usuaris u ON f.usuari_id = u.id
        WHERE f.data = CURDATE()
        AND f.hora_entrada IS NOT NULL
        AND f.hora_sortida IS NULL";

$stmt = $pdo->query($sql);
$fitxatgesOberts = $stmt->fetchAll();

$totalTancats = 0;

foreach ($fitxatgesOberts as $fitxatge) {
    $sqlHorari = "SELECT hora_sortida, hores_minimes
                  FROM horaris
                  WHERE id = :horari_id";

    $stmtHorari = $pdo->prepare($sqlHorari);
    $stmtHorari->execute([
        ':horari_id' => $fitxatge['horari_id']
    ]);

    $horari = $stmtHorari->fetch();

    $horaSortidaAutomatica = $horari['hora_sortida'];

    $sql = "UPDATE fitxatges
            SET hora_sortida = :hora_sortida,
                total_minuts = TIMESTAMPDIFF(MINUTE, hora_entrada, :hora_sortida),
                estat = 'auto_tancat'
            WHERE id = :id";

    $stmtUpdate = $pdo->prepare($sql);
    $stmtUpdate->execute([
        ':hora_sortida' => $horaSortidaAutomatica,
        ':id' => $fitxatge['id']
    ]);

    $sql = "INSERT INTO incidencies
            (usuari_id, fitxatge_id, tipus, descripcio, data)
            VALUES
            (:usuari_id, :fitxatge_id, 'No ha fitxat sortida', :descripcio, CURDATE())";

    $stmtIncidencia = $pdo->prepare($sql);
    $stmtIncidencia->execute([
        ':usuari_id' => $fitxatge['usuari_id'],
        ':fitxatge_id' => $fitxatge['id'],
        ':descripcio' => 'La jornada s\'ha tancat automàticament perquè l\'usuari no ha fitxat la sortida.'
    ]);

    $totalTancats++;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Tancament automàtic</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">
    <div class="content" style="max-width: 900px; margin: 40px auto;">
        <div class="page-header-card">
            <h1 class="page-title">Tancament automàtic de jornades</h1>
            <p>S'han tancat automàticament <?= $totalTancats ?> jornades obertes.</p>
            <br>
            <a href="admin.php" class="btn-secondary">Tornar al panell admin</a>
        </div>
    </div>
</body>
</html>
