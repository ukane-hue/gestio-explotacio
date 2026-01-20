<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    json_out(false, ['error' => 'No autenticat', 'redirect' => 'login.html']);
    exit;
}

// Si es crida directament, retorna info de l'usuari
if (basename($_SERVER['PHP_SELF']) == 'auth_check.php') {
    json_out(true, [
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'],
        'user_role' => $_SESSION['user_role']
    ]);
}
