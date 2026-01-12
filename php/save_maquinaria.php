<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    $nom = trim($in['nom'] ?? '');
    
    if (!$nom) {
        json_out(false, ['error' => 'El nom de la màquina és obligatori.']);
    }
    
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO maquinaria (nom, tipus, matricula, data_compra, estat)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $nom,
        $in['tipus'] ?? null,
        $in['matricula'] ?? null,
        $in['data_compra'] ?? null,
        $in['estat'] ?? 'actiu'
    ]);
    
    json_out(true, ['id' => $pdo->lastInsertId()]);

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
