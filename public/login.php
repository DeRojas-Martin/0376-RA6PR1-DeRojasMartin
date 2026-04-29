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

        if ($usuari['rol'] === 'admin') {
            header('Location: admin.php');
            exit;
        }

        if ($usuari['rol'] === 'rrhh') {
            header('Location: rrhh.php');
            exit;
        }

        if ($usuari['rol'] === 'comptabilitat') {
            header('Location: comptabilitat.php');
            exit;
        }

        if ($usuari['rol'] === 'direccio') {
            header('Location: direccio.php');
            exit;
        }

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
    <title>Login - Control Horari</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">

    <div class="login-card">
        <h1 class="login-title">Login</h1>
        <p class="login-subtitle">Accedeix al sistema de control horari</p>

        <?php if ($error): ?>
            <div class="error-box">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Correu electrònic</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= htmlspecialchars($emailRecordat) ?>" 
                    placeholder="empleat@test.com"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Contrasenya</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Introdueix la contrasenya"
                    required
                >
            </div>

            <button type="submit" class="btn-primary">Iniciar sessió</button>
        </form>
    </div>

</body>
</html>
