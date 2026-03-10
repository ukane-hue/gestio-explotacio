<?php
require_once __DIR__ . '/config.php';

// Iniciar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $in = get_json_input();
    $email = trim($in['email'] ?? '');
    $password = $in['password'] ?? '';

    if (!$email || !$password) {
        json_out(false, ['error' => 'Falten credencials.']);
    }

    $pdo = db();
    $stmt = $pdo->prepare("SELECT id_usuari, nom, contrasenya_hash, rol FROM usuaris WHERE email = ? AND actiu = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['contrasenya_hash'])) {
        // Login correcte
        $_SESSION['user_id'] = $user['id_usuari'];
        $_SESSION['user_name'] = $user['nom'];
        $_SESSION['user_role'] = $user['rol'];
        
        json_out(true, ['message' => 'Login correcte', 'redirect' => 'index.html']);
    } else {
        json_out(false, ['error' => 'Credencials incorrectes o usuari inactiu.']);
    }

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
