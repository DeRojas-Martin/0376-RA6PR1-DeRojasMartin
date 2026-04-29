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

$sqlUsuari = "SELECT u.email, u.nom, h.hora_entrada, h.marge_retard
              FROM usuaris u
              JOIN horaris h ON u.horari_id = h.id
              WHERE u.id = :usuari_id";

$stmtUsuari = $pdo->prepare($sqlUsuari);
$stmtUsuari->execute([
    ':usuari_id' => $usuariId
]);

$usuari = $stmtUsuari->fetch();

$horaPrevista = $usuari['hora_entrada'];
$margeRetard = (int)$usuari['marge_retard'];

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

    $descripcio = "L'usuari ha arribat $retard minuts tard.";

    $sql = "INSERT INTO incidencies
            (usuari_id, fitxatge_id, tipus, descripcio, data)
            VALUES
            (:usuari_id, :fitxatge_id, 'Arribada tard', :descripcio, CURDATE())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuari_id' => $usuariId,
        ':fitxatge_id' => $fitxatgeId,
        ':descripcio' => $descripcio
    ]);

    $assumpte = 'Avís d’arribada tard';
    $missatge = "Hola " . $usuari['nom'] . ", avui has fitxat l'entrada amb un retard de $retard minuts.";

    $sql = "INSERT INTO avisos_email
            (usuari_id, tipus, email_desti, assumpte, missatge)
            VALUES
            (:usuari_id, 'Arribada tard', :email_desti, :assumpte, :missatge)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuari_id' => $usuariId,
        ':email_desti' => $usuari['email'],
        ':assumpte' => $assumpte,
        ':missatge' => $missatge
    ]);
}

header('Location: dashboard.php?ok=entrada');
exit;
