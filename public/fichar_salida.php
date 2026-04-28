<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$usuariId = $_SESSION['usuari_id'];

$sql = "SELECT id, hora_entrada, hora_sortida
        FROM fitxatges
        WHERE usuari_id = :usuari_id
        AND data = CURDATE()";

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

$sql = "UPDATE fitxatges
        SET hora_sortida = CURTIME(),
            total_minuts = TIMESTAMPDIFF(MINUTE, hora_entrada, CURTIME()),
            estat = 'tancat'
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $fitxatge['id']
]);

header('Location: dashboard.php');
exit;
