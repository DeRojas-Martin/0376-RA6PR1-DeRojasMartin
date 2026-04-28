<?php

require_once __DIR__ . '/../app/config/database.php';

session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    setcookie('recordar_email', $email, time() + 3600 * 24 * 30, '/', '', false, true);

    $sql = "SELECT id, nom, email, password, rol
            FROM usuaris
            WHERE email = :email AND actiu = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email
    ]);

    $usuari = $stmt->fetch();

    if ($usuari && password_verify($password, $usuari['password'])) {
        $_SESSION['usuari_id'] = $usuari['id'];
        $_SESSION['nom'] = $usuari['nom'];
        $_SESSION['rol'] = $usuari['rol'];

        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Correu o contrasenya incorrectes.';
    }
}

$emailRecordat = $_COOKIE['recordar_email'] ?? '';

?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Correu electrònic:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($emailRecordat) ?>" required><br><br>

        <label>Contrasenya:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Iniciar sessió</button>
    </form>
</body>
</html>
