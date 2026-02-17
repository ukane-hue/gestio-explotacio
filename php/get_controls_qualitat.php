<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $sql = "
        SELECT cq.id_control, cq.data_control, cq.calibre, cq.percentatge_comercialitzable,
               c.id_collita, 
               COALESCE(p.nom_parcela, p2.nom_parcela, 'Desconeguda') as nom_parcela,
               v.nom_varietat
        FROM controls_qualitat cq
        JOIN collites c ON cq.id_collita = c.id_collita
        JOIN plantacions pl ON c.id_plantacio = pl.id_plantacio
        LEFT JOIN parceles p ON pl.id_parcela = p.id_parcela
        LEFT JOIN sectors s ON pl.id_sector = s.id_sector
        LEFT JOIN parceles p2 ON s.id_parcela = p2.id_parcela
        LEFT JOIN varietats v ON pl.id_varietat = v.id_varietat
        ORDER BY cq.data_control DESC
    ";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_out(true, ['data' => $data]);
} catch (Throwable $e) {
    json_out(false, ['error' => $e->getMessage()]);
}
?>
