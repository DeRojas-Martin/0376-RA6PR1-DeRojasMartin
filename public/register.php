<?php

require_once __DIR__ . '/../app/config/database.php';
session_start();

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8');
    $cognoms = htmlspecialchars($_POST['cognoms'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($nom && $email && $password) {
        $sqlCheck = "SELECT id FROM usuaris WHERE email = :email";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([':email' => $email]);

        if ($stmtCheck->fetch()) {
            $error = 'Aquest correu ja existeix.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuaris
                    (nom, cognoms, email, password, rol, departament_id, horari_id, actiu)
                    VALUES
                    (:nom, :cognoms, :email, :password, 'empleat', 4, 1, 1)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':cognoms' => $cognoms,
                ':email' => $email,
                ':password' => $passwordHash
            ]);

            $ok = 'Usuari registrat correctament. Ara ja pots iniciar sessió.';
        }
    } else {
        $error = 'Omple tots els camps obligatoris.';
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Registre - Control Horari</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">

    <div class="login-card">
        <h1 class="login-title">Registre</h1>
        <p class="login-subtitle">Crear nou usuari</p>

        <?php if ($error): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($ok): ?>
            <div class="message-box"><?= htmlspecialchars($ok) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <div class="form-group">
                <label for="cognoms">Cognoms</label>
                <input type="text" id="cognoms" name="cognoms">
            </div>

            <div class="form-group">
                <label for="email">Correu electrònic</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Contrasenya</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Registrar-se</button>

            <div style="margin-top: 15px; text-align: center;">
                <a href="login.php" class="btn-secondary" style="display:inline-block; width:100%;">Tornar al login</a>
            </div>
        </form>
    </div>

</body>
</html>
