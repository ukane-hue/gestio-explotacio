<?php
require_once 'config.php';

try {
    $pdo = db();
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; // Guardem qui és
        $_SESSION['nom'] = $user['nom'];
        json_out(true, ['missatge' => 'Login correcte']);
    } else {
        json_out(false, ['missatge' => 'Credencials incorrectes']);
    }
} catch (Exception $e) {
    json_out(false, ['missatge' => 'Error: ' . $e->getMessage()]);
}
?>