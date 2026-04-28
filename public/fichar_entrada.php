<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$usuariId = $_SESSION['usuari_id'];

$sql = "SELECT id
        FROM fitxatges
        WHERE usuari_id = :usuari_id
        AND data = CURDATE()";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $usuariId
]);

$fitxatge = $stmt->fetch();

if ($fitxatge) {
    header('Location: dashboard.php?error=ja_fitxat');
    exit;
}

$sqlHorari = "SELECT h.hora_entrada, h.marge_retard
              FROM usuaris u
              JOIN horaris h ON u.horari_id = h.id
              WHERE u.id = :usuari_id";

$stmtHorari = $pdo->prepare($sqlHorari);
$stmtHorari->execute([
    ':usuari_id' => $usuariId
]);

$horari = $stmtHorari->fetch();

$horaPrevista = $horari['hora_entrada'];
$margeRetard = (int)$horari['marge_retard'];

$sql = "INSERT INTO fitxatges 
        (usuari_id, data, hora_entrada, estat)
        VALUES 
        (:usuari_id, CURDATE(), CURTIME(), 'obert')";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':usuari_id' => $usuariId
]);

$fitxatgeId = $pdo->lastInsertId();

$sqlRetard = "SELECT TIMESTAMPDIFF(
                    MINUTE,
                    CONCAT(CURDATE(), ' ', :hora_prevista),
                    NOW()
              ) AS retard";

$stmtRetard = $pdo->prepare($sqlRetard);
$stmtRetard->execute([
    ':hora_prevista' => $horaPrevista
]);

$retard = (int)$stmtRetard->fetch()['retard'];

if ($retard > $margeRetard) {
    $sql = "UPDATE fitxatges
            SET retard_minuts = :retard
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':retard' => $retard,
        ':id' => $fitxatgeId
    ]);

    $sql = "INSERT INTO incidencies
            (usuari_id, fitxatge_id, tipus, descripcio, data)
            VALUES
            (:usuari_id, :fitxatge_id, 'Arribada tard', :descripcio, CURDATE())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuari_id' => $usuariId,
        ':fitxatge_id' => $fitxatgeId,
        ':descripcio' => "L'usuari ha arribat $retard minuts tard."
    ]);
}

header('Location: dashboard.php');
exit;
