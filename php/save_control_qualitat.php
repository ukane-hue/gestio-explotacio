<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    
    $id_collita = isset($in['id_collita']) ? intval($in['id_collita']) : null;
    $data_control = trim($in['data_control'] ?? date('Y-m-d'));
    
    // Paràmetres de qualitat
    $calibre = isset($in['calibre']) ? floatval($in['calibre']) : null;
    $color = trim($in['color'] ?? '');
    $fermesa = isset($in['fermesa']) ? floatval($in['fermesa']) : null;
    $defectes = trim($in['defectes'] ?? '');
    $percentatge = isset($in['percentatge']) ? floatval($in['percentatge']) : null;
    
    if (!$id_collita) {
        json_out(false, ['error' => 'Falta ID de collita.']);
    }
    
    $pdo = db();
    $stmt = $pdo->prepare(
        "INSERT INTO controls_qualitat (
            id_collita, data_control, calibre, color, fermesa, defectes_visibles, percentatge_comercialitzable
        ) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    
    $stmt->execute([
        $id_collita, $data_control, $calibre, $color, $fermesa, $defectes, $percentatge
    ]);
    
    json_out(true, ['id' => $pdo->lastInsertId()]);
    
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
