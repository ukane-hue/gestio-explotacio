<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    
    $id_plantacio = $in['id_plantacio'] ?? null;
    $plaga = trim($in['plaga'] ?? '');
    
    if (!$id_plantacio || !$plaga) {
        json_out(false, ['error' => 'Falten dades obligatòries (plantació, plaga).']);
    }
    
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO observacions_fitosanitaries (id_plantacio, data_observacio, plaga_o_malaltia, nivell_incidencia, id_operari, localitzacio_geo)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $id_plantacio,
        $in['data_observacio'] ?? date('Y-m-d'),
        $plaga,
        $in['nivell'] ?? 'baix',
        $_SESSION['user_id'] ?? null, // Si tenim sessió
        isset($in['lat']) ? json_encode(['lat' => $in['lat'], 'lng' => $in['lng']]) : null
    ]);
    
    json_out(true, ['id' => $pdo->lastInsertId()]);

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
