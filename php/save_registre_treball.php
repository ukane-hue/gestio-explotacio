<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    
    $id_treballadors = $in['id_treballadors[]'] ?? [];
    $id_tasca = $in['id_tasca'] ?? null;
    $data = $in['data'] ?? date('Y-m-d');
    
    // Fallback if the frontend sends the old single 'id_treballador' (shouldn't happen with our JS fix but good for safety)
    if (isset($in['id_treballador']) && empty($id_treballadors)) {
        $id_treballadors = [$in['id_treballador']];
    }
    
    if (empty($id_treballadors) || !$id_tasca) {
        json_out(false, ['error' => 'Falten dades (treballador(s) o tasca).']);
    }
    
    $pdo = db();
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO registre_treball (id_treballador, id_tasca, id_parcela, data, hora_inici, hora_fi, observacions)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $insertedIds = [];
    foreach ($id_treballadors as $id_treballador) {
        $stmt->execute([
            $id_treballador,
            $id_tasca,
            $in['id_parcela'] ?? null,
            $data,
            $in['hora_inici'] ?? null,
            $in['hora_fi'] ?? null,
            $in['observacions'] ?? null
        ]);
        $insertedIds[] = $pdo->lastInsertId();
    }
    
    $pdo->commit();
    json_out(true, ['ids' => $insertedIds]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_out(false, ['error' => $e->getMessage()]);
}
