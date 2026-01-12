<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    $nom = trim($in['nom'] ?? '');
    
    if (!$nom) {
        json_out(false, ['error' => 'El nom de la tasca és obligatori.']);
    }
    
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO tasques (nom_tasca, descripcio) VALUES (?, ?)");
    $stmt->execute([$nom, $in['descripcio'] ?? null]);
    
    json_out(true, ['id' => $pdo->lastInsertId()]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
