<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$usuariId = $_SESSION['usuari_id'];

$sql = "SELECT f.id, f.hora_entrada, f.hora_sortida, u.horari_id
        FROM fitxatges f
        JOIN usuaris u ON f.usuari_id = u.id
        WHERE f.usuari_id = :usuari_id
        AND f.data = CURDATE()";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $usuariId
]);

$fitxatge = $stmt->fetch();

if (!$fitxatge) {
    header('Location: dashboard.php?error=sense_entrada');
    exit;
}

if ($fitxatge['hora_sortida']) {
    header('Location: dashboard.php?error=ja_sortida');
    exit;
}

$sqlHorari = "SELECT hora_sortida, hores_minimes
              FROM horaris
              WHERE id = :horari_id";

$stmtHorari = $pdo->prepare($sqlHorari);
$stmtHorari->execute([
    ':horari_id' => $fitxatge['horari_id']
]);

$horari = $stmtHorari->fetch();

$sql = "UPDATE fitxatges
        SET hora_sortida = CURTIME(),
            total_minuts = TIMESTAMPDIFF(MINUTE, hora_entrada, CURTIME()),
            estat = 'tancat'
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $fitxatge['id']
]);

$sqlActualitzat = "SELECT id, hora_entrada, hora_sortida, total_minuts
                   FROM fitxatges
                   WHERE id = :id";

$stmtActualitzat = $pdo->prepare($sqlActualitzat);
$stmtActualitzat->execute([
    ':id' => $fitxatge['id']
]);

$fitxatgeActualitzat = $stmtActualitzat->fetch();

$horaSortidaPrevista = $horari['hora_sortida'];
$horesMinimes = (int)$horari['hores_minimes'];
$totalMinuts = (int)$fitxatgeActualitzat['total_minuts'];
$minimsMinuts = $horesMinimes * 60;

/*
|--------------------------------------------------------------------------
| Detectar sortida anticipada
|--------------------------------------------------------------------------
*/
$sqlSortidaAnticipada = "SELECT TIMESTAMPDIFF(
                            MINUTE,
                            CONCAT(CURDATE(), ' ', CURTIME()),
                            CONCAT(CURDATE(), ' ', :hora_sortida_prevista)
                         ) AS minuts_anticipats";

$stmtSortidaAnticipada = $pdo->prepare($sqlSortidaAnticipada);
$stmtSortidaAnticipada->execute([
    ':hora_sortida_prevista' => $horaSortidaPrevista
]);

$minutsAnticipats = (int)$stmtSortidaAnticipada->fetch()['minuts_anticipats'];

if ($minutsAnticipats > 0) {
    $sql = "UPDATE fitxatges
            SET sortida_anticipada_minuts = :minuts
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':minuts' => $minutsAnticipats,
        ':id' => $fitxatgeActualitzat['id']
    ]);

    $sql = "INSERT INTO incidencies
            (usuari_id, fitxatge_id, tipus, descripcio, data)
            VALUES
            (:usuari_id, :fitxatge_id, 'Sortida anticipada', :descripcio, CURDATE())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuari_id' => $usuariId,
        ':fitxatge_id' => $fitxatgeActualitzat['id'],
        ':descripcio' => 'L\'usuari ha sortit ' . $minutsAnticipats . ' minuts abans de l\'hora prevista.'
    ]);
}

/*
|--------------------------------------------------------------------------
| Detectar menys hores treballades
|--------------------------------------------------------------------------
*/
if ($totalMinuts < $minimsMinuts) {
    $minutsFaltants = $minimsMinuts - $totalMinuts;

    $sql = "INSERT INTO incidencies
            (usuari_id, fitxatge_id, tipus, descripcio, data)
            VALUES
            (:usuari_id, :fitxatge_id, 'Menys hores treballades', :descripcio, CURDATE())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuari_id' => $usuariId,
        ':fitxatge_id' => $fitxatgeActualitzat['id'],
        ':descripcio' => 'L\'usuari no ha completat les hores mínimes. Li falten ' . $minutsFaltants . ' minuts.'
    ]);
}

header('Location: dashboard.php?ok=sortida');
exit;
