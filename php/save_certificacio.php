<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    
    $id_treballador = $in['id_treballador'] ?? null;
    $tipus = trim($in['tipus'] ?? '');
    
    if (!$id_treballador || !$tipus) {
        json_out(false, ['error' => 'Falten dades obligatòries (treballador, tipus).']);
    }
    
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO certificacions (id_treballador, tipus, data_obtencio, data_caducitat)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $id_treballador,
        $tipus,
        $in['data_obtencio'] ?? null,
        $in['data_caducitat'] ?? null
    ]);
    
    json_out(true, ['id' => $pdo->lastInsertId()]);

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
