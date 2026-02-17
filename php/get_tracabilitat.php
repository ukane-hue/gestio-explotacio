<?php
require_once __DIR__ . '/config.php';

try {
    $lot_id = $_GET['lot_id'] ?? null;
    
    if (!$lot_id) {
        json_out(false, ['error' => 'Falta el Lot ID.']);
    }
    
    $pdo = db();
    
    // 1. Dades principals de la collita i lot
    $stmt = $pdo->prepare("
        SELECT 
            c.id_collita, c.data_inici, c.quantitat_recoltada, c.unitat,
            COALESCE(p.nom_parcela, p2.nom_parcela, 'Desconeguda') as nom_parcela,
            v.nom_varietat, pl.data_plantacio,
            l.client_final, l.data_creacio
        FROM lots l
        JOIN collites c ON l.id_collita = c.id_collita
        JOIN plantacions pl ON c.id_plantacio = pl.id_plantacio
        LEFT JOIN parceles p ON pl.id_parcela = p.id_parcela
        LEFT JOIN sectors s ON pl.id_sector = s.id_sector
        LEFT JOIN parceles p2 ON s.id_parcela = p2.id_parcela
        JOIN varietats v ON c.id_varietat = v.id_varietat
        WHERE l.id_lot = ?
    ");
    $stmt->execute([$lot_id]);
    $info = $stmt->fetch();
    
    if (!$info) {
        json_out(false, ['error' => 'Lot no trobat.']);
    }
    
    // 2. Control de Qualitat
    $stmt_qual = $pdo->prepare("
        SELECT * FROM controls_qualitat WHERE id_collita = ?
    ");
    $stmt_qual->execute([$info['id_collita']]);
    $qualitat = $stmt_qual->fetch();
    
    // 3. Tractaments aplicats a la plantació (últims 6 mesos abans de la collita)
    // Ara hem de fer JOIN amb tractaments_productes
    $stmt_tract = $pdo->prepare("
        SELECT t.data_aplicacio, pr.nom_comercial, pr.materia_activa, tp.quantitat_aplicada, tp.unitat
        FROM tractaments t
        JOIN tractaments_productes tp ON t.id_tractament = tp.id_tractament
        JOIN productes pr ON tp.id_producte = pr.id_producte
        JOIN collites c ON c.id_collita = ?
        WHERE t.id_plantacio = c.id_plantacio
        AND t.data_aplicacio <= c.data_inici
        AND t.data_aplicacio >= DATE_SUB(c.data_inici, INTERVAL 6 MONTH)
        ORDER BY t.data_aplicacio DESC
    ");
    $stmt_tract->execute([$info['id_collita']]);
    $tractaments = $stmt_tract->fetchAll();
    
    json_out(true, [
        'info' => $info,
        'qualitat' => $qualitat,
        'tractaments' => $tractaments
    ]);
    
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
