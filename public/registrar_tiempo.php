<?php

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$missatge = '';

$sql = "SELECT id, nom
        FROM projectes
        WHERE estat = 'actiu'
        ORDER BY nom";

$stmt = $pdo->query($sql);
$projectes = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projecteId = filter_var($_POST['projecte_id'], FILTER_VALIDATE_INT);
    $data = $_POST['data'] ?? '';
    $horaInici = $_POST['hora_inici'] ?? '';
    $horaFi = $_POST['hora_fi'] ?? '';
    $descripcio = htmlspecialchars($_POST['descripcio'] ?? '', ENT_QUOTES, 'UTF-8');

    $inici = strtotime("$data $horaInici");
    $fi = strtotime("$data $horaFi");

    if ($projecteId && $inici && $fi && $fi > $inici) {
        $totalMinuts = (int)(($fi - $inici) / 60);

        $sql = "INSERT INTO tasques
                (usuari_id, projecte_id, data, hora_inici, hora_fi, total_minuts, descripcio)
                VALUES
                (:usuari_id, :projecte_id, :data, :hora_inici, :hora_fi, :total_minuts, :descripcio)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuari_id' => $_SESSION['usuari_id'],
            ':projecte_id' => $projecteId,
            ':data' => $data,
            ':hora_inici' => $horaInici,
            ':hora_fi' => $horaFi,
            ':total_minuts' => $totalMinuts,
            ':descripcio' => $descripcio
        ]);

        $missatge = 'Temps registrat correctament.';
    } else {
        $missatge = 'Les dades introduïdes no són correctes.';
    }
}

?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Registrar temps</title>
</head>
<body>
    <h1>Registrar temps en projecte</h1>

    <?php if ($missatge): ?>
        <p><?= htmlspecialchars($missatge) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Projecte:</label><br>
        <select name="projecte_id" required>
            <?php foreach ($projectes as $projecte): ?>
                <option value="<?= htmlspecialchars($projecte['id']) ?>">
                    <?= htmlspecialchars($projecte['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Data:</label><br>
        <input type="date" name="data" required><br><br>

        <label>Hora inici:</label><br>
        <input type="time" name="hora_inici" required><br><br>

        <label>Hora fi:</label><br>
        <input type="time" name="hora_fi" required><br><br>

        <label>Descripció:</label><br>
        <textarea name="descripcio" required></textarea><br><br>

        <button type="submit">Guardar temps</button>
    </form>

    <br>
    <a href="dashboard.php">Tornar</a>
</body>
</html>
