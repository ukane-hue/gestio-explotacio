<?php
require_once 'config.php';

try {
    $pdo = db();

    // 1. Recollim les dades
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validació bàsica
    if (empty($nom) || empty($email) || empty($password)) {
        json_out(false, ['missatge' => 'Tots els camps són obligatoris.']);
    }

    // 2. COMPROVAR SI L'USUARI JA EXISTEIX (Això és el que faltava fer bé)
    $stmtCheck = $pdo->prepare("SELECT id FROM usuaris WHERE email = :email");
    $stmtCheck->execute([':email' => $email]);
    
    if ($stmtCheck->fetch()) {
        // Si trobem un resultat, és que el correu ja està a la base de dades
        json_out(false, ['missatge' => 'Aquest correu ja està registrat. Prova amb un altre o fes Login.']);
    }

    // 3. SI NO EXISTEIX, EL CREEM
    $hash = password_hash($password, PASSWORD_DEFAULT); // Encriptem la contrasenya

    $sql = "INSERT INTO usuaris (nom, email, password) VALUES (:nom, :email, :pass)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([':nom' => $nom, ':email' => $email, ':pass' => $hash])) {
        json_out(true, ['missatge' => 'Usuari creat correctament!']);
    } else {
        json_out(false, ['missatge' => 'Error al guardar l\'usuari.']);
    }

} catch (PDOException $e) {
    // Si hi ha un error tècnic (ex: taula no existeix), mostrarem l'error real per poder arreglar-ho
    json_out(false, ['missatge' => 'Error de Base de Dades: ' . $e->getMessage()]);
} catch (Exception $e) {
    json_out(false, ['missatge' => 'Error general: ' . $e->getMessage()]);
}
?>