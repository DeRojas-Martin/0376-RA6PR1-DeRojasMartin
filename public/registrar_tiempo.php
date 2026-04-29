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
        <a href="registrar_tiempo.php" class="active">Registrar temps</a>
        <a href="historial.php">Historial</a>
        <a href="incidencias.php">Incidències</a>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin.php">Panell admin</a>
        <?php endif; ?>
        <a href="logout.php">Tancar sessió</a>
    </aside>

    <main class="content">
        <div class="page-header-card">
            <h1 class="page-title">Pantalla de registre de temps en projectes</h1>
            <p>Registra el temps dedicat a una tasca o projecte.</p>
        </div>

        <div class="form-card">
            <?php if ($missatge): ?>
                <div class="message-box"><?= htmlspecialchars($missatge) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="projecte_id">Projecte</label>
                        <select name="projecte_id" id="projecte_id" required>
                            <?php foreach ($projectes as $projecte): ?>
                                <option value="<?= htmlspecialchars($projecte['id']) ?>">
                                    <?= htmlspecialchars($projecte['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="data">Data</label>
                        <input type="date" name="data" id="data" required>
                    </div>

                    <div class="form-group">
                        <label for="hora_inici">Hora inici</label>
                        <input type="time" name="hora_inici" id="hora_inici" required>
                    </div>

                    <div class="form-group">
                        <label for="hora_fi">Hora fi</label>
                        <input type="time" name="hora_fi" id="hora_fi" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="descripcio">Descripció de la tasca</label>
                        <textarea name="descripcio" id="descripcio" required></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Guardar temps</button>
                    <a href="dashboard.php" class="btn-secondary">Tornar</a>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>
