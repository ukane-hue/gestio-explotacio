<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    // Obtenim plantacions amb informació de parcel·la i varietat
    $sql = "
        SELECT 
            pl.id_plantacio,
            p.nom_parcela,
            v.nom_varietat,
            pl.data_plantacio
        FROM plantacions pl
        JOIN parceles p ON pl.id_parcela = p.id_parcela
        JOIN varietats v ON pl.id_varietat = v.id_varietat
        ORDER BY p.nom_parcela, v.nom_varietat
    ";
    
    $stmt = $pdo->query($sql);
    $plantacions = $stmt->fetchAll();
    
    // Formategem per al frontend
    $data = array_map(function($row) {
        return [
            'id' => $row['id_plantacio'],
            'nom' => "{$row['nom_parcela']} - {$row['nom_varietat']} ({$row['data_plantacio']})"
        ];
    }, $plantacions);
    
    json_out(true, ['data' => $data]);
    
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
