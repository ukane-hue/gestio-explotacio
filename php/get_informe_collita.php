<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    // 1. Total collit per varietat
    $stmt_var = $pdo->query("
        SELECT v.nom_varietat, SUM(c.quantitat_recoltada) as total_kg
        FROM collites c
        JOIN varietats v ON c.id_varietat = v.id_varietat
        GROUP BY v.nom_varietat
    ");
    $per_varietat = $stmt_var->fetchAll();
    
    // 2. Total collit per parcel·la (a través de plantació)
    $stmt_par = $pdo->query("
        SELECT p.nom_parcela, SUM(c.quantitat_recoltada) as total_kg
        FROM collites c
        JOIN plantacions pl ON c.id_plantacio = pl.id_plantacio
        JOIN parceles p ON pl.id_parcela = p.id_parcela
        GROUP BY p.nom_parcela
    ");
    $per_parcela = $stmt_par->fetchAll();
    
    // 3. Evolució temporal (per mes)
    $stmt_temp = $pdo->query("
        SELECT DATE_FORMAT(data_inici, '%Y-%m') as mes, SUM(quantitat_recoltada) as total_kg
        FROM collites
        GROUP BY mes
        ORDER BY mes
    ");
    $evolucio = $stmt_temp->fetchAll();
    
    // 4. Últimes collites (per al llistat)
    $stmt_recents = $pdo->query("
        SELECT c.id_collita, c.data_inici, p.nom_parcela, v.nom_varietat, c.quantitat_recoltada, c.unitat, c.lot_id
        FROM collites c
        JOIN plantacions pl ON c.id_plantacio = pl.id_plantacio
        JOIN parceles p ON pl.id_parcela = p.id_parcela
        JOIN varietats v ON c.id_varietat = v.id_varietat
        ORDER BY c.data_inici DESC, c.id_collita DESC
        LIMIT 10
    ");
    $recents = $stmt_recents->fetchAll();
    
    json_out(true, [
        'per_varietat' => $per_varietat,
        'per_parcela' => $per_parcela,
        'evolucio' => $evolucio,
        'recents' => $recents
    ]);
    
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
