<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    $stmt = $pdo->query("
        SELECT o.*, p.nom_parcela, v.nom_varietat 
        FROM observacions_fitosanitaries o
        JOIN plantacions pl ON o.id_plantacio = pl.id_plantacio
        JOIN parceles p ON pl.id_parcela = p.id_parcela
        JOIN varietats v ON pl.id_varietat = v.id_varietat
        ORDER BY o.data_observacio DESC
        LIMIT 50
    ");
    
    $observacions = $stmt->fetchAll();
    json_out(true, ['observacions' => $observacions]);

} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
