<?php
require_once __DIR__ . '/config.php';

try {
    $in = get_json_input();
    
    $id_treballador = $in['id_treballador'] ?? null;
    $id_tasca = $in['id_tasca'] ?? null;
    $data = $in['data'] ?? date('Y-m-d');
    
    if (!$id_treballador || !$id_tasca) {
        json_out(false, ['error' => 'Falten dades (treballador o tasca).']);
    }
    
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO registre_treball (id_treballador, id_tasca, id_parcela, data, hora_inici, hora_fi, observacions)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $id_treballador,
        $id_tasca,
        $in['id_parcela'] ?? null,
        $data,
        $in['hora_inici'] ?? null,
        $in['hora_fi'] ?? null,
        $in['observacions'] ?? null
    ]);
    
    json_out(true, ['id' => $pdo->lastInsertId()]);

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
