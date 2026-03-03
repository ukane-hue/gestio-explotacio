<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    $pdo = db();

    // Get all collites with variety name, kg and date
    $sql = "
        SELECT 
            v.nom_varietat AS nom,
            c.quantitat_recoltada AS kg,
            c.data_inici AS data_collita,
            c.lot_id
        FROM collites c
        JOIN variats v ON c.id_varietat = v.id_varietat
        ORDER BY c.data_inici DESC
    ";

    $sql = "
        SELECT 
            c.id_collita,
            v.nom_varietat AS nom,
            c.quantitat_recoltada AS kg,
            DATE(c.data_inici) AS data_collita,
            c.lot_id
        FROM collites c
        JOIN varietats v ON c.id_varietat = v.id_varietat
        ORDER BY c.data_inici DESC
    ";

    $stmt = $pdo->query($sql);
    $collites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array_map(function($r) {
        return [
            'id_collita'   => intval($r['id_collita']),
            'nom'          => $r['nom'],
            'kg'           => floatval($r['kg']),
            'data_collita' => $r['data_collita'],
            'lot_id'       => $r['lot_id'],
        ];
    }, $collites));

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
