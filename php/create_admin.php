<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    $email = 'admin@example.com';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Comprovar si ja existeix
    $stmt = $pdo->prepare("SELECT id_usuari FROM usuaris WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "L'usuari admin ja existeix.\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO usuaris (nom, cognoms, email, contrasenya_hash, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Administrador', 'Sistema', $email, $hash, 'admin']);
        echo "Usuari admin creat correctament (admin@example.com / admin123).\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
